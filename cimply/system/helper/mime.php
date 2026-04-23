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
    abstract class Mime {
        public static function GetMime($fileName, $fromExtension = false, $mimeFile = '') {
            $mime = null;
            $file = (is_array($fileName)) ? ($fileName['tmp_name'] ?? '') : (string)$fileName;

            if ($fromExtension && \str_contains($file, '/')) {
                return trim($file) !== '' ? trim($file) : 'application/octet-stream';
            }

            if (empty($mimeFile)) {
                $mimeFile = \Cimply\System\Settings::SystemPath . 'mime.types';
                if (!is_file($mimeFile)) {
                    return 'application/octet-stream';
                }
            }

            if (is_file($file) && function_exists('finfo_open') && function_exists('finfo_file') && !$fromExtension) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file);
                finfo_close($finfo);
            } else {
                $fileext = strtolower(ltrim((string)pathinfo($file, PATHINFO_EXTENSION), '.'));
                if ($fileext === '') {
                    $fileext = strtolower(ltrim($file, '.'));
                }

                if (empty($fileext)) {
                    $mime = null;
                }

                $lines = file($mimeFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lines === false) {
                    return 'application/octet-stream';
                }

                foreach ($lines as $line) {
                    $line = trim((string)$line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }

                    $parts = preg_split('/\s+/', $line);
                    if (!is_array($parts) || count($parts) < 2) {
                        continue;
                    }

                    $candidateMime = array_shift($parts);
                    foreach ($parts as $extension) {
                        if (strtolower((string)$extension) === $fileext) {
                            $mime = (string)$candidateMime;
                            break 2;
                        }
                    }
                }
            }

            return $mime ?? 'application/octet-stream';
        }
    }
}
