<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Routing
{
    use Cimply\System\System;
    use Cimply\Core\{Core, View\View, Request\Uri\UriManager};
    use Cimply\Interfaces\ICast;

    class Routing implements ICast
    {
        use \Properties, \Cast;

        protected mixed $scope = null;
        protected mixed $route = null;
        protected mixed $lastRoute = '/';
        protected mixed $nextRoute = null;

        private ?string $file = null;
        private ?string $fileName = null;
        private ?string $baseFile = null;
        private ?string $fileType = null;
        private ?string $path = null;
        private ?string $action = null;
        private ?array $routeParams = null;
        private bool $external = false;

        public function __construct($query = [])
        {
            $this->setRoute(new UriManager(null, null, null))
                ->setScope($query);
        }

        public static function Cast($mainObject, $selfObject = null): self
        {
            $selfObject = $selfObject ?? static::class;
            return Core::Cast($mainObject, $selfObject);
        }

        protected function setRoute(UriManager $route): self
        {
            $this->path = $route->getFilePath();
            $this->file = $route->currentFile();
            $this->fileName = $route->getFileName();
            $this->baseFile = $route->getFileBasename();
            $this->fileType = $route->getFileType();
            $this->lastRoute = isset($this->route) ? clone($this->route) : $this->lastRoute;
            $this->route = $route->getFileNameUrl() ?? $this->lastRoute;
            return $this;
        }

        protected function setNextRoute(string $route): self
        {
            $this->nextRoute = $route ?: $this->route;
            return $this;
        }

        private function setScope($query = null): void
        {
            $this->setRouteParams();

            if ($query !== null) {
                if (!empty(View::GetVars())) {
                    System::SetSession('storageData', View::GetVars());
                }
                $this->setExternalRoute($query);
            }
        }

        private function setExternalRoute($query = null): void
        {
            $params = ['requires' => $this->routeParams ?? []];

            $this->scope = (function ($params) use ($query) {
                return array_merge(
                    $query[$this->getPath()] ??
                    $query[$this->getBaseFile()] ??
                    $query[$this->getFilename()] ??
                    $query[$this->getFile()] ??
                    $query[$this->action] ??
                    (($this->external = true) ? [
                        'type' => $this->fileType,
                        'params' => $this->routeParams,
                        'action' => '\Cimply\App\Base\FileCtrl::Init',
                        'target' => '{->' . $this->getBaseFile() . '}',
                        'caching' => 'false'
                    ] : null),
                    $params
                );
            })($params);
        }

        private function setRouteParams(): void
        {
            $path = (string)($this->path ?? '');
            $path = trim($path, '/');

            if ($path === '') {
                $this->action = null;
                $this->routeParams = [];
                return;
            }

            $parts = array_values(array_filter(explode('/', $path), static fn($v) => $v !== ''));

            if ($parts === []) {
                $this->action = null;
                $this->routeParams = [];
                return;
            }

            if (isset($parts[0]) && strcasecmp($parts[0], 'Rest') === 0) {
                array_shift($parts);
            }

            $segments = [];
            foreach ($parts as $part) {
                $sub = array_values(array_filter(explode('_', (string)$part), static fn($v) => $v !== ''));
                $segments = array_merge($segments, $sub);
            }

            $this->routeParams = $this->parseParams($segments);
        }

        private function parseParams(array $keyParam): array
        {
            $keyParam = array_values(array_map(static fn($v) => (string)$v, $keyParam));
            $result = [];
            $count = count($keyParam);

            if ($count === 0) {
                $this->action = null;
                return [];
            }

            $this->action = $keyParam[0] ?? null;

            for ($i = 0; $i < $count; $i += 2) {
                $key = $keyParam[$i] ?? '';
                $value = $keyParam[$i + 1] ?? '';

                if ($key === '') {
                    continue;
                }

                $result[$key] = $value;
            }

            return $result;
        }

        public function getFile(): ?string
        {
            return $this->file;
        }

        public function getPath($path = null): ?string
        {
            return str_replace('/', '_', rtrim((string)($path ?? $this->path), '/'));
        }

        public function getActionPath($path = null): ?string
        {
            return rtrim((string)$path, '/');
        }

        public function getAction(): ?string
        {
            return $this->action;
        }

        public function getFilename(): string
        {
            return (string)$this->fileName;
        }

        public function getBaseFile(): string
        {
            return (string)$this->baseFile;
        }

        public function getScope(): ?array
        {
            return is_array($this->scope) ? $this->scope : null;
        }

        public function getParams(): array
        {
            return $this->routeParams ?? [];
        }

        public function execute(): object
        {
            return (object)[
                'file' => $this->getFile(),
                'path' => $this->getPath(),
                'params' => $this->getParams(),
                'scope' => (object)($this->getScope() ?? [])
            ];
        }

        public function isExternal(): bool
        {
            return $this->external;
        }
    }
}