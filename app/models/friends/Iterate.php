<?php

namespace app\models\friends;
use yii;

class Iterate extends \app\common\ActiveArray
{
    public $count;
  		
		public static function fileName(){
			return 'friends.iterate.dat';
		}
		
		public function increment(){
			$this->count++;
			$this->save();
		}
		
		public static function createNew(){
			self::deleteAll();
			(new self(['count'=>0]))->save();
		}
		
    public function rules()
    {
        return [
            [ ['count'], 'required', 'message'=>null],
						['index', 'safe'],
       ];
    }
		
}












