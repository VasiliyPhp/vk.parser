<?php

namespace app\controllers;

use Yii;
use VK\VK;
use VK\VKException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\User;

class SiteController extends Controller
{
	  private $vk;
		private $flag_file;
		private $log_file;
	  public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
												'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions'=>['login', 'get-vk-token', 'error'],
												'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions'=>['test','index', 'error'],
												'allow' => true,
                        'roles' => ['?'],
												'matchCallback' => function () {
													if(($key = yii::$app->request->get('key') ) ){
														$user = User::findOne(['index'=>(int)$key]);
														if($user){
															yii::$app->user->login($user);
															return true;
														}
													}
													return false;
												}
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
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'people-info-parser' => [
                'class' => '\app\actions\parser\PeopleInfoParser',
            ],
            'group-parser' => [
                'class' => '\app\actions\parser\GroupParser',
            ],
            'people-from-search-parser' => [
                'class' => '\app\actions\parser\PeopleFromSearchParser',
            ],
            'people-from-group-parser' => [
                'class' => '\app\actions\parser\PeopleFromGroupParser',
            ],
            'get-cities' => [
                'class' => '\app\actions\parser\GetCities',
            ],
            'get-regions' => [
                'class' => '\app\actions\parser\GetRegions',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
  
	public function actionIndex(){
		$result = null;
		$GroupParser = new \app\models\parser\GroupParser;
		$PeopleSearch = new \app\models\parser\PeopleFromSearchParser;
		$PeopleFromGroup = new \app\models\parser\PeopleFromGroupParser;
		$PeopleInfo = new \app\models\parser\PeopleInfoParser;
		return $this->render('index', compact('result','PeopleSearch','PeopleFromGroup','GroupParser','PeopleInfo'));
  }
	
	public function actionDelete($index){
		$index = (int)$index;
		$model = \app\models\SourceAlbum::findOne(compact('index'));
		if(!$model){
			throw new \yii\web\BadRequestHttpException('wrong data');
		}
		$model->delete();
		return $this->goHome();
	}
	
// login 
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
			  $vk = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key']);
				$LoginForm = new LoginForm;
				$LoginForm->load(yii::$app->request->post());
		    if($LoginForm->validate()){
					$token = VK::getTokenFromUrl($LoginForm->vk_token); 
					$vk->setAccessToken($token);
					try{ 
						$profile = $vk->getProfileInfo();
						$user = new User();
						$user->vk_user_id = $profile['id'];
						$user->vk_access_token = $token;
						$user->vk_last_name = $profile['last_name'];
						$user->vk_first_name = $profile['first_name'];
						$user->index = $profile['id'];
						$user->accessToken = yii::$app->security->generateRandomString(15);
						$user->authKey = yii::$app->security->generateRandomString(10);
						if($user->login() and $user->save()){
							return $this->redirect(yii::$app->homeUrl);
						} else {
							throw new \yii\web\BadRequestHttpException($user->errors);			
						}					
					} catch(VKException $vkex){
						throw new \yii\base\UserException($vkex->getMessage() . '. код - ' . $vkex->getCode());
					}
				}
				$authorize_url = $vk->getAuthorizeUrl('audio,wall,groups,friends,photos,offline', 'https://api.vk.com/blank.html', 'token');
        // print_r([$authorize_url, $this->getVkCallbackUrl()]);exit;
				return $this->render('login', [
            'authorize_url' => $authorize_url,
						'model'=>$LoginForm,
        ]);
    }
		
		protected function getVkCallbackUrl(){
			return \yii::$app->urlManager->createAbsoluteUrl('site/get-vk-token');
		}
		
		public function actionGetVkToken($code = null, $error = null, $error_description = null){
			if(!$code){
				throw new \yii\web\BadRequestHttpException($error . ' ' . $error_description);
			}
			$vk = new VK(yii::$app->params['vk_app_id'], yii::$app->params['vk_secret_key']);
			try{ 
			  $rs = $vk->getAccessToken($code, $this->getVkCallbackUrl());
				$profile = $vk->getProfileInfo();

				$user = new User();
				
				$user->vk_user_id = $rs['user_id'];
				$user->vk_access_token = $rs['access_token'];
				$user->vk_last_name = $profile['last_name'];
				$user->vk_first_name = $profile['first_name'];
				$user->index = $rs['user_id'];
				$user->accessToken = yii::$app->security->generateRandomString(15);
				$user->authKey = yii::$app->security->generateRandomString(10);
				if($user->login() and $user->save()){
					return $this->redirect(yii::$app->homeUrl);
				} else {
				  throw new \yii\web\BadRequestHttpException($user->errors);			
				}					
			} catch(VKException $vkex){
				throw new \yii\web\BadRequestHttpException($vkex->getMessage() . '. код - ' . $vkex->getCode());
			}
		}
		
		public function actionClearLog(){
		  $this->log_file = yii::getAlias('@runtime') . '/log';
      file_put_contents($this->log_file, '');
			return $this->redirect(['index']);
		}
    public function actionLogout()
    {
			  $user = Yii::$app->user->identity;
        $user->logout();
				
        return $this->goHome();
    }

}
