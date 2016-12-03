<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class PeopleFromSearchParser extends yii\base\Action{
	
	public function run(){
		session_write_close();
		$PeopleSearch = new \app\models\parser\PeopleFromSearchParser;
		$PeopleSearch->load(yii::$app->request->post());
		$resultPeopleFrom = null;
		if($PeopleSearch->validate()){
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$resultPeopleFrom = $this->getPeoples($PeopleSearch, $VK);
		}
		// j($resultPeopleFrom);
		$PeopleFromGroup = new \app\models\parser\PeopleFromGroupParser;	
		$GroupParser = new \app\models\parser\GroupParser;	
		$PeopleInfo = new \app\models\parser\PeopleInfoParser;
		return $this->controller->render('index', compact('PeopleSearch', 'PeopleInfo', 'PeopleFromGroup', 'GroupParser','resultPeopleFrom'));
	}
	
	private function getPeoples($PeopleSearch, $vk){
		
		$result = [];
		$queries = array_filter(explode("\n", $PeopleSearch->queries));
		foreach($queries as $q){
			$params = [
				'q'=>$q,
				'sex'=>$PeopleSearch->sex,
				'age_from'=>$PeopleSearch->age_from,
				'age_to'=>$PeopleSearch->age_to,
				'city'=>$PeopleSearch->m_city? : $PeopleSearch->city,
				'country'=>$PeopleSearch->country,
				'sex'=>$PeopleSearch->sex,
				'count'=>1000,
				'fields'=>'can_write_private_message, can_post',
			];
			$rs = $vk->api('users.search', $params);
			if(isset($rs['error'])){
				throw new \VK\VKException($result['error']['error_msg']);
			}
			$rs = $rs['response']['items'];
			// j([$rs,$params]);
			if(!count($rs)){
				continue;
			}
			$rs = array_map(function($i){
				unset($i['first_name'],$i['last_name']);
				$i['id'] = 'http://vk.com/id' . $i['id'];
				return $i;
			},$rs);
			$rs = array_filter($rs, function($i) use($PeopleSearch){
				return $PeopleSearch->open_mess == $i['can_write_private_message'] && $PeopleSearch->open_wall == $i['can_post'];
			});
			$result = array_merge($result, $rs);
			usleep(200000);
		}
		// j($result);
		return $result;
		
	}
	
}