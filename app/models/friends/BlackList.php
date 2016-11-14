<?php

namespace app\models\friends;
use yii;

class BlackList extends \app\common\ActiveArray
{
    public $black;
		
		public static function fileName(){
			return 'friends.black.dat';
		}
		
    public function rules()
    {
        return [
            [ ['black'], 'required', 'message'=>null],
						['index', 'safe'],
       ];
    }
		
		public static function add($id){
			$black = new static();
			$black->black = $id;
			$black->save();
		}
		
}












