<?php

namespace app\friends;

use Yii;
use VK\VK;
use VK\VKException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\ContactForm;
use app\models\TargetAlbum;
use app\models\SourceAlbum;
use app\models\friends\User;

class FriendsController extends Controller
{
	  private $vk;
		public $layout = 'friends';
		public $log_file;
		
		public function __construct($id, $module, $config = []){
			parent::__construct($id, $module, $config);
			$this->log_file = yii::getAlias('@runtime') . '/friends.log';
		}
	  public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions'=>['create-admin-accaunt'],
												'allow' => false,
												'roles' => ['@'],
												'matchCallback' => function () {
													if(!User::getIssetRecords()){
														return false;
													}
													return true;
												}
                    ],
                    [
                        'actions'=>['create-admin-accaunt'],
												'allow' => true,
												'roles' => ['?', '@'],
												'matchCallback' => function () {
													if(!User::getIssetRecords()){
														return true;
													}
													return false;
												}
                    ],
                    [
                        'actions'=>['create-user'],
												'allow' => true,
												'roles' => ['@'],
												'matchCallback' => function () {
													if(yii::$app->user->identity->is_admin){
														return true;
													}
													return false;
												}
                    ],
                    [
												'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions'=>['login', 'get-vk-token', 'error', 'geo-photos'],
												'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
      return [
            'create-admin-accaunt' => [
                'class' => 'app\actions\friends\CreateAdminAccaunt',
            ],
            'create-user' => [
                'class' => 'app\actions\friends\CreateUser',
            ],
            'add-friends' => [
                'class' => 'app\actions\friends\AddFriends',
            ],
            'clear-log' => [
                'class' => 'app\actions\friends\ClearLog',
            ],
            'find-users-id' => [
                'class' => 'app\actions\id\FindUsersId',
            ],
            'geo-photos' => [
                'class' => 'app\actions\geo_photos\GeoPhotos',
            ],
            'delete' => [
                'class' => 'app\actions\friends\Delete',
            ],
            'clear' => [
                'class' => 'app\actions\friends\Clear',
            ],
            'save-black-list' => [
                'class' => 'app\actions\friends\SaveBlackList',
            ],
            'download-black' => [
                'class' => 'app\actions\friends\DownloadBlack',
            ],
            'login' => [
                'class' => 'app\actions\friends\Login',
            ],
            'index' => [
                'class' => 'app\actions\friends\Index',
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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

	public function actionLogout()
	{
			$user = Yii::$app->user->identity;
			$user->logout();
			
			return $this->goHome();
	}

}











