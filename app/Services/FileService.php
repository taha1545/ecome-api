<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{

    public function storeProductFile(UploadedFile $file, $type = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;

        $type = is_string($type) ? $type : 'other';

        $path = $this->getPathForType($type, $fileName);

        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }



    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }


    private function getPathForType(string $type, string $fileName): string
    {
        return match ($type) {
            'image' => "products/images/{$fileName}",
            'document' => "products/documents/{$fileName}",
            '3d_model' => "products/models/{$fileName}",
            default => "products/other/{$fileName}",
        };
    }
}
