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
    abstract class YamlParser {
        
        public static function AddYaml($array, $yaml_file) {
            try {
                $a = \sfYaml::load($yaml_file);
            } catch (\InvalidArgumentException) {
                return $array;
            }	
            if (is_array($a)) {
                $array = \ArrayParser::MergeArrays($a, $array);
            }
            return $array;
        }
        
        public static function ArrayToYAML($result = array(), $spaces = ""): string {
            $output = '';
            foreach ($result as $key=>$value) {
                if(is_array($value)) {
                    $output .= $spaces.$key.":\n".self::ArrayToYAML($value, '   '.$spaces);
                } else {
                    if($value !== null && $value !== '') {
                        $output .= $spaces.$key.":    ".$value."\n";
                    }
                }
            }
            return $output;
        }

    }
}
