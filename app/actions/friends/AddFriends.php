<?php

namespace app\actions\friends;
use app\models\friends\People;
use app\models\friends\Token;
use app\models\friends\Message;
use app\models\friends\Iterate;
use app\models\friends\Antigate;
use app\models\friends\BlackList;
use yii;
use VK\VK;
use VK\VKException;

class AddFriends extends yii\base\Action{
	
	private $vk;
	public function output($m){
		$this->controller->output($m);
		}
	public function stop($m){
		$this->controller->stop($m);
		}

	public function run(){
		
		$tokens = Token::findAll();
		if(!$tokens){
			$this->stop('Нет токенов');
		}
		
		$peoples = People::findAll(['status'=>0]);
		if(!$peoples){
			$this->stop('Нет id');
		}
		
	  $this->vk = $vk = new VK(yii::$app->params['vk_standalone_app_id'], yii::$app->params['vk_standalone_secret_key']);	
		$limit = count($tokens) < count($peoples) ? count($tokens) : count($peoples);
		$this->output(sprintf('%10s', 'Старт программы'));
		for($i = 0; $i<$limit; $i++){
			$iterate = Iterate::findOne();
			$people = $peoples[$i]['people'];
			if(!$people){
				$unexists = People::findOne(compact('people'));
				if($unexists){
					$unexists->delete();
				}
				continue;
			}
			$antigate_key = Antigate::findOne() ? : null;
			$antigate_key and ($antigate_key = $antigate_key->antigate_key);
			$token = $tokens[$i]['vk_token'];
			$vk->setAccessToken($token);
			$message = Message::next((int)$iterate->count);
			$iterate->increment();
			// try{
				$params = [
				  'user_id'=>$people,
				  'text'=>$message ? : null,
				];
				
				$rs = $vk->api('friends.add', $params);
				
				if(isset($rs['error'])){
					switch($rs['error']['error_code']){
					case 14 : 
					  $this->output('Необходимо разгадать капчу ' . $rs["error"]['captcha_img']);
					  if(!$antigate_key){
							$this->stop('Необходим ключ антигейт');
						}
					  if(!($rs = $this->guess($rs['error'], $params, $antigate_key))){
							$comment = "Не получилось разгадать капчу";
							$status = 14;
						}
						break;
					case 17 : 
					  $comment = "Требуется валидация пользователя. Для продолжения нужно перейти ".
						"<a href='{$rs['error']['redirect_uri']}' target='_blank' >по ссылке</a>, затем скопировать новый токен в приложение"; $status = 17; break;
					case 174 : 
					  $comment = "Попытка добавить самого себя"; $status = 174; break;
					case 175 : 
					  $comment = "Попытка добавить пользователя, который занес Вас в черный список"; $status = 175; break;
					case 176 : 
					  $comment = "Попытка добавить пользователя, занесенного в Ваш черный список"; $status = 176; break;
					default:
					  $comment = $rs['error']['error_msg']; 
						$status = $rs['error']['error_code'];
						// echo '<pre>';print_r($rs);exit;
					}
				} 
				if(isset($rs['response'])){
					switch($rs['response']){
						case 1:
						  $comment = "Заявка успешно отправлена"; $status = 1; break;
						case 2:
						  $comment = "Заявка одобрена"; $status = 2; break;
						case 4:
						  $comment = "Заявка отправлена повторно"; $status = 4; break;
					  default:
						  $comment = print_r($rs, 1); $status = -1;
					}
				}
					Blacklist::add($people);
					$who = Token::findOne(['vk_token'=>$token])->vk_user_name;
					$when = date('d-m-Y H:i', time() - 3*3600);
					$people = People::findOne(compact('people'));
					$people->attributes = compact('when', 'who', 'status','comment');
					$people->save();
					$this->output(sprintf('заявка пользователю %s с акаунта %s. %s', $people->people, $who, $comment));
					usleep(330000);
			// } catch ( NeedCaptchaException $e ){
				// list($t)
			// }
			
		}
		// return $this->controller->render('index')
		
		
	}
	public function  recognize(		$filename,
		$apikey,
		$is_verbose = true,
		$sendhost = "antigate.com",
		$rtimeout = 2,
		$mtimeout = 20,
		$is_phrase = 0,
		$is_regsense = 0,
		$is_numeric = 0,
		$min_len = 0,
		$max_len = 0,
		$is_russian = 0)
	{
		// if (!file_exists($filename))
		// {
			// if ($is_verbose) echo "file $filename not found\n";
			// return false;
		// }
		// $fp=fopen($filename,"r");
		// if ($fp!=false)
		// {
			// $body="";
			// while (!feof($fp)) $body.=fgets($fp,1024);
			// fclose($fp);
			// $ext=substr($filename,strpos($filename,".")+1);
		// }
		// else
		// {
			// if ($is_verbose) echo "could not read file $filename\n";
			// return false;
		// }
		  $body = file_get_contents($filename);
		  $ext = 'jpg';
			$postdata = array(
					'method'    => 'base64', 
					'key'       => $apikey, 
					'body'      => base64_encode($body), //������ ���� � �����
					'ext' 		=> $ext,
					'phrase'	=> $is_phrase,
					'regsense'	=> $is_regsense,
					'numeric'	=> $is_numeric,
					'min_len'	=> $min_len,
					'max_len'	=> $max_len,
					'is_russian'	=> $is_russian,
					
			);
			
			$poststr="";
			while (list($name,$value)=each($postdata))
			{
				if (strlen($poststr)>0) $poststr.="&";
				$poststr.=$name."=".urlencode($value);
			}
			
			if ($is_verbose) echo "connecting to antigate...";
			$fp=@fsockopen($sendhost,80);
			if ($fp!=false)
			{
				echo "OK\n";
				echo "sending request...";
				$header="POST /in.php HTTP/1.0\r\n";
				$header.="Host: $sendhost\r\n";
				$header.="Content-Type: application/x-www-form-urlencoded\r\n";
				$header.="Content-Length: ".strlen($poststr)."\r\n";
				$header.="\r\n$poststr\r\n";
				//echo $header;
				//exit;
				fputs($fp,$header);
				echo "OK\n";
				echo "getting response...";
				$resp="";
				while (!feof($fp)) $resp.=fgets($fp,1024);
				fclose($fp);
				$result=substr($resp,strpos($resp,"\r\n\r\n")+4);
				echo "OK\n";
			}
			else 
			{
				if ($is_verbose) echo "could not connect to antigate\n";
				return false;
			}
			
			if (strpos($result, "ERROR")!==false)
			{
				if ($is_verbose) echo "server returned error: $result\n";
					return false;
			}
			else
			{
					$ex = explode("|", $result);
					$captcha_id = $ex[1];
				if ($is_verbose) echo "captcha sent, got captcha ID $captcha_id\n";
					$waittime = 0;
					if ($is_verbose) echo "waiting for $rtimeout seconds\n";
					sleep($rtimeout);
					while(true)
					{
							$result = file_get_contents("http://$sendhost/res.php?key=".$apikey.'&action=get&id='.$captcha_id);
							if (strpos($result, 'ERROR')!==false)
							{
								if ($is_verbose) echo "server returned error: $result\n";
									return false;
							}
							if ($result=="CAPCHA_NOT_READY")
							{
								if ($is_verbose) echo "captcha is not ready yet\n";
								$waittime += $rtimeout;
								if ($waittime>$mtimeout) 
								{
									if ($is_verbose) echo "timelimit ($mtimeout) hit\n";
									break;
								}
							if ($is_verbose) echo "waiting for $rtimeout seconds\n";
								sleep($rtimeout);
							}
							else
							{
								$ex = explode('|', $result);
								if (trim($ex[0])=='OK') {
									file_put_contents(trim($ex[1]).'.'.$ext, $body);
									return trim($ex[1]);
								}
							}
					}
					
					return false;
			}
	}
	
