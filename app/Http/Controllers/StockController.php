<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    private $cacheDuration = 3600;

    /**
     * Get products with highest stock levels
     */
    public function highestStock(Request $request)
    {
        try {
            $cacheKey = 'highest_stock_products_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $limit = $request->get('limit', 10);
                $minStock = $request->get('min_stock', 0);
                
                $query = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COALESCE(SUM(variants.quantity), 0) as total_stock')
                    ])
                    ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
                    ->groupBy('products.id')
                    ->having('total_stock', '>=', $minStock)
                    ->orderBy('total_stock', 'desc')
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                        'variants:id,product_id,size,color,quantity'
                    ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews');

                $products = $query->paginate($limit);

                return response()->json([
                    'status' => true,
                    'message' => 'Products with highest stock retrieved successfully',
                    'data' => new ProductCollection($products),
                    'meta' => [
                        'limit' => $limit,
                        'min_stock' => $minStock,
                        'total' => $products->total(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products with highest stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products with lowest stock levels
     */
    public function lowestStock(Request $request)
    {
        try {
            $cacheKey = 'lowest_stock_products_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $limit = $request->get('limit', 10);
                $maxStock = $request->get('max_stock', 100);
                
                $query = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COALESCE(SUM(variants.quantity), 0) as total_stock')
                    ])
                    ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
                    ->groupBy('products.id')
                    ->having('total_stock', '<=', $maxStock)
                    ->having('total_stock', '>', 0)
                    ->orderBy('total_stock', 'asc')
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                        'variants:id,product_id,size,color,quantity'
                    ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews');

                $products = $query->paginate($limit);

                return response()->json([
                    'status' => true,
                    'message' => 'Products with lowest stock retrieved successfully',
                    'data' => new ProductCollection($products),
                    'meta' => [
                        'limit' => $limit,
                        'max_stock' => $maxStock,
                        'total' => $products->total(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products with lowest stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get out of stock products
     */
    public function outOfStock(Request $request)
    {
        try {
            $cacheKey = 'out_of_stock_products_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $limit = $request->get('limit', 10);
                
                $query = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COALESCE(SUM(variants.quantity), 0) as total_stock')
                    ])
                    ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
                    ->groupBy('products.id')
                    ->having('total_stock', '=', 0)
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                        'variants:id,product_id,size,color,quantity'
                    ])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews');

                $products = $query->paginate($limit);

                return response()->json([
                    'status' => true,
                    'message' => 'Out of stock products retrieved successfully',
                    'data' => new ProductCollection($products),
                    'meta' => [
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
                'message' => 'Failed to retrieve out of stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock alerts for low stock products
     */
    public function stockAlerts(Request $request)
    {
        try {
            $cacheKey = 'stock_alerts_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $threshold = $request->get('threshold', 10);
                $limit = $request->get('limit', 10);
                
                $query = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COALESCE(SUM(variants.quantity), 0) as total_stock')
                    ])
                    ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
                    ->groupBy('products.id')
                    ->having('total_stock', '<=', $threshold)
                    ->having('total_stock', '>', 0)
                    ->orderBy('total_stock', 'asc')
                    ->with([
                        'categories:id,name,image',
                        'tags:id,name,icon',
                        'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                        'variants:id,product_id,size,color,quantity'
                    ]);

                $products = $query->paginate($limit);

                return response()->json([
                    'status' => true,
                    'message' => 'Stock alerts retrieved successfully',
                    'data' => new ProductCollection($products),
                    'meta' => [
                        'threshold' => $threshold,
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
                'message' => 'Failed to retrieve stock alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock movement history
     */
    public function stockHistory(Request $request)
    {
        try {
            $cacheKey = 'stock_history_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $limit = $request->get('limit', 20);
                $period = $request->get('period', 'month'); // day, week, month, year
                
                $query = StockHistory::query()
                    ->with(['product:id,name,sku', 'user:id,name'])
                    ->when($period === 'day', function ($query) {
                        return $query->whereDate('created_at', today());
                    })
                    ->when($period === 'week', function ($query) {
                        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    })
                    ->when($period === 'month', function ($query) {
                        return $query->whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year);
                    })
                    ->when($period === 'year', function ($query) {
                        return $query->whereYear('created_at', now()->year);
                    })
                    ->orderBy('created_at', 'desc');

                $history = $query->paginate($limit);

                return response()->json([
                    'status' => true,
                    'message' => 'Stock history retrieved successfully',
                    'data' => $history,
                    'meta' => [
                        'period' => $period,
                        'limit' => $limit,
                        'total' => $history->total(),
                        'current_page' => $history->currentPage(),
                        'last_page' => $history->lastPage()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve stock history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock value summary
     */
    public function stockValue(Request $request)
    {
        try {
            $cacheKey = 'stock_value_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $query = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COALESCE(SUM(variants.quantity), 0) as total_stock'),
                        DB::raw('COALESCE(SUM(variants.quantity * variants.price), 0) as total_value')
                    ])
                    ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
                    ->groupBy('products.id');

                $summary = [
                    'total_products' => $query->count(),
                    'total_stock' => $query->sum('total_stock'),
                    'total_value' => $query->sum('total_value'),
                    'average_stock' => $query->avg('total_stock'),
                    'average_value' => $query->avg('total_value'),
                    'low_stock_products' => $query->having('total_stock', '<=', 10)->count(),
                    'out_of_stock_products' => $query->having('total_stock', '=', 0)->count()
                ];

                return response()->json([
                    'status' => true,
                    'message' => 'Stock value summary retrieved successfully',
                    'data' => $summary
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve stock value summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock movement trends
     */
    public function stockTrends(Request $request)
    {
        try {
            $cacheKey = 'stock_trends_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $period = $request->get('period', 'month'); // week, month, year
                
                $query = StockHistory::query()
                    ->select([
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(CASE WHEN type = "in" THEN quantity ELSE 0 END) as stock_in'),
                        DB::raw('SUM(CASE WHEN type = "out" THEN quantity ELSE 0 END) as stock_out'),
                        DB::raw('COUNT(DISTINCT product_id) as products_affected')
                    ])
                    ->when($period === 'week', function ($query) {
                        return $query->whereBetween('created_at', [now()->subWeek(), now()]);
                    })
                    ->when($period === 'month', function ($query) {
                        return $query->whereBetween('created_at', [now()->subMonth(), now()]);
                    })
                    ->when($period === 'year', function ($query) {
                        return $query->whereBetween('created_at', [now()->subYear(), now()]);
                    })
                    ->groupBy('date')
                    ->orderBy('date');

                $trends = $query->get();

                return response()->json([
                    'status' => true,
                    'message' => 'Stock trends retrieved successfully',
                    'data' => $trends,
                    'meta' => [
                        'period' => $period,
                        'total_days' => $trends->count()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve stock trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 