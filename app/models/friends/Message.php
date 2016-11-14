<?php

namespace app\models\friends;
use yii;

class Message extends \app\common\ActiveArray
{
    public $message;
		
		public static function fileName(){
			return 'friends.message.dat';
		}
		
    public function rules()
    {
        return [
            [ ['message'], 'required', 'message'=>null],
						['index', 'safe'],
       ];
    }
		
		public function attributeLabels(){
			return [
			  'message'=>'Сообщение',
		  ];
		}
		
		public static function next($i){

  		$count = count(self::findAll());
			if(!$count){
				return null;
			}
			$index = $i ? ($count + $i) % $count : $i;
			$messages = self::findAll();
		  return $messages[array_rand($messages)]['message'];
			
		}
		
}












