<?php

namespace app\actions\friends;
use yii;

class DownloadBlack extends yii\base\Action{
	public function run(){
		$list = array_map(function($item){
				return trim($item['black']);
			},
			\app\models\friends\BlackList::findAll());
		return yii::$app->response->sendContentAsFile(implode("\n", $list), 'ignor.txt');
  }
}











