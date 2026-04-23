<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace {
    abstract class JsonDeEncoder {

        public static function Encode($value = [], $options = 0): string {
            $encoded = \json_encode($value, $options);
            return \is_string($encoded) ? $encoded : '{}';
        }

        public static function Decode($value = null, $assoc = false, $depth = 512, $options = 0) {
            if ($value === null) {
                return [];
            }

            if (\is_array($value) || \is_object($value)) {
                return $value;
            }

            return \json_decode((string)$value, $assoc, $depth, $options);
        }

        public static function CheckJSON($value): string {
            try {
                $fixed = html_entity_decode((string)$value, ENT_NOQUOTES, 'UTF-8');
                $result = \json_decode($fixed);
                if (is_null($result)) {
                    return '{}';
                }
            } catch (\Throwable) {
                return '{}';
            }
            return $fixed;
        }

        public static function IsJson($value = null): bool {
            if ($value === null || \is_array($value) || \is_object($value)) {
                return false;
            }

            \json_decode((string)$value);
            return \json_last_error() === JSON_ERROR_NONE;
        }
        
    }
}
