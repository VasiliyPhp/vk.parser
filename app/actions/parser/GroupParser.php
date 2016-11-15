<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class GroupParser extends yii\base\Action{
	
	public function run(){
		$GroupParser = new \app\models\parser\GroupParser();
		$GroupParser->load(yii::$app->request->post());
		$result = null;
		if($GroupParser->validate()){
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$params = json_encode([
			  'city_id'=>$GroupParser->city,
			  'country_id'=>$GroupParser->country,
			  'count'=>600
			]);
			$result = [];
			$queries = json_encode(array_filter(explode("\n", $GroupParser->queries)), JSON_UNESCAPED_UNICODE );
			$code = "
			var groups = [],
				qs = $queries,
				params = $params,
				lim = qs.length;
			while(lim){
				lim = lim - 1;
				params.q = qs[lim];
				// return API.groups.search(params).items;
				groups = groups + API.groups.search(params).items;
			}
			return groups;
			";
			$rs = $VK->api('execute', compact('code'));
			if($GroupParser->closed){
				$result = array_filter($rs['response'], function($item){
				  return $item['is_closed']==1;
				});
			}else{
				$result = $rs['response'];
			}
			if(count($result)){
				$result = $this->getGroupInfo(array_column($result, 'id'), $VK);
				if(isset($result['error'])){
					throw new \VK\VKException($result['error']['error_msg']);
				}
				$result = array_filter($result['response'], function($item){
					$res = isset($item['site']) && strlen(trim($item['site'])) && isset($item['contacts']) && count($item['contacts']);
					return $res;
				});
				$result = array_map(function($item){
					$item['id'] ='http://vk.com/club'.$item['id'];// yii\helpers\Html::a('http://vk.com/club'.$item['id'],'http://vk.com/club'.$item['id'], ['title'=>$item['name'], 'target'=>'_blank']);
					$item['contacts'] = implode('<br>', array_map(function($i){
						$s = '';
						$s .= isset($i['desc'])? '(' . $i['desc'] . ') '  : '';
						$s .= isset($i['user_id'])?  'vk - http://vk.com/id'.$i['user_id']:'';//yii\helpers\Html::a('vk - http://vk.com/id'.$i['user_id'], 'http://vk.com/id'.$i['user_id'], ['target'=>'_blank']) : '';
						$s .= isset($i['email'])? '; email - ' . $i['email'] : '';
						$s .= isset($i['phone'])? '; tel - ' . $i['phone'] : '';
						return $s;
					}, $item['contacts']));
					return $item;
				}, $result);
			}
		}
		$PeopleFromGroup = new \app\models\parser\PeopleFromGroupParser;	
		$PeopleSearch = new \app\models\parser\PeopleFromSearchParser;
		return $this->controller->render('index', compact('result','PeopleFromGroup', 'PeopleSearch', 'GroupParser','resultPeopleFromGroup'));
	}
	
	private function getGroupInfo($ids, $vk){
		$max = 500;
		$count = count($ids);
		$collected = 0;
		$res = [];
		$index = 0;
		// x([$collected, $count]);
		// $js_ids = json_encode($ids);
		// $js_ids = json_encode(array_map(function($item){return implode(',',$item);},array_chunk($ids, 500)));
		$js_ids = json_encode($ids);
		// $js_ids = json_encode(range(500,1200));
		// j($ids);
		while($collected < $count){
			
			$code = "
			var lim = 25,
				collected = $collected,
				count = $count,
				index = $index,
				res = [],
				ids = $js_ids,
				params = {
					fields: \"site,contacts\",
				};
			while(count > collected && lim > 0){
				lim = lim - 1;
				var length = ids.length,
					ar = ids.slice(collected);
				// return ar;
				params.group_ids = ar;
				// res = res + [collected, $max];
				res = res + API.groups.getById(params);
				collected = collected + $max;
				index = index + 1;
			}
			return res;
			";
			$index += 25;
			$collected += 25*$max;
			// $res += q
			usleep(200000);
			$res = array_merge($res, $vk->api('execute', compact('code')));
		}
		// j($res);
		return $res;
		
	}
	
}