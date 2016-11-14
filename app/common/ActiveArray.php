<?php

namespace app\common;
use yii\base\Model;

abstract class ActiveArray extends Model{
	
	const FIND_ONE = 1;
	const FIND_ALL = 0;
	
	public $index; 
		
	static public function findAll($needle = null) {
		$arrayAttributes = self::find($needle, self::FIND_ALL);
		// if(!$arrayAttributes){
			// return false;
		// }
		return $arrayAttributes;
		$models = [];
		foreach($arrayAttributes as $attributes){
			$modelClass = new get_called_class();
			$modelClass->attributes = $attributes;
			$models[] = $modelClass;
		}
		return $models;
	}
	
	static public function findOne($needle = null) {
		$class = get_called_class();
		$modelClass = new $class;
		$attributes = self::find($needle, self::FIND_ONE);
		if(!$attributes){
			return null;
		}
		$modelClass->attributes = $attributes;
		return $modelClass;
	}
	
	static protected function find($needle, $flag){
	  $array = self::getArrayFromFile();
		// print_r($array);
		if(!($array && count($array))){
			return [];
		}
		// print_r($needle);exit;

		$foundArray = [];
		
		foreach($array as $item){
			
			$found = true;
			if(!$needle){
				if($flag){
					return $item;
				}
				$foundArray[] = $item;
				continue;
			}
			
			foreach($needle as $key=>$val){
				// echo "{$item[$key]} !== $val<br>";
  			if( (is_numeric( $item[$key]) && !is_numeric($val))  ||
    				(!is_numeric( $item[$key]) && is_numeric($val)) ){
					if ( $item[$key] !== $val ) {
						$found = false;					
					}
				} else {
				  if ( $item[$key] != $val ) {
						$found = false;					
					}
				}
				

			}
			if($found){
				if($flag){
					return $item;
				}
				$foundArray[] = $item;
			}

		}
		return count($foundArray)? $foundArray : [];
		
	}
	
	public static function getIssetRecords(){
		return count(self::getArrayFromFile());
	}
	
	protected static function getDataPath(){
		return \yii::getAlias('@app') . '/models/' . static::fileName();
	}
	
	protected static function saveArrayToFile($array){
		$fileName = self::getDataPath();
		file_put_contents($fileName, serialize($array));
	}
	
	protected static function getArrayFromFile(){
		
		$fileName = self::getDataPath();;
		if(!file_exists($fileName)){
			touch($fileName);
			// throw new \Exception('ActiveArray exception - '.$fileName.' is not exists');
		}
		
		$array = unserialize(file_get_contents($fileName));
		return $array ? : [];
		
	}
	
	public static function saveMultiple(array $array){
		
		$out = [];
		
		if(!count($array)){
			return false;
		}

		foreach($array as $key=>$item){
			$out[$key] = $item;
			$out[$key]['index'] = $key;
		}
		
		self::saveArrayToFile($out);
		
		return true;
		
	}
	
	public function save(){
		
		$array = self::getArrayFromFile();
		$max = count($array) ? max(array_keys($array)) + 1 : 0;
		$this->index = $index = (!is_numeric($this->index)) ? 
		  $max :
		  $this->index;
		$array[$index] = $this->attributes;
		self::saveArrayToFile($array);
		return $this;
	}
	
	public static function deleteAll(){
		self::saveArrayToFile([]);
		return true;
	}
	
	public function delete(){
		
		$array = self::getArrayFromFile();
		unset($array[$this->index]);
		self::saveArrayToFile($array);
		unset($this);
	}
}













