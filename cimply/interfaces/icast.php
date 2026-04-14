<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Interfaces {
    /**
     * Description of ICast
     * 
     * @version 1.0
     * @package Cimply\Interfaces
     */
    const Cull = '\Cast::Cull';
    interface ICast {
        public static function Cull($mainObject, $selfObject);
    }
}