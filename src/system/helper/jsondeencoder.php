<?php

namespace {

    abstract class JsonDeEncoder {

        public static function Encode($value = [], $options = 0) {
            return is_array($value) ? \json_encode($value, $options) : "{}";
        }

        public static function Decode($value = null, $assoc = false, $depth = 512, $options = 0) {
            return isset($value) ? ( (is_array($value)) ? $value : \json_decode($value, $assoc, $depth, $options) ) : [];
        }

        public static function CheckJSON($value) {
            try {
                $fixed = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
                $result = \json_decode($fixed);
                if (is_null($result)) {
                    return '{}';
                }
            } catch (Exception $e) {
                return '{}';
            }
            return $fixed;
        }

        public static function IsJson($value = null): bool {
            return isset($value) ? ( is_array($value) ? false : (bool)is_array(\json_decode($value, true))) : false;
        }
        
    }
}