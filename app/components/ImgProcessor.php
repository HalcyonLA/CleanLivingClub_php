<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01.03.16
 * Time: 16:25
 */

namespace app\components;


use yii\base\Component;
use Yii;
use yii\base\Exception;

class ImgProcessor extends Component
{
    const IMG_BIG = 'big';
    const IMG_SMALL = 'small';
    const IMG_TYMESYNC_SMALL = 'img_tymesync_small';

    public $bigWidth;
    public $smallWidth;
    public $timesyncThumbWith;
    public $defaultDir;

    private $_userId;
    private $_imagePath;
    private $_imageInfo = false;
    private $_targetDir;
    private $_exifData = '';

    private function _measure()
    {
        list($width, $height) = getimagesize($this->_imagePath);
        if (!isset($width) || $width == 0 || !isset($height) || $height == 0) {
            return false;
        }
        return [
            'w' => $width,
            'h' => $height
        ];
    }

    private function _imgInfo()
    {
        $type = exif_imagetype($this->_imagePath);
        if ($type !== 2 && $type !== 3) {
            $this->_imageInfo = false;
            return;
        }
        $imagick = new \Imagick($this->_imagePath);


        $orientation = $imagick->getImageOrientation();
        switch($orientation) {
            case \Imagick::ORIENTATION_BOTTOMRIGHT:
                $imagick->rotateimage("#000", 180); // rotate 180 degrees
                break;

            case \Imagick::ORIENTATION_RIGHTTOP:
                $imagick->rotateimage("#000", 90); // rotate 90 degrees CW
                break;

            case \Imagick::ORIENTATION_LEFTBOTTOM:
                $imagick->rotateimage("#000", -90); // rotate 90 degrees CCW
                break;
            case \Imagick::ORIENTATION_TOPRIGHT:
                $imagick->rotateimage("#000", 90); // rotate 90 degrees CCW
                break;
        }
        $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
        $imagick->writeImage($this->_imagePath);

        $size = $this->_measure();
        if (!$size) {
            $this->_imageInfo = false;
            return;
        }

        $this->_imageInfo = [
            'path' => $this->_imagePath,
            'type' => $type,
            'width' => $size['w'],
            'height' => $size['h'],
        ];
    }

    private function _getNewDimensions($w, $h, $size = self::IMG_BIG)
    {

        $newWidth = $this->bigWidth;
        if ($size == self::IMG_SMALL) {
            $newWidth = $this->smallWidth;
        }

        if ($size == self::IMG_TYMESYNC_SMALL) {
            $newWidth = $this->timesyncThumbWith;
        }

        if ($w <= $newWidth) return false;

        $newHeight = floor($this->_imageInfo['height'] * $newWidth / $this->_imageInfo['height']);

        return [
            'w' => $newWidth,
            'h' => $newHeight,
        ];

    }

    private function _createRamImage()
    {
        if ($this->_imageInfo['type'] == IMAGETYPE_JPEG) {
            $file = imagecreatefromjpeg($this->_imageInfo['path']);
        }
        if ($this->_imageInfo['type'] == IMAGETYPE_PNG) {
            $file = imagecreatefrompng($this->_imageInfo['path']);
        }

        if (!$file) {
            return false;
        }
        return $file;
    }

