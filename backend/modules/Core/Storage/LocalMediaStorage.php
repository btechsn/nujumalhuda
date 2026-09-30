<?php

declare(strict_types=1);

namespace Modules\Core\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Contracts\MediaStorage;

class LocalMediaStorage implements MediaStorage
{
    public function store(UploadedFile $file, string $path): string
    {
        $stored = $file->store($path, 'public');

        return $stored === false ? '' : $stored;
    }

    public function delete(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    public function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    public function createVariant(string $path, string $variant, array $options = []): string
    {
        if (!function_exists('imagecreatetruecolor') || !Storage::disk('public')->exists($path)) {
            return '';
        }

        $absolute = Storage::disk('public')->path($path);
        $source = $this->open($absolute);
        if ($source === null) {
            return '';
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $max = (int) ($options['max_width'] ?? 480);
        $targetWidth = min($width, $max);
        $targetHeight = (int) max(1, round($height * ($targetWidth / max($width, 1))));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $variantPath = preg_replace('/(\.[^.]+)$/', '-' . $variant . '.jpg', $path) ?? ($path . '-' . $variant . '.jpg');
        $destination = Storage::disk('public')->path($variantPath);
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }
        imagejpeg($canvas, $destination, 82);
        imagedestroy($source);
        imagedestroy($canvas);

        return $variantPath;
    }

    private function open(string $absolute): ?\GdImage
    {
        $info = @getimagesize($absolute);
        $image = match ($info['mime'] ?? '') {
            'image/jpeg' => @imagecreatefromjpeg($absolute),
            'image/png' => @imagecreatefrompng($absolute),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolute) : false,
            'image/gif' => @imagecreatefromgif($absolute),
            default => false,
        };

        return $image instanceof \GdImage ? $image : null;
    }
}
