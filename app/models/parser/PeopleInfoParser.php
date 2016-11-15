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
    public $peoples	;
    public $sex;
    public $city;
    public $country;
    public $age;
    public $bdate;
    public $last_name;
    public $first_name;
    public $open_mess;
    public $open_wall;
    public $last_seen;
		
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['peoples'], 'required', 'message'=>'Обязательно'],
			[['sex', 'last_seen', 'country', 'city', 'age', 'age_to', 'open_mess', 'open_wall'], 'safe'],
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
