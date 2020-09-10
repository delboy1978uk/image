<?php

use Codeception\Test\Unit;
use Del\Image;
use Del\Image\Strategy\GifStrategy;
use Del\Image\Strategy\JpegStrategy;
use Del\Image\Strategy\WebPStrategy;

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
     * @throws Image\Exception\NothingLoadedException
     */
    public function testLoadWebpInConstructor()
    {
        $path = 'tests/_data/landscape.webp';
        $image = new Image($path);
        $output = $image->output(true);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
        $this->assertEquals('image/webp', $image->getHeader());
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
        $output = $image->output(true);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
    }


    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testLoadGif()
    {
        $path = 'tests/_data/superman.gif';
        $image = new Image();
        $image->load($path);
        $output = $image->output(true);
        $image->destroy();
        $this->assertTrue(strlen($output) > 0);
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
        $checksum = md5($image->output(true));
        $image->save($savePath);
        $this->assertTrue(file_exists($savePath));
        $image->load($savePath);
        $output = md5($image->output(true));
        unlink($savePath);
        $this->assertEquals($checksum, $output);
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
    public function testSaveWebp()
    {
        $path = 'tests/_data/landscape.webp';
        $savePath = 'tests/_data/landscape2.webp';
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
        $checksum = md5($image->output(true));
        $image->save($savePath);
        $this->assertTrue(file_exists($savePath));
        $image->load($savePath);
        $output = md5($image->output(true));
        unlink($savePath);
        $this->assertEquals($checksum, $output);
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
        $checksum = md5($image->output(true));
        $image->resizeToWidth(100);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
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
        $checksum = md5($image->output(true));
        $image->resizeToWidth(100);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeToHeight()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->resizeToHeight(100);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testScale()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->scale(50);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testCrop()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->crop(50, 50);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCrop()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->resizeAndCrop(50, 50);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCropByHeight()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->resizeAndCrop(50, 100);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCropByWidth()
    {
        $path = 'tests/_data/transparentgif.gif';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->resizeAndCrop(100, 50);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testResizeAndCropTransparentPng()
    {
        $path = 'tests/_data/transparentpng.png';
        $image = new Image();
        $image->load($path);
        $checksum = md5($image->output(true));
        $image->resizeAndCrop(50, 50);
        $output = md5($image->output(true));
        $this->assertNotEquals($checksum, $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testBase64String()
    {
        $path = 'tests/_data/troll.png';
        $image = new Image();
        $image->load($path);
        $output = $image->outputBase64Src();
        $this->assertEquals('data:image/png;base64,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', $output);
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testConvert()
    {
        $path = 'tests/_data/transparentpng.png';
        $image = new Image();
        $image->load($path);
        $image->setImageStrategy(new WebPStrategy());
        $image->save('tests/_data/transparentpng.webp');
        $this->assertTrue(file_exists('tests/_data/transparentpng.webp'));
        unlink('tests/_data/transparentpng.webp');
    }

    /**
     * @throws Image\Exception\NotFoundException
     */
    public function testExtensions()
    {
        $strategy = new WebPStrategy();
        $this->assertEquals('webp', $strategy->getFileExtension());
        $strategy = new GifStrategy();
        $this->assertEquals('gif', $strategy->getFileExtension());
        $strategy = new JpegStrategy();
        $this->assertEquals('jpg', $strategy->getFileExtension());
        $strategy = new Image\Strategy\PngStrategy();
        $this->assertEquals('png', $strategy->getFileExtension());
    }
}