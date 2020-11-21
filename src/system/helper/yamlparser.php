<?php
namespace {   
    abstract class YamlParser {
        
        public static function AddYaml($array, $yaml_file) {
            try {
                $a = \sfYaml::load($yaml_file);
            } catch (InvalidArgumentException $e) {
                die($e->getMessage());
            }	
            if (is_array($a)) {
                $array = ArrayParser::MergeArrays($a, $array);
            }
            return $array;
        }
        
        public static function ArrayToYAML($result = array(), $spaces = "") {
            $i = 0;
            $output = '';
            foreach ($result as $key=>$value) {
                if(is_array($value)) {
                    $output.=$spaces.$key.":"; 
                    $space[$i] = $spaces;
                    $output.= "
".self::ArrayToYAML($value, '   '.$space[$i]);  
                } else {
                    if($value) {
                        $output.= $spaces.$key.":    ".$value."
";
                    }
                }
                $i++;
            }
            return $output;
        }

    }
}
