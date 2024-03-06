<?php

namespace Del\Image\Strategy;

use GdImage;

interface ImageTypeStrategyInterface
{
    public function create(string $filename): GdImage;
    public function save(GdImage $resource, string $filename, int $compression): void;
    public function getContentType(): string;
    public function render(GdImage $resource): void;
    public function handleTransparency(GdImage $newImage, GdImage $image): void;
}
