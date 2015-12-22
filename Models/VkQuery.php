<?php

class VkQuery{
	private $count = -1;
	private $client_id = '';
	private $client_secret = '';
	private $token = '';
	
	//Формирование _GET запроса к API
	public function jsonGetQuery($method, array $data){
		
		$this->count ++;
        if($this->count >= 3){
            $this->count = 0;
            sleep(1);
        }
        
        $params = array();
        if($data != null){
			foreach($data as $name => $val){
				$params[$name] = $val;
			}
		}
        
        $params['v'] = '5.41';
        
		$json = file_get_contents('https://api.vk.com/method/' . $method . '?' . http_build_query($params));
        return json_decode($json);
	}
	
	//Формирование _POST запроса к API
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
	
	//Реализация _GET запроса
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
	
	//Реализация _GET запроса для Серверных методов
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
	
	//Реализация _POST запроса
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
