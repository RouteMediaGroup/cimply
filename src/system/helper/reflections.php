<?php

/*
# 200 OK 
# 301 Moved Permanently 
# 302 Found 
# 304 Not Modified 
# 307 Temporary Redirect 
# 400 Bad Request 
# 401 Unauthorized 
# 403 Forbidden 
# 404 Not Found 
# 500 Internal Server Error 
# 501 Not Implemented
 */

/**
 * Description of CIM
 *
 * @author MikeCorner
 */
namespace { 
    trait Reflections {
        private static $reflection;
        public static function Reflection($Reflection = __CLASS__) {
            self::$reflection = $Reflection;
        }
        public function GetReflection() {
           return self::$reflection;
        }
        public static function Info($currentClass = __CLASS__) {
            $currentClass ?? $currentClass = null;
            return new \ReflectionClass($currentClass);
        }
    }
}
