<?php

namespace app\actions\friends;
use app\models\friends\User;
use yii;

class CreateAdminAccaunt extends yii\base\Action{
	
	
	public function run(){
		$issetUserRecords = User::getIssetRecords();
		$user = new User();
		if($user->load(yii::$app->request->post()) and $user->validate()){
			$user->is_admin = 1;
			$user->save();
			yii::$app->user->login($user, 3600*24*30*12*5);
			return $this->controller->goHome();
		}	
		// exit(print_r($user));

		$createAdmin = true;
		return $this->controller->render('login', compact('createAdmin', 'user'));
	}
	
}