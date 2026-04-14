<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
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

