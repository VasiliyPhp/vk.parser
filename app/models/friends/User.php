<?php

namespace app\models\friends;
use yii;

class User extends \app\common\ActiveArray implements \yii\web\IdentityInterface
{
    public $index;
    public $login;
    public $is_admin = 0;
    public $password;
    public $authKey;
    public $accessToken;
    public $antigate_key;

		public static function fileName(){
			return 'friends.user.dat';
		}
		
    public function login()
    {
        if ($this->validate() and ($user = self::findOne(['login'=>$this->login, 'password'=>$this->password]))) {
						return yii::$app->user->login($user,  3600*24*30*12*5);
        }
				$this->addError('login', 'login or password is incorrect');
				$this->addError('password', 'login or password is incorrect');
        return false;
    }
	
		
		public function logout(){
			
			yii::$app->user->logout();
			
		}
		
    public function rules()
    {
        return [
            [ ['login', 'password'], 'required'],
						[['index', 'is_admin'], 'integer'],
						['antigate_key', 'safe'],
       ];
    }
		
		public function getIsAdmin(){
			return $this->login === 'admin';
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












