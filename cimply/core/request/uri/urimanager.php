<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Request\Uri {

    use Cimply\Core\Core;

    class UriManager
    {
        private static array $actionPath = [];
        private static ?string $baseUrl = null;

        protected static ?string $filePath = null;
        protected static ?string $fileName = null;
        protected static ?string $fileBasename = null;
        protected static ?string $fileType = null;
        protected static ?string $fileNameUrl = null;
        protected static ?string $currentFile = null;

        protected static string $basePath = '/';

        public function __construct($executeFile = null, $defaultIndex = 'index', $basePath = null)
        {
            $defaultIndex = ($defaultIndex !== null && $defaultIndex !== '') ? (string)$defaultIndex : 'index';
            if ($basePath !== null && $basePath !== '') {
                self::$basePath = (string)$basePath;
            }
           

            if ($executeFile !== null && $executeFile !== '') {
                $rawUri = '/' . ltrim((string)$executeFile, '/');
            } else {
                $rawUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
            } 

            $path = (string)(\parse_url($rawUri, PHP_URL_PATH) ?? '/');
            if ($path === '') {
                $path = '/';
            }
            if ($path === '/') {
                $path .= $defaultIndex;
            }
            
            self::$baseUrl = $path;
            self::$filePath = $this->normalizeRoutePath($path);

            $this->setCurrentFile();
            $this->setBaseUrl();
        }

        final public static function Cast($mainObject, $selfObject = self::class): self
        {
            return Core::Cast($mainObject, $selfObject);
        }

        public function getFileNameUrl(): ?string
        {
            return self::$fileNameUrl;
        }

        public function getFileBasename(): ?string
        {
            return self::$fileBasename;
        }

        public function getFilePath(): ?string
        {
            if (self::$filePath === null) {
                return null;
            }

            return ltrim((string)self::$filePath, '/');
        }

        public function getRoutingPath($setBaseUrl = null): ?string
        {
            $filePath = (string)$this->getFilePath();
            if ($filePath === '') {
                self::ActionPath([]);
                self::$filePath = '';
                return null;
            }

            $segments = $this->extractSegmentsFromFilePath($filePath);

            if ($segments === []) {
                self::ActionPath([]);
                self::$filePath = '';
                return null;
            }

            $basePath = trim((string)self::$basePath);
            if ($basePath !== '' && $basePath !== '/' && $basePath !== '1') {
                $baseSegments = array_values(array_filter(explode('/', trim($basePath, '/')), static fn($v) => $v !== ''));
                if ($baseSegments !== []) {
                    $matches = true;
                    for ($i = 0; $i < count($baseSegments); $i++) {
                        if (!isset($segments[$i]) || $segments[$i] !== $baseSegments[$i]) {
                            $matches = false;
                            break;
                        }
                    }
                    if ($matches) {
                        $segments = array_slice($segments, count($baseSegments));
                    }
                }
            }

            if (isset($segments[0]) && strcasecmp($segments[0], 'Rest') === 0) {
                array_shift($segments);
            }
            self::ActionPath($segments);
            if ($segments === []) {
                self::$filePath = '';
                return null;
            }
            self::$filePath = '/' . implode('_', $segments);
            return implode('_', $segments);
        }

        public function getFileName(): ?string
        {
            return self::$fileName;
        }

        public function getFileType(): ?string
        {
            return self::$fileType;
        }

        public function setBaseUrl(): void
        {
            $newUri = self::$filePath ?? '/index';

            $host = getHostName();
            $hostIP = $host ? getHostByName($host) : '127.0.0.1';

            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;

            $url = $scheme . $hostIP;

            if ($port !== 80 && $port !== 443) {
                $url .= ':' . $port;
            }

            $url .= '/' . ltrim((string)$newUri, '/');

            $baseUrl = pathinfo($url);

            self::$fileNameUrl = $baseUrl['dirname'] ?? null;
            self::$fileBasename = $baseUrl['basename'] ?? null;
            self::$fileName = $baseUrl['filename'] ?? null;
            self::$fileType = $baseUrl['extension'] ?? null;
        }

        private function setCurrentFile(): void
        {
            $filePath = (string)(self::$filePath ?? '/');
            $segments = $this->extractSegmentsFromFilePath($filePath);
            self::$currentFile = $segments[0] ?? null;
        }

        public function currentFile(): ?string
        {
            return self::$currentFile;
        }

        public static function ActionPath($actionPath = null): array
        {
            if (is_array($actionPath)) {
                self::$actionPath = array_values(array_filter($actionPath, static fn($v) => $v !== null && $v !== ''));
            } elseif (is_string($actionPath) && $actionPath !== '') {
                self::$actionPath = array_values(array_filter(explode('_', $actionPath), static fn($v) => $v !== ''));
            } elseif ($actionPath === null) {
            } else {
                self::$actionPath = [(string)$actionPath];
            }
            return self::$actionPath;
        }

        private function normalizeRoutePath(string $path): string
        {
            $segments = array_values(array_filter(explode('/', trim($path, '/')), static fn($v) => $v !== ''));

            if ($segments === []) {
                return '/';
            }

            if (count($segments) === 1) {
                return '/' . $segments[0];
            }

            $head = array_shift($segments);
            return '/' . $head . '/' . implode('_', $segments);
        }

        private function extractSegmentsFromFilePath(string $filePath): array
        {
            $normalized = trim($filePath, '/');
            if ($normalized === '') {
                return [];
            }

            $parts = explode('/', $normalized, 2);
            $segments = [];

            if (isset($parts[0]) && $parts[0] !== '') {
                $segments[] = $parts[0];
            }

            if (isset($parts[1]) && $parts[1] !== '') {
                $tailSegments = array_values(array_filter(explode('_', $parts[1]), static fn($v) => $v !== ''));
                $segments = array_merge($segments, $tailSegments);
            }

            return $segments;
        }
    }
}