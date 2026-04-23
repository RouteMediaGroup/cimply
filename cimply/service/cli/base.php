<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Service\Cli {
    use \Cimply\System\System;
    class Base extends System {

        protected static $app, $currentSelect = null, $projects = [];

        static function CLI():bool {
            return \in_array(PHP_SAPI, ['cli', 'phpdbg'], true);
        }

        protected static function GetMessage($tolower = true): string {
            $message = '';

            if (self::CLI()) {
                if (\function_exists('readline')) {
                    $message = (string)\readline();
                } elseif (\defined('STDIN')) {
                    $message = (string)\fgets(STDIN);
                }
            } else {
                $message = (string)($_REQUEST['choice'] ?? '');
            }

            $message = trim($message);

            return $tolower ? strtolower($message) : $message;
        }

        protected static function GetProjectName($project = null): ?string {
            if ($project === null || $project === '') {
                return null;
            }

            $newName = str_replace([' ', '_', '-', '/', '\\', '&', '.', ',', '=', '?', '#'], ' ', ucwords(strtolower($project), ' '));
            return str_replace(' ','', ucwords(strtolower($newName), ' '));
        }

        protected static function LoadProject($projects, $show = true): array {
            $i = 0;
            $project = [];
            $directories = \scandir($projects);
            if ($directories === false) {
                return [];
            }

            $directories = array_diff($directories, array('..', '.'));
            foreach($directories as $value) {
                $i++;
                $project[strtolower($value)] = strtolower($value);
                $project[$value] = $value;
                $output = $i.': '.$value;
                $project[$i] = $value;
                if($show) {
echo <<<Inhalt
$output

Inhalt;
                }
            }
            return $project;
        }

        protected function recurseCopy($src,$dst): bool { 
            $dir = opendir($src);
            if ($dir === false) {
                return false;
            }
            @mkdir($dst); 
            while(false !== ( $file = readdir($dir)) ) { 
                if (( $file != '.' ) && ( $file != '..' )) { 
                    if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) { 
                        self::recurseCopy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file); 
                    }
                    else {
                        \is_file($srcFile = $src . DIRECTORY_SEPARATOR . $file) ? copy($srcFile,$dst . DIRECTORY_SEPARATOR . $file) : exit; 
                    }
                } 
            } 
            closedir($dir);
            return true;
        }

        protected function extractZip($zipfile, $path): bool {
            $ready = false;
            $zip = new \ZipArchive;
            if (($zip->open($zipfile)) && (filesize($zipfile)>1024)) {
                $zip->extractTo($path);
                $zip->close();
                $ready = true;
            }
            return $ready;
        }

        protected function pushFile($src, $dest, $filename) {
            $destDir = $src . DIRECTORY_SEPARATOR . $dest;
            if((bool)(\is_dir($destDir)) ? true : \mkdir($destDir)) {
               $source = $src . DIRECTORY_SEPARATOR . $filename;
               $target = $destDir . DIRECTORY_SEPARATOR . $filename;
               \is_file($source) ? rename($source, $target) : null; 
            }
        }
    }
}
