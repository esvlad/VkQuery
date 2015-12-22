<?php

include('Models/VkQuery.php'); //Подключаем класс VkQuery

$vk = new VkQuery();

//Создаем массив передаваемых параметров
$user_params = array(
'user_ids' => '205387401,16512807,15006777,67838708,208760504',
'fields' => 'sex,bdate,city,country,photo_50',
'name_case' => 'abl'
);

 /* 
 * В метод getData передаем значения:
 * название метода users.get, параметры описанные в массиве user_params
 * если требуется токен в конце дописываем true, если нет, то ничего не пишем
 */
 
$user = $vk->getData('users.get',$user_params);

//Простой метод вывода пола пользователя в буквеном варианте
function vkSex($data){
	if($data == 1) $male = 'Жен.';
	else if($data == 2) $male = 'Муж.';
	else $male = 'Неуказан';
	return $male;
}

//Выводим результат обращения к API

foreach($user->response as $v){
	echo $v->id.'<br>';
	echo $v->first_name.' '.$v->last_name.'<br>';
	echo vkSex($v->sex).'<br>';
	if($v->bdate && $v->bdate!=null) echo $v->bdate.'<br>';
	echo $v->photo_50.'<br>';
	if($v->city && $v->city!=null){
		echo $v->city->title.'<br>';
	}
	if($v->country && $v->country!=null){
		echo $v->country->title.'<br>';
	}
	echo '<br>';
}


?>
