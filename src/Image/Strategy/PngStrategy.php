<?php

namespace Del\Image\Strategy;

class PngStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create($filename)
    {
        return imagecreatefrompng($filename);
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
        imagepng($resource, $filename);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return 'image/png';
    }


}