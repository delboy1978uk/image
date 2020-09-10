<?php

namespace Del\Image\Strategy;

class WebPStrategy extends TransparentStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create(string $filename)
    {
        return \imagecreatefromwebp($filename);
    }

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     * @return void
     */
    public function save($resource, string $filename = null, int $compression = 100): void
    {
        \imagewebp($resource, $filename, $compression);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'image/webp';
    }

    /**
     * @param $resource
     */
    public function render($resource): void
    {
        imagealphablending($resource, true);
        imagesavealpha($resource, true);
        imagewebp($resource);
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'webp';
    }
}