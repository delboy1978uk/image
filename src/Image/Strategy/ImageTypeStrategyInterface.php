<?php

namespace Del\Image\Strategy;

interface ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create(string $filename);

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     * @return void
     */
    public function save($resource, string $filename = null, int $compression = 100): void;

    /**
     * @return string
     */
    public function getContentType(): string;

    /**
     * @return string
     */
    public function getFileExtension(): string;

    /**
     * @return void
     */
    public function render($resource): void;

    /**
     * @param resource $newImage
     * @param resource $image
     * @return void
     */
    public function handleTransparency($newImage, $image): void;
}