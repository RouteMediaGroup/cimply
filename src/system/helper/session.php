<?php
namespace {  
    abstract class Session {
        /**
         * 
         * @param type $value
         * @param type $return
         * @return type
         * 
         */
        public static function HasRegist($value = null, $session = null) {
            if(is_array($value)) {
                foreach($value as $key => $val) {
                    if((bool)$session::GetSession($key)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
}