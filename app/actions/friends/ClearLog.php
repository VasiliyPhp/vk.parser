<?php

namespace app\actions\friends;
use yii;

class ClearLog extends yii\base\Action{
	public function run(){
		file_put_contents($this->controller->log_file, '');
		return $this->controller->goHome();
  }
}











