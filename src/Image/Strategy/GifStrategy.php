<?php

namespace Del\Image\Strategy;

use GdImage;

class GifStrategy implements ImageTypeStrategyInterface
{
    public function create(string $filename): GdImage
    {
        return \imagecreatefromgif($filename);
    }

    public function save(GdImage $resource, string $filename, int $compression = 100): void
    {
        \imagegif($resource, $filename);
    }

    public function getContentType(): string
    {
        return 'image/gif';
    }

    public function render(GdImage $resource): void
    {
        \imagegif($resource);
    }

    public function handleTransparency(GdImage $newImage, GdImage $image): void
    {
        // Get transparency color's index number
        $transparency = $this->getTransparencyIndex($image);

        // Is a strange index other than -1 set?
        if ($transparency >= 0) {
            $this->prepWithCustomTransparencyIndex($newImage, $image, $transparency);
        }
    }

    private function prepWithCustomTransparencyIndex(GdImage $newImage, GdImage $image, int $index): void
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

    private function getTransparencyIndex(GdImage $image): int
    {
        return \imagecolortransparent($image);
    }
}
