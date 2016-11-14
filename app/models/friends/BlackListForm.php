<?php

namespace app\models\friends;
use yii;

class BlackListForm extends yii\base\model
{  
    public $black;		
    public function rules()
    {
        return [
            [ ['black'], 'required', 'message'=>null],
            [ ['black'], 'file', 'extensions'=>'txt'],
       ];
    }
		
		public function load($data,$formName = null){
	    if(!($parent = parent::load($data, $formName))){
				return $parent;
			}
			$this->black = yii\web\UploadedFile::getInstance($this, 'black');
			return $parent;
		}
		public function attributeLabels(){
			return ['black'=>'Залить игнор лист' ];
		}
}












