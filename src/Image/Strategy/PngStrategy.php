<?php

namespace Del\Image\Strategy;

class PngStrategy extends TransparentStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create(string $filename)
    {
        return \imagecreatefrompng($filename);
    }

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     * @return void
     */
    public function save($resource, string $filename = null, int $compression = 100): void
    {
        unset($compression);
        \imagepng($resource, $filename);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'image/png';
    }

    /**
     * @param $resource
     */
    public function render($resource): void
    {
        imagealphablending($resource, true);
        imagesavealpha($resource, true);
        imagepng($resource);
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'png';
    }
}