	public function guess($rs, $params, $key){
		$cnt = 3;
		$limit = $cnt; 
		$captcha_img = $rs['captcha_img'];
		$captcha_sid = $rs['captcha_sid'];
		while($limit--){
			$this->output(sprintf('Осталось %s попыток', $limit+1));
		  if(($captcha_key = $this->recognize($captcha_img, $key))){
			  $this->output(sprintf('Ответ антигейта - <b>%s</b>', $captcha_key));
				$captcha_params = array_merge($params, compact('captcha_sid', 'captcha_key'));
				$_rs = $this->vk->api('friends.add', $captcha_params);
				if(isset($_rs['response'])){
					$this->output('Капча разгадана');
					return $_rs;
				}
				if(isset($_rs['error']['captcha_sid'])){
					$this->output('Капча разгадана неправильно');
					$captcha_sid = $_rs['error']['captcha_sid'];
					$captcha_img = $_rs['error']['captcha_img'];
					continue;
				}
				$this->output('Неизвестная ошибка - ' . $_rs['error']['error_msg']);
				return false;
			} else{
			  $this->output('Антигейт не смог распознать капчу');
			  continue;
			}
		}
		$this->output(sprintf('Не получилось разгадать капчу с %s попыток', $cnt));
	  return false;
	}
}












