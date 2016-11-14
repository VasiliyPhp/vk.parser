<?php

namespace app\models\friends;
use yii;

class PeopleForm extends \yii\base\Model
{
    public $people;
		
    public function rules()
    {
        return [
            [ ['people'], 'required', 'message'=>null],
       ];
    }
		
		public function import(){
			\app\models\friends\People::saveMultiple($this->people);
		}
		
		public function attributeLabels(){
			return [
			  'people'=>'Люди',
		  ];
		}
		
}












