<?php

class VkQuery{
	private $count = -1; //Счётчик обращений к приложению
	private $client_id = ''; //ID Приложения
	private $client_secret = ''; //Секретный ключ приложения
	private $token = ''; //Пользовательский токен
	
	//Формирование GET запроса к API
	public function jsonGetQuery($method, array $data){
		//Если запросов к API больше 3 то ждем 1 сек.
		$this->count ++;
		if($this->count >= 3){
            	$this->count = 0;
            	sleep(1);
        	}
        
		//Формируем массив передаваемх значений
        	$params = array();
        	if($data != null){
			foreach($data as $name => $val){
				$params[$name] = $val;
			}
		}
        
        	//Какую версию API использовать
        	$params['v'] = '5.41';
        
		$json = file_get_contents('https://api.vk.com/method/' . $method . '?' . http_build_query($params));
        	return json_decode($json);
	}
	
	//Формирование POST запроса к API, аналогично GET запросу с некоторыми особенностями
	public function jsonPostQuery($method, array $data){
		
		$this->count ++;
        	if($this->count >= 3){
            		$this->count = 0;
            		sleep(1);
        	}
        
        	$params = array();
        	foreach($data as $name => $val){
            		$params[$name] = $val;
        	}
        
        	$params['v'] = '5.80';
        
        	$postdata = http_build_query($params);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);

		$context  = stream_context_create($opts);

		$json = file_get_contents('https://api.vk.com/method/' . $method . '?', false, $context);
		return json_decode($json);
	}
	
	//Массив передаваемых значений
	private $arrdata = array();
	
	/*
	* Реализация GET и POST запросов
	* Метод передайт трипараметра, название метода API, параметры передаваемых значений, неоходимость передачи токена
	* по умолчанию токен false, если токен необходим, то при запросе нужно указать true
	*/
	
	public function getData($methods,$user_params,$token = false){
		if($user_params!=null){
			foreach($user_params as $k=>$v){
				$this->arrdata[$k]=$v;
			}
		}
		if($token==true) $this->arrdata['access_token'] = $this->token;
		$user_info = $this->jsonGetQuery($methods,$this->arrdata);
		return $user_info;
	}
	
	//Реализация GET запроса для Серверных методов, дополнительно включена передача секретного ключа приложения
	public function secData($methods,$user_params,$token){
		if($user_params!=null){
			foreach($user_params as $k=>$v){
				$this->arrdata[$k]=$v;
			}
		}
		if($token==true) $this->arrdata['access_token'] = $this->token;
		$this->arrdata['client_secret'] = $this->client_secret;
		
		$user_info = $this->jsonGetQuery($methods,$this->arrdata);
		return $user_info;
	}
	
	//Реализация POST запроса
	public function postData($methods,$user_params,$token = false){
		if($user_params!=null){
			foreach($user_params as $k=>$v){
				$this->arrdata[$k]=$v;
			}
		}
		if($token==true) $this->arrdata['access_token'] = $this->token;
		$user_info = $this->jsonPostQuery($methods,$this->arrdata);
		return $user_info;
	}
}

?>
