<?php

namespace app\models\geo_photos;
use yii;

class Coords extends yii\base\model
{  
  public $coords;
  public $radius;
  public $point;
  public $page;
		
    public function rules()
    {
        return [
            [ ['coords'], 'safe'],
            [ ['radius', 'point', 'page'], 'required', 'message'=>''],
       ];
    }
		
		public function attributeLabels(){
			return ['coords'=>'Адрес' ];
		}
}












