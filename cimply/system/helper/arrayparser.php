<?php
namespace { 
    abstract class ArrayParser {      
        
        static function ArrayAs($key, $value) {
            $result = array();
            foreach($value as $k => $v) {
                $result[$key][$k] = $v;
            }
            return $result;
        }
        
        static function ToArray($string = '', $delimiter = ';', $find = null) {
            $result = \explode($delimiter, $string);
            if(isset($find)) {
                foreach($result as $k => $v) {
                    if(isset($v[$find])) {
                        return $v;
                    }
                }
            }
            return $result;
        }
        
        static function ToStringImplode($params = [], $sep = ',', $keys = false): String {
            $result = [];
            if(is_array($params)) {
                foreach($params as $k => $v) {
                    if($keys) {
                        $result[]= $k;
                        $result[]= $v;
                    } else { 
                        $result[] = $v;
                    }
                }
            }
            $result = implode($sep, $result) ?? "";
            return $result;
        }
        
        static function ArrayToString($params = null, $key = null, $delimiter = ' ', $addkey = true) {
            $result = array();
            if(is_array($params[$key])) {
                foreach($params[$key] as $key => $value) {
                    if($addkey) {
                        $result[] = $key.'="'.$value.'"';
                    } else {
                        $result[] = $value;
                    }
                }
            }
            return implode($delimiter, $result);
        }
        
        static function MergeArrays($arr1 = [], $arr2 = []) {
            if(isset($arr2)) {
                foreach($arr2 as $key => $value) {
                    if(array_key_exists($key, $arr1 ?? []) && is_array($value)) {
                        $arr1[$key] = self::MergeArrays($arr1[$key] ?? '', $arr2[$key] ?? '');
                    } else {
                        $arr1[$key] = $value ?? '';
                    }
                }
            }
            return $arr1;
        }
        
        static function AddArray($array1, $array2) {
            if (is_array($array2)) {
                return self::MergeArrays($array1, $array2);
            }
        }
        
        static function FlattenArray($multiArray = array(), $toObject = null, $depth = null) {
            $result = [];
            foreach($multiArray as $key => $value) {
                if (is_array($value)) {
                    foreach($value as $k => $v) {
                        $ucKey = ucfirst($k);
                        $result[$key.':'.$ucKey] = $v;
                    }
                }
                else {
                    $result[$key] = isset($depth) ? $multiArray[$depth]  : $multiArray[$key];
                }
            }
            return isset($toObject) ? (object)$result : $result;
        }
        
        static function GetArray($array = array(), $filter = null, $needle = null) {
            $result = array();
            if(isset($filter)) {
                $filters = explode("/", $filter);
                if(isset($filters[1])) {
                    $result = self::SearchArrayRecursive($filters, $array);
                    isset($needle) ? (in_array($needle, $result)) ? true : false : $result;
                    return $result;
                }
                return isset($array[$filter]) ? $array[$filter] : null;
            }
            
        }
        
        static function FilterArray($multiArray = array(), $filter = null) {
            if(isset($filter)) {
                foreach ($multiArray as $key => $value) {
                    $multiArray[$key] = $key;
                    if(isset($value[$filter])) {
                        $multiArray[$key] = $value[$filter];    
                    }
                }
            }
            return $multiArray;
        }
        
        static function SearchArrayRecursive($needles, $haystack) {
            $count = count($needles);
            $result = null;
            foreach($needles as $key => $value) {
                if(isset($haystack[$value])) {
                    if(($result = $haystack[$value]) && $count > 1) {
                        unset($needles[$key]);
                        $result = self::SearchArrayRecursive($needles, $result);
                        break;
                    } else {
                       if(is_array($haystack)) {
                            $result = $haystack[$value];    
                       } else {
                            $result = $haystack;
                       }       
                    }
                } 
            }
            return $result;
        }

        static function IsAssocArray($var)
        {
            return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
        }
    }
}
