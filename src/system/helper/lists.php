<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CIM
 *
 * @author MikeCorner
 */
namespace { 
    abstract class Lists {

        private static $reference;

        public static function ListOfObjects($value, $name = null, $setArray = false, $json = false)
        {
            $result = $value;
            (is_array($value)) ? $value = \JsonDeEncoder::Encode($value) : null;
            $decodeObject = \JsonDeEncoder::Decode($json ? \JsonDeEncoder::Encode($value) : $value, $setArray);
            if(isset($name)) {
                $name = self::NameHelper($name);
                if($setArray) {
                    $keys = explode('/', $name);
                    self::$reference = self::KeyTree($keys, $decodeObject);
                } else {
                    self::$reference[$name] = $decodeObject;
                }
            } else {
                self::$reference = (array)$decodeObject;
            }
            return self::$reference;
        }
        
        public static function KeyTree($keys, $a) {
             $i=count($keys)-1;
            foreach($keys as $key){
                $a = array($keys[$i] => $a);
                $i--;
            }
            return $a;
        }
        
        public static function KeyPath($arrayPath, $level=0) {
            $newArray = array();
            $lastKey = null;
            foreach($arrayPath as $key => $value) {
                if($lastKey) {
                    $newArray[$lastKey] = array($value => null);
                    $lastKey = $value;
                } else {
                    $newArray = array($value => array());
                    $lastKey = $value;
                }
                $level++;
            }
            return $newArray;
        }
        
        public static function ArrayList($value, $name = null)
        {
            $name = self::NameHelper($name);
            isset($name) ? self::$reference[$name] = is_array($value) ? $value : \JsonDeEncoder::Decode($value, true) : self::$reference = is_array($value) ? $value : \JsonDeEncoder::Decode($value, true);
            return (array)self::$reference;
        }
        
        public static function ObjectList($value, $name = null)
        {
            is_array($value) ? $value = \JsonDeEncoder::Encode($value) : null;
            $name = self::NameHelper($name);
            isset($name) ? self::$reference[$name] = \JsonDeEncoder::Decode($value) : self::$reference = \JsonDeEncoder::Decode($value);
            return (object)self::$reference;
        }
        
        protected static function NameHelper($name = null) {
            isset($name) ? $name : (
                (isset($value) && is_array($value))  ? 
                $name = key($value) : 
                null 
            );
            return $name;
        }
        
        public static function GetValues($objectList) {
            $list = array();
            foreach(get_object_vars($objectList)  as $key => $value) {
                $list[] = $key.':'.$value.PHP_EOL;
            }
            return $list;
        }
        
    }
}
/*
namespace { 
    abstract class Lists {

        private static $reference;

        public static function ListOfObjects($value, $name = null)
        {
            (is_array($value)) ? $value = \JsonDeEncoder::Encode($value) : null;
            if((bool)isset($name) ? : $name = self::NameHelper($value)) {
                self::$reference[$name] = (array)\JsonDeEncoder::Decode($value);
            } else {
                self::$reference = (array)\JsonDeEncoder::Decode($value);
            }
            return self::$reference;
        }
        
        public static function ArrayList($value, $name = null)
        {
            isset($name) ? : $name = self::NameHelper($value);
            isset($name) ? self::$reference[$name] = is_array($value) ? $value : \JsonDeEncoder::Decode($value, true) : self::$reference = is_array($value) ? $value : \JsonDeEncoder::Decode($value, true);
            return (array)self::$reference;
        }
        
        public static function ObjectList($value, $name = null)
        {
            is_array($value) ? $value = \JsonDeEncoder::Encode($value) : null;
            isset($name) ? : $name = self::NameHelper($value);
            isset($name) ? self::$reference[$name] = \JsonDeEncoder::Decode($value) : self::$reference = \JsonDeEncoder::Decode($value);
            return (object)self::$reference;
        }
        
        protected static function NameHelper($value = null) {
            $name = null;
            is_array($value) ? $name = key($value) : $name;
            return $name;
        }
        
        public static function GetValues($objectList) {
            $list = array();
            foreach(get_object_vars($objectList)  as $key => $value) {
                $list[] = $key.':'.$value.PHP_EOL;
            }
            return $list;
        }
        
    }
}
*/