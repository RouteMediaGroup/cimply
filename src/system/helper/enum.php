<?php
namespace {
    abstract class Enum {

        private static $constCacheArray = NULL;

        private static function GetConstants() {
            if (self::$constCacheArray == NULL) {
                self::$constCacheArray = array();
            }
            $calledClass = get_called_class();
            if (!array_key_exists($calledClass, self::$constCacheArray)) {
                $reflect = new \ReflectionClass($calledClass);
                self::$constCacheArray[$calledClass] = $reflect->getConstants();
            }
            return self::$constCacheArray[$calledClass];
        }

        public static function isValidName($name, $strict = false) {
            $constants = self::GetConstants();

            if ($strict) {
                return array_key_exists($name, $constants);
            }

            $keys = array_map('strtolower', array_keys($constants));
            return in_array(strtolower($name), $keys);
        }

        public static function isValidValue($value, $strict = true) {
            $values = array_values(self::GetConstants());
            return in_array($value, $values, $strict);
        }

        public static function getValueType($value) {
            $currentValue = null;
            $values = array_values(self::GetConstants());
            if(in_array($value, $values, true)) {
                $currentValue = self::ValueType($value);
            }
            return $currentValue;
        }

        private static function ValueType($value) {
            return strtoupper(gettype($value));
        }

        public static function GetValueList() {
            return self::GetConstants();
        }

    }
}