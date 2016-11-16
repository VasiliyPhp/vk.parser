<?php

namespace app\actions\parser;
use yii;
use VK\VK;

class PeopleInfoParser extends yii\base\Action{
	
	public function run(){
		session_write_close();
		$PeopleInfo = new \app\models\parser\PeopleInfoParser;
		$PeopleInfo->load(yii::$app->request->post());
		$resultPeopleInfo = null;
		if($PeopleInfo->validate()){
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$rs = $this->getInfo($PeopleInfo, $VK);
			if(isset($rs['error'])){
				throw new \VK\VKException($rs['error']['error_msg']);
			}
			$resultPeopleInfo = $rs;
			$resultPeopleInfo = array_map(function($i){
				$i['bdate'] = isset($i['bdate'])?$i['bdate']:'';
				$i['id'] = 'http://vk.com/id' . $i['id'];
				$i['open_mess'] = $i['can_write_private_message']? 'откр личка':'закр личка';
				$i['last_seen'] = isset($i['last_seen'])? date('d.m.Y H:i', $i['last_seen']['time']) : '';
				$i['country'] = isset($i['country'])?$i['country']['title']:'';
				$i['city'] = isset($i['city'])?$i['city']['title']:'';
				$i['sex'] = str_replace([0,1,2],['','женский','мужской'],$i['sex']);
				$i['relation'] = isset($i['relation'])?str_replace([0,1,2,3,4,5,6,7],['','не женат/не замужем',
				  'есть друг/подруга','помолвлен/помолвлена','женат/замужем','всё сложно',' в активном поиске','влюблён/влюблена',],$i['relation']):'';
				if(isset($i['relatives'])){
					$rel_type = array_column($i['relatives'], 'type');
				  $i['child'] = (in_array('child', $rel_type) || in_array('grandchild', $rel_type))? 'есть дети':'';
				}else{
					 $i['child'] = '';
				}
				return $i;
			}, $resultPeopleInfo);
		}else{
			throw new \yii\base\UserException($PeopleInfo->getFirstError('peoples'));
		}
		 // j($PeopleInfo);
		$PeopleFromGroup = new \app\models\parser\PeopleFromGroupParser;	
		$GroupParser = new \app\models\parser\GroupParser;	
		$PeopleSearch = new \app\models\parser\PeopleFromSearchParser;
		return $this->controller->render('index', compact('PeopleSearch','PeopleInfo', 'PeopleFromGroup', 'GroupParser','resultPeopleInfo'));
	}
	
	private function getInfo($PeopleInfo, $vk){
		$max = 1000;
		$bit = 2000;
		$ids = array_filter(array_map(function($item){
			$item = trim($item);
			if(preg_match('~vk\.com/id(\d+)/?$~',$item,$tmp)){
				$item = $tmp[1];
			}elseif(preg_match('~vk\.com/(.+)/?$~',$item,$tmp)){
				$item = $tmp[1];
			}else{
				$item = null;
			}
			return $item;
		}, explode("\n", $PeopleInfo->peoples)));
		$collected = 0;
		$res = [];
		$count = count($ids);
		// $ids = range(1,3000);
		while($collected < $count){
			$ids_slice = array_slice($ids, $collected, $bit);
  		$js_count = count($ids_slice);
  		// x($ids_slice);
			$js_ids = json_encode($ids_slice);
			$code = "
			var lim = 25,
				collected = 0,
				count = $js_count,
				res = [],
				ids = $js_ids,
				params = {
					fields: \"bdate,last_seen,country,city,relation,relatives,can_post,can_write_private_message,sex\",
				};
			while(count > collected && lim > 0){
				lim = lim - 1;
				var length = ids.length,
					ar = ids.slice(collected, collected + $max);
					 // ar = [collected,collected + $max];
				params.user_ids = ar;
				// res = res + ar;
				res = res + API.users.get(params);
				collected = collected + $max;
			}
			return res;
			";
		  // $code = "return $js_ids;";
			$collected += count($ids_slice);
// x($vk->api('execute', compact('code')));
			usleep(200000);
			$res = array_merge($res, (array)$vk->api('execute', compact('code'))['response']);
		}
		// j($res);
		return $res;
		
	}
	
}