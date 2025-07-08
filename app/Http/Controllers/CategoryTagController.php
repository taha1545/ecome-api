<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Categorie;
use App\Models\Tag;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\ProductCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryTagController extends Controller
{
    public function getCategories()
    {
        try {
            $categories = Categorie::all();

            return response()->json([
                'status' => true,
                'message' => 'Categories retrieved successfully',
                'data' => CategoryResource::collection($categories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTags()
    {
        try {
            $tags = Tag::all();

            return response()->json([
                'status' => true,
                'message' => 'Tags retrieved successfully',
                'data' => TagResource::collection($tags)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductsByCategory(Categorie $category, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $products = $category->products()
                ->where('is_active', true)
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
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully',
                'category' => $category->only(['id', 'name', 'image']),
                'data' => new ProductCollection($products)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductsByTag(Tag $tag, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $products = $tag->products()
                ->where('is_active', true)
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
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully',
                'tag' => $tag->only(['id', 'name', 'icon']),
                'data' => new ProductCollection($products)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createTag(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50|unique:tags,name',
                'icon' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tag = new Tag();
            $tag->name = $request->name;
            $tag->icon = $request->icon;
            $tag->save();

            return response()->json([
                'status' => true,
                'message' => 'Tag created successfully',
                'data' => new TagResource($tag)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:categories,name',
                'image' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $category = new Categorie();
            $category->name = $request->name;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $category->image = $path;
            }

            $category->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Category created successfully',
                'data' => new CategoryResource($category)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteTag(Tag $tag, Request $request)
    {
        try {
            $productsCount = $tag->products()->count();
            if ($productsCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Cannot delete tag. It is used by {$productsCount} products."
                ], 400);
            }
            $tag->delete();
            return response()->json([
                'status' => true,
                'message' => 'Tag deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCategory(Categorie $category, Request $request)
    {
        try {
            $productsCount = $category->products()->count();
            if ($productsCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Cannot delete category. It is used by {$productsCount} products."
                ], 400);
            }
            DB::beginTransaction();
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addTag(Product $product, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:tags,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $tagId = $request->tag_id;
            if ($product->tags()->where('tag_id', $tagId)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tag is already attached to this product'
                ], 400);
            }
            $product->tags()->attach($tagId);
            return response()->json([
                'status' => true,
                'message' => 'Tag added successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addCategory(Product $product, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:categories,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $categoryId = $request->category_id;
            if ($product->categories()->where('categorie_id', $categoryId)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category is already attached to this product'
                ], 400);
            }
            $product->categories()->attach($categoryId);
            return response()->json([
                'status' => true,
                'message' => 'Category added successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
