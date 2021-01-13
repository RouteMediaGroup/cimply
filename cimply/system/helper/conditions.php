<?php

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