<?php
namespace {
    abstract class Mime {
        public static function GetMime($fileName, $fromExtension = false, $mimeFile = '') {
            $mime = null;
            if (empty($mimeFile)) {
                $mimeFile = Cimply\System\Settings::SystemPath.'/mime.types';
                if (!is_file($mimeFile)) {
                    return "Mime.File not found.";
                }
            }
            $file = (is_array($fileName)) ? $fileName['tmp_name'] : $fileName;
            if (is_file($file) && function_exists('finfo_open') && function_exists('finfo_file') && !$fromExtension) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file);
                finfo_close($finfo);
            } else {
                $fileext = substr(strrchr($file, '.'), 1);
                if (empty($fileext))
                    $mime = null;
                $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
                $lines = file($mimeFile);
                foreach ($lines as $line) {
                    if (substr($line, 0, 1) == '#')
                        continue; // skip comments
                    $line = rtrim($line) . " ";
                    if (!preg_match($regex, $line, $matches)) {
                        continue;
                    } // no match to the extension
                    $mime = ($matches[1]);
                }
            }
            return $mime;
        }
    }
}