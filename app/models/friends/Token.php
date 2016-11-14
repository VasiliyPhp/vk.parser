<?php

namespace app\models\friends;
use yii;

class Token extends \app\common\ActiveArray
{
    public $vk_token;
    public $vk_user_name;
    public $vk_photo;
    public $vk_id;
		
		public static function fileName(){
			return 'friends.token.dat';
		}
		
    public function rules()
    {
        return [
            [ ['vk_token'], 'required', 'message'=>null],
						[['vk_user_name', 'vk_id', 'vk_photo', 'index'], 'safe'],
       ];
    }
		
		public function attributeLabels(){
			return [
			  'vk_token'=>'Ввести аксес токен Вк, или нажать "Получить токен", чтобы скопировать из адресной строки',
		  ];
		}
		
		public function load($data, $formName = null){
			$result = parent::load($data, $formName);
			if( ($fragment = parse_url($this->vk_token, PHP_URL_FRAGMENT) ) ){
				$this->vk_token = preg_replace('|^access_token=([^&]+)&.*$|', "$1", $fragment);
			}
			return $result;
		}
}












