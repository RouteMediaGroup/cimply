<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\Basics\Repository\Images {
    /**
     * Description of CIM
     *
     * @author MikeCorner
     */
    class Crop {
        public function __constructor($filePath, $x1, $y1, $x2, $y2, $mimeType) {
            try {
                $imagick = new \Imagick(realpath($filePath));
                $imagick->cropImage($x1, $y1, $x2, $y2);
                die($imagick->getImageBlob());    
            } catch (Exception $ex) {
                die($ex);
            }
        }        
    }    
}

