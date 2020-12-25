<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\Basics {
    use \Cimply\System\{System, Config};
    use \Cimply\Core\ {
        View\View, Validator\Validator, Request\Uri\UriManager
    };
    class Basics extends System {
        use \Properties;
        protected $actionPath = null;
        public $type = 'html', $action = null, $controller = null, $method = null, $target = null, $markupFile = '', $markup = [], $routings = [], $templating = [], $requires =  [], $validate = null, $params = [], $session = [], $caching = false;

        function __construct($instance = null) {
            parent::__construct(new Config(), 'system.config.yml');
        }

        final function route($path, $action, $options = null) {
            $this->actionPath = str_replace('/', '_', $path);
            $this->routings[(string)$this->actionPath] = $action;
            return $this;
        }

        final function assign($params = []): self {
            isset($this->actionPath) ? View::Assign(array_merge($this->params, $params)) : null;
            return $this;
        }

        final function validates($requires = []): self {
            isset($this->actionPath) ? $this->validate = (new Validator)->addRules($requires) : null;
            return $this;
        }

        final function action($action = ''): self {
            isset($this->actionPath) ? $this->action = $action : null;
            return $this;
        }

        private function validRoutingChecker($expActionPath, $expPath) {
            $checked = [];
            $validType = [];
            foreach($expActionPath as $key => $value) {
                $typeCheck = [];
                $var = explode(':', $expPath[$key]);
                if(isset($var[1])) {
                    ($var[1][0] == 'i') ? $typeCheck[$var[1]] = is_numeric($value) : false;
                    ($var[1][0] == 'b') ? $typeCheck[$var[1]] = is_bool($value) : false;
                    ($var[1][0] == 'f') ? $typeCheck[$var[1]] = is_float($value) : false;
                    ($var[1][0] == 's') ? $typeCheck[$var[1]] = is_string($value) : false;
                    $validType[] = !empty($typeCheck[$var[1]]) ? $typeCheck[$var[1]] : false;
                } else {
                    ($value != $expPath[$key] ? $checked[] = $value : null);
                }
            }

            return empty($checked) ? (bool)!in_array(false, $validType) : false;
        }

        final function routing(string $actionPath = null): ?array {
            $this->actionPath = UriManager::ActionPath();
            if(($currentRoute = $this->routings[$actionPath] ?? null) === null) {
                array_walk($this->routings, function($scope, $path) use(&$currentRoute, $actionPath) {
                    if(count($expPath = explode('_', $path)) === count($expActionPath = explode('_', $actionPath)) && implode('_', array_combine($expPath, $expActionPath)) ?? false) {
                        $this->validRoutingChecker($expActionPath, $expPath) === true ? $currentRoute = $this->routings[$path] : null;
                    }
                });
            } else {
                $currentRoute = $this->routings[$actionPath];
            }
            return (isset($currentRoute) ? [$this->actionPath => (array)($currentRoute)($this)] : ['externalFile' => true]);
        }
    }
}
