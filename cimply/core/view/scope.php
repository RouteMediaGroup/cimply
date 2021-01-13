<?php

namespace Cimply\Core\View
{
	/**
	 * Scope short summary.
	 *
	 * Scope description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
	class Scope
	{
        use \Properties, \Cast;
        /**
         * Summary of $session
         * @var mixed
         */
        private $type, $params, $scheme, $action, $controller, $method, $target, $theme, $tpls, $markup, $markupFile, $templating, $validation, $requires, $caching, $session;

        function __construct() {}

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        function getType(): ?string {
            return $this->type;
        }

        function getParams($key = null) {
            return $this->params[$key] ?? $this->params;
        }

        function getScheme() {
            return $this->scheme;
        }

        function getAction() {
            return $this->action;
        }
        
        function getController() {
            return $this->controller;
        }
        
        function getMethod() {
            return $this->method;
        }
        
        function getTarget() {
            return $this->target;
        }

        function getTpls() {
            return $this->tpls;
        }

        function getMarkup() {
            return $this->markup;
        }

        function getMarkupFile() {
            return $this->markupFile;
        }

        function getTemplating() {
            return $this->templating ?? null;
        }        

        function getRequires() {
            return $this->requires;
        }
        
        function getValidation(): ?array {
            return $this->validation ?? [];
        }
        
        function getSession() {
            return $this->session;
        }
        
        function getCaching() {
            return $this->caching;
        }
    }
}