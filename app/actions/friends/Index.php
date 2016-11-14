<?php

namespace app\actions\friends;
use app\models\friends\Token;
use app\models\friends\BlackList;
use app\models\friends\Antigate;
use yii;
use VK\VK;
use VK\VKException;

class Index extends yii\base\Action{
	public function run(){
		
		$antigate = new Antigate;
		if($antigate->load(yii::$app->request->post())){
			$antigate->index = 0;
			$antigate->save();
			// print_r($antigate);exit;
		} else {
			$antigate = Antigate::findOne();
		}
	  $vk = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key']);	
  	$token = new Token();
		$error = null;
		if( $token->load(yii::$app->request->post() ) ){
			 try{
				 $vk->setAccessToken($token->vk_token);
				 $rs = $vk->api('users.get', ['fields'=>'photo_50']);
				 if(isset($rs['error'])){
					 throw new VKException($rs['error']['error_msg'] . ' Не удалось получить данные. Неверный токен.');
				 }
				 $rs = $rs['response'];
				 $token->vk_id = $rs[0]['id'];
				 $token->vk_user_name = $rs[0]['first_name'] . ' ' . $rs[0]['last_name'];
				 $token->vk_photo = $rs[0]['photo_50'];
				 $token->save();
			} catch (VKException $e){
				$error = $e->getMessage();
			}
		}
		$get_token_url = $vk->getAuthorizeUrl('photos, groups, friends, offline', null, 'token');
       
		
		$tokens = new yii\data\ArrayDataProvider([
		  'allModels'=>Token::findAll(),
			'pagination'=>[
			  'pageSize'=>30,
		  ],
		]);
		$message = new \app\models\friends\Message;
		if($message->load(yii::$app->request->post()) and $message->validate()){
			$message->save();
		}
		$messages = new \yii\data\ArrayDataProvider([
		  'allModels'=>\app\models\friends\Message::findAll(),
			'pagination'=>[
			  'pageSize'=>30,
		  ],
		]);
		$people_form = new \app\models\friends\PeopleForm;
		if($people_form->load(yii::$app->request->post()) and $people_form->validate()){
			$black = array_map(function($item){return $item['black'];},BlackList::findAll());
			$peoples = [];
			foreach(explode("\n", $people_form->people) as $item){
				if(!trim($item) or in_array(trim($item), $black)){
					continue;
				}
				$peoples[] = $item;
			}
			$peoples_array = array_map(
			    function($item){
						return [
							'people'=>trim($item), 
							'status'=>0, 
							'comment'=>'запрос еще не отправлен',
							'who'=>'',
							'when'=>'',
							];
					}, $peoples);
			\app\models\friends\People::saveMultiple($peoples_array);
			\app\models\friends\Iterate::createNew();
		  file_put_contents($this->controller->log_file, '');
		}
		$log = file_exists($this->controller->log_file)? file_get_contents($this->controller->log_file) : '' ;
		$people_form->people = null;
		$all_peoples = \app\models\friends\People::findAll();
		$stat = $this->calculateStat($all_peoples);
		$peoples =new \yii\data\ArrayDataProvider([
		  'allModels'=>$all_peoples,
			'pagination'=>false,
		]);
		$black_list_form = new \app\models\friends\BlackListForm;
		return $this->controller->render('index', compact('antigate', 'black_list_form', 'stat', 'log', 'people_form', 'peoples', 'message', 'messages', 'error', 'token', 'tokens', 'get_token_url'));
	}
	
	protected function calculateStat($ar){
		$total = count($ar);
		$success = 0;
		$fail = 0;
		foreach($ar as $a){
			if(in_array($a['status'], [1,2,4])){
				$success++;
			} elseif($a['status'] != 0){
				$fail++;
			}
		}
		return compact('total', 'success', 'fail');
	}
	
}











