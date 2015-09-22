<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 15/6/23
 * Time: 下午4:51
 */
//遍历文件夹
function getUnbindedImg($dirforscan){
    $unbindedfiles = array();
    $files = scandir($dirforscan);
    foreach($files as $path){
        if($path!=='.'&&$path!=='..'){
            $imgs = scandir($dirforscan.$path);
            foreach($imgs as $img){
                if($img!=='.'&&$img!=='..') {
                    $file = $dirforscan . $path . '/' . $img;
                    $imgtype_ = explode('.',$file);
                    $imgtype = end($imgtype_);
                    if($imgtype=='jpeg'||$imgtype=='jpg'||$imgtype=='png'||$imgtype=='gif'){
                        array_push($unbindedfiles,$file);
                    }
                }
            }
        }
    }
    return $unbindedfiles;
}

