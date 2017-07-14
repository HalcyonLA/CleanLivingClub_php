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
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class VideoProcessor extends Component
{
    public $defaultDir;
    private $_targetDir;

    private function _checkDir($userId)
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . $this->defaultDir . 'id' . $userId;
        if (!is_dir($dir)) {
            if(mkdir($dir, 0777)) {
                $this->_targetDir = $dir;
                return true;
            } else {
                return false;
            }
        }
        $this->_targetDir = $dir;
        return true;

    }

    public function save($userId, $path) {
        if (!$this->_checkDir($userId)) return '';
        $type = explode('/', mime_content_type($path));
        $filename = $userId . '_' . md5_file($path) . '_' . md5(microtime()) . '.' . end($type);

        if (move_uploaded_file($path, $this->_targetDir . '/' . $filename)) {

            $file = $this->_targetDir . '/' . $filename;

            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($file);

            $video
                ->frame(TimeCode::fromSeconds(1))
                ->save($file . '.jpg');

            return [
                'image' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $file . '.jpg'),
                'video' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $file)
            ];
        } else return [
            'image' => '',
            'video' => ''
        ];
    }
}