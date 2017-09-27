<?php

namespace Del;

use Exception;

class Image 
{
    /** @var resource $image */
    private $image;

    /** @var int $imageType */
    private $imageType;

    /** @var string $fileName */
    private $fileName;


    /**
     * @param null $filename
     */
    public function __construct($filename = null)
    {
        if ($filename) {
            $this->fileName = $filename;
            $this->load($filename);
        }
    }


    /**
     * @param $filename
     * @throws Exception
     */
    public function load($filename)
    {
        if (!file_exists($filename)) {
            throw new Exception("$filename does not exist");
        }

        $imageInfo = getimagesize($filename);
        $this->imageType = $imageInfo[2];

        if( $this->imageType == IMAGETYPE_JPEG ) {
            $this->image = imagecreatefromjpeg($filename);
        }  elseif( $this->imageType == IMAGETYPE_GIF ) {
            $this->image = imagecreatefromgif($filename);
        } elseif( $this->imageType == IMAGETYPE_PNG ) {
            $this->image = imagecreatefrompng($filename);
        }
    }


    /**
     *  @param string $filename
     *  @param int $compression
     *  @param string $permissions
     */
    public function save($filename = null, $compression=100, $permissions=null)
    {
        $filename = ($filename) ?: $this->fileName;
        switch ($this->getImageType()) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image,$filename,$compression);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image,$filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image,$filename);
                break;
        }
        if( $permissions != null)
        {
            chmod($filename,$permissions);
        }
    }


    /**
     *  @param string
     */
    public function output()
    {
        switch ($this->getImageType()) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image);
                break;
            case IMAGETYPE_PNG:
                imagealphablending($this->image,true);
                imagesavealpha($this->image,true);
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
        $this->resize($width,$height);
    }

    /**
     * @param int $width
     */
    public function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width,$height);
    }

    /**
     * @param $scale
     */
    public function scale($scale)
    {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getHeight() * $scale/100;
        $this->resize($width,$height);
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function resizeAndCrop($width,$height)
    {
        $target_ratio = $width / $height;
        $actual_ratio = $this->getWidth() / $this->getHeight();

        if ($target_ratio == $actual_ratio){
            // Scale to size
            $this->resize($width,$height);
        } elseif ($target_ratio > $actual_ratio) {
            // Resize to width, crop extra height
            $this->resizeToWidth($width);
            $this->crop($width,$height,true);
        } else {
            // Resize to height, crop additional width
            $this->resizeToHeight($height);
            $this->crop($width,$height,true);
        }
    }


    /**
     *  Now with added Transparency resizing feature
     *  @param int $width
     *  @param int $height
     */
    public function resize($width,$height)
    {
        $newImage = imagecreatetruecolor($width, $height);
        if ( ($this->getImageType() == IMAGETYPE_GIF) || ($this->getImageType()  == IMAGETYPE_PNG) ) {
            // Get transparency color's index number
            $transparency = imagecolortransparent($this->image);

            // Transparent Gifs have index > 0
            // Transparent Png's have index -1
            if ($transparency >= 0) {
                // Get the array of RGB vals for the transparency index
                $transparentColor = imagecolorsforindex($this->image, $transparency);

                // Now allocate the color
                $transparency = imagecolorallocate($newImage, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);

                // Fill the background with the color
                imagefill($newImage, 0, 0, $transparency);

                // And set that color as the transparent one
                imagecolortransparent($newImage, $transparency);
            }  elseif ($this->getImageType() == IMAGETYPE_PNG) {
                // Set blending mode as false
                imagealphablending($newImage, false);

                // Tell it we want to save alpha channel info
                imagesavealpha($newImage, true);

                // Set the transparent color
                $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);

                // Fill the image with nothingness
                imagefill($newImage, 0, 0, $color);
            }
        }
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
    function crop($width,$height, $trim = 'center')
    {
        $offset_x = 0;
        $offset_y = 0;
        $current_width = $this->getWidth();
        $current_height = $this->getHeight();

        if ($trim != 'left') {
            if ($current_width > $width) {
                $diff = $current_width - $width;
                $offset_x = ($trim == 'center') ? $diff / 2 : $diff; //full diff for trim right
            }
            if ($current_height > $height) {
                $diff = $current_height - $height;
                $offset_y = ($trim = 'center') ? $diff / 2 : $diff;
            }
        }

        $newImage = imagecreatetruecolor($width,$height);
        imagecopyresampled($newImage, $this->image, 0, 0, $offset_x, $offset_y, $width, $height, $width, $height);
        $this->image = $newImage;
    }

    /**
     * @return mixed
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        if( $this->imageType == IMAGETYPE_JPEG ) {
            return 'image/jpeg';
        } elseif( $this->imageType == IMAGETYPE_GIF ) {
            return 'image/gif';
        } elseif( $this->imageType == IMAGETYPE_PNG ) {
            return 'image/png';
        }
        return null;
    }

    /**
     *  Free's up memory
     */
    public function destroy()
    {
        imagedestroy($this->image);
    }
}
