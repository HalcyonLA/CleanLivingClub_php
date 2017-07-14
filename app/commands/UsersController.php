<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Messages as CMessages;

use app\models\Users;
use yii\console\Controller;
use Yii;
use yii\helpers\VarDumper;


class UsersController extends Controller
{
    public function actionIndex()
    {

    }

    public function actionUpdateUsersInfo()
    {
        $users = Users::find()->all();

        /** @var Users $user */
        foreach ($users as $user) {
            echo "User ", $user->id, " updating \n";
            $user->updateFriendsRequestsCount();
        }
    }
}
