<?php

namespace Del\Image\Strategy;

class GifStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create($filename)
    {
        return imagecreatefromgif($filename);
    }

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     * @return void
     */
    public function save($resource, $filename, $compression)
    {
        unset($compression);
        imagegif($resource, $filename);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return 'image/gif';
    }
}