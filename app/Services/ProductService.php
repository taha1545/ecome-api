<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{

    public function createProduct(array $data): Product
    {
        DB::beginTransaction();

        try {
            //
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'brand' => $data['brand'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            //
            if (isset($data['categories']) && is_array($data['categories'])) {
                $product->categories()->attach($data['categories']);
            }

            //
            if (isset($data['tags']) && is_array($data['tags'])) {
                $product->tags()->attach($data['tags']);
            }

            //
            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $product->variants()->create([
                        'size' => $variantData['size'] ?? null,
                        'color' => $variantData['color'] ?? null,
                        'description' => $variantData['description'] ?? null,
                        'quantity' => $variantData['quantity'],
                        'price' => $variantData['price'],
                    ]);
                }
            }

            //
            if (isset($data['files']) && is_array($data['files'])) {
                $this->processProductFiles($product, $data['files'], $data['file_types']);
            }

            DB::commit();
            //
            return $product->fresh(['categories', 'tags', 'variants', 'files']);
            //
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    protected function processProductFiles(Product $product, array $files, array $fileTypes): void
    {
        //
        if (empty($files) || empty($fileTypes)) {
            return;
        }

        foreach ($files as $index => $file) {
            //
            if (!isset($fileTypes[$index])) {
                continue;
            }

            $fileType = $fileTypes[$index];

            //
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::uuid() . '.' . $extension;

            //
            $path = match ($fileType) {
                'image' => "products/images/{$fileName}",
                'document' => "products/documents/{$fileName}",
                '3d_model' => "products/models/{$fileName}",
                default => "products/other/{$fileName}",
            };

            //
            Storage::disk('public')->put($path, file_get_contents($file));

            //
            $product->files()->create([
                'path' => $path,
                'type' => $fileType,
            ]);
        }
    }

    public function updateProduct(Product $product, array $data): Product
    {
        DB::beginTransaction();

        try {
            // 
            $product->update([
                'description' => $data['description'] ?? $product->description,
                'brand' => $data['brand'] ?? $product->brand,
                'price' => $data['price'] ?? $product->price,
                'discount_price' => $data['discount_price'] ?? $product->discount_price,
                'is_active' => $data['is_active'] ?? $product->is_active,
            ]);

            if (isset($data['categories']) && is_array($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            if (isset($data['tags']) && is_array($data['tags'])) {
                $product->tags()->sync($data['tags']);
            }

            DB::commit();

            // 
            return $product->load('categories', 'tags')->fresh();
            //
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function deleteProduct(Product $product): bool
    {
        DB::beginTransaction();

        try {
            // Delete physical files from storage
            foreach ($product->files as $file) {
                if (Storage::disk('public')->exists($file->path)) {
                    Storage::disk('public')->delete($file->path);
                }
            }

            //
            $product->variants()->delete();
            $product->files()->delete();
            $product->reviews()->delete();
            $product->comments()->delete();

            // Detach relationships
            $product->categories()->detach();
            $product->tags()->detach();
            $product->savedUsers()->detach();

            // Delete the product
            $product->delete();

            DB::commit();

            return true;
            //
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
