<?php

namespace app\models\find_users_id;
use yii;

class GetPeopleForm extends yii\base\model
{  
  public $peoples;
  public $cross = 1;
  
  public function rules() 
    {
        return [
				  ['peoples', 'string'],
				  ['cross', 'number'],
				];
    }
		
		public function attributeLabels(){
			return [
			  'peoples'=>'Список id групп вк без пробелов каждый с новой строки',
			  'cross'=>'В скольких сообществах должен быть одновременно',
				];
		}
		
		public function attributeHints(){
			return ['search'=>'Текст, по которуму будут искаться группы' ];
		}
}












