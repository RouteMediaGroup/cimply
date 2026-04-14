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

/**
* Description of Files
*
* @version 1.0
* @package Cimply\Core
*/

    trait Files
    {
        public static function GetFileContent($filename, $path = false, $options = null, $method = 'GET', $param1 = null, $param2 = null): ?string
        {
            $currentFile = "";
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
            if (\is_file($filename)) {
                $opts = $options ?? [
                    'http' => [
                        'method' => $method,
                        'header' => "Accept-language: en\r\n" . "Cookie: set=null\r\n",
                    ]
                ];
                $context = stream_context_create($opts);
                $currentFile = \file_get_contents($filename, (bool)$path, $context, $param1 ?? 0, $param2 ?? null) ?: null;
            }
            return !empty($currentFile) ? $currentFile : null;
        }

        public static function PutFileContent($filename, $data = "", $options = null, $method = 'POST', $deep = 0): void
        {
            if (\is_file($filename)) {
                $opts = $options ?? [
                    'http' => [
                        'method' => $method,
                        'header' => "Accept-language: en\r\n" . "Cookie: set=null\r\n",
                    ]
                ];
                $context = stream_context_create($opts);
                \file_put_contents($filename, $data, $deep, $context);
            }
        }

        public static function GetFilePath($path = '', $options = null)
        {
            return $path ? ($options ? \pathinfo($path, $options) : \pathinfo($path)) : [];
        }

        public static function GetHttpsFile($url, $path = null, $username = "", $password = "", $timeout = 60, $method = 'POST', $param1 = -1, $param2 = 40000)
        {
            $body = '';
            $opts = [
                'http' => [
                    'method' => $method,
                    'header' => "Content-Type: text/xml\r\n" .
                        "Authorization: Basic " . base64_encode("{$username}:{$password}") . "\r\n",
                    'content' => $body,
                    'timeout' => $timeout
                ]
            ];
            return self::GetFileContent($url, $path, $opts, $method, $param1, $param2);
        }

        public static function GetUtf8File($fn)
        {
            $content = self::GetFileContent($fn) ?? '';
            return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
        }

        public static function SetFileKey($project = 'global', $filename = '')
        {
            return md5($project . str_replace(' ', '', $filename));
        }

        public static function RemoveFile($filePath)
        {
            try {
                if (\is_file($filePath)) {
                    \unlink($filePath);
                }
            } catch (\Throwable $ex) {
                \Debug::VarDump($ex);
            }
        }

        public static function FileForceContents(?string $filename = null, $data = null, $flags = 0): bool
        {
            if ($filename === null || $filename === '') {
                return false;
            }

            $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
            $directory = dirname($filename);
            if ($directory !== '' && !\is_dir($directory)) {
                \mkdir($directory, 0775, true);
            }

            return \file_put_contents($filename, $data, $flags) !== false;
        }

        /*public static function FileForceContents($filename = null, $data, $flags = 0): bool
        {
            return (bool)file_put_contents(str_replace('\\', DIRECTORY_SEPARATOR, $filename), $data, $flags) ?? false;
        }*/

        public static function CacheFile($filePath, $cacheFile)
        {
            \ob_start();
            include $filePath;
            $fileData = \ob_get_contents();
            \ob_end_clean();
            self::FileForceContents($cacheFile, $fileData, 0);
            return $fileData;
        }

        public static function HasFileCached($fileName): bool
        {
            return (bool)\is_file($fileName);
        }

        public static function DeCryptFile($fileName): void
        {
            $tmpFile = ".compile";
            \ob_start();
            require_once($fileName);
            $cryptedData = \ob_get_contents();
            \ob_end_clean();
            $splitData = \explode('=', $cryptedData);
            $code_base64 = Crypto::Decrypt($splitData[0] . '=', 'AES-256-CBC', end($splitData));
            if (self::FileForceContents($tmpFile, $code_base64, 0)) {
                require_once($tmpFile);
            }
        }
    }
}
