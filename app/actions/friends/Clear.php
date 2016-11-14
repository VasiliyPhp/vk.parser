<?php

namespace app\actions\friends;
use app\models\friends\People;
use yii;

class Clear extends yii\base\Action{
	
	public function run(){
		
		People::deleteAll();
		
		return $this->controller->goHome();
		
  }
	
}











