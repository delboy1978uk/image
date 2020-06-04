<?php

namespace Del\Image\Strategy;

abstract class TransparentStrategy
{
    /**
     * @param resource $newImage
     * @param resource $image
     */
    public function handleTransparency($newImage, $image): void
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