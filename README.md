# image
[![Latest Stable Version](https://poser.pugx.org/delboy1978uk/image/v/stable)](https://packagist.org/packages/delboy1978uk/image) [![Total Downloads](https://poser.pugx.org/delboy1978uk/image/downloads)](https://packagist.org/packages/delboy1978uk/image) [![Latest Unstable Version](https://poser.pugx.org/delboy1978uk/image/v/unstable)](https://packagist.org/packages/delboy1978uk/image) [![License](https://poser.pugx.org/delboy1978uk/image/license)](https://packagist.org/packages/delboy1978uk/image)<br />
[![Build Status](https://travis-ci.org/delboy1978uk/image.png?branch=master)](https://travis-ci.org/delboy1978uk/image) [![Code Coverage](https://scrutinizer-ci.com/g/delboy1978uk/image/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/delboy1978uk/image/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/delboy1978uk/image/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/delboy1978uk/image/?branch=master)<br />
A PHP image class utilising the `gd` library
## Installing
```composer require delboy1978uk/image```
## Usage
### Instantiation
You can pass a path name into the constructor, or you can use the `load()` method. Accepts `png`, `jpg`, or `gif` images.
```php
<?php

use Del\Image;

$image = new Image('/path/to/my.jpg');

// Or...
$image = new Image();
$image->load('/path/to/my.png');
```
### Available Methods
```php
<?php

use Del\Image;

$image = new Image('/path/to/my.jpg');
$image->crop($width, $height, 'center'); // Crops the image, also accepts left or right as 3rd arg
$image->destroy(); // remove loaded image in the class. Frees up any memory
$image->getHeader(); // returns image/jpeg or equivalent
$image->getHeight(); // returns height in pixels
$image->getWidth(); // returns width in pixels
$image->output(); // output to browser
$image->output(true); // passing true returns raw image data string
$image->outputBase64Src(); // for use here <img src="HERE" />
$image->resize($width, $height); // resize to the given dimensions
$image->resizeAndCrop($width, $height); // resize to the given dimensions, cropping top/bottom or sides
$image->save(); // Save the image
$image->save('/path/to/save.jpg', $permissions, $compression); // Save as a different image
$image->scale(50); // Scale image to a percentage
```