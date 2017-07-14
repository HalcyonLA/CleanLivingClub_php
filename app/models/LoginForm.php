<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\query\Users as UsersQuery;
use app\models\Users as UsersModel;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;
    public $userInfo = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // email and password are both required
            [['email', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user) {
                $this->addError('email', 'Incorrect email.');
            }

            if ($user && !$user->validatePassword($this->password)) {
                $this->addError('password', 'Incorrect password.');
            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if($this->validate()) {
            $user = $this->getUser();
            if ($user->extendedLogin()) {
                return true;
            }
            $this->addError('status', 'Your profile was deleted.');
            return false;
        }

        return false;
    }

    /**
     * Finds user by [[email]]
     *
     * @return Users|null
     */
    public function getUser()
    {
        if($this->userInfo === false) {
            $this->userInfo = UsersQuery::findByEmail($this->email);
        }

        return $this->userInfo;
    }
}
