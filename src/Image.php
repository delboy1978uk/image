<?php

namespace Del;

use Del\Image\Exception\NotFoundException;
use Del\Image\Exception\NothingLoadedException;
use Del\Image\Strategy\GifStrategy;
use Del\Image\Strategy\ImageTypeStrategyInterface;
use Del\Image\Strategy\JpegStrategy;
use Del\Image\Strategy\PngStrategy;

class Image 
{
    /** @var resource $image */
    private $image;

    /** @var string $fileName */
    private $fileName;

    /** @var ImageTypeStrategyInterface $strategy */
    private $strategy;

    /** @var array $strategies */
    private $strategies = [
        IMAGETYPE_JPEG => JpegStrategy::class,
        IMAGETYPE_GIF => GifStrategy::class,
        IMAGETYPE_PNG => PngStrategy::class,
    ];

    /**
     * @param string $filename
     */
    public function __construct($filename = null)
    {
        if ($filename !== null) {
            $this->fileName = $filename;
            $this->load($filename);
        }
    }

    /**
     * @param $path
     * @throws NotFoundException
     */
    private function checkFileExists($path)
    {
        if (!file_exists($path)) {
            throw new NotFoundException("$path does not exist");
        }
    }


    /**
     * @param string $filename
     * @throws NotFoundException
     */
    public function load($filename)
    {
        $this->checkFileExists($filename);
        $this->strategy = new $this->strategies[getimagesize($filename)[2]]();
        $this->image = $this->strategy->create($filename);
    }


    /**
     *  @param string $filename
     *  @param int $compression
     *  @param string $permissions
     */
    public function save($filename = null, $compression = 100, $permissions = null)
    {
        $filename = ($filename) ?: $this->fileName;
        $this->strategy->save($this->image, $filename, $compression);
        $this->setPermissions($filename, $permissions);
    }

    /**
     * @param $filename
     * @param $permissions
     */
    private function setPermissions($filename, $permissions)
    {
        if ($permissions !== null) {
            chmod($filename, (int) $permissions);
        }
    }


    /**
     * @param bool $return either output directly
     * @return null|string image contents
     */
    public function output($return = false)
    {
        $contents = null;

        if ($return) {
            ob_start();
        }

        $this->renderImage();

        if ($return) {
            $contents = ob_get_flush();
        }

        return $contents;
    }

    private function renderImage()
    {
        $this->strategy->render($this->image);
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * @param int $height
     */
    public function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    /**
     * @param int $width
     */
    public function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width, $height);
    }

    /**
     * @param int $scale %
     */
    public function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getHeight() * $scale / 100;
        $this->resize($width, $height);
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function resizeAndCrop($width, $height)
    {
        $targetRatio = $width / $height;
        $actualRatio = $this->getWidth() / $this->getHeight();

        if ($targetRatio == $actualRatio) {
            // Scale to size
            $this->resize($width, $height);
        } elseif ($targetRatio > $actualRatio) {
            // Resize to width, crop extra height
            $this->resizeToWidth($width);
            $this->crop($width, $height);
        } else {
            // Resize to height, crop additional width
            $this->resizeToHeight($height);
            $this->crop($width, $height);
        }
    }


    /**
     *  Now with added Transparency resizing feature
     *  @param int $width
     *  @param int $height
     */
    public function resize($width, $height)
    {
        $newImage = imagecreatetruecolor($width, $height);

        $this->strategy->handleTransparency($newImage, $this->image);

        // Now resample the image
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        // And allocate to $this
        $this->image = $newImage;
    }




    /**
     * @param int $width
     * @param int $height
     * @param string $trim
     */
    public function crop($width, $height, $trim = 'center')
    {
        $offsetX = 0;
        $offsetY = 0;
        $currentWidth = $this->getWidth();
        $currentHeight = $this->getHeight();

        if ($trim != 'left') {
            $offsetX = $this->getOffsetX($currentWidth, $width, $trim);
            $offsetY = $this->getOffsetY($currentHeight, $height, $trim);
        }

        $newImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newImage, $this->image, 0, 0, $offsetX, $offsetY, $width, $height, $width, $height);
        $this->image = $newImage;
    }

    /**
     * @param $currentWidth
     * @param $width
     * @param $trim
     * @return float|int
     */
    private function getOffsetX($currentWidth, $width, $trim)
    {
        $offsetX = 0;
        if ($currentWidth > $width) {
            $diff = $currentWidth - $width;
            $offsetX = ($trim == 'center') ? $diff / 2 : $diff; //full diff for trim right
        }
        return $offsetX;
    }

    /**
     * @param $currentHeight
     * @param $height
     * @param $trim
     * @return float|int
     */
    private function getOffsetY($currentHeight, $height, $trim)
    {
        $offsetY = 0;
        if ($currentHeight > $height) {
            $diff = $currentHeight - $height;
            $offsetY = ($trim == 'center') ? $diff / 2 : $diff;
        }
        return $offsetY;
    }

    /**
     * @return mixed
     * @throws NothingLoadedException
     */
    public function getHeader()
    {
        if (!$this->strategy) {
            throw new NothingLoadedException();
        }
        return $this->strategy->getContentType();
    }

    /**
     *  Frees up memory
     */
    public function destroy()
    {
        imagedestroy($this->image);
    }
}