<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function getReviews($productId)
    {
        try {
            // Find the product
            $product = Product::findOrFail($productId);

            // Get reviews with pagination
            $reviews = $product->reviews()
                ->with('user:id,name,profile_image')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Reviews retrieved successfully',
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reviewProduct($productId, Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'message' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the product
            $product = Product::findOrFail($productId);
            $user = $request->user();

            // Check if user has already reviewed this product
            $existingReview = Review::where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->first();

            $isNewReview = !$existingReview;
            $review = $existingReview ?: new Review([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);

            // Update or set review data
            $review->rating = $request->rating;
            $review->message = $request->message;
            $review->save();

            // Load the user relationship
            $review->load('user:id,name,profile_image');

            return response()->json([
                'status' => true,
                'message' => $isNewReview ? 'Review added successfully' : 'Review updated successfully',
                'data' => $review
            ], $isNewReview ? 201 : 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save review',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
