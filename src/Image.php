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
    public function __construct(string $filename = null)
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
    private function checkFileExists(string $path): void
    {
        if (!file_exists($path)) {
            throw new NotFoundException("$path does not exist");
        }
    }


    /**
     * @param string $filename
     * @throws NotFoundException
     */
    public function load(string $filename): void
    {
        $this->checkFileExists($filename);
        $index = getimagesize($filename)[2];
        $this->strategy = new $this->strategies[$index]();
        $this->image = $this->strategy->create($filename);
    }

    /**
     * @param ImageTypeStrategyInterface $imageTypeStrategy
     */
    public function setImageStrategy(ImageTypeStrategyInterface $imageTypeStrategy): void
    {
        $this->strategy = $imageTypeStrategy;
    }


    /**
     *  @param string $filename
     *  @param int $compression
     *  @param string $permissions
     */
    public function save(string $filename = null, int $permissions = null, int $compression = 100): void 
    {
        $filename = ($filename) ?: $this->fileName;
        $this->strategy->save($this->image, $filename, $compression);
        $this->setPermissions($filename, $permissions);
    }

    /**
     * @param string $filename
     * @param int|null $permissions
     */
    private function setPermissions(string $filename, ?int $permissions = null): void
    {
        if ($permissions !== null) {
            chmod($filename, $permissions);
        }
    }


    /**
     * @param bool $return either output directly
     * @return null|string image contents
     */
    public function output(bool $return = false): ?string
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

    private function renderImage(): void
    {
        $this->strategy->render($this->image);
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return imagesx($this->image);
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return imagesy($this->image);
    }

    /**
     * @param int $height
     */
    public function resizeToHeight(int $height): void
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    /**
     * @param int $width
     */
    public function resizeToWidth(int $width): void
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width, $height);
    }

    /**
     * @param int $scale %
     */
    public function scale(int $scale): void
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getHeight() * $scale / 100;
        $this->resize($width, $height);
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function resizeAndCrop(int $width, int $height): void 
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
    public function resize(int $width, int $height): void 
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
     * @param string $trim from either left or center
     */
    public function crop(int $width, int $height, string $trim = 'center'): void
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
     * @param int $currentWidth
     * @param int $width
     * @param string $trim
     * @return int
     */
    private function getOffsetX(int $currentWidth, int $width, string $trim): int
    {
        $offsetX = 0;
        if ($currentWidth > $width) {
            $diff = $currentWidth - $width;
            $offsetX = ($trim == 'center') ? $diff / 2 : $diff; //full diff for trim right
        }
        
        return (int) $offsetX;
    }

    /**
     * @param int $currentHeight
     * @param int $height
     * @param string $trim
     * @return int
     */
    private function getOffsetY(int $currentHeight, int $height, string $trim): int 
    {
        $offsetY = 0;
        if ($currentHeight > $height) {
            $diff = $currentHeight - $height;
            $offsetY = ($trim == 'center') ? $diff / 2 : $diff;
        }
        
        return (int) $offsetY;
    }

    /**
     * @return string
     * @throws NothingLoadedException
     */
    public function getHeader(): string
    {
        if (!$this->strategy) {
            throw new NothingLoadedException();
        }
        
        return $this->strategy->getContentType();
    }

    /**
     *  Frees up memory
     */
    public function destroy(): void
    {
        imagedestroy($this->image);
    }
}