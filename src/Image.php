<?php

namespace Del;

use Exception;

class Image {

    /**
     * @var
     */
    protected $_image;
    /**
     * @var
     */
    protected $_image_type;

    /**
     *  @var string
     */
    protected $file_name;


    /**
     * @param null $filename
     */
    public function __construct($filename = null)
    {
        if($filename){
            $this->file_name = $filename;
            $this->load($filename);
        }
    }


    /**
     * @param $filename
     * @throws Exception
     */
    public function load($filename)
    {
        if(!file_exists($filename))
        {
            throw new Exception("$filename does not exist");
        }

        $image_info = getimagesize($filename);
        $this->_image_type = $image_info[2];

        if( $this->_image_type == IMAGETYPE_JPEG )
        {
            $this->_image = imagecreatefromjpeg($filename);
        }
        elseif( $this->_image_type == IMAGETYPE_GIF )
        {
            $this->_image = imagecreatefromgif($filename);
        }
        elseif( $this->_image_type == IMAGETYPE_PNG )
        {
            $this->_image = imagecreatefrompng($filename);
        }
    }


    /**
     *  @param string $filename
     *  @param int $compression
     *  @param string $permissions
     */
    public function save($filename = null, $compression=100, $permissions=null)
    {
        $filename = ($filename) ?: $this->file_name;
        switch($this->getImageType())
        {
            case IMAGETYPE_JPEG:
                imagejpeg($this->_image,$filename,$compression);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->_image,$filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->_image,$filename);
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
        switch($this->getImageType())
        {
            case IMAGETYPE_JPEG:
                imagejpeg($this->_image);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->_image);
                break;
            case IMAGETYPE_PNG:
                imagealphablending($this->_image,true);
                imagesavealpha($this->_image,true);
                imagepng($this->_image);
                break;
        }
    }

    /**
     * @return int
     */
    public function getWidth()
    {

        return imagesx($this->_image);
    }

    /**
     * @return int
     */
    public function getHeight()
    {

        return imagesy($this->_image);
    }

    /**
     * @param $height
     */
    public function resizeToHeight($height)
    {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width,$height);
    }

    /**
     * @param $width
     */
    public function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width,$height);
    }

    /**
     * @param $scale
     */
    public function scale($scale)
    {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getheight() * $scale/100;
        $this->resize($width,$height);
    }


    public function resizeAndCrop($width,$height)
    {
        $target_ratio = $width / $height;
        $actual_ratio = $this->getWidth() / $this->getHeight();

        if($target_ratio == $actual_ratio){
            // Scale to size
            $this->resize($width,$height);
        } elseif($target_ratio > $actual_ratio) {
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

        $new_image = imagecreatetruecolor($width, $height);
        if ( ($this->getImageType() == IMAGETYPE_GIF) || ($this->getImageType()  == IMAGETYPE_PNG) )
        {
            // Get transparency color's index number
            $transparency = imagecolortransparent($this->_image);

            // Transparent Gifs have index > 0
            // Transparent Png's have index -1
            if ($transparency >= 0)
            {
                // Get the array of RGB vals for the transparency index
                $transparent_color = imagecolorsforindex($this->_image, $transparency);

                // Now allocate the color
                $transparency = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);

                // Fill the background with the color
                imagefill($new_image, 0, 0, $transparency);

                // And set that color as the transparent one
                imagecolortransparent($new_image, $transparency);
            }
            // Or, if its a PNG
            elseif ($this->getImageType() == IMAGETYPE_PNG)
            {
                // Set blending mode as false
                imagealphablending($new_image, false);

                // Tell it we want to save alpha channel info
                imagesavealpha($new_image, true);

                // Set the transparent color
                $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);

                // Fill the image with nothingness
                imagefill($new_image, 0, 0, $color);
            }
        }
        // Now resample the image
        imagecopyresampled($new_image, $this->_image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        // And allocate to $this
        $this->_image = $new_image;
    }


    /**
     * @param $width
     * @param $height
     * @param string $trim
     */
    function crop($width,$height, $trim = 'center')
    {
        $offset_x = 0;
        $offset_y = 0;
        $current_width = $this->getWidth();
        $current_height = $this->getHeight();

        if($trim != 'left')
        {
            if($current_width > $width) {
                $diff = $current_width - $width;
                $offset_x = ($trim == 'center') ? $diff / 2 : $diff; //full diff for trim right
            }
            if($current_height > $height) {
                $diff = $current_height - $height;
                $offset_y = ($trim = 'center') ? $diff / 2 : $diff;
            }
        }

        $new_image = imagecreatetruecolor($width,$height);
        imagecopyresampled($new_image,$this->_image,0,0,$offset_x,$offset_y,$width,$height,$width,$height);
        $this->_image = $new_image;
    }




    /**
     * @return mixed
     */
    public function getImageType()
    {
        return $this->_image_type;
    }


    /**
     * @return string
     */
    public function getHeader()
    {
        if( $this->_image_type == IMAGETYPE_JPEG )
        {
            return 'image/jpeg';
        }
        elseif( $this->_image_type == IMAGETYPE_GIF )
        {
            return 'image/gif';
        }
        elseif( $this->_image_type == IMAGETYPE_PNG )
        {
            return 'image/png';
        }
        return null;
    }


    /**
     *  Free's up memory
     */
    public function destroy()
    {
        imagedestroy($this->_image);
    }
}
