<?php

include('./VkQuery.php');
$vk = new VkQuery();

$img = 'FILE.jpg'; //Линк на файл с картинкой
$group_id = 'GROUP_ID'; //ID Группы, если постим в группу
$photo = $vk->uploadsImage($img,$group_id);

//Массив данных для поста
$wp = array(
'owner_id' => -$group_id,
'from_group' => 1,
'message' => 'Kakoyto text!',
'attachments' => 'photo'.$photo->response[0]->owner_id.'_'.$photo->response[0]->pid
);

$wallpost = $vk->getData('wall.post',$wp); //Публикуем пост
echo 'ID Post - '.$wallpost->response->post_id; //Выводим ID опубликованного поста
?>
