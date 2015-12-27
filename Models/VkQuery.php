<?php
/*
 * Class VkQuery
 * @author: Старцев Владислав
 * @link: https://github.com/esvlad/VkQuery
 * @version: 1.2
 */

class VkQuery{
	private $count = -1; //Счётчик обращений к приложению
	private $client_id = ''; //ID Приложения
	private $client_secret = ''; //Секретный ключ приложения
	private $token = ''; //Пользовательский токен
	private $arrdata = array(); //Массив передаваемых значений
	
	//Формирование GET запроса к API, данные возвращаются методом getData()
	public function jsonGetQuery($method, array $data){
		//Если запросов к API больше 3 то спим 1 сек.
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
        //$params['v'] = '5.42';
        
        //Осуществляем запрос на сервер ВК
	$json = file_get_contents('https://api.vk.com/method/' . $method . '?' . http_build_query($params));
        return json_decode($json);
	}
	
	/*
	 * Выполнение GET запроса
	 * @param string $method - метод API, http://vk.com/dev/methods
	 * @param array $data - параметры метода
	 * @param boolean $token - токен, если не нужен, то false (по умолчанию), либо true
	 * @return array - выводит массив данных
	*/
	
	public function getData($methods,$data,$token = false){
		//Если используется метод wall.post, то следует вернуть массив данных,
		//обработанный методом wallPost()
		if($methods == 'wall.post') return $this->wallPost($data);
		
		//Если есть параметры метода, то преобразовать их в массив объекта $this->arrdata
		if($data!=null){
			foreach($data as $k=>$v){
				$this->arrdata[$k]=$v;
			}
		}
		
		//Если необходимо передать токен, то присваиваем массиву $this->arrdata ключ access_token и сам токен
		if($token==true) $this->arrdata['access_token'] = $this->token;
		
		return $this->jsonGetQuery($methods,$this->arrdata);
	}
	
	/*
	 * Реализация GET запроса для Серверных методов, аналогично getData(),
	 * дополнительно включена передача секретного ключа приложения  
	 * $this->arrdata['client_secret']
	*/
	public function secData($methods,$data,$token = false){
		if($data!=null){
			foreach($data as $k=>$v){
				$this->arrdata[$k]=$v;
			}
		}
		if($token==true) $this->arrdata['access_token'] = $this->token;
		$this->arrdata['client_secret'] = $this->client_secret;
		
		return $this->jsonGetQuery($methods,$this->arrdata);
	}
	
