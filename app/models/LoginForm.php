<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $email;

    public $password;

    public $rememberMe = true;

    public function rules()
    {
        return [
            // username and password are both required
            [['email', 'password'], 'required'],
            // password is validated by validatePassword()
            [['password'], 'validatePassword'],
            // rememberMe must be a boolean value
            [['rememberMe'], 'boolean'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        $user = User::findByEmail($this->email);
        if (!$user) {
            $user = Yii::$app->collectors['identityProviders']->attemptCreate($this->email, $this->password);
        }
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = User::findByEmail($this->email);
            Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);
            return true;
        } else {
            return false;
        }
    }
}
