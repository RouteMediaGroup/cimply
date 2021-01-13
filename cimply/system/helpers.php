<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\System {
    class Helpers {

        public static $response, $namespace = 'default', $project = 'default', $vars = [];

        /**
         * 
         * @param type $key
         * @param type $var
         * @param type $filter
         * @param type $overwrite
         * 
         */
        public static function Setter($key = null, $var = null, $filter = null, $overwrite = false) {	           
            $vars = array();
            if(isset($key) && (isset($var))) {
                (is_string($key) && array_key_exists($key, self::$vars)) ? $vars = self::$vars[$key] : null;
                $var = \ArrayParser::FilterArray($var, $filter);
                if(!(empty($vars)) && $overwrite == false) {
                    if(isset($filter)) {
                        self::$vars[$key] = (isset($var[$filter]) && is_array($var[$filter])) ? array_merge($vars, $var[$filter]) : $vars;
                    } else {
                        self::$vars[$key] =  is_array($var) ? array_merge($vars, $var) : $vars;
                    }
                } else {
                    if(!isset($vars) || !$overwrite == true) {
                        (is_string($key) || is_integer($key)) ? self::$vars[$key] = $var : null;    
                    }    
                }
            }
        }
        
        /**
         * 
         * @param type $key
         * @param type $isArray
         * @return type
         * 
         */
        public static function Getter($key, $isArray = true) {
            if($isArray) {
                $result = array();
                $result[$key] = self::$vars[$key];
            } else {
                $result = self::$vars[$key];
            }
            return $result;
        }

        /**
         * 
         * @param type $key
         * @param type $subkey
         * @param type $explicitly
         * @return type
         * 
         */
        public static function GetItems($key = null, $subkey = null, $explicitly = false) {
            if($explicitly == true) {
                if(isset(self::$vars[$key][$subkey])) {
                    return self::$vars[$key][$subkey];
                }
                return null;
            }
            if(isset(self::$vars[$key])) {
                if(isset($subkey) && (isset(self::$vars[$key][$subkey]))) {
                    return self::$vars[$key][$subkey];
                } else {
                    return self::$vars[$key];    
                }
            }
            if(isset($key)) {
                return null;
            }
            return self::$vars;
        }

        public static function GetUnique($varName = '') {
            $result = null;
            if(isset($_SESSION[$varName])) {
                $result = $_SESSION[$varName];
                self::ClearSession($varName);
            }
            return $result;
        }

        public static function GetGlobal($varName) {
            $return = $_SESSION[$varName] ?? null;
                //CIM::assignVars($varName, $return);
                //self::ClearSession($varName);
            
            return $return;
        }

        public static function SetSession($varName, $varValue) {
            if(isset($varName) && (isset($varValue))) {
                if(isset($_SESSION[$varName]) && is_array($_SESSION[$varName])) {
                    $_SESSION[$varName] = array_merge($_SESSION[$varName], $varValue); 
                } else {
                    $_SESSION[$varName] = $varValue;  
                }
            } 
        }

        public static function GetSession($varName = '', $key = null) {
            if(isset($_SESSION[$varName])) {
                if(isset($key) && (is_array($_SESSION[$varName]))) {
                    return $_SESSION[$varName][$key];
                } else {
                    return $_SESSION[$varName];
                }
            } else {
                return null;
            }
        }

        public static function ClearSession($varName='') {
            if(!($varName)) {
                $data = isset($_SESSION) ? $_SESSION : [];
                foreach($data as $key=>$vars) {
                    if(!($vars == 'project')) {
                        unset($_SESSION[$key]);
                    }
                }
            } else {
                isset($_SESSION[$varName]) ? $_SESSION[$varName] = null : null;
            }
        }

        public function SetBaseDir($dir = '') {
            //$this->conf = CIM::useConfig();
            if(!($dir == '/') AND ($this->conf['symlink']==true)) {
                if($baseDir === self::$conf['baseDir']) {
                    $this->baseDir = '/'.$baseDir.'/';
                }
                return $this->baseDir = $this->baseDir.$dir;
            }
        }

        public function __invoke() {
            call_user_func_array($this->callable ?? '__construct', array_merge($this->args ?? [], func_get_args() ?? []));
        }

        public static function Invoke($name = null, $class = null, $method = null, $data = array(), $viewModel = null) {
            if (\class_exists(str_replace('/', '\\', $class))) {
                $abstractClass = new \ReflectionClass(str_replace('\\\\', '\\', $class));
                if(!$abstractClass->isAbstract()) {
                    class_exists($class) ? $objClass = new $class($data, $viewModel) : null;
                } else {
                    return false;
                }
                if(empty($method)) {
                    $method = '__construct';
                } else {                   
                    self::Setter('Annotate', \Annotation::RouteClass($class, $method) ?? []);
                }
                $parseData = is_array($data) ? $data : \JsonDeEncoder::Decode($data, true) ?? [];
                foreach ($parseData as $key => $value) {
                    if (is_array($value)) {
                        $objClass = self::Setter($objClass, $value);
                    } else {
                        $objClass->parameter[$key] = $value;
                    }
                }
                return self::CallFunction($objClass, $method, $parseData);
            }
        }
        
        private static function CallFunction($objClass = null, $method = null, $parseData = array()) {
            if(isset($objClass)) {
                $reflection = new \ReflectionMethod($objClass, $method);
                if ($reflection->isPublic()) {
                    $objClass->$method = \call_user_func_array(array($objClass, $method), $parseData);
                } else {
                    return ("expects parameter 1 to be a valid callback, cannot access private method '{$method}()'");    
                }
            }
            if(is_array($path = self::GetItems('CurrentObject', 'databinding'))) {
                $datamodel = isset($method) && !is_array($parseData) ? call_user_func_array(array( $objClass, $method), $parseData) : null;
                if(isset($path['name']) && isset($path['filetype']) && isset($path['callback']) && isset($datamodel)) {
                    self::SetStorage($path['name'].'.'.$path['filetype'], $datamodel, $path['callback']);    
                } else {
                    isset($objClass->$method) ? self::Callback($objClass->$method, self::GetItems('CurrentObject', 'type')) : null;    
                }
            }
            return $objClass->$method;
        }

        /**
         * 
         * @param type $result
         * @param type $type
         * @param type $key
         * @return boolean
         * 
         */
        public static function Callback($result, $type = true, $key = null) {
            if(empty($result)) {
                return false;
            }
            switch ($type) {
                case "config":
                    return json_encode(self::GetItems());
                    break;

                case "xml":
                    $xml = new SimpleXMLElement('<root/>');
                    array_walk_recursive($result, array ($xml, 'addChild'));
                    print $xml->asXML();
                    break;

                case "yml":
                    //header('Content-type: text/html');
                    return \YamlParser::ArrayToYAML($result);

                case "json":
                    header('Content-Type: application/json');
                    die(json_encode($result, true));
                    break;

                case "_json":
                    header('Content-Type: application/json');
                    return json_encode($result);
                    break;

                case "localstorage":
                    return json_encode($result);
                    break;

                case "jsVar":
                    return "<script> var ".$key." = '". $result ."'; console.log(".$key.");</script>";
                    break;
                
                case "console":
                    echo json_encode($result);
                    if($result) {
                        exit;
                    }
                    break;

                case "html":
                    return htmlentities($result);
                    break;

                case "text":
                    return strip_tags($result);
                    break;

                case "serialize":
                    return serialize($result);
                    break;

                case "unserialize":
                    return unserialize($result);
                    break;

                case "array":
                    foreach($result as $key=>$val) {
                        $array[$key] = $val;
                    }
                    return $array;
                    break;

                case "yaml":
                    return self::ArrayToYAML($result);
                    break;
                
                case "object":
                    echo $result;
                    break;
                
                case "trim":
                    return trim($result, '"');
                    break;
                
                case "list":
                    return self::__invoke('result', 'TableBuilder', 'buildTable', $result)->result;
                    break;

                case "clean":
                    return $result;
                    break;
                
                default:
                    echo json_encode($result);
                    break;
            }
        }

        /**
         * 
         * @param type $code
         * @return type
         * 
         */
        private static function determiningClass($code) {
            $classes = array();
            $tokens = token_get_all($code);
            $count = count($tokens);
            for ($i = 2; $i < $count; $i++) {
                if ($tokens[$i - 2][0] == T_NAMESPACE && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                    $namespace = $tokens[$i][1];
                    $classes[] = $namespace;
                }
                if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                    $class_name = $tokens[$i][1];
                    $classes[] = $class_name;
                }
            }
            return $classes;
        }


    }
}