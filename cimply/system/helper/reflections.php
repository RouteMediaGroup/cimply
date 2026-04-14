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
