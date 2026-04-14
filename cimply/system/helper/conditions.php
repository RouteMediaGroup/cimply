<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace {

    abstract class Conditions {

        public static function IsNullOrEmpty($value = null, $return = false, $depth = null) {
            if(isset($value)) {
                if(isset($depth)) {
                    $value = isset($value[$depth]) ? $value[$depth] : $value;
                }
                return !(isset($value) && !empty($value)) ? false : ((!$return) ? true : $value);
            }
            return $value;
        }
        
        public static function IfThanElse($conditions, $value1 = null, $value2 = null) {
            return self::IsNullOrEmpty($conditions) ? (self::IsNullOrEmpty($value2) ? $value2 : $conditions) : $value1;
        }
    }
}