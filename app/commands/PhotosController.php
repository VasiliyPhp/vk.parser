<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\TargetAlbum;
use app\models\SourceAlbum;
use app\models\user;
use VK\VK;
use VK\VKException;

class PhotosController extends Controller
{
  protected $vk;
	private $flag_file;
	private $log_file;
  public function actionIndex($key){
		$this->flag_file = yii::getAlias('@runtime') . '/flag';
		$this->log_file = yii::getAlias('@runtime') . '/log';
		$user = User::findOne(['index'=>(int)$key]);
		if(!$user){
			$this->stop('user is not authorized');
	  }
		file_exists($this->flag_file) and $this->stop('Программа уже запущена, надо дождаться завершения');
		touch($this->flag_file);
		$source = SourceAlbum::findAll();
		($source and count($source)) or $this->stop('There are not source albums in app settings');
		$target = TargetAlbum::findOne(['index'=>0]);
		if(!$target->enabled){
				$this->stop('The application is now disabled');
		}
		$target or $this->stop('There are not target album in app settings');
		$this->vk = new VK(yii::$app->params['vk_app_id'], yii::$app->params['vk_secret_key'], $user->vk_access_token);
		// $rs = $this->vk->api('photos.getUploadServer' , ['group_id'=>'113428074','album_id'=>'228317374']);
		// exit(print_r($rs));
		$interval = $target->interval;		
		try {
			set_time_limit(0);
			$this->output("\n\tЗапуск программы");
			$needle = $this->findNeedlePhotos($source, $interval);
			$this->placeNeedlePhotos($needle, $target);
			$this->output(\yii\helpers\Html::a('На главную', ['index']));
		  file_exists($this->flag_file) and unlink($this->flag_file);
		} catch (VKException $vkex) {
			$this->stop('Some error occured. ' . $vkex->getMessage() );
		}
 	}
	
	protected function findNeedlePhotos($source, $interval){
	 $result = [];

		foreach($source as $item ){
				$params = $this->parseVkUrl($item['album']);
				$params['rev'] = 1;
				$rs = $this->vk->api('photos.get',$params);
				if(!isset($rs['response'])){
					throw new VKException(implode(' ', $params) . '. I cannot take photos. ' . $rs['error']['error_msg']);
					// throw new VKException(implode(' ', $params) . '. I cannot take photos. ' . print_r($rs,1));
				}
			  $photos = $rs['response']['items'];
				
				$start_time = time() - ( 3600 * $interval );
				foreach($photos as $photo){
					// echo date('r', $start_time) .' ' .date('r',$photo['date']) .'<br>';
					if ( $start_time < $photo['date'] ){
						$result[] = $photo;
					}
					// print_r($photo);
				}
		
		}
		if(!count($result)){
			throw new VKException('Not found any photo to download.');
		}
    return $result;
		
	}
	
	protected function parseVkUrl($url){
		
		$parsed = preg_replace('~\?.*$~', '', $url);
		
		if ( !preg_match('~album(-?\d+)_(\d+)$~', $parsed, $tmp) ) {
			$this->stop('Incorrect format received album URL - ' . $url);
		}
		
		$owner_id = $tmp[1];
		
		switch($tmp[2]){
		case 00 :
		  $album_id = 'wall'; break;
		case 0 : 
		  $album_id = 'profile'; break;
		case 000 :
		  $album_id = 'saved'; break;
		default :
		  $album_id = $tmp[2];
		}
		
		return compact('owner_id', 'album_id');
		
	}
	
	protected function placeNeedlePhotos($needle, $target){
		extract($this->parseVkUrl($target->album));
		$upload_url = $this->getUploadServer($album_id, $owner_id);
		foreach($needle as $item){
			if(!file_exists($this->flag_file)){
				break;
			}
		  $caption = $item['text'];
			$caption = $this->remakeDescription($item['text'], $target->toArray() );
			if( !$caption ){
				continue;
			}
			$image_url = $this->findMostPhoto($item);
			
			$this->uploadPhoto( compact( 'caption', 'upload_url', 'album_id', 'image_url', 'owner_id' ) );
			sleep((int)$target->delay);
		}
		
		
	}
	
	protected function uploadPhoto($params){
		
		extract($params);
		
		$tmp_name = yii::getAlias('@runtime') . '/vk.image.' . strtolower(pathinfo($image_url,PATHINFO_EXTENSION));
		
		file_put_contents($tmp_name, file_get_contents($image_url));
		
		$rs = json_decode($this->vk->request($upload_url, 'POST', ['file1' => "@" . $tmp_name] ), true);
		unlink($tmp_name);
		
		if(!count(json_decode($rs['photos_list'])) ){
			throw new VKException('I cannot upload image ' . $image_url . ' to upload server');
		}
		
		$params = [
	    'hash'=> $rs['hash'],
	    'server'=> $rs['server'],
	    'photos_list'=> $rs['photos_list'],
	    'group_id'=> strpos($owner_id, '-') === 0 ? str_replace('-', '', $owner_id) : null,
	    'caption'=> $caption,
	    'album_id'=> $album_id,
		];
		
		$rs = $this->vk->api('photos.save', $params );
		
		if(!isset($rs['response'])){
			throw new VKException('I cannot save uploaded photo');
		}
		
		$this->output('One photo has been saved to the album. Description - ' . $caption );
	}
	
