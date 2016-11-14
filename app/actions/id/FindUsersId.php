<?php

namespace app\actions\id;
use app\models\find_users_id\SearchForm;
use app\models\find_users_id\GetPeopleForm;
use app\models\friends\Token;
use yii;
use VK\VK;
use VK\VKException;

class FindUsersId extends yii\base\Action{

  public $vk;
	
	public function run(){
				
		$controller = $this->controller;
		$search_form = new SearchForm;
		$get_people_form = new GetPeopleForm;
		
		if(isset(yii::$app->request->post()['contact_ids'])){
 
		  return yii::$app->response
			  ->sendContentAsFile(implode("\r\n", explode(',', yii::$app->request->post()['contact_ids'])), 'vk_contact_ids.txt');
			
		}
		
		if($search_form->load(yii::$app->request->post()) and $search_form->validate()){
			
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
					  
			return $this->find_groups($search_form);
		
		}
		
		if($get_people_form->load(yii::$app->request->post()) and $get_people_form->validate()){
			
			// \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		  return yii::$app->response
			  ->sendContentAsFile(implode("\r\n", $this->find_peoples($get_people_form)), 'vk_ids.txt');
		
			return $this->find_peoples($get_people_form);
		
		}
		
		return $controller->render('find-users-id', compact('search_form', 'get_people_form'));
		
  }
	
	private function find_groups($search){
		$token = Token::findOne();
		if(!$token){
			return ['error'=>['error_msg'=>'Нет токена доступа']];
		}
		$q = $search->search;
		$sort = $search->sort;
		$count = $search->count;
		$token = $token->vk_token;
		$code ="var groups,
		            group_ids = \"\",
								len;
						groups = API.groups.search({
							sort : $sort,
							count : $count,
							v : 5.53,
							q : \"{$q}\",
						});
						len = groups.items.length;
						if(!len){
							return {error:{message:\"Ничего не найдено\"}};
						}
						while(len){
							len = len - 1;
							group_ids = group_ids.length ? group_ids + \",\" + groups.items[len].id : \"\" + groups.items[len].id;
						}
						return API.groups.getById({
							group_ids : group_ids,
							fields : \"members_count,contacts,city,country\",
							v : 5.53,
						});;
						";
		// $offset = $coords->page * $count;
		$vk = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);	
		$query = compact('code');
  	return array_merge(['query'=>$query], $vk->api('execute', $query));
	}
	
	private function find_peoples($search){
 		// echo '<pre>';
    $token = Token::findOne();
		if(!$token){
			return ['error'=>['error_msg'=>'Нет токена доступа']];
		}
		$token = $token->vk_token;
		$vk = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);	
		$users = [];
		$cross = $search->cross;
  	$groups = json_decode($search->peoples);
		
		foreach($groups as $group){
			$count = $group->count;
			$id = $group->id;
			$offset = 0;
			$tmp = [];
			while($count > 0){
				$code = "
				  var offset = $offset,
					    total = $count,
							config,
							users = [],
							limit = total < 25000 ? total : 25000;
				  while(limit > 0){
						config = {
							offset : offset,
							count : 1000,
							v : 5.53,
							group_id : $id,
						};
						users = users + API.groups.getMembers(config).items;
						limit = limit - 1000;
						offset = offset + 1000;
					}
					return users;
				";
				$count -= 25000;
				$offset += 25000; 
				$query = compact('code');
				$tmp = array_merge($tmp, $vk->api('execute', $query)['response']);
				usleep(300000);
			}
			foreach($tmp as $value){
				if(isset($users[$value])){
					$users[$value]['coincidence']++; 
					$users[$value]['groups'][] = $id;
					
				} else {
				  $users[$value] = [
					  'coincidence'=>1,
						'id'=>$value,
						'groups'=>[$id],
					];
				}
			}
		// usleep(300);
		}
	// echo "<pre>";
			foreach($users as $key=>$user){
				// print_r($user);
				if ( ($user['coincidence'] < $cross)	&& (count($groups) > 1 )){
					// echo (int)($user['coincidence']) .' '.$user['id']. ' '.$key. ' '.(int)(count($groups) > 1).'<br><br>'; 
					// print_r($users[$key]);
					unset($users[$key]);
				} else {
					// echo $user['coincidence'].' '.$user['id'].'<br><br>';
				}
			}
		// print_r($users);
		// exit;
		return array_column($users, 'id');
		
	}
}











