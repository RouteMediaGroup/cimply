<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply\Basics\Repository\Images {

    /**
     * Description of Cim_View_Form
     *
     * @author MikeCorner
     */
    class Resize {
        
        public function __constructor($name, $filePath, $newWidth, $newHeight, $mimeType = null) {
            switch ($mimeType) {
                case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_return_func = 'imagejpeg';
                    break;

                case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    $image_return_func = 'imagepng';
                    break;

                case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_return_func = 'imagegif';
                    break;

                case 'image/svg+xml':
                    die($this->ConvertFromSvg($filePath, $newWidth, $newHeight));
                    break;

                default: 
                    $img = imagecreatefromstring(file_get_contents($filePath));
                    $image_return_func = 'imagejpg';
            }
            if(!isset($img)) {
                $img = $image_create_func($filePath);    
            }
            list($width, $height) = getimagesize($filePath);

            $newHeight = ($height / $width) * $newWidth;
            $tmp = imagecreatetruecolor($newWidth, $newHeight);
            imagecolortransparent($tmp, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            return $image_return_func($tmp);

        }
        
        public function ConvertFromSvg($filePath, $newWidth = 100, $newHeight = 0, $outputFormat = 'png') {
            $im = new \Imagick();
            $im->readImage($filePath);
            $res = $im->getImageResolution();
            $x_ratio = $res['x'] / $im->getImageWidth();
            $y_ratio = $res['y'] / $im->getImageHeight();
            if($newHeight == 0) {
                $percent = $newWidth;
                $newWidth = ($im->getImageWidth() / 100) * $percent;
                $newHeight = ($im->getImageHeight() / 100) * $percent;
            }
            $im->removeImage();
            $im->setResolution($newWidth * $x_ratio, $newHeight * $y_ratio);
            $im->readImage($filePath);
            // Now you can do anything with the image, such as convert to a raster image and output it to the browser:
            $im->setImageFormat($outputFormat);
            header("Content-Type: image/".$outputFormat);
            return $im;
        }
    
    }

}