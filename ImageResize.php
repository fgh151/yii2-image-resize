<?php

namespace fgh151\imageresize;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use yii\imagine\Image;

class ImageResize
{

    const IMAGE_OUTBOUND = ManipulatorInterface::THUMBNAIL_OUTBOUND;
    const IMAGE_INSET = ManipulatorInterface::THUMBNAIL_INSET;

    /** @var string $cachePath path alias relative with webroot where the cache files are kept */
    public $cachePath = '@frontend/upload';

    /** @var int $cacheExpire */
    public $cacheExpire = 0;

    /** @var int $imageQuality */
    public $imageQuality = 50;

    /** @var int $useFilename if true show filename in url */
    public $useFilename = true;

    /** @var int $absoluteUrl if true include domain in url */
    public $absoluteUrl = false;

    /** @var  string $cacheFolder folder to store thumb */
    public $cacheFolder = 'upload/thumb';

    /**
     * Creates and caches the image and returns URL from resized file.
     *
     * @param string $filePath to original file
     * @param integer $width
     * @param integer $height
     * @param string $mode
     * @param integer $quality (1 - 100 quality)
     * @param string $fileName (custom filename)
     * @return string
     * @throws InvalidParamException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function getUrl($filePath, $width, $height, $mode = 'outbound', $quality = null, $fileName = null)
    {
        //get original file
        $normalizePath = FileHelper::normalizePath(Yii::getAlias($filePath));
        //generate file
        $resizedFilePath = $this->generateImage($normalizePath, $width, $height, $mode, $quality, $fileName);
        //get resized file
        $normalizeResizedFilePath = FileHelper::normalizePath($resizedFilePath);
        $resizedFileName = pathinfo($normalizeResizedFilePath, PATHINFO_BASENAME);
        //get url
        $sFileUrl = $this->cacheFolder . '/' . substr($resizedFileName, 0, 2) . '/' . $resizedFileName;
        //return path

        return $sFileUrl;
    }

    /**
     * Creates and caches the image thumbnail and returns full path from thumbnail file.
     *
     * @param string $filePath to original file
     * @param integer $width
     * @param integer $height
     * @param string $mode
     * @param integer $quality (1 - 100 quality)
     * @param string $chosenFileName (custom filename)
     * @return string
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidParamException
     * @throws InvalidArgumentException
     */
    public function generateImage($filePath, $width, $height, $mode = 'outbound', $quality = null, $chosenFileName = null)
    {
        $filePath = FileHelper::normalizePath(Yii::getAlias($filePath));
        if (!is_file($filePath)) {
            throw new Exception("File $filePath doesn't exist");
        }

        //set resize mode
        $resizeMode = null;
        switch ($mode) {
            case 'outbound':
                $resizeMode = ImageResize::IMAGE_OUTBOUND;
                break;
            case 'inset':
                $resizeMode = ImageResize::IMAGE_INSET;
                break;
            default:
                throw new Exception('generateImage $mode is not valid choose for "outbound" or "inset"');
        }

        //create some vars
        $cachePath = Yii::getAlias($this->cachePath);
        //get file info
        $aFileInfo = pathinfo($filePath);
        //set default filename
        $sFileHash = md5($filePath . $width . $height . $resizeMode . filemtime($filePath));
        $imageFileName = null;
        //if $this->useFilename set to true? use seo friendly name
        if ($this->useFilename === true) {
            //set hash and default name
            $sFileHashShort = substr($sFileHash, 0, 6);
            $sFileName = $aFileInfo['filename'];
            //set chosen filename if $chosenFileName not null.
            if ($chosenFileName !== null) {
                $sFileName = preg_replace('/(\.\w+)$/', '', $chosenFileName);
            }
            //replace for seo friendly file name
            $sFilenameReplace = preg_replace("/[^\w\.\-]+/", '-', $sFileName);
            //set filename
            $imageFileName = $sFileHashShort . '_' . $sFilenameReplace;
            //else use file hash as filename
        } else {
            $imageFileName = $sFileHash;
        }

        $imageFileExt = '.' . $aFileInfo['extension'];
        $imageFilePath = $cachePath . DIRECTORY_SEPARATOR . $this->cacheFolder . DIRECTORY_SEPARATOR . substr($imageFileName, 0, 2);
        $imageFile = $imageFilePath . DIRECTORY_SEPARATOR . $imageFileName . $imageFileExt;
        if (file_exists($imageFile)) {
            if ($this->cacheExpire !== 0 && (time() - filemtime($imageFile)) > $this->cacheExpire) {
                unlink($imageFile);
            } else {
                return $imageFile;
            }
        }
        //if dir not exist create cache edir
        if (!is_dir($imageFilePath)) {
            FileHelper::createDirectory($imageFilePath, 0755);
        }
        //create image
        $box = new Box($width, $height);
        $image = Image::getImagine()->open($filePath);
        $image = $image->thumbnail($box, $resizeMode);

        $options = [
            'quality' => $quality === null ? $this->imageQuality : $quality
        ];
        $image->save($imageFile, $options);
        return $imageFile;
    }

    /**
     * Clear cache directory.
     *
     * @return bool
     * @throws InvalidParamException
     * @throws ErrorException
     * @throws Exception
     */
    public function clearCache()
    {
        $cachePath = Yii::getAlias($this->cachePath) . DIRECTORY_SEPARATOR . $this->cacheFolder;
        FileHelper::removeDirectory($cachePath);
        return FileHelper::createDirectory($cachePath, 0755);
    }

}
