<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

if (!function_exists('_utf8_encode')) {
    function _utf8_encode($value): string
    {
        $string = (string)$value;
        if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
            $encoding = mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8';
            return mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        return function_exists('utf8_encode') ? utf8_encode($string) : $string;
    }
}

if (!function_exists('_utf8_decode')) {
    function _utf8_decode($value): string
    {
        $string = (string)$value;
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
        }

        return function_exists('utf8_decode') ? utf8_decode($string) : $string;
    }
}
