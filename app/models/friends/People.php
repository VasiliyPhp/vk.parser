<?php

namespace app\models\friends;
use yii;

class People extends \app\common\ActiveArray
{
    public $people;
    public $status;
    public $comment;
    public $when;
    public $who;
		
		public static function fileName(){
			return 'friends.people.dat';
		}
		
    public function rules()
    {
        return [
            [ ['people'], 'required', 'message'=>null],
						[['who', 'status', 'when', 'comment', 'index'], 'safe'],
       ];
    }
		
}












