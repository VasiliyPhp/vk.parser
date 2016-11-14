<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class GroupParser extends yii\base\Action{
	
	public function run(){
		$GroupParser = new \app\models\parser\GroupParser();
		$GroupParser->load(yii::$app->request->post());
		if($GroupParser->validate()){
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$params = [
			  'city_id'=>$GroupParser->city,
			  'country_id'=>$GroupParser->country,
			  'closed'=>$GroupParser->closed,
				'q'=>$GroupParser->queries,
				'count'=>1000
			];
			$result = [];
			$VK->bulkApi('groups.search', $params, $result);
			
		}
		return $this->controller->render('index', compact('GroupParser','result'));
	}
	
}