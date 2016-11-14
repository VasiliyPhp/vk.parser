<?php

namespace app\models;
use yii;

class User extends \app\common\ActiveArray implements \yii\web\IdentityInterface
{
    public $index;
    public $vk_user_id;
    public $vk_access_token;
    public $vk_first_name;
    public $vk_last_name;
    public $authKey;
    public $accessToken;

		static function fileName(){
			return 'user.dat';
		}
		
    public function login()
    {
        if ($this->validate()) {
            return yii::$app->user->login($this,  3600*24*30*12*5);
        }
        return false;
    }
		
		public function logout(){
			
			$this->delete();
			yii::$app->user->logout();
			
		}
		
    public function rules()
    {
        return [
            [ ['authKey', 'accessToken', 'vk_access_token', 'vk_user_id', 'vk_last_name', 'vk_first_name'], 'required'],
            [ ['index'], 'integer'],
       ];
    }
		
    /**
     * @inheritdoc
     */
    public static function findIdentity($index)
    {
        return self::findOne(compact('index'));
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($accessToken, $type = null)
    {
       return self::findOne(compact('accessToken'));
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->index;
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

}












