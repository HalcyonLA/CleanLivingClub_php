<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 08.06.16
 * Time: 15:52
 */

namespace app\components;
use yii\base\BootstrapInterface;
use app\models\Users;

class AppBootstrap implements BootstrapInterface{

    public function bootstrap($app){
//        $app->user->on(Users::EVENT_BEFORE_LOGIN,['app\models\Users', 'beforeLogin']);
//        $app->user->on(Users::EVENT_AFTER_LOGIN,['app\models\Users', 'afterLogin']);
//        $app->user->on(Users::EVENT_BEFORE_LOGOUT,['app\models\Users', 'beforeLogout']);
    }
}