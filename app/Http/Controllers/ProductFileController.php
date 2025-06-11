<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductFileRequest;
use App\Http\Requests\DeleteProductFileRequest;
use App\Services\FileService;
use App\Repositories\ProductFileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductFileController extends Controller
{

    public function getFiles($productId)
    {
        try {
            //
            $product = Product::findOrFail($productId);
            //
            if (!$product->is_active && (!Auth::check() || Auth::user()->role !== 'admin')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            //
            $files = $product->files;
            //
            return response()->json([
                'status' => true,
                'message' => 'Files retrieved successfully',
                'data' => $files
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve files',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function addFile($productId, ProductFileRequest $request, FileService $fileService)
    {
        try {
            DB::beginTransaction();
            //
            $product = Product::findOrFail($productId);
            // 
            $validatedData = $request->validated();
            // 
            $path = $fileService->storeProductFile(
                $validatedData['file'],
                $validatedData['type']
            );
            // 
            $productFile = $product->files()->create([
                'path' => $path,
                'type' => $validatedData['type'],
            ]);
            //
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'File added successfully',
                'data' => $productFile
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Handle file not found
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Handle validation errors
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $e->errors()
                ], 422);
            }

            return response()->json([
                'status' => false,
                'message' => 'Failed to add file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteFile($productId, $fileId, DeleteProductFileRequest $request, ProductFileRepository $repository)
    {
        DB::beginTransaction();
        try {
            //
            $repository->deleteFile($productId, $fileId);
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'File deleted successfully'
            ]);
            //
        } catch (\Exception $e) {
            DB::rollBack();
            // 
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'status' => false,
                    'message' => 'File or product not found'
                ], 404);
            }
            // 
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete this file'
                ], 403);
            }
            //
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
