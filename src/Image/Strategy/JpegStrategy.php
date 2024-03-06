<?php

namespace Del\Image\Strategy;

use GdImage;

class JpegStrategy implements ImageTypeStrategyInterface
{
    public function create(string $filename): GdImage
    {
        return \imagecreatefromjpeg($filename);
    }

    public function save(GdImage $resource, string $filename, int $compression): void
    {
        \imagejpeg($resource, $filename, $compression);
    }

    public function getContentType(): string
    {
        return 'image/jpeg';
    }

    public function render(GdImage $resource): void
    {
        \imagejpeg($resource);
    }

    public function handleTransparency(GdImage $newImage, GdImage $image): void
    {
        // Jpeg's aren't transparent.
    }
}
