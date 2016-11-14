<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $vk_token;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
           ['vk_token', 'required'],
           ['vk_token', 'url'],
        ];
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    public function attributeLabels()
    {
        return ['vk_token'=>'Токен доступа'];
    }
}
