<?php

namespace Del\Image\Strategy;

interface ImageTypeStrategyInterface
{
    /**
     * @param string $filename
     * @return resource
     */
    public function create($filename);

    /**
     * @param resource $resource
     * @param string $filename
     * @param int $compression
     * @return mixed
     */
    public function save($resource, $filename, $compression);

    /**
     * @return string
     */
    public function getContentType();
}