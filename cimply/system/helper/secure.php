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

    trait Secure
    {
        public static function Add($secureFiles = []): void
        {
            if ($secureFiles === null) {
                return;
            }

            self::consume($secureFiles);
        }

        private static function consume($value): void
        {
            if ($value === null) {
                return;
            }

            if (\is_array($value)) {
                foreach ($value as $v) {
                    self::consume($v);
                }
                return;
            }

            if ($value instanceof \Traversable) {
                foreach ($value as $v) {
                    self::consume($v);
                }
                return;
            }

            if (\is_object($value)) {
                if (\method_exists($value, '__toString')) {
                    self::handlePath((string)$value);
                    return;
                }

                if (isset($value->extends)) {
                    self::consume($value->extends);
                    return;
                }

                foreach (\get_object_vars($value) as $v) {
                    self::consume($v);
                }
                return;
            }

            self::handlePath($value);
        }

        private static function handlePath($path): void
        {
            if ($path === null) {
                return;
            }

            if (\is_array($path) || $path instanceof \Traversable || \is_object($path)) {
                self::consume($path);
                return;
            }

            $path = (string)$path;
            if ($path === '') {
                return;
            }

            if (\is_file($path)) {
                static::DeCryptFile($path);
                return;
            }

            if (\is_dir($path)) {
                static::AddFiles($path);
                return;
            }
        }

        public static function AddFiles($secureDirectory = ''): void
        {
            $secureDirectory = $secureDirectory !== null ? (string)$secureDirectory : '';
            if ($secureDirectory === '' || !\is_dir($secureDirectory)) {
                return;
            }

            $scannedFiles = \scandir($secureDirectory);
            if ($scannedFiles === false) {
                return;
            }

            $entries = \array_diff($scannedFiles, ['..', '.']);

            foreach ($entries as $entry) {
                $entry = (string)$entry;
                if ($entry === '') {
                    continue;
                }

                $filePath = $secureDirectory . DIRECTORY_SEPARATOR . $entry;

                if (\is_dir($filePath)) {
                    static::AddFiles($filePath);
                    continue;
                }

                if (!\is_file($filePath)) {
                    continue;
                }

                $lower = \strtolower($entry);
                $hasDot = \strpos($entry, '.') !== false;
                $isPhp = \str_ends_with($lower, '.php');

                if ($isPhp || !$hasDot) {
                    static::Add($filePath);
                }
            }
        }
    }
}