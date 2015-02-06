<?php

/**
 * $imgval 获取文件
 * $newwidth 生成缩略图宽度
 * $newheight 生成缩略图高度
 * $typeval 图片类型
**/
function upimg($imgval,$newwidth,$newheight,$typeval){
    //extFrm($imgval."+".$newwidth."+".$newheight);
    $img=$imgval;//$sdir.$filename;//获取该图片
    $type=$typeval;//获取文件类型
    list($width,$height)=getimagesize($img);//获取该图片大小
    $newimg=imagecreatetruecolor($newwidth,$newheight);
    if($type=="gif" || $type=="GIF"){
        $source=imagecreatefromgif($img);
    }
    else if($type=="jpg" || $type=="JPG" || $type=="jpeg" || $type=="JPEG"){
        $source=imagecreatefromjpeg($img);
    }
    else if($type=="png" || $type=="PNG"){
        $source=imagecreatefrompng($img);
    }
    imagecopyresampled($newimg,$source,0,0,0,0,$newwidth,$newheight,$width,$height);
    if($type=="gif" || $type=="GIF"){
        imagegif($newimg,$img);
    }
    else if($type=="jpg" || $type=="JPG" || $type=="jpeg" || $type=="JPEG"){
        imagejpeg($newimg,$img);
    }
    else if($type=="png" || $type=="PNG"){
        imagepng($newimg,$img);
}