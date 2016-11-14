<?php

namespace app\models\parser;

use Yii;
use VK\VK;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class GroupParser extends Model
{
    public $queries;
    public $region;
    public $country;
    public $city;
    public $closed;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['queries', 'country', 'city', 'closed'], 'required', 'message'=>'Обязательно'],
        ];
    }
		
		
		public static function getRegions($country){
			$cache = yii::$app->cache;
			$regions = [];
			if($regions = $cache->get($country . 'regions')){
				return $regions;
			}
			$regions = [];
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$VK->bulkApi('database.getRegions', ['country_id'=>$country,'need_all'=>1,'count'=>1000], $regions);
			$regions = array_column($regions, 'title', 'id');
			$cache->set($country . 'regions', $regions, 60*60*24*365);
			return $regions;
		}
		
		public static function getCountries(){
			$cache = yii::$app->cache;
			$countries = [];
			if($countries = $cache->get('countries')){
				return $countries;
			}
			$countries = [];
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$VK->bulkApi('database.getCountries', ['need_all'=>1,'count'=>1000], $countries);
			$countries = array_column($countries, 'title', 'id');
			$cache->set('countries', $countries, 60*60*24*365);
			return $countries;
		}
		
		public function getCities($country, $region = null){
		  $cache = yii::$app->cache;
			$cities = [];
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
			return $cities;
		}
		
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'queries' => 'Запросы',
            'country' => 'Страна',
            'region' => 'Регион',
            'city' => 'Город',
            'closed' => 'Закрытое',
        ];
    }

}
