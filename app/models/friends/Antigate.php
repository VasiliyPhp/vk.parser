<?php

namespace app\models\friends;
use yii;

class Antigate extends \app\common\ActiveArray
{
    public $index;
    public $antigate_key;

		public static function fileName(){
			return 'friends.antigate.dat';
		}
		
		
    public function rules()
    {
        return [
            [ ['antigate_key'], 'required'],
						[['index'], 'integer'],
       ];
    }
		
}












