<?php

namespace Del;

use Del\Image\Exception\NotFoundException;
use Del\Image\Exception\NothingLoadedException;
use Del\Image\Strategy\GifStrategy;
use Del\Image\Strategy\ImageTypeStrategyInterface;
use Del\Image\Strategy\JpegStrategy;
use Del\Image\Strategy\PngStrategy;
use Del\Image\Strategy\WebPStrategy;
use GdImage;

class Image
{
    private GdImage $image;
    private ?string $fileName = null;
    private ?ImageTypeStrategyInterface $strategy = null;

    private array $strategies = [
        IMAGETYPE_JPEG => JpegStrategy::class,
        IMAGETYPE_GIF => GifStrategy::class,
        IMAGETYPE_PNG => PngStrategy::class,
        IMAGETYPE_WEBP => WebPStrategy::class,
    ];

    public function __construct(string $filename = null)
    {
        if ($filename !== null) {
            $this->fileName = $filename;
            $this->load($filename);
        }
    }

    /** @throws NotFoundException  */
    private function checkFileExists(string $path): void
    {
        if (!\file_exists($path)) {
            throw new NotFoundException("$path does not exist");
        }
    }

    /** @throws NotFoundException  */
    public function load(string $filename): void
    {
        $this->checkFileExists($filename);
        $index = \getimagesize($filename)[2];
        $this->strategy = new $this->strategies[$index]();
        $this->image = $this->strategy->create($filename);
    }

    public function setImageStrategy(ImageTypeStrategyInterface $imageTypeStrategy): void
    {
        $this->strategy = $imageTypeStrategy;
    }

    public function save(string $filename = null, int $permissions = null, int $compression = 100): void
    {
        $filename = ($filename) ?: $this->fileName;
        $this->strategy->save($this->image, $filename, $compression);
        $this->setPermissions($filename, $permissions);
    }

    private function setPermissions(string $filename, ?int $permissions = null): void
    {
        if ($permissions !== null) {
            \chmod($filename, $permissions);
        }
    }

    public function output(bool $return = false): ?string
    {
        $contents = null;

        if ($return) {
            \ob_start();
        }

        $this->renderImage();

        if ($return) {
            $contents = \ob_get_clean();
        }

        return $contents;
    }

    /** @throws NothingLoadedException */
    public function outputBase64Src(): string
    {
        return 'data:' . $this->getHeader() . ';base64,' . \base64_encode( $this->output(true) );
    }

    private function renderImage(): void
    {
        $this->strategy->render($this->image);
    }

    public function getWidth(): int
    {
        return \imagesx($this->image);
    }

    public function getHeight(): int
    {
        return \imagesy($this->image);
    }

    public function resizeToHeight(int $height): void
    {
        $ratio = $height / $this->getHeight();
        $width = (int) ($this->getWidth() * $ratio);
        $this->resize($width, $height);
    }

    public function resizeToWidth(int $width): void
    {
        $ratio = $width / $this->getWidth();
        $height = (int) ($this->getHeight() * $ratio);
        $this->resize($width, $height);
    }

    public function scale(int $scale): void
    {
        $width = (int) ($this->getWidth() * $scale / 100);
        $height = (int) ($this->getHeight() * $scale / 100);
        $this->resize($width, $height);
    }

    public function resizeAndCrop(int $width, int $height): void
    {
        $targetRatio = $width / $height;
        $actualRatio = $this->getWidth() / $this->getHeight();

        if ($targetRatio === $actualRatio) {
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

    public function resize(int $width, int $height): void
    {
        $newImage = \imagecreatetruecolor($width, $height);

        $this->strategy->handleTransparency($newImage, $this->image);

        // Now resample the image
        \imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        // And allocate to $this
        $this->image = $newImage;
    }

    /** $trim can be either left or center */
    public function crop(int $width, int $height, string $trim = 'center'): void
    {
        $offsetX = 0;
        $offsetY = 0;
        $currentWidth = $this->getWidth();
        $currentHeight = $this->getHeight();

        if ($trim !== 'left') {
            $offsetX = $this->getOffsetX($currentWidth, $width, $trim);
            $offsetY = $this->getOffsetY($currentHeight, $height, $trim);
        }

        $newImage = \imagecreatetruecolor($width, $height);
        \imagecopyresampled($newImage, $this->image, 0, 0, $offsetX, $offsetY, $width, $height, $width, $height);
        $this->image = $newImage;
    }

    private function getOffsetX(int $currentWidth, int $width, string $trim): int
    {
        $offsetX = 0;

        if ($currentWidth > $width) {
            $diff = $currentWidth - $width;
            $offsetX = ($trim === 'center') ? $diff / 2 : $diff; //full diff for trim right
        }

        return (int) $offsetX;
    }

    private function getOffsetY(int $currentHeight, int $height, string $trim): int
    {
        $offsetY = 0;

        if ($currentHeight > $height) {
            $diff = $currentHeight - $height;
            $offsetY = ($trim === 'center') ? $diff / 2 : $diff;
        }

        return (int) $offsetY;
    }

    /** @throws NothingLoadedException */
    public function getHeader(): string
    {
        if (!$this->strategy) {
            throw new NothingLoadedException();
        }

        return $this->strategy->getContentType();
    }

    public function destroy(): void
    {
        \imagedestroy($this->image);
    }
}
