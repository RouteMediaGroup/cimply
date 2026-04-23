<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\View
{
    class Scope
    {
        use \Properties, \Cast;

        private ?string $type = null;
        private mixed $params = null;
        private mixed $scheme = null;
        private mixed $action = null;
        private mixed $controller = null;
        private mixed $method = null;
        private mixed $target = null;
        private mixed $theme = null;
        private mixed $tpls = null;
        private mixed $markup = null;
        private mixed $markupFile = null;
        private mixed $templating = null;
        private ?array $validation = null;
        private mixed $requires = null;
        private mixed $caching = null;
        private mixed $session = null;

        public function __construct()
        {
        }

        final public static function Cast($mainObject, $selfObject = self::class): self
        {
            return self::Cull($mainObject, $selfObject);
        }

        public function getType(): ?string
        {
            return $this->type;
        }

        public function getParams($key = null): mixed
        {
            if ($key !== null && \is_array($this->params)) {
                return $this->params[$key] ?? null;
            }

            return $this->params;
        }

        public function getScheme(): mixed
        {
            return $this->scheme;
        }

        public function getAction(): mixed
        {
            return $this->action;
        }

        public function getController(): mixed
        {
            return $this->controller;
        }

        public function getMethod(): mixed
        {
            return $this->method;
        }

        public function getTarget(): mixed
        {
            return $this->target;
        }

        public function getTpls(): mixed
        {
            return $this->tpls;
        }

        public function getMarkup(): mixed
        {
            return $this->markup;
        }

        public function getMarkupFile(): mixed
        {
            return $this->markupFile;
        }

        public function getTemplating(): mixed
        {
            return $this->templating ?? null;
        }

        public function getRequires(): mixed
        {
            return $this->requires;
        }

        public function getValidation(): ?array
        {
            return $this->validation ?? [];
        }

        public function getSession(): mixed
        {
            return $this->session;
        }

        public function getCaching(): mixed
        {
            return $this->caching;
        }
    }
}