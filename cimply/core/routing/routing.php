<?php
namespace Cimply\Core\Routing
{
	/**
	 * Router short summary.
	 *
	 * Router description.
	 *
	 * @version 1.0.1
	 * @author MikeCorner
	 */
    use \Cimply\System\System;
    use \Cimply\Core\{Core, View\View, Request\Uri\UriManager};
    use \Cimply\Interfaces\ICast;
	class Routing implements ICast
	{
        use \Properties, \Cast;

        protected $scope, $route, $lastRoute = '/', $nextRoute = null;
        private $file, $fileName, $baseFile, $fileType, $path, $action, $routeParams = null, $external = false;
        function __construct($query = []) {
            $this->setRoute(new UriManager())->setScope($query);
        }

        public final static function Cast($mainObject, $selfObject = self::class): self {
            return Core::Cast($mainObject, $selfObject);
        }

        /**
         * Set route with fallback to lastRoute
         * @param UriManager $route
         * @return Routing
         */
        protected function setRoute(UriManager $route): Routing {
            $this->path = $route->getFilePath();
            $this->file = $route->currentFile();
            $this->fileName = $route->getFileName();
            $this->baseFile = $route->getFileBasename();
            $this->fileType = $route->getFileType();
            isset($this->route) ? $this->lastRoute = clone($this->route) : null;
            $this->route = $route->getFileNameUrl() ?? $this->lastRoute;
            return $this;
        }

        /**
         * Set upcoming Route
         * @param string $route
         * @return Routing
         */
        protected function setNextRoute(string $route): Routing {
            $this->nextRoute = $route ??  $this->route;
            return $this;
        }

        /**
         * Summary of setScope
         * @param mixed $query
         * @return void
         */
        private function setScope($query = null): void {
            $this->setRouteParams();
            if(isset($query)) {
                (View::GetVars() !== []) ? System::SetSession('storageData', View::GetVars()) : null;
                $this->setExternalRoute($query);
            }
        }

        private function setExternalRoute($query = null) {
            $params = [];
            $params['requires'] = $this->routeParams ?? [];
            $this->scope = (function($params) use ($query) {
                return array_merge(
                    $query[$this->getPath()] ??
                    $query[$this->getBaseFile()] ??
                    $query[$this->getFilename()] ??
                    $query[$this->getFile()] ??
                    $query[$this->action] ?? (($this->external = true) ? ($this->external ? [
                        'type' => $this->fileType,
                        'params' => $this->routeParams,
                        'action' => '\Cimply\App\Base\FileCtrl::Init',
                        'target' => '{->'.$this->get('baseFile').'}',
                        'caching' => 'false'
                    ] : null) : null)
                , $params);
            })($params);
        }

        /**
         * Summary of setRouteParams
         * @param mixed $query
         * @return void
         */
        private function setRouteParams(): void {
            $explPath = explode('/', $this->path);
            $arrayResult = explode('_', ((count($explPath) %2) ? '_' : '').end($explPath));
            $this->routeParams = $this->parseParams($arrayResult);
        }

        /**
         * Summary of parseParams
         * @param array $keyParam
         * @return array
         */
        function parseParams(array $keyParam): array {
            ksort($keyParam);
            $result = [];
            $keyName = "";
            foreach($keyParam as $key => $value) {
                if($key % 2) {
                    $keyName = $value;
                } else {
                    !(empty($key)) ? $result[$keyName] = $value : $this->action = $value;
                }
            }
            return $result;
        }

        /**
         * Summary of getFile
         * @return mixed
         */
        function getFile(): ?string {
            return empty($this->file) ? null : $this->file;
        }

        /**
         * Return action String
         * @return string
         */
        function getPath($path = null): ?string {
            (substr( $path, -1 ) == '/') ? $path = substr( $path, 0, -1 ) : null;
            return str_replace('/', '_', $path ?? $this->path);
        }

        /**
         * Return action-Path String
         * @return string
         */
        function getActionPath($path = null): ?string {
            (substr( $path, -1 ) == '/') ? $path = substr( $path, 0, -1 ) : null;
            return $path;
        }

        /**
         * Return action
         * @return string
         */
        function getAction() {
            return $this->action;
        }

        /**
         * Summary of getFilename
         * @return mixed
         */
        function getFilename(): string {
            return $this->fileName;
        }

        /**
         * Summary of getBaseFile
         * @return mixed
         */
        function getBaseFile(): string {
            return $this->baseFile;
        }

        /**
         * Summary of getScope
         * @return mixed
         */
        function getScope(): ?array {
            return $this->scope;
        }

        /**
         * Summary of getParams
         * @return array
         */
        function getParams() {
            return ($this->routeParams);
        }

        function execute(): object {
            return (object)[
                "file" => $this->getFile(),
                "path" => $this->getPath(),
                "params" => $this->getParams(),
                "scope" => (object)$this->getScope()
            ];
        }

        function isExternal(): bool {
            return (bool)$this->external;
        }
    }
}
