<?php

namespace app\commands;

use Yii;
use VK\VK;
use VK\VKException;
use yii\console\Controller;

class FriendsController extends Controller
{
		public $log_file;
		
		public function __construct($id, $module, $config = []){
			parent::__construct($id, $module, $config);
			$this->log_file = yii::getAlias('@runtime') . '/friends.log';
		}
		
    public function actions()
    {
      return [
            'index' => [
                'class' => 'app\actions\friends\AddFriends',
            ],
        ];
    }
  
	public function stop($message){
		file_put_contents($this->log_file, date('d-m-Y H:i', time() - 3*3600) . ' ' . $message . PHP_EOL, FILE_APPEND);
		if(!isset($this->interactive)){		
		  throw new \yii\web\BadRequestHttpException ( $message );
		}
		exit($message);
		
	}
	public function output($message){
		file_put_contents($this->log_file, date('d-m-Y H:i', time() - 3*3600) . ' ' . $message . PHP_EOL, FILE_APPEND);
		print( $message . PHP_EOL);
		
	}

}











