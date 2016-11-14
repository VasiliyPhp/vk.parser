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
			  'count'=>500
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
			// j($rs);
			if($GroupParser->closed){
				$result = array_filter($rs['response'], function($item){
					return true;$item['is_closed']==1;
				});
			}else{
				$result = $rs['response'];
			}
			$result = $this->getGroupInfo(array_column($result, 'id'), $VK);
			// $VK->bulkApi('groups.search', $params, $result);
			j($result);
			
		}
		
		return $this->controller->render('index', compact('GroupParser','result'));
	}
	
	private function getGroupInfo($ids, $vk){
		
		$max = 500;
		$count = count($ids);
		$collected = 0;
		$res = [];
		$index = 0;
		// x([$collected, $count]);
		// $js_ids = json_encode($ids);
		$js_ids = json_encode(array_map(function($item){return implode(',',$item);},array_chunk($ids, 500)));
		// j($js_ids);
		while($collected < $count){
			$code = "
			var lim = 1,
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
					str_ids = ids[index];
				
				// return length;
				params.group_ids = str_ids;
				
				res = res + API.groups.getById(params);
				collected = collected + $max;
				index = index + 1;
			}
			return [res, index];
			";
			$index += 25;
			$collected += 25*$max;
			// $res += q
			x($vk->api('execute', compact('code')));
		}
		j($res);
		
	}
	
}