	/*
	 * Метод для отправки файлов POST запросом используя CURL
	 * @param string $uploadUrl - ссылка для загрузки файлов
	 * @param array $data - массив данных включая файл для загрузки
	 * @return array - выводит массив данных
	 */
	private function postData($uploadUrl, $data){
		$ch = curl_init($uploadUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: multipart/form-data'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, 1);
		if($data != null) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$json = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($json);
	}
	
	/*
	 * Метод для добавления фотографии в ВК на стену юзера или группы
	 * @param string $file - загружаемый файл
	 * @param string $type - тип публикации, при публикации на стену указываем 'wall', при добавлении в альбом 'album'
	 * по умолчанию 'wall'
	 * @param int $group_id  - id группы (по умолчанию null, если загружаем на стену пользователя)
	 * @param array $param - доп. параметры, если нужно загружать в альбом, то нужно передать $param['album_id'] = ID альбома
	 * @return array - выводит массив данных
	 */
	public function uploadsImage($file, $type = 'wall', $group_id = null, $param = null){
		
		if($group_id!=null) $this->arrdata['group_id']=$group_id;
		else $this->arrdata = null; 
		
		if($type == 'wall'){
			$upload = $this->getData('photos.getWallUploadServer',$this->arrdata,true);
			$files = array('photo' => '@'.$file);
			$saveMethod = 'photos.saveWallPhoto';
			$typeData = 'photo';
		} else {
			if(array_key_exists('album_id',$param)) $this->arrdata['album_id']=$param['album_id'];
			$upload = $this->getData('photos.getUploadServer',$this->arrdata,true);
			$files = array('file1' => '@'.$file);
			$saveMethod = 'photos.save';
			$typeData = 'photos_list';
		}
		$uploadURL = $upload->response->upload_url;
		//Отправляем файл на сервер
		$json = $this->postData($uploadURL, $files);
		
		$photoParams = array(
			'server' => $json->server,
			$typeData => $json->$typeData,
			'hash' => $json->hash
		);
		
		if(array_key_exists('group_id',$this->arrdata)) {
			unset($photoParams['user_id']);
			$photoParams['group_id'] = $this->arrdata['group_id'];
		}
		
		if($param!=null){
			foreach($param as $k=>$v){
				$photoParams[$k]=$v;
			}
		}
		
		//Сохраняем фотографию
		$photo = $this->getData($saveMethod,$photoParams,true);
		
		if(isset($photo->response[0]->id)){
			return $photo;
		} else {
			return false;
		}
	}
	
	/*
	 * Метод для добавления аудиозаписей
	 * @param string $file - загружаемый файл
	 * @param array $data - массив данных, либо null
	 * @return array - выводит массив данных
	 */
	public function uploadsAudio($file,$data = null){
		$upload = $this->getData('audio.getUploadServer',null,true);
		$uploadURL = $upload->response->upload_url;
		$files = array('file' => '@'.$file);
		$json = $this->postData($uploadURL, $files);
		
		$audioParams = array(
			'server' => $json->server,
			'audio' => ($json->audio),
			'hash' => $json->hash
		);
		
		if($data!=null){
			foreach($data as $k=>$v){
				$audioParams[$k]=$v;
			}
		}
		
		$audio = $this->getData('audio.save',$audioParams,true);
		
		if(isset($audio)){
			return $audio;
		} else {
			return false;
		}
	}
	
	/*
	 * Метод для добавления аудиозаписей
	 * @param string $file - загружаемый файл, если добавляем не с сервера, то null
	 * @param array $data - массив данных, если file = null, 
	 * то нужно передать хотябы $data['link'], либо null
	 * @return array - выводит массив данных
	 */
	public function uploadsVideo($file = null,$data = null){
		if($data!=null){
			foreach($data as $k=>$v){
				$videoParams[$k]=$v;
			}
		}

		if($file!=null){	
			$video = $this->getData('video.save',$videoParams,true);
			$uploadURL = $video->response->upload_url; 
			$files = array('video_file' => '@'.$file);
			$json = $this->postData($uploadURL, $files);
			
			if($json) return $video;
			else return $err[0] = 'Видео не добавленно!';
			
		} else {
			if(array_key_exists('link',$videoParams)){
				$video = $this->getData('video.save',$videoParams,true);
				$uploadURL = $video->response->upload_url;
				$json = json_decode(file_get_contents($uploadURL));
				
				if($json) return $video;
				else return $err[0] = '<br>Видео не добавленно';
				
			} else {
				return $err[0] = 'Неуказана ссылка';
			}
		}
		
	}
	
	/*
	 * Метод для добавления документов
	 * @param string $file - загружаемый файл
	 * @param array $info - массив данных для docs.save, по умолчанию null
	 * @param int $data - id группы, если нужно, либо null
	 * @return array - выводит массив данных
	 */
	public function uploadsDocuments($file,$info = null,$data = null){
		$doc = $this->getData('docs.getUploadServer',$data,true);
		$uploadURL = $doc->response->upload_url;
		$files = array('file' => '@'.$file);
		$json = $this->postData($uploadURL, $files);
		$docParams['file'] = $json->file;
		
		if($info!=null){
			foreach($info as $k=>$v){
				$docParams[$k]=$v;
			}
		}
		
		$documents = $this->getData('docs.save',$docParams,true);
		
		if(isset($documents)){
			return $documents;
		} else {
			return false;
		}
	}
	
	/*
	 * Приватный метод для добавления постов на стены
	 * @param array $data - массив данных берётся из метода getData()
	 * @return int - возращает id Поста
	 * Bспользуется: getData('wall.post',$data)
	 */
	private function wallPost($data){
		$wallURL = 'https://api.vk.com/method/wall.post?access_token='.$this->token;
		return $json = $this->postData($wallURL,$data);
	}
	
}

?>
