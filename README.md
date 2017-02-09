Image resize for Yii2
========================

A Yii2 component for resizing images and store it in a cache folder

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

* Either run

```
php composer require "fgh151/yii2-image-resize" "*"
```
or add

```json
"fgh151/yii2-image-resize" : "*"
```

to the require section of your application's `composer.json` file.

* Add a new component in `components` section of your application's configuration file, for example:

```php
'components' => [
    'imageresize' => [
		'class' => 'fgh151\imageresize\ImageResize',
		//path web root 
        'cachePath' => '@frontend/web',
        //path where to store thumbs
        'cacheFolder' => 'upload/thumb',
		//use filename (seo friendly) for resized images else use a hash
		'useFilename' => true,
		//show full url (for example in case of a API)
		'absoluteUrl' => false,
	],
],
```

Usage
-----

If you want to get a image url:

```php
/*
 * $sImageFilePath_id: (required) path to file
 * $width/$height: (required) width height of the image
 * $mode: "outbound" or "inset" 
 * $$quality: (1 - 100)
 * $chosenFileName: if config -> components -> imageresize -> useFilename is true? its an option to give a custom name else use original file name
 */
\Yii::$app->imageresize->getUrl($imageFile, $width, $height, $mode, $quality, $chosenFileName);
```
