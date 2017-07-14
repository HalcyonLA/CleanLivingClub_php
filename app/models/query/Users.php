<?php

namespace app\models\query;

use yii\db\ActiveQuery;
use \app\models\Users as UsersModel;
/**
 * This is the ActiveQuery class for [[\app\models\Users]].
 *
 * @see \app\models\Users
 */
class Users extends ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Users[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Users|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $email
     * @return UsersModel|array|null
     */
    public static function findByEmail($email)
    {
        return UsersModel::find()
            ->where('email = :e', [':e' => $email])
            ->one();
    }

    /**
     * @param $facebookInfo
     * @return UsersModel|array|null
     */
    public static function findByFacebookId($facebookInfo)
    {
        return UsersModel::find()
            ->where('email = :f', [':f' => $facebookInfo['email']])
            ->one();
    }

    /**
     * @param $id
     * @return UsersModel|array|null
     */
    public static function findById($id)
    {
        return UsersModel::find()
            ->where('id = :i', [':i' => $id])
            ->one();
    }

    public function activateUser(UsersModel $model)
    {
        return $this->_changeStatus($model, UsersModel::STATUS_ACTIVE);
    }

    public function deactivateUser(UsersModel $model)
    {
        return $this->_changeStatus($model, UsersModel::STATUS_INACTIVE);
    }

    public function deleteUser(UsersModel $model)
    {
        return $this->_changeStatus($model, UsersModel::STATUS_DELETED);
    }

    private function _changeStatus(UsersModel $model, $status) {
        $model->status = $status;
        $model->save();
        return $model;
    }

    public function batchFindByEmail($emails)
    {
        $list = $this->where(['in', 'email', $emails])
            ->andWhere('status = :status', [':status' => UsersModel::STATUS_ACTIVE])
            ->all();
        foreach ($list as &$l) {
            $l = $l->getAttributes();
            unset($l['password']);
            unset($l['status']);
            if ($l['photo'] === null) {
                $l['photo'] = '';
            }
            if ($l['photo_thumbnail'] === null) {
                $l['photo_thumbnail'] = '';
            }
        }
        return $list;
    }

    public function batchFindByFacebookId($ids)
    {
        $list = $this->where(['in', 'facebookId', $ids])
            ->andWhere('status = :status', [':status' => UsersModel::STATUS_ACTIVE])
            ->all();
        foreach ($list as &$l) {
            $l = $l->getAttributes();
            unset($l['password']);
            unset($l['status']);
            if ($l['photo'] === null) {
                $l['photo'] = '';
            }
            if ($l['photo_thumbnail'] === null) {
                $l['photo_thumbnail'] = '';
            }
        }
        return $list;
    }
}