    private function _saveImage($size = self::IMG_BIG)
    {
        if (!$this->_checkUserDirectory()) return false;

        $extension = '.jpg';

        if ($this->_imageInfo['type'] == 3) {
            $extension = '.png';
        }

        $img_name = $this->_userId . '_' . md5(microtime()) . $extension;
        $img_path = $this->_targetDir . '/' . $img_name;

        $ramImage = $this->_createRamImage();
        if (!$ramImage) return false;


        if ($size == self::IMG_BIG) {
            $newSize = $this->_getNewDimensions($this->_imageInfo['width'], $this->_imageInfo['height']);
        } else {
            $newSize = $this->_getNewDimensions($this->_imageInfo['width'], $this->_imageInfo['height'], self::IMG_SMALL);
        }

        if (!$newSize) {

            if(move_uploaded_file($this->_imageInfo['path'], $img_path)) {
                return $img_path;
            } else {
                return false;
            }
        }

        $image_p = imagecreatetruecolor($newSize["w"], $newSize["h"]);
        $th = imagecopyresampled($image_p, $ramImage, 0, 0, 0, 0, $newSize["w"], $newSize["h"], $this->_imageInfo['width'], $this->_imageInfo['height']);

        if ($this->_imageInfo['type'] == IMAGETYPE_PNG) {
            $f = imagepng($image_p, $img_path, 0);
        }
        if ($this->_imageInfo['type'] == IMAGETYPE_JPEG) {
            $f = imagejpeg($image_p, $img_path, 100);
        }

        imagedestroy($ramImage);
        imagedestroy($image_p);

        if (!is_file($img_path)) {
            return false;
        }

        return $img_path;

    }

    private function _checkUserDirectory()
    {
        $root = $_SERVER['DOCUMENT_ROOT'] . $this->defaultDir . 'id' . $this->_userId;

        if (!is_dir($root)) {
            mkdir($root, 0777);
        }

        if (!is_dir($root)) return false;
        $this->_targetDir = $root;
        return true;
    }

    public function getBigImage($path, $userId)
    {
        $this->_userId = $userId;
        $this->_imagePath = $path;
        $this->_imgInfo();

        if (!$this->_imageInfo) return false;

        return $this->_saveImage();
    }

    public function getSmallImage($path, $userId)
    {
        $this->_userId = $userId;
        $this->_imagePath = $path;
        $this->_imgInfo();
        if (!$this->_imageInfo) return false;
        return $this->_saveImage(self::IMG_SMALL);
    }

    private function _calculateSizeByWidth($newWidth) {
        if ($this->_imageInfo['width'] <= $newWidth) {
            return [
                'width' => $this->_imageInfo['width'],
                'height' => $this->_imageInfo['height'],
                'need_resize' => false
            ];
        }

        $newHeight = floor($this->_imageInfo['height'] * $newWidth / $this->_imageInfo['width']);

        return [
            'width' => $newWidth,
            'height' => $newHeight,
            'need_resize' => true
        ];
    }

    private function _resizeAndSave($width, $height, $target)
    {


        $ramImage = $this->_createRamImage();
        if (!$ramImage) return false;

        $image_p = imagecreatetruecolor($width, $height);
        imagecopyresampled($image_p, $ramImage, 0, 0, 0, 0, $width, $height, $this->_imageInfo['width'], $this->_imageInfo['height']);

        if ($this->_imageInfo['type'] == IMAGETYPE_PNG) {
            imagepng($image_p, $target, 0);
        }
        if ($this->_imageInfo['type'] == IMAGETYPE_JPEG) {
            imagejpeg($image_p, $target, 100);
        }

        imagedestroy($ramImage);
        imagedestroy($image_p);

        if (!is_file($target)) {
            return false;
        }

        return $target;

    }

    public function fitByWidth($userId, $path, $width = 100) {
        if (empty($path) || !is_file($path)) {
            return false;
        }
        $this->_userId = $userId;
        $this->_imagePath = $path;
        $this->_imgInfo();

        $extension = '.jpg';

        if ($this->_imageInfo['type'] == 3) {
            $extension = '.png';
        }

        if (!$this->_checkUserDirectory()) return false;
        $img_name = $this->_userId . '_' . md5(microtime()) . $extension;
        $img_path = $this->_targetDir . '/' . $img_name;

        $newDimensions = $this->_calculateSizeByWidth($width);

        if ($newDimensions['need_resize']) {
            $path = $this->_resizeAndSave($newDimensions['width'], $newDimensions['height'], $img_path);
        } else {
            if (move_uploaded_file($this->_imageInfo['path'], $img_path)) {
                $path = $img_path;
            }
        }

        if (is_file($path)) {
            return $path;
        }
        return false;
    }
}