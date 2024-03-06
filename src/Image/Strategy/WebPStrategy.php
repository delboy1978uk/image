<?php

namespace Del\Image\Strategy;

use GdImage;

class WebPStrategy extends TransparentStrategy implements ImageTypeStrategyInterface
{
    public function create(string $filename): GdImage
    {
        return \imagecreatefromwebp($filename);
    }

    public function save(GdImage $resource, string $filename, int $compression = 100): void
    {
        \imagewebp($resource, $filename);
    }

    public function getContentType(): string
    {
        return 'image/webp';
    }

    public function render(GdImage $resource): void
    {
        imagealphablending($resource, true);
        imagesavealpha($resource, true);
        imagewebp($resource);
    }
}
