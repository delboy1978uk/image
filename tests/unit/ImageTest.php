<?php

use Codeception\Test\Unit;
use Del\Image;

class ImageTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testLoadJpgInConstructor()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image($path);
        $output = md5($image->output(true));
        $this->assertEquals('8823a6958464adb1440b1b83ad97aff5', $output);
        $image->destroy();
    }


    public function testLoadJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image();
        $image->load($path);
        $output = md5($image->output(true));
        $this->assertEquals('8823a6958464adb1440b1b83ad97aff5', $output);
    }


    public function testLoadPng()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $output = md5($image->output(true));
        $this->assertEquals('aa9783d7b949216b39d297836d772c47', $output);
    }


    public function testLoadGif()
    {
        $path = 'tests/_data/superman.gif';
        $image = new Image();
        $image->load($path);
        $output = md5($image->output(true));
        $this->assertEquals('ddc1d414f51592bcdc695fb53e6f304e', $output);
    }

    public function testLoadThrowsException()
    {
        $this->expectException('Del\Image\Exception\NotFoundException');
        $path = 'this/is/the/road/to/nowhere';
        $image = new Image();
        $image->load($path);
    }

    public function testGetHeaderForJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image();
        $image->load($path);
        $contentType = $image->getHeader();
        $this->assertEquals('image/jpeg', $contentType);
    }

    public function testGetHeaderForPng()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $contentType = $image->getHeader();
        $this->assertEquals('image/png', $contentType);
    }

    public function testGetHeaderForGif()
    {
        $path = 'tests/_data/superman.gif';
        $image = new Image();
        $image->load($path);
        $contentType = $image->getHeader();
        $this->assertEquals('image/gif', $contentType);
    }

    public function testGetHeaderThrowsException()
    {
        $this->expectException('Del\Image\Exception\NothingLoadedException');
        $image = new Image();
        $image->getHeader();
    }

    public function testSaveGif()
    {
        $path = 'tests/_data/superman.gif';
        $savePath = 'tests/_data/superman2.gif';
        $image = new Image();
        $image->load($path);
        $image->save($savePath);
        $this->assertTrue(file_exists($savePath));
        $image->load($savePath);
        $output = md5($image->output(true));
        $this->assertEquals('ddc1d414f51592bcdc695fb53e6f304e', $output);
        unlink($savePath);
    }

    public function testSaveJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $savePath = 'tests/_data/sonsofanarchy2.jpg';
        $image = new Image();
        $image->load($path);
        $image->save($savePath, 100, 777);
        $this->assertTrue(file_exists($savePath));
        $image->load($savePath);
        $output = md5($image->output(true));
        $this->assertEquals('9542c75b1d090f26bac2f7729f6e4629', $output);
        unlink($savePath);
    }

    public function testSavePng()
    {
        $path = 'tests/_data/troll.png';
        $savePath = 'tests/_data/troll2.png';
        $image = new Image();
        $image->load($path);
        $image->save($savePath);
        $this->assertTrue(file_exists($savePath));
        $image->load($savePath);
        $output = md5($image->output(true));
        $this->assertEquals('aa9783d7b949216b39d297836d772c47', $output);
        unlink($savePath);
    }

    public function testGetWidth()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $width = $image->getWidth();
        $this->assertEquals(233, $width);
    }

    public function testGetHeight()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $height = $image->getHeight();
        $this->assertEquals(238, $height);
    }

    public function testResizeToWidth()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->resizeToWidth(100);
        $output = md5($image->output(true));
        $this->assertEquals('4c35a3f2a089531333568afa1b5daaac', $output);
    }

    public function testResizeJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image();
        $image->load($path);
        $image->resizeToWidth(100);
        $output = md5($image->output(true));
        $this->assertEquals('', $output);
    }

    public function testResizeToHeight()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->resizeToHeight(100);
        $output = md5($image->output(true));
        $this->assertEquals('728bb56bb00dbd7ee64b24258124e267', $output);
    }

    public function testScale()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->scale(50);
        $output = md5($image->output(true));
        $this->assertEquals('1ad91304c4aaffce7497f765d21c7645', $output);
    }

    public function testCrop()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->crop(50, 50);
        $output = md5($image->output(true));
        $this->assertEquals('8f6a566615be521def3e3558e25567cb', $output);
    }

    public function testResizeAndCrop()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $image->resizeAndCrop(50, 50);
        $output = md5($image->output(true));
        $this->assertEquals('c4c5cf18f9235c19f83699865223ad66', $output);
    }

    public function testResizeAndCropByHeight()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $image->resizeAndCrop(50, 100);
        $output = md5($image->output(true));
        $this->assertEquals('bacd8b29e86935bc234d95ea634ea1a8', $output);
    }

    public function testResizeAndCropTransparentPng()
    {
        $path = 'tests/_data/transparentpng.png';
        $image = new Image();
        $image->load($path);
        $image->resizeAndCrop(50, 50);
        $output = md5($image->output(true));
        $this->assertEquals('cde91d966111091c172f10c8bc0244e5', $output);
    }
}