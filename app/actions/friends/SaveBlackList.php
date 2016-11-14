<?php

namespace app\actions\friends;
use yii;

class SaveBlackList extends yii\base\Action{
	public function run(){
		$list = new \app\models\friends\BlackListForm;
		if($list->load(yii::$app->request->post())){
			$array = array_map(function($item){
				return ['black'=>trim($item)];
			}, explode("\n", file_get_contents($list->black->tempName)));
			\app\models\friends\BlackList::saveMultiple($array);
		}
		return $this->controller->goBack();
  }
}











