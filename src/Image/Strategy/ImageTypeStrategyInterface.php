<?php

namespace Del\Image\Strategy;

interface ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create(string $filename): resource;

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     * @return void
     */
    public function save(resource $resource, string $filename, int $compression): void;

    /**
     * @return string
     */
    public function getContentType(): string;

    /**
     * @return void
     */
    public function render($resource): void;

    /**
     * @param resource $newImage
     * @param resource $image
     * @return void
     */
    public function handleTransparency(resource $newImage, resource $image): void;
}