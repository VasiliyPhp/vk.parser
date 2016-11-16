<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class PeopleFromGroupParser extends yii\base\Action{
	
	public function run(){
		$PeopleParser = new \app\models\parser\PeopleFromGroupParser();
		$PeopleParser->load(yii::$app->request->post());
		$resultPeopleFrom = null;
		if($PeopleParser->validate()){
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$resultPeopleFrom = $this->getPeoples($PeopleParser, $VK);
		}
		// j($resultPeopleFrom);
		$PeopleFromGroup = new \app\models\parser\PeopleFromGroupParser;	
		$GroupParser = new \app\models\parser\GroupParser;	
		$PeopleSearch = new \app\models\parser\PeopleFromSearchParser;
		$PeopleInfo = new \app\models\parser\PeopleInfoParser;
		return $this->controller->render('index', compact('PeopleFromGroup', 'PeopleInfo', 'PeopleSearch', 'GroupParser','resultPeopleFrom'));
	}
	
	private function getPeoples($PeopleParser, $vk){
		
		$result = [];
		$groups = array_filter(explode("\n", $PeopleParser->groups));
		foreach($groups as $g){
			if(preg_match('~vk\.com/club(\d+)/?$~',$g,$tmp)){
				$g = $tmp[1];
			}elseif(preg_match('~vk\.com/public(\d+)/?$~',$g,$tmp)){
				$g = $tmp[1];
			}elseif(preg_match('~vk\.com/event(\d+)/?$~',$g,$tmp)){
				$g = $tmp[1];
			}elseif(preg_match('~vk\.com/(.+)/?$~',$g,$tmp)){
				$g = $tmp[1];
			}else{
				x($g.' - is wrong');
				continue;
			}
			$params = [
				'group_id'=>$g,
				'count'=>1000,
				'fields'=>'can_write_private_message, can_post',
			];
			$tmp = [];
			$vk->bulkApi('groups.getMembers', $params, $tmp);
			$tmp = array_map(function($i){
				unset($i['first_name'],$i['last_name']);
				$i['id'] = 'http://vk.com/id' . $i['id'];
				return $i;
			},$tmp);
			$tmp = array_filter($tmp, function($i) use($PeopleParser){
				return $PeopleParser->open_mess == $i['can_write_private_message'] && $PeopleParser->open_wall == $i['can_post'];
			});
			$result = array_merge($result, $tmp);
			usleep(200000);
		}
		// j($result);
		return $result;
		
	}
	
}