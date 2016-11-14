<?php

namespace app\actions\friends;
use app\models\friends\User;
use yii;

class CreateUser extends yii\base\Action{
	
	public function run(){
		$user = new User();
		if($user->load(yii::$app->request->post()) and $user->validate()){
			$user->save();
		}
		$create_user = true;
		$user_list = User::findAll();
		unset($user_list[yii::$app->user->identity->index]);
		$user_list = new yii\data\ArrayDataProvider([
		  'allModels'=>$user_list,
			'pagination'=>[
			  'pageSize'=>10,
			],
		]);
		return $this->controller->render('login', compact('user_list', 'create_user', 'user'));
	}
	
}