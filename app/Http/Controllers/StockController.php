<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{

    //total quentity of product 

    public function highestStock(Request $request)
    {
        try {
            //
            $limit = $request->get('limit', 10);
            $minStock = $request->get('min_stock', 0);
            //
            $query = Product::query()
                ->select([
                    'products.*',
                    DB::raw('COALESCE(SUM(product_variants.quantity), 0) as total_stock')
                ])
                ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->groupBy('products.id')
                ->havingRaw('COALESCE(SUM(product_variants.quantity), 0) >= ?', [$minStock])
                ->orderByDesc(DB::raw('COALESCE(SUM(product_variants.quantity), 0)'))
                ->with([
                    'categories:id,name,image',
                    'tags:id,name,icon',
                    'files' => fn($q) => $q->where('type', 'image')->orderBy('id')->limit(1),
                    'variants:id,product_id,size,color,quantity'
                ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');
            //
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products with highest stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function lowestStock(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $maxStock = $request->get('max_stock', 100);

            $query = Product::query()
                ->select([
                    'products.*',
                    DB::raw('COALESCE(SUM(product_variants.quantity), 0) as total_stock')
                ])
                ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->groupBy('products.id')
                ->havingRaw('COALESCE(SUM(product_variants.quantity), 0) <= ?', [$maxStock])
                ->havingRaw('COALESCE(SUM(product_variants.quantity), 0) > 0')
                ->orderByRaw('COALESCE(SUM(product_variants.quantity), 0) asc')
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products with lowest stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function outOfStock(Request $request)
    {
        try {

            $limit = $request->get('limit', 10);
            $query = Product::query()
                ->select([
                    'products.*',
                    DB::raw('COALESCE(SUM(product_variants.quantity), 0) as total_stock')
                ])
                ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->groupBy('products.id')
                ->havingRaw('COALESCE(SUM(product_variants.quantity), 0) = 0')
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve out of stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
