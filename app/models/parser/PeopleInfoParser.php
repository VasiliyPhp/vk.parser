<?php

namespace app\models\parser;

use Yii;
use VK\VK;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class PeopleInfoParser extends Model
{
    public $peoples	;
    static $max_peoples = 50000;
		// public $sex;
    // public $city;
    // public $country;
    // public $age;
    // public $bdate;
    // public $last_name;
    // public $first_name;
    // public $open_mess;
    // public $open_wall;
    // public $relatives;
    // public $relation;
    // public $last_seen;
		
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['peoples'], 'required', 'message'=>'Обязательно'],
            [['peoples'], 'peoplesValidator', 'params'=>self::$max_peoples],
						// [['relation', 'sex', 'last_seen', 'country', 'city', 'open_mess', 'open_wall'], 'safe'],
        ];
    }
		
		public function peoplesValidator($attr, $max){
			$this->$attr = trim($this->$attr);
			if( count(array_filter(explode("\n", $this->$attr))) >  $max){
				$this->addError($attr, 'Максимальное колличество ссылок на страницы - ' . $max );
			}
		}
		
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'peoples' => 'Список ссылок на пользователей ВК. Каждый на новой строке',
            // 'open_mess' => 'Открытая личка',
            // 'open_wall' => 'Открытая стена',
            // 'age_from' => 'Возраст от',
            // 'age_to' => 'Возраст до',
            // 'sex' => 'Пол',
            // 'city' => 'Город',
            // 'region' => 'Регион',
            // 'country' => 'Страна',
        ];
    }

}
