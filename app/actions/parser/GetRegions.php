<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class GetRegions extends yii\base\Action{
	
	public function run($country = 1){
		session_write_close();
		$GroupParser = new \app\models\parser\GroupParser();
		yii::$app->response->format = yii\web\Response::FORMAT_JSON;
		return $cities = $GroupParser->getRegions($country);
	}
	
}