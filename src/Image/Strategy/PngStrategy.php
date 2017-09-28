<?php

namespace Del\Image\Strategy;

class PngStrategy extends AbstractTransparentImage  implements ImageTypeStrategyInterface
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
        // Get transparency color's index number
        $transparency = $this->getTransparencyIndex($image);

        // Is a strange index other than -1 set?
        if ($transparency >= 0) {

            // deal with alpha channels
            $this->prepWithExistingIndex($newImage, $image, $transparency);

        } else {

            // deal with alpha channels
            $this->prepTransparentPng($newImage);
        }
    }
}