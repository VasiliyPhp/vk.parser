<?php

namespace app\actions\geo_photos;
use app\models\geo_photos\coords;
use app\models\friends\Token;
use yii;
use VK\VK;
use VK\VKException;

class GeoPhotos extends yii\base\Action{

		public $vk;
	
	public function run(){
				
		$controller = $this->controller;
		$coords = new Coords;
		if($coords->load(yii::$app->request->post()) and $coords->validate() and Yii::$app->request->isAjax ){
	    
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
					  
			return $this->find_photos($coords);
			
		}
		return $controller->render('geo-photos', compact('coords') );
		
  }
	
	private function find_photos($coords){
		$token = Token::findOne();
		if(!$token){
			return ['error'=>['error_msg'=>'Нет токена доступа']];
		}
		list($long, $lat) = explode(' ', $coords->point);
		$sort = 0;
		$count = 100;
		$radius = $coords->radius;
		$token = $token->vk_token;
		$offset = $coords->page * $count;
		$vk = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);	
		$query = compact('offset', 'long', 'lat', 'sort', 'count', 'radius');
		return array_merge(['query'=>$query], $vk->api('photos.search', $query));
	}
	
}











