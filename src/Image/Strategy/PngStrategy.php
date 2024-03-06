<?php

namespace Del\Image\Strategy;

use GdImage;

class PngStrategy extends TransparentStrategy implements ImageTypeStrategyInterface
{
    public function create(string $filename): GdImage
    {
        return \imagecreatefrompng($filename);
    }

    public function save(GdImage $resource, string $filename, int $compression = 100): void
    {
        \imagepng($resource, $filename);
    }

    public function getContentType(): string
    {
        return 'image/png';
    }

    public function render(GdImage $resource): void
    {
        \imagealphablending($resource, true);
        \imagesavealpha($resource, true);
        \imagepng($resource);
    }
}
