<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductFileRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\DeleteProductFileRequest;
use App\Services\FileService;
use App\Repositories\ProductFileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FileResource;

class ProductFileController extends Controller
{

    public function getFiles(Product $product)
    {
        try {
            $files = $product->files()->get();
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

    public function addFile(Product $product, StoreFileRequest $request, FileService $fileService)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $path = $fileService->storeProductFile(
                $validatedData['file'],
                $validatedData['type'] ?? null
            );
            $productFile = $product->files()->create([
                'path' => $path,
                'type' => $validatedData['type'] ?? null,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'File added successfully',
                'data' => $productFile
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }
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

    public function deleteFile(Product $product, $fileId, Request $request, ProductFileRepository $repository)
    {
        DB::beginTransaction();
        try {
            $repository->deleteFile($product->id, $fileId);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'status' => false,
                    'message' => 'File or product not found'
                ], 404);
            }
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete this file'
                ], 403);
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