	protected function findMostPhoto($array){
		$max = 0;
		foreach(array_keys($array) as $key){
				if(strpos($key, 'photo_') !== 0){
					continue;
				}
				$val = (int)str_replace('photo_', '', $key);
				if( $val > $max ){
					$max = $val;
				}
		}
		return $array['photo_' . $max];
	}
	
	protected function remakeDescription($i, $array){
   $s = '[ :\.\-]*';
		
		$t = '~(?:цена|стоимость)?' 
		    . $s . '(?:опт|розн|розница)?'
				. $s . '(\d+)\s*'
				. '%s'
				// . '(?:\s|\.|$)~iu';
				. '~iu';
		
		$cur = '$';
		
		$dollarSignReg = sprintf($t, '\$' );
		$ueReg = sprintf($t, 'у\.?\s?е' );
		$dollarReg = sprintf($t, 'дол' );
		$usdReg = sprintf($t, 'usd' );
		$grnReg = sprintf($t, 'гр(иве)?н?' );
		echo $withoutCurrency = '~(?:цена|стоимость)?' 
		    . $s . 'опт'
				. $s . '(\d+)\s*'
				. $s
				. '~iu';;
		extract($array);
		
		if(preg_match_all($dollarSignReg, $i, $out)){
			$reg = $dollarSignReg;
		} elseif(preg_match_all($dollarReg, $i, $out)){
			$reg = $dollarReg;
		} elseif(preg_match_all($usdReg, $i, $out)){
			$reg = $usdReg;
		} elseif(preg_match_all($ueReg, $i, $out)){
			$reg = $ueReg;
		} elseif(preg_match_all($grnReg, $i, $out)){
			$reg = $grnReg;
			$cur = 'grn';
		} elseif(preg_match_all($withoutCurrency, $i, $out)){
			$reg = $withoutCurrency;
			$cur = 'grn';
		} else {
			return $i;
		}
		
		$prices = $out[1];
		foreach($prices as $price){
			$pfice = str_replace(' ', '', $price);
			$usd = $cur === '$' ? $price + $extra_price : ( $price / $exchange_g_u ) + $extra_price;
			$ussd = number_format($usd, 1, ',', ' ');
			$byr = number_format($usd * $exchange_u_b, 0, ',', ' ');
			$rub = number_format($usd * $exchange_u_r, 0, ',', ' ');
			$byrdem = number_format($usd * $exchange_u_b / 10000, 0, ',', ' ');
		  $output[] = "\r\nЦена: $ussd USD = $byr BYR = $byrdem руб BYR = $rub RUR\r\n";
		}
		$str = '~#~~%~#~~';
		$tmp = preg_replace($reg, $str, $i);	
		$tmp = preg_replace('~( у(\.|\s)?е(\.|:|\s)|$|\s?опт(\.|:|\s)|\s?розница(\.|:|\s)|\s?грн(\.|\s))~iu', '', $tmp);
		foreach($output as $o){
			$tmp = preg_replace("|$str|", $o, $tmp, 1);
		}
		return preg_replace("~(\r?\n)+~", "\r\n", $tmp);
		return $tmp;
	}
	protected function getUploadServer($album, $owner){
		$params = [
		  'album_id' => $album,
	    'group_id' => strpos($owner, '-') === 0 ? str_replace('-', '', $owner) : null,
		];
		$rs = $this->vk->api('photos.getUploadServer', $params);
		// print_r($params);exit;
		if(!isset($rs['response'])){
			// throw new VKException('I cannot take upload server. ' . print_r($rs,1));
			throw new VKException('I cannot take upload server. ' . $rs['error']['error_msg']);
		}
		return $rs['response']['upload_url'];
	}
	
	protected function stop($message){
		file_exists($this->flag_file) and unlink($this->flag_file);
		file_put_contents($this->log_file, date('d-m-Y H:i:s') . "   $message" . PHP_EOL, FILE_APPEND);
		if(!isset($this->interactive)){		
		  throw new \yii\web\BadRequestHttpException ( $message );
		}
		$this->stderr('Error: ' . $message);
		exit;
	}
	protected function output($message){
		file_put_contents($this->log_file, date(PHP_EOL . 'd-m-Y H:i:s') . "   $message" . PHP_EOL, FILE_APPEND);
		if(!isset($this->interactive)){		
  		print( $message . PHP_EOL);
		} else {
			
			$this->stdout($message);
		}
	}

}















