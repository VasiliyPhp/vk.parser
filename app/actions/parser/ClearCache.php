<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class ClearCache extends yii\base\Action{
	
	public function run(){
		session_write_close();
		$cache = yii::$app->cache;
		/**/
		$cache->flush();
		yii::$app->response->format = yii\web\Response::FORMAT_JSON;
		return ['yes'];
	}
	
}