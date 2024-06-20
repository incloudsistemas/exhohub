<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;

if (!function_exists('CreateThumb')) {
    /**
     * Create a thumb image
     * Types = fit, resize, background, resizeCanvas
     *
     * @param
     * @return
     */
    function CreateThumb(
        string $src,
        int $width,
        int $height,
        string $disk = 'public',
        string $type = 'fit',
        bool $watermark = false,
        string $watermarkPosition = 'center',
        int $quality = 80
    ): string {
        // Creates a unique key for the cache based on src, width, height and type.
        $cacheKey = 'thumb_' . md5($src . $width . $height . $type . $quality);
        // Cache::forget($cacheKey);

        // Try getting the thumbnail URL from cache first.
        return Cache::remember(
            $cacheKey,
            now()->addDay(),
            function () use ($src, $width, $height, $disk, $type, $watermark, $watermarkPosition, $quality) {
                $src = ltrim($src, '/');

                // For temporary $url
                // if (strpos($src, '~inclou17/dominio.com.br') !== false) {
                //     $src = str_replace('~inclou17/dominio.com.br', '', $src);
                // }

                // if (is_null($width) || is_null($height)) {
                //     return $src;
                // }

                // Check if it is a complete URL
                if (strpos($src, 'storage') !== false) {
                    $src = parse_url($src, PHP_URL_PATH);
                    $src = str_replace('/storage/', '', $src);
                }

                // Get paths and names
                $filePartials = explode('/', $src);
                $fileName = end($filePartials);
                $dirPath = str_replace("/$fileName", '', $src);
                $thumbSrc = "{$dirPath}/thumbs/{$width}x{$height}/{$fileName}";

                // If original image doesn't exists returns a default image placeholder generated from https://placeholder.com/
                if (!Storage::disk($disk)->exists($src)) {
                    return PlaceholderImg(width: $width, height: $height);
                }

                // If thumbnail exist returns it
                if (Storage::disk($disk)->exists($thumbSrc)) {
                    return url(Storage::url($thumbSrc));
                }

                $file = Storage::disk($disk)
                    ->get($src);

                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);

                if ($watermark) {
                    $image->place(public_path('web-build/images/watermark.png'), $watermarkPosition);
                }

                match ($type) {
                    'resize'       => $image->resizeDown($width, $height),
                    'scale'        => $image->scale($width, $height),
                    'fit'          => $image->cover($width, $height),
                    'pad'          => $image->pad($width, $height, '000'),
                    'resizeCanvas' => $image->resizeCanvas($width, $height, '000'),
                    default        => throw new InvalidArgumentException("Invalid type: $type"),
                };

                $encoded = $image->encode(new AutoEncoder(quality: $quality));

                // Storage the file
                Storage::disk($disk)
                    ->put($thumbSrc, $encoded);

                return url(Storage::url($thumbSrc));
            }
        );
    }
}

if (!function_exists('PlaceholderImg')) {
    /**
     * Generate a placeholder img
     * placeholder.com/
     *
     * @param
     * @return
     */
    function PlaceholderImg(
        int $width,
        int $height,
        string $text = 'S/ Img',
        string $background = 'EFEFEF',
        string $textColor = 'AAAAAA'
    ): string {
        $text = str_replace(' ', '+', $text);
        return "https://via.placeholder.com/{$width}x{$height}/{$background}/{$textColor}?text={$text}";
    }
}
