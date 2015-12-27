<?php
include('./VkQuery.php');

$vk = new VkQuery();

$img = array('file1.jpg','file1.jpg');
$group_id = 12345; //id группы
$param['album_id'] = 12345; //id альбома
$countImg = count($img); //считаем кол-во изображений

//циклом загружаем фото (цикл делаем если фото больше 1, кол-во загружаемых фото неограничено)
for($i=1;$i<=$countImg;$i++){
$photoSave = $vk->uploadsImage($img[$i-1],'album',$group_id,$param);
}
?>
