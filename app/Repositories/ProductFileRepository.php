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


    public function deleteFile(Product $product, ProductFile $file): bool
    {
        //
        $this->fileService->deleteFile($file->path);

        // 
        return $file->delete();
    }


    public function getProductFiles(product $product)
    {
        return $product->files;
    }


    public function getProductFilesByType(product $product, string $type)
    {
        return $product->files()->where('type', $type)->get();
    }
}
