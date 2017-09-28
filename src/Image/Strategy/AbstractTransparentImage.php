<?php

namespace Del\Image\Strategy;

class AbstractTransparentImage
{
    /**
     * @param resource $newImage
     * @param resource $image
     * @param int $index
     */
    protected function prepWithExistingIndex($newImage, $image, $index)
    {
        // Get the array of RGB vals for the transparency index
        $transparentColor = imagecolorsforindex($image, $index);

        // Now allocate the color
        $transparency = imagecolorallocate($newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);

        // Fill the background with the color
        imagefill($newImage, 0, 0, $transparency);

        // And set that color as the transparent one
        imagecolortransparent($newImage, $transparency);
    }

    /**
     * @param $resource
     */
    protected function prepTransparentPng($resource)
    {
        // Set blending mode as false
        imagealphablending($resource, false);

        // Tell it we want to save alpha channel info
        imagesavealpha($resource, true);

        // Set the transparent color
        $color = imagecolorallocatealpha($resource, 0, 0, 0, 127);

        // Fill the image with nothingness
        imagefill($resource, 0, 0, $color);
    }

    /**
     * @param resource $image
     * @return int
     */
    protected function getTransparencyIndex($image)
    {
        return imagecolortransparent($image);
    }
}