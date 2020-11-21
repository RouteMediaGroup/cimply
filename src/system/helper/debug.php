<?php

namespace {
    abstract class Debug {
        private static $result, $backtrace;
        public static function VarDump($value = null, $stop = true) {
            self::$backtrace = debug_backtrace();
            if(isset($value) && is_object($value)) {
                self::runReflection(new \ReflectionClass($value), $value);
            } else {
                self::runAll($value);
            }
            var_dump((object)self::$result);
            if($stop) {
                die();
            }
        }
        public function __debugInfo() {
            return [
                'debugInfo' => self::$result,
            ];
        }
        private static function runReflection(ReflectionClass $reflection, $value) {
            self::$result = array(
                "ReflectionFile" => $reflection->getFileName(),
                "Start" => '----------- '.date("Y-m-d H:i:s").': debuging start at line '.$reflection->getStartLine().' -------------',
                "ParentClass" => isset($reflection->getParentClass()->name) ? $reflection->getParentClass()->name : $reflection->getParentClass(),
                "Result" => $value,
                "BackTrace" => @array("CurrentFile" => self::$backtrace[0]['file'].':'.self::$backtrace[0]['line'], "ProcessFile" => self::$backtrace[1]['file'].':'.self::$backtrace[1]['line']),
                "End" => '----------- '.date("Y-m-d H:i:s").': debuging stop at line '.$reflection->getEndLine().' -------------'
            );
        }
        
        private static function runAll($value) {
            $reflection = self::$backtrace;
            asort($reflection);
            self::$result = array(
                "Start" => '----------- '.date("Y-m-d H:i:s").': debuging start -------------',
                "Result" => $value,
                "Reflections" =>isset($value) ? @self::$backtrace[1] : $reflection,
                "BackTrace" => @array("CurrentFile" => self::$backtrace[0]['file'].':'.self::$backtrace[0]['line'], "ProcessFile" => self::$backtrace[1]['file'].':'.self::$backtrace[1]['line']),
                "End" => '----------- '.date("Y-m-d H:i:s").': debuging stop -------------'
            );
        }
    }
}