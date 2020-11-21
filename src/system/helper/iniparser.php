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
namespace Cimply_Cim_System {
    abstract class IniParser {
        
        public function ParseIniString($iniString) {

            if (empty($iniString)) {
                return false;
            }

            $lines = explode("\n", $iniString);
            $ret = Array();
            $inside_section = false;

            foreach ($lines as $line) {

                $line = trim($line);

                if (!$line || $line[0] == "#" || $line[0] == ";") {
                    continue;
                }

                if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
                    $inside_section = substr($line, 1, $endIdx - 1);
                    continue;
                }

                if (!strpos($line, '=')) {
                    continue;
                }
                $tmp = explode("=", $line, 2);

                if ($inside_section) {

                    $key = rtrim($tmp[0]);
                    $value = ltrim($tmp[1]);

                    if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
                        $value = mb_substr($value, 1, mb_strlen($value) - 2);
                    }

                    $t = preg_match("^\[(.*?)\]^", $key, $matches);
                    if (!empty($matches) && isset($matches[0])) {

                        $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

                        if (!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
                            $ret[$inside_section][$arr_name] = array();
                        }

                        if (isset($matches[1]) && !empty($matches[1])) {
                            $ret[$inside_section][$arr_name][$matches[1]] = $value;
                        } else {
                            $ret[$inside_section][$arr_name][] = $value;
                        }
                    } else {
                        $ret[$inside_section][trim($tmp[0])] = $value;
                    }
                } else {
                    $ret[trim($tmp[0])] = ltrim($tmp[1]);
                }
            }
            return $ret;
        }
    }
}