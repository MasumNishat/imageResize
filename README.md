image-resize-php
================

PHP library to resize image to desire file size by only one compression.

------------------

Warning
-----
This is a development package. I encourage not to use in production environment.

Please inform if any bug or security issue or any other problem found.

Any suggestion will be taken seriously. 

Setup
-----

This package is available through Packagist with the vendor and package identifier the same as this repo.

If using [Composer](https://getcomposer.org/), run following command:

```command
composer require "masum-nishat/image-resize-php":"dev-master@dev"
```

> Note: This library uses GD class which do not support resizing animated gif files

------------------

Resize
------

Only supported resizing param is according to file size. Image dimension will
be changed and final image will be under 250KB (Default). 

```php
imageResize::convert('image.jpg', 'image-converted.jpg');
//or
imageResize::convert('image.png', 'image-converted.png');
```

Let the extension detect automatically from mime type: 

```php
imageResize::convert('image.jpg', 'image-converted');
//or
imageResize::convert('image.png', 'image-converted');
```

Declare required maximum size:

```php
imageResize::$targetSize = 300000; //maximum 300KB

imageResize::convert('image.jpg', 'image-converted');
//or
imageResize::convert('image.png', 'image-converted');
```

This class creat unique temp directory and delete it after using.
To use custom temp directory and not delete temp components (useful 
for debugging):

```php
imageResize::$tempDir = 'path/to/temp/folder';

imageResize::convert('image.jpg', 'image-converted');
//or
imageResize::convert('image.png', 'image-converted');
```


Supported Image Types
-----------

- `IMAGETYPE_JPEG`
- `IMAGETYPE_PNG`
- `IMAGETYPE_GIF`


Quality
-------

Maximum quality is selected by default. 

Quality change param is not implemented yet;

Exceptions
--------

Exception handling not implemented yet.