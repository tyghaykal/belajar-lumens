<?php

namespace App\Helpers;

class ImageResize {
    public static function resize ($dataImages, $type, $name){
        $returnImages = [];
        foreach ($dataImages as $dataImage){
            $img = explode('.',$dataImage[$name]);
            switch($type){
                case 'cropped' : 
                    $img[count($img) - 1] = '-cropped.'.$img[count($img) - 1];
                break;
            }
            
            $dataImage[$name] = implode('',$img);
            array_push($returnImages, $dataImage);
        }
        return $returnImages;
        
    }
}