<?php

namespace app\actions\friends;
use app\models\friends\User;
use yii;

class Login extends yii\base\Action{
	
	public function run(){
		$issetUserRecords = User::getIssetRecords();
		if(!$issetUserRecords){
			return $this->controller->redirect(['create-admin-accaunt']);
		}
		
		$user = new User();
		
		if($user->load(yii::$app->request->post()) and $user->login()){
			return $this->controller->goHome();
		}
		
		return $this->controller->render('login', compact('user'));
	}
	
}