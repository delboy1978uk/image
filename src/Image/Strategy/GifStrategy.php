<?php

namespace Del\Image\Strategy;

class GifStrategy implements ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create(string $filename)
    {
        return \imagecreatefromgif($filename);
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
        \imagegif($resource, $filename);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'image/gif';
    }

    /**
     * @param resource $resource
     */
    public function render($resource): void
    {
        \imagegif($resource);
    }

    public function handleTransparency($newImage, $image): void
    {
        // Get transparency color's index number
        $transparency = $this->getTransparencyIndex($image);

        // Is a strange index other than -1 set?
        if ($transparency >= 0) {
            $this->prepWithCustomTransparencyIndex($newImage, $image, $transparency);
        }
    }

    /**
     * @param resource $newImage
     * @param resource $image
     * @param int $index
     */
    private function prepWithCustomTransparencyIndex($newImage, $image, int $index): void
    {
        // Get the array of RGB vals for the transparency index
        $transparentColor = \imagecolorsforindex($image, $index);

        // Now allocate the color
        $transparency = \imagecolorallocate($newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);

        // Fill the background with the color
        \imagefill($newImage, 0, 0, $transparency);

        // And set that color as the transparent one
        \imagecolortransparent($newImage, $transparency);
    }

    /**
     * @param resource $image
     * @return int
     */
    private function getTransparencyIndex($image): int
    {
        return \imagecolortransparent($image);
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'gif';
    }
}
