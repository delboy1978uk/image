<?php

namespace Del\Image\Strategy;

class JpegStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create($filename)
    {
        return imagecreatefromjpeg($filename);
    }

    public function save($resource, $filename, $compression)
    {
        imagejpeg($resource, $filename, $compression);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return 'image/jpeg';
    }

    /**
     * @param $resource
     */
    public function render($resource)
    {
        imagejpeg($resource);
    }

    /**
     * @return void
     */
    public function handleTransparency($newImage, $image)
    {
        return;
    }


}