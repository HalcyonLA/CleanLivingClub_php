<?php

namespace app\models;

use Yii;
use app\models\query\Users as UsersQuery;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $facebookId
 * @property string $firstName
 * @property string $lastName
 * @property string $photo
 * @property string $photoThumbnail
 * @property string $status
 * @property string $createAt
 *
 * @property string $facebookToken
 */
class Users extends ActiveRecord implements IdentityInterface
{
    public $facebookToken;
    public $authKey;
    public $accessToken;

    const SESSION_TIME = 5184000;
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_DELETED = 'DELETED';


    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required', 'except' => ['facebookLogin', 'facebookUpdate', 'update']],
            [['facebookToken'], 'required', 'on' => 'facebookLogin'],
            [['createAt'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['firstName', 'lastName'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['password'], 'string', 'max' => 64],
            [['facebookId'], 'string', 'max' => 30],
            [['email'], 'unique', 'targetAttribute' => 'email'],
            [['facebookId'], 'unique', 'targetAttribute' => 'facebookId'],
            [['facebookToken'], 'safe'],
            [['photo', 'photoThumbnail'], 'image', 'extensions' => 'png, jpg',
                'minWidth' => 150, 'maxWidth' => 3000,
                'minHeight' => 150, 'maxHeight' => 3000,
            ],
        ];
    }

    public function selfRemove()
    {
        $this->status = self::STATUS_DELETED;
        $this->update(true, ['status']);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'status' => Yii::t('app', 'Status'),
            'facebookId' => Yii::t('app', 'Facebook ID'),
            'firstName' => Yii::t('app', 'Firstname'),
            'lastName' => Yii::t('app', 'Lastname'),
            'photo' => Yii::t('app', 'Photo'),
            'photoThumbnail' => Yii::t('app', 'Photo thumbnail'),
            'createAt' => Yii::t('app', 'Create At'),
        ];
    }




    /**
     * @param array $emails
     * @return Users[]|array
     */
    public static function findByEmails ($emails)
    {
        return self::find()->where(['in', 'email', $emails])->all();
    }

    /**
     * @param array $facebookIds
     * @return Users[]|array
     */
    public static function findByFacebookIds ($facebookIds)
    {
        return self::find()->where(['in', 'facebookId', $facebookIds])->all();
    }

    /**
     * @return $this|bool|Users
     */
    public function facebookLogin()
    {
        $facebookInfo = Yii::$app->facebook->login($this->facebookToken);
        if (isset($facebookInfo['facebookId'])) {
            /** @var Users $user */
            $user = UsersQuery::findByFacebookId($facebookInfo);

            if (is_null($user)) {
                $this->attributes = $facebookInfo;
                $this->photo = Yii::$app->facebookPicture->setFacebookId($facebookInfo['facebookId'])->getBigImageUrl();
                $this->photo_thumbnail = Yii::$app->facebookPicture->setFacebookId($facebookInfo['facebookId'])->getSmallImageUrl();
                $this->firstname = $facebookInfo['first_name'];
                $this->lastname = $facebookInfo['last_name'];
                $this->facebookId = $facebookInfo['facebookId'];

                if ($this->validate()) {
                    $this->save();
                    $this->extendedLogin();
                    return $this->getAttributes();
                }
            } else {
                $user->setScenario('facebookLogin');
                $user->facebookToken = $this->facebookToken;
                $this->facebookId = $facebookInfo['facebookId'];
                if ($user->email == '' && isset($facebookInfo['email'])) {
                    $user->email = $facebookInfo['email'];
                }

                if (is_null($user->photo) || empty($user->photo)) {
                    $user->photo = Yii::$app->facebookPicture->setFacebookId($user->facebookId)->getBigImageUrl();
                    $user->photo_thumbnail = Yii::$app->facebookPicture->setFacebookId($user->facebookId)->getSmallImageUrl();
                }
                if ($user->validate()) {
                    $user->save();
                    $user->extendedLogin();
                    return $user->getAttributes();
                }
            }
        } else {
            $this->addError('facebookToken', 'Token fail');
            return false;
        }
    }

    public function extendedLogin()
    {
        if ($this->status !== self::STATUS_ACTIVE) return false;
        Yii::$app->user->login($this, Users::SESSION_TIME);
        return $this;
    }

    /**
     * @inheritdoc
     * @return \app\models\query\Users the active query used by this AR class.
     */
    public static function find()
    {
        return new UsersQuery(get_called_class());
    }

    public static function activity()
    {
        return new UsersQuery(get_called_class());
    }

    /**
     * Create session data
     */
    public function createAuthenticate()
    {
        Yii::$app->session->set('userId', $this->id);
        Yii::$app->session->set('email', $this->email);
    }

    /**
     * Validates password
     *
     * @param  string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === Users::hashPassword($password);
    }

    /**
     * @param $password
     * @return string
     */
    public function hashPassword($password)
    {
        return md5(PASS_SALT . $password);
    }

    public static function findByFbId($id) {
        $user = self::find()->where('facebookId = :fbid', [':fbid' => $id])->one();
        if (is_null($user)) return false;
        return $user;
    }

    /**
     * @param Users $user
     * @param Users $me
     * @return array|boolean
     */
    public static function renderOne($user, $me = null)
    {
        if (NULL === $user) {
            return false;
        }
        $data = $user->getAttributes(null, ['password', 'status']);
        return $data;

    }
}
