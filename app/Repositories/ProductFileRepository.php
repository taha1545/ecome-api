<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductFile;
use App\Services\FileService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductFileRepository
{
 
    protected $fileService;
    

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    

    public function deleteFile(int $productId, int $fileId): bool
    {
        // 
        $product = Product::findOrFail($productId);
        
        // 
        $file = $product->files()->where('id', $fileId)->firstOrFail();
        
        //
        $this->fileService->deleteFile($file->path);
        
        // 
        return $file->delete();
    }
    
   
    public function getProductFiles(int $productId)
    {
        $product = Product::findOrFail($productId);
        return $product->files;
    }
    
 
    public function getProductFilesByType(int $productId, string $type)
    {
        $product = Product::findOrFail($productId);
        return $product->files()->where('type', $type)->get();
    }
}
