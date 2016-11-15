<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class PeopleFromSearchParser extends yii\base\Action{
	
	public function run(){
		$PeopleSearch = new \app\models\parser\PeopleFromSearchParser;
		$PeopleSearch->load(yii::$app->request->post());
		$resultPeopleFromSearch = null;
		if($PeopleSearch->validate()){
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$resultPeopleFromSearch = $this->getPeoples($PeopleSearch, $VK);
		}
		// j(resultPeopleFromSearch);
		$PeopleFromGroup = new \app\models\parser\PeopleFromGroupParser;	
		$GroupParser = new \app\models\parser\GroupParser;	
		return $this->controller->render('index', compact('PeopleSearch', 'PeopleFromGroup', 'GroupParser','resultPeopleFromSearch'));
	}
	
	private function getPeoples($PeopleSearch, $vk){
		
		$result = [];
		$queries = array_filter(explode("\n", $PeopleSearch->queries));
		foreach($queries as $q){
			$params = [
				'query'=>$q,
				'sex'=>$PeopleSearch->sex,
				'age_from'=>$PeopleSearch->age_from,
				'age_to'=>$PeopleSearch->age_to,
				'city_id'=>$PeopleSearch->city,
				'country_id'=>$PeopleSearch->country,
				'sex'=>$PeopleSearch->sex,
				'count'=>1000,
				'fields'=>'can_write_private_message, can_post',
			];
			$tmp = [];
			$vk->bulkApi('users.search', $params, $tmp);
			j($tmp);
			$tmp = array_map(function($i){
				unset($i['first_name'],$i['last_name']);
				$i['id'] = 'http://vk.com/id' . $i['id'];
				return $i;
			},$tmp);
			$tmp = array_filter($tmp, function($i) use($PeopleSearch){
				return $PeopleSearch->open_mess == $i['can_write_private_message'] && $PeopleSearch->open_wall == $i['can_post'];
			});
			$result = array_merge($result, $tmp);
			usleep(200000);
		}
		// j($result);
		return $result;
		
	}
	
}