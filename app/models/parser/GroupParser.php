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
    public $m_city;
    public $closed;
	  static $max_queries = 25;
		static $is_closed;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['queries'], 'required', 'message'=>'Обязательно'],
			[['country', 'm_city', 'city', 'closed'], 'safe'],
			['queries', 'queriesValidator', 'params'=>self::$max_queries],
        ];
    }
	
	public function queriesValidator($attr, $params){
		$this->$attr = trim($this->$attr);
		$count = count(explode("\n", $this->$attr));
		if($count > $params){
			$this->addError($attr, sprintf('Введено запросов - %s. Максимальное количество запросов - %s', $count, $params));
		}
	}
	
	public static function getRegions($country){
		$cache = yii::$app->cache;
		$regions = null;
		/**/
		// $cache->flush();
		if( !($regions = $cache->get($country . 'regions'))){
			$regions = [];
			$token = yii::$app->user->identity->vk_access_token;
			$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
			$VK->bulkApi('database.getRegions', ['country_id'=>$country,'need_all'=>1,'count'=>1000], $regions);
			$regions = array_column($regions, 'title', 'id');
		  $regions = compact('regions');
			$cache->set($country . 'regions', $regions, 60*60*24*365);
		}
		return $regions['regions'] ? $regions : self::getCities($country);
	}
	
	public static function getCountries(){
		$cache = yii::$app->cache;
		$countries = [];
		/**/
		// $cache->flush();
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
	
	public static function getMainCities($country){
		$cache = yii::$app->cache;
		/**/
		// $cache->flush();
		if($cities = $cache->get($country . '-main_cities')){
			return $cities;
		}
	  $cities = [];
		$token = yii::$app->user->identity->vk_access_token;
		$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
		
		$cities = $VK->api('database.getCities', [ 'country_id'=>$country, 'need_all'=>0,'count'=>1000])['response']['items'];
		// j($cities);
		for($i = 0; $i < count($cities); $i++){
			$city = &$cities[$i];
			$_cities[$city['id']] = $city['title'];
			unset($city);
		}
		$main_cities = $_cities;
		$main_cities = compact('main_cities');
		$cache->set($country . '-main_cities', $main_cities, 60*60*24*365);
		return $main_cities;
	}
	
	public static function getCities($country, $region = null){
	  $cache = yii::$app->cache;
		/**/
		// $cache->flush();
		if($cities = $cache->get($country . '-' . $region . 'cities')){
			die;
			return $cities;
		}
		$cities = [];
		$token = yii::$app->user->identity->vk_access_token;
		$VK = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key'], $token);
		
		$VK->bulkApi('database.getCities', ['region_id'=>$region, 'country_id'=>$country, 'need_all'=>1,'count'=>1000], $cities);
		$_cities= [];
		for($i = 0; $i < count($cities); $i++){
			$city = &$cities[$i];
			$_cities[$city['id']] = $city['title'] .(isset($city['area'])?   ' (' . $city['area'] . ')' : '');
			unset($city);
		}
		$cities = $_cities;
		$cities = compact('cities');
		$cache->set($country . '-' . $region . 'cities', $cities, 60*60*24*365);
		return $cities;
	}
		
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'queries' => 'Список запросов. Каждый на новой строке',
            'country' => 'Страна',
            'region' => 'Регион',
            'm_city' => 'Основные города',
            'city' => 'Город',
            'closed' => 'Закрытое',
        ];
    }

}
