<?php

use Codeception\Test\Unit;
use Del\Image;
use Del\Image\Strategy\GifStrategy;
use Del\Image\Strategy\JpegStrategy;

class ImageTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     *
     */
    public function testLoadJpgInConstructor()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image($path);
        $output = $image->output(true);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testLoadJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image();
        $image->load($path);
        $output = $image->output(true);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testLoadPng()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $output = md5($image->output(true));
        $this->assertEquals('aa9783d7b949216b39d297836d772c47', $output);
    }


    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testLoadGif()
    {
        $path = 'tests/_data/superman.gif';
        $image = new Image();
        $image->load($path);
        $output = md5($image->output(true));
        $this->assertEquals('ddc1d414f51592bcdc695fb53e6f304e', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testLoadThrowsException()
    {
        $this->expectException('Del\Image\Exception\NotFoundException');
        $path = 'this/is/the/road/to/nowhere';
        $image = new Image();
        $image->load($path);
    }

    /**
     * @throws Image\Exception\NotFoundException
     * @throws Image\Exception\NothingLoadedException
     */
    public function testGetHeaderForJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image();
        $image->load($path);
        $contentType = $image->getHeader();
        $this->assertEquals('image/jpeg', $contentType);
    }

    /**
     * @throws Image\Exception\NotFoundException
     * @throws Image\Exception\NothingLoadedException
     */
    public function testGetHeaderForPng()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $contentType = $image->getHeader();
        $this->assertEquals('image/png', $contentType);
    }

    /**
     * @throws Image\Exception\NotFoundException
     * @throws Image\Exception\NothingLoadedException
     */
    public function testGetHeaderForGif()
    {
        $path = 'tests/_data/superman.gif';
        $image = new Image();
        $image->load($path);
        $contentType = $image->getHeader();
        $this->assertEquals('image/gif', $contentType);
    }

    /**
     * @throws Image\Exception\NothingLoadedException
     */
    public function testGetHeaderThrowsException()
    {
        $this->expectException('Del\Image\Exception\NothingLoadedException');
        $image = new Image();
        $image->getHeader();
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
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
        unlink($savePath);
        $this->assertEquals('ddc1d414f51592bcdc695fb53e6f304e', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testSaveJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $savePath = 'tests/_data/sonsofanarchy2.jpg';
        $image = new Image();
        $image->load($path);
        $image->save($savePath, 777);
        $this->assertTrue(file_exists($savePath));
        $image->load($savePath);
        $output = $image->output(true);
        unlink($savePath);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
    }


    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testSetStrategy()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image($path);
        $reflection = new ReflectionClass(Image::class);
        $property = $reflection->getProperty('strategy');
        $property->setAccessible(true);
        $strategy = $property->getValue($image);
        $this->assertInstanceOf(JpegStrategy::class, $strategy);
        $image->setImageStrategy(new GifStrategy());
        $strategy = $property->getValue($image);
        $this->assertInstanceOf(GifStrategy::class, $strategy);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
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
        unlink($savePath);
        $this->assertEquals('aa9783d7b949216b39d297836d772c47', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testGetWidth()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $width = $image->getWidth();
        $this->assertEquals(233, $width);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testGetHeight()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $height = $image->getHeight();
        $this->assertEquals(238, $height);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeToWidth()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->resizeToWidth(100);
        $output = md5($image->output(true));
        $this->assertEquals('4c35a3f2a089531333568afa1b5daaac', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeJpg()
    {
        $path = 'tests/_data/sonsofanarchy.jpg';
        $image = new Image();
        $image->load($path);
        $image->resizeToWidth(100);
        $output = $image->output(true);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeGif()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $image->resizeToWidth(100);
        $output = md5($image->output(true));
        $this->assertEquals('e3f0ddc390f9efc6167403e96dfc50aa', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeToHeight()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->resizeToHeight(100);
        $output = md5($image->output(true));
        $this->assertEquals('728bb56bb00dbd7ee64b24258124e267', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testScale()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->scale(50);
        $output = md5($image->output(true));
        $this->assertEquals('1ad91304c4aaffce7497f765d21c7645', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testCrop()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $image->crop(50, 50);
        $output = md5($image->output(true));
        $this->assertEquals('8f6a566615be521def3e3558e25567cb', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCrop()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $image->resizeAndCrop(50, 50);
        $output = md5($image->output(true));
        $this->assertEquals('67ac4c5c1316ee0202526c069094fe9f', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCropByHeight()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $image->resizeAndCrop(50, 100);
        $output = md5($image->output(true));
        $this->assertEquals('bd8f12cb62d50d1052f27ca83b39ee79', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCropByWidth()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $image->resizeAndCrop(100, 50);
        $output = md5($image->output(true));
        $this->assertEquals('68222f434214aa6b095cd156f50293b7', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
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