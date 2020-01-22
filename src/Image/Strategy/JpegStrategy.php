<?php

namespace Del\Image\Strategy;

class JpegStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return null|resource
     */
    public function create(string $filename)
    {
        return \imagecreatefromjpeg($filename);
    }

    public function save(resource $resource, string $filename, int $compression): void
    {
        \imagejpeg($resource, $filename, $compression);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'image/jpeg';
    }

    /**
     * @param resource $resource
     */
    public function render($resource)
    {
        \imagejpeg($resource);
    }

    /**
     * @param resource $newImage
     * @param resource $image
     */
    public function handleTransparency($newImage, $image): void
    {
        // Jpeg's aren't transparent.
        return;
    }
}