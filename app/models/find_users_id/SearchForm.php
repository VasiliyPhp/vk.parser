<?php

namespace app\models\find_users_id;
use yii;

class SearchForm extends yii\base\model
{  
  public $sort;
  public $search;
  public $count = 10;
	public $sort_variants = [
    0 => 'сортировать по умолчанию (аналогично результатам поиска в полной версии сайта)',
    1 => 'сортировать по скорости роста',
    2 => 'сортировать по отношению дневной посещаемости к количеству пользователей',
    3 => 'сортировать по отношению количества лайков к количеству пользователей',
    4 => 'сортировать по отношению количества комментариев к количеству пользователей',
    5 => 'сортировать по отношению количества записей в обсуждениях к количеству пользователей',
	];
	
  public function rules() 
    {
        return [
				  ['count', 'number', 'max'=>10, 'tooBig'=>'', 'tooSmall'=>''],
          [ ['search', 'sort'], 'required', 'message'=>''],
       ];
    }
		
		public function attributeLabels(){
			return [
			  'search'=>'Поисковый запрос (слово или фраза должны содержаться в названии группы)',
			  'count'=>'Сколько групп искать (max 10)',
			  'sort'=>'Способ сортировки'
				];
		}
		
		public function attributeHints(){
			return ['search'=>'Текст, по которуму будут искаться группы' ];
		}
}












