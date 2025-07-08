<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\ProductCollection;
use Exception;
use Illuminate\Http\Request;

class SavedProductController extends Controller
{
    public function toggleSaveProduct(Product $product, Request $request)
    {
        try {
            $user = $request->user();
            $isSaved = $user->savedProducts()->where('product_id', $product->id)->exists();
            if ($isSaved) {
                $user->savedProducts()->detach($product->id);
                $message = 'Product removed from saved products';
            } else {
                $user->savedProducts()->attach($product->id);
                $message = 'Product saved successfully';
            }
            return response()->json([
                'status' => true,
                'message' => $message,
                'is_saved' => !$isSaved
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to toggle save status for product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSavedProducts(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);
            //
            $savedProducts = $user->savedProducts()
                ->with([
                    'categories:id,name,image',
                    'tags:id,name,icon',
                    'files' => function ($query) {
                        $query->where('type', 'image')->limit(1);
                    },
                ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->paginate($perPage);
            //
            return response()->json([
                'status' => true,
                'message' => 'Saved products retrieved successfully',
                'data' => new ProductCollection($savedProducts)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve saved products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function isProductSaved(Product $product, Request $request)
    {
        try {
            $user = $request->user();
            $isSaved = $user->savedProducts()->where('product_id', $product->id)->exists();
            return response()->json([
                'status' => true,
                'is_saved' => $isSaved
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to check if product is saved',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
