<?php
namespace {
    class Helper {
        
        private static $variable = array();

        public static function Setter($key = null, $var = null, $filter = null, $overwrite = false) {	           
            $variable = array();
            if(isset($key) && (isset($var))) {
                (is_string($key) && array_key_exists($key, self::$variable)) ? $variable = self::$variable[$key] : null;
                $var = ArrayParser::FilterArray($var, $filter);
                if(!(empty($variable)) && $overwrite == false) {
                    if(isset($filter)) {
                        self::$variable[$key] = (isset($var[$filter]) && is_array($var[$filter])) ? array_merge($variable, $var[$filter]) : $variable;
                    } else {
                        self::$variable[$key] =  is_array($var) ? array_merge($variable, $var) : $variable;
                    }
                } else {
                    if(!isset($variable) || !$overwrite == true) {
                        self::$variable[$key] = $var;    
                    }    
                }
            }
        }

        public static function Getter($key) {
            return self::$variable[$key] ? self::$variable[$key] : null;
        }

        public static function GetItems($key = null, $subkey = null, $explicitly = false) {
            if($explicitly == true) {
                if(isset(self::$variable[$key][$subkey])) {
                    return self::$variable[$key][$subkey];
                }
                return null;
            }
            if(isset(self::$variable[$key])) {
                if(isset($subkey) && (isset(self::$variable[$key][$subkey]))) {
                    return self::$variable[$key][$subkey];
                } else {
                    return self::$variable[$key];    
                }
            }
            if(isset($key)) {
                return null;
            }
            return self::$variable;
        }
        
    }
}