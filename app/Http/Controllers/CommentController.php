<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function getComments($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            $comments = $product->comments()
                ->with('user:id,name,profile_image')
                ->orderBy('id', 'desc')
                ->paginate(20);

            return response()->json([
                'status' => true,
                'message' => 'Comments retrieved successfully',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addComment($productId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|min:2|max:1000',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $product = Product::findOrFail($productId);
            $user = $request->user();
            
            $comment = $product->comments()->create([
                'message' => $request->message,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Comment added successfully',
                'data' => $comment->load('user:id,name,profile_image')
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteComment($productId, $commentId, Request $request)
    {
        try {
            $product = Product::findOrFail($productId);
            $comment = Comment::findOrFail($commentId);
            $user = $request->user();
            
            if ($comment->product_id !== $product->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment does not belong to this product'
                ], 400);
            }
            
            if ($user->role !== 'admin' && $comment->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete this comment'
                ], 403);
            }
            
            $comment->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Comment deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
