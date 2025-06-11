<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
    public function getVariants($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $variants = $product->variants;

            return response()->json([
                'status' => true,
                'message' => 'Variants retrieved successfully',
                'data' => $variants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve variants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addVariant($productId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'size' => 'nullable|string|max:10',
                'color' => 'nullable|string|max:50',
                'quantity' => 'required|integer|min:0',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::findOrFail($productId);

            // Create variant
            $variant = $product->variants()->create([
                'size' => $request->size,
                'color' => $request->color,
                'quantity' => $request->quantity,
                'price' => $request->price,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Variant added successfully',
                'data' => $variant
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add variant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateVariant($productId, $variantId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'size' => 'sometimes|string|max:10',
                'color' => 'sometimes|string|max:50',
                'quantity' => 'sometimes|integer|min:0',
                'price' => 'sometimes|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::findOrFail($productId);
            $variant = ProductVariant::findOrFail($variantId);

            // Check if variant belongs to the product
            if ($variant->product_id !== $product->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Variant does not belong to this product'
                ], 400);
            }

            // Update variant
            $variant->update($request->only(['size', 'color', 'quantity', 'price']));

            return response()->json([
                'status' => true,
                'message' => 'Variant updated successfully',
                'data' => $variant
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update variant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteVariant($productId, $variantId, Request $request)
    {
        try {
            $product = Product::findOrFail($productId);
            $variant = ProductVariant::findOrFail($variantId);
            
            if ($variant->product_id !== $product->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Variant does not belong to this product'
                ], 400);
            }
            
            if ($request->user()->role !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete product variants'
                ], 403);
            }
            
            $variant->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Variant deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete variant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStock($productId, $variantId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $product = Product::findOrFail($productId);
            $variant = ProductVariant::findOrFail($variantId);
            
            if ($variant->product_id !== $product->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Variant does not belong to this product'
                ], 400);
            }
            
            $variant->quantity = $request->quantity;
            $variant->save();
            
            return response()->json([
                'status' => true,
                'message' => 'Stock updated successfully',
                'data' => $variant
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
