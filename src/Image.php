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

    /** @var int $imageType */
    private $imageType;

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
        $imageInfo = getimagesize($filename);
        $this->imageType = $imageInfo[2];
        $this->strategy = new $this->strategies[$this->imageType]();
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
     * @return void|string image contents
     */
    public function output($return = false)
    {
        if ($return) {
            ob_start();
        }

        $this->renderImage();

        if ($return) {
            $contents = ob_get_flush();
            return $contents;
        }
    }

    private function renderImage()
    {
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image);
                break;
            case IMAGETYPE_PNG:
                imagealphablending($this->image, true);
                imagesavealpha($this->image, true);
                imagepng($this->image);
                break;
        }
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

        $this->handleTransparency($newImage);

        // Now resample the image
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        // And allocate to $this
        $this->image = $newImage;
    }

    private function handleTransparency($resource)
    {
        if ($this->getImageType() == IMAGETYPE_JPEG) {
            return;
        }

        // Get transparency color's index number
        $transparency = imagecolortransparent($this->image);

        // Is a strange index other than -1 set?
        if ($transparency >= 0) {

            // deal with alpha channels
            $this->prepWithExistingIndex($resource, $transparency);

        } elseif ($this->getImageType() == IMAGETYPE_PNG) {

            // deal with alpha channels
            $this->prepTransparentPng($resource);
        }

    }

    /**
     * @param $resource
     * @param $index
     */
    private function prepWithExistingIndex($resource, $index)
    {
        // Get the array of RGB vals for the transparency index
        $transparentColor = imagecolorsforindex($this->image, $index);

        // Now allocate the color
        $transparency = imagecolorallocate($resource, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);

        // Fill the background with the color
        imagefill($resource, 0, 0, $transparency);

        // And set that color as the transparent one
        imagecolortransparent($resource, $transparency);
    }

    /**
     * @param $resource
     */
    private function prepTransparentPng($resource)
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
     */
    public function getImageType()
    {
        return $this->imageType;
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
