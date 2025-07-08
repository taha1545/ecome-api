<?php

namespace App\Http\Controllers;

use App\Filters\ProductFilter;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{

    private $cacheDuration = 3600;

    public function index(Request $request)
    {
        try {
            $cacheKey = 'products_' . md5(json_encode($request->all()));
            //
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                //
                $query = Product::query();
                $filter = new ProductFilter($request);
                $query = $filter->apply($query);
                //
                $PerPage = $request['PerPage'] ?? 20;
                $products = $query
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                    ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->paginate($PerPage);
                //
                return response()->json([
                    'status' => true,
                    'message' => 'Products retrieved successfully',
                    'data' => new ProductCollection($products)
                ]);
            });
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Product $product, Request $request)
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Product retrieved successfully',
                'data' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreProductRequest $request, ProductService $productService)
    {
        try {
            //
            $validatedData = $request->validated();
            $product = $productService->createProduct($validatedData);
            //
            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product)
            ], 201);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, Product $product, ProductService $productService)
    {
        try {
            $validatedData = $request->validated();
            $updatedProduct = $productService->updateProduct($product, $validatedData);
            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($updatedProduct)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            app(ProductService::class)->deleteProduct($product);
            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'query' => 'required|string|min:1',
            ]);
            $query = $validatedData['query'];
            //
            $products = Product::search($query)
                ->query(fn($query) => $query->with([
                    'categories:id,name,image',
                    'tags:id,name,icon',
                    'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews'))
                ->paginate(20);
            //
            return response()->json([
                'status' => true,
                'message' => 'Search results',
                'data' => new ProductCollection($products)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bestSelling(Request $request)
    {
        try {
            $cacheKey = 'best_selling_products_' . md5(json_encode($request->all()));
            //
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                //
                $limit = $request->get('limit', 10);
                $period = $request->get('period', 'month');
                //
                $query = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COUNT(order_items.id) as total_orders'),
                        DB::raw('SUM(order_items.quantity) as total_quantity'),
                        DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
                    ])
                    ->join('order_items', 'products.id', '=', 'order_items.product_id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'confirmed')
                    ->when($period === 'day', function ($query) {
                        return $query->whereDate('orders.created_at', today());
                    })
                    ->when($period === 'week', function ($query) {
                        return $query->whereBetween('orders.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    })
                    ->when($period === 'month', function ($query) {
                        return $query->whereMonth('orders.created_at', now()->month)
                            ->whereYear('orders.created_at', now()->year);
                    })
                    ->when($period === 'year', function ($query) {
                        return $query->whereYear('orders.created_at', now()->year);
                    })
                    ->when($period === 'all', function ($query) {
                        return $query;
                    })
                    ->groupBy('products.id')
                    ->orderBy('total_quantity', 'desc')
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                    ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews');
                //
                $products = $query->paginate($limit);
                //
                return response()->json([
                    'status' => true,
                    'message' => 'Best selling products retrieved successfully',
                    'data' => new ProductCollection($products),
                    'meta' => [
                        'period' => $period,
                        'limit' => $limit,
                        'total' => $products->total(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve best selling products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function suggestProducts(Request $request, Product $product)
    {
        try {
            //
            $limit = $request->get('limit', 5);
            // 
            $relatedProducts = Product::query()
                ->where('id', '!=', $product->id)
                ->where(function ($query) use ($product) {
                    // 
                    $query->whereHas('categories', function ($q) use ($product) {
                        $q->whereIn('categories.id', $product->categories->pluck('id'));
                    })
                        //
                        ->orWhereHas('tags', function ($q) use ($product) {
                            $q->whereIn('tags.id', $product->tags->pluck('id'));
                        });
                })
                ->with([
                    'categories:id,name,image',
                    'tags:id,name,icon',
                    'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->inRandomOrder() // 
                ->take($limit)
                ->get();

            // 
            if ($relatedProducts->count() < $limit) {
                $priceRange = $product->price * 0.2; // 
                $additionalProducts = Product::query()
                    ->where('id', '!=', $product->id)
                    ->whereNotIn('id', $relatedProducts->pluck('id'))
                    ->whereBetween('price', [
                        $product->price - $priceRange,
                        $product->price + $priceRange
                    ])
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                    ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->inRandomOrder()
                    ->take($limit - $relatedProducts->count())
                    ->get();

                $relatedProducts = $relatedProducts->concat($additionalProducts);
            }

            return response()->json([
                'status' => true,
                'message' => 'Suggested products retrieved successfully',
                'data' =>  [
                    $relatedProducts->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'description' => $product->description,
                            'categories' => $product->categories->map(fn($cat) => [
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'image' => $cat->image
                            ]),
                            'tags' => $product->tags->map(fn($tag) => [
                                'id' => $tag->id,
                                'name' => $tag->name,
                                'icon' => $tag->icon
                            ]),
                            'files' => $product->files->map(fn($file) => [
                                'id' => $file->id,
                                'url' => $file->url,
                                'type' => $file->type
                            ]),
                            'reviews_avg_rating' => $product->reviews_avg_rating,
                            'reviews_count' => $product->reviews_count
                        ];
                    })
                ],
                'meta' => [
                    'limit' => $limit,
                    'total_suggestions' => $relatedProducts->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve suggested products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
