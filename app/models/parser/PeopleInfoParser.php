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
			$this->$attr = implode("\n", array_slice(array_filter(explode("\n", $this->$attr)), 0, $max));
			// if( count(array_filter(explode("\n", $this->$attr))) >  $max){
				// $this->addError($attr, 'Максимальное колличество ссылок на страницы - ' . $max );
			// }
		}
		
    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
          'peoples' => 'Список ссылок на пользователей ВК. Каждый на новой строке',
        ];
    }

    public function attributeHints()
    {
        return [
          'peoples' => 'Максимум ' . self::$max_peoples,
        ];
    }

}
