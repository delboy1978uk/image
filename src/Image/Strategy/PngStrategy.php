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

    /**
     * @param $resource
     */
    public function render($resource)
    {
        imagealphablending($resource, true);
        imagesavealpha($resource, true);
        imagepng($resource);
    }

    public function handleTransparency($newImage, $image)
    {
        // Set blending mode as false
        imagealphablending($newImage, false);

        // Tell it we want to save alpha channel info
        imagesavealpha($newImage, true);

        // Set the transparent color
        $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);

        // Fill the image with nothingness
        imagefill($newImage, 0, 0, $color);
    }
}