<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01.03.16
 * Time: 16:24
 */

namespace app\components;


use yii\base\Component;
use Yii;

class FacebookPicture extends Component
{
    public $bigWidth;
    public $smallWidth;
    private $_facebookId;
    private $_requestUrl;

    public function setFacebookId($id)
    {
        $this->_facebookId = $id;
        return $this;
    }

    private function _makeUrl($width)
    {
        $this->_requestUrl = "http://graph.facebook.com/" . $this->_facebookId . "/picture?width=" . $width;
        return $this;
    }

    private function _send()
    {
        if( $curl = curl_init() ) {
            curl_setopt($curl, CURLOPT_URL, $this->_requestUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_exec($curl);
            $out = curl_getinfo($curl);
            $url = $out['redirect_url'];
            curl_close($curl);
            return $url;
        }
        return null;

    }

    public function getBigImageUrl()
    {

        return $this->_makeUrl($this->bigWidth)->_send();
    }

    public function getSmallImageUrl()
    {
        return $this->_makeUrl($this->smallWidth)->_send();
    }

}