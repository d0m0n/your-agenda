<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    private const MAX_DIMENSION = 800;

    private const QUALITY = 85;

    /**
     * Resize and re-encode an uploaded image to JPEG, then store it on the
     * public disk. Re-encoding guarantees the stored file is a clean raster
     * image regardless of what the original upload actually contained.
     *
     * @return string Path relative to storage/app/public.
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $manager = new ImageManager(new Driver);

        $image = $manager->decodePath($file->getRealPath())
            ->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);

        $relativePath = trim($directory, '/').'/'.Str::uuid().'.jpg';
        $fullPath = storage_path('app/public/'.$relativePath);

        File::ensureDirectoryExists(dirname($fullPath));

        $image->encodeUsingFileExtension('jpg', quality: self::QUALITY)->save($fullPath);

        return $relativePath;
    }

    public function delete(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        File::delete(storage_path('app/public/'.$relativePath));
    }
}
