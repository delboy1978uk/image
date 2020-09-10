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

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     */
    public function save($resource, string $filename = null, int $compression = 100): void
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
    public function render($resource): void
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

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'jpg';
    }
}