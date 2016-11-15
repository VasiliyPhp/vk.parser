<?php

namespace app\models\parser;

use Yii;
use VK\VK;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PeopleFromGroupParser extends Model
{
    public $groups;
    public $open_mess;
    public $open_wall;
		
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['groups'], 'required', 'message'=>'Обязательно'],
						[['open_mess', 'open_wall'], 'safe'],
        ];
    }
	
	public static function getCities($country, $region = null){
	  $cache = yii::$app->cache;
		$cities = [];
		/**/
		// $cache->flush();
		if($cities = $cache->get($country . '-' . $region . 'cities')){
			return $cities;
		}
		$cities = [];
		$token = yii::$app->user->identity->vk_access_token;
		$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
		
		$VK->bulkApi('database.getCities', ['region_id'=>$region, 'country_id'=>$country, 'need_all'=>1,'count'=>1000], $cities);
		$cities = array_column($cities, 'title', 'id');
		$cache->set($country . 'cities', $cities, 60*60*24*365);
		return compact('cities');
	}
		
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'groups' => 'Список групп. Каждая на новой строке',
            'open_mess' => 'Открытая личка',
            'open_wall' => 'Открытая стена',
        ];
    }

}
