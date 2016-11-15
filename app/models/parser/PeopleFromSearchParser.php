<?php

namespace app\models\parser;

use Yii;
use VK\VK;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PeopleFromSearchParser extends Model
{
    public $queries;
    public $sex;
    public $country;
    public $region;
    public $city;
    public $age_from;
    public $age_to;
    public $open_mess;
    public $open_wall;
		
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['queries'], 'required', 'message'=>'Обязательно'],
						[['sex', 'region', 'country', 'city', 'age_from', 'age_to', 'open_mess', 'open_wall'], 'safe'],
        ];
    }
	
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'queries' => 'Список запросов. Каждый на новой строке',
            'open_mess' => 'Открытая личка',
            'open_wall' => 'Открытая стена',
            'age_from' => 'Возраст от',
            'age_to' => 'Возраст до',
            'sex' => 'Пол',
            'city' => 'Город',
            'region' => 'Регион',
            'country' => 'Страна',
        ];
    }

}
