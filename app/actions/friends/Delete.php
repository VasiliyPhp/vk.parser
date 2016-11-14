<?php

namespace app\actions\friends;
use app\models\friends\User;
use yii;

class Delete extends yii\base\Action{
	
	public function run($model, $index){
		// $model = call_user_func([ucfirst($model), 'findOne'], $index);
		$model = '\app\models\friends\\' . ucfirst($model);
		// $model = $model == 'user' ? '\app\models\friends\\' . ucfirst($model) : 
		                            // '\app\models\\' . ucfirst($model);
		$model = $model::findOne(compact('index'));
		// exit(print_r($model));
		if($model){
			$model->delete();
		}
		// exit();
		return $this->controller->goHome();
	}
	
}