<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Basics {

    use Cimply\System\{System, Config};
    use Cimply\Core\{
        View\View,
        Validator\Validator,
        Request\Uri\UriManager
    };

    class Basics extends System
    {
        use \Properties;

        protected ?string $actionPath = null;
        protected array $actionPathSegments = [];

        public string $type = 'html';
        public mixed $action = null;
        public mixed $controller = null;
        public mixed $method = null;
        public mixed $target = null;

        public string $markupFile = '';
        public array $markup = [];
        public array $routings = [];
        public array $templating = [];
        public array $requires = [];
        public ?Validator $validate = null;
        public array $params = [];
        public array $session = [];
        public bool $caching = false;

        public function __construct($instance = null)
        {
            parent::__construct(new Config(), 'system.config.yml');
        }

        final public function route($path, $action, $options = null): self
        {
            $this->actionPath = \str_replace('/', '_', (string)$path);
            $this->routings[(string)$this->actionPath] = $action;
            return $this;
        }

        final public function assign($params = []): self
        {
            if ($this->actionPath !== null) {
                View::Assign(\array_merge($this->params, (array)$params));
            }
            return $this;
        }

        final public function validates($requires = []): self
        {
            if ($this->actionPath !== null) {
                $this->validate = (new Validator())->addRules((array)$requires);
            }
            return $this;
        }

        final public function action($action = ''): self
        {
            if ($this->actionPath !== null) {
                $this->action = $action;
            }
            return $this;
        }

        private function validRoutingChecker(array $expActionPath, array $expPath): bool
        {
            $checked = [];
            $validType = [];

            foreach ($expActionPath as $key => $value) {
                $typeCheck = [];

                $needle = $expPath[$key] ?? '';
                $var = \explode(':', (string)$needle, 2);

                if (isset($var[1]) && $var[1] !== '') {
                    $t = $var[1][0] ?? '';

                    if ($t === 'i') { $typeCheck[$var[1]] = \is_numeric($value); }
                    if ($t === 'b') { $typeCheck[$var[1]] = \is_bool($value) || $value === '0' || $value === '1' || $value === 0 || $value === 1; }
                    if ($t === 'f') { $typeCheck[$var[1]] = \is_float($value) || (\is_string($value) && \is_numeric($value)); }
                    if ($t === 's') { $typeCheck[$var[1]] = \is_string($value) || \is_numeric($value); }

                    $validType[] = !empty($typeCheck[$var[1]]) ? (bool)$typeCheck[$var[1]] : false;
                } else {
                    (($value != ($expPath[$key] ?? null)) ? $checked[] = $value : null);
                }
            }

            return empty($checked) ? (bool)!\in_array(false, $validType, true) : false;
        }

        /**
         * @param ?string
         * @return ?array
         */
        final public function routing(?string $actionPath = null): ?array
        {
            $this->actionPathSegments = UriManager::ActionPath();
            

            $this->actionPath = \implode('_', (array)$this->actionPathSegments);
            $actionPath = ($actionPath !== null && $actionPath !== '') ? $actionPath : ($this->actionPath ?? '');

            $currentRoute = $this->routings[$actionPath] ?? null;
            
            if ($currentRoute === null) {
                \array_walk($this->routings, function ($scope, $path) use (&$currentRoute, $actionPath) {
                    $expPath = \explode('_', (string)$path);
                    $expActionPath = \explode('_', (string)$actionPath);

                    if (\count($expPath) === \count($expActionPath)) {
                        $combined = \array_combine($expPath, $expActionPath);
                        if ($combined !== false && \implode('_', $combined) !== '') {
                            if ($this->validRoutingChecker($expActionPath, $expPath) === true) {
                                $currentRoute = $this->routings[$path] ?? null;
                            }
                        }
                    }
                });
            }

            if (isset($currentRoute) && $currentRoute !== null) {
                $key = (string)($this->actionPath ?? $actionPath);

                $result = (array)($currentRoute)($this);
                $result['_actionPathSegments'] = $this->actionPathSegments;

                return [$key => $result];
            }

            return ['externalFile' => true];
        }
    }
}