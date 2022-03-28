<?php
namespace Cimply\Core\Request\Uri {
    use \Cimply\Core\{Core};
    class UriManager {
        private static $actionPath, $baseUrl;
        protected static $filePath, $fileName, $fileBasename, $fileType, $fileNameUrl, $currentFile;
        private $basePath = '/';

        public function __construct($executeFile = null, $defaultIndex = 'index', $basePath = null)
        {
            if(isset($_SERVER['REQUEST_URI'])) {
                if(isset($basePath)) {
                    $this->basePath = $basePath;
                    $expl = \explode($basePath, $_SERVER['REQUEST_URI']);
                    $explFirst = $expl[0].$basePath;
                    $explSecond = str_replace('/','_',end($expl));
                    $_SERVER['REQUEST_URI'] = $explFirst.$explSecond;
                }    
            }
            
            $value = isset($executeFile) ? '/'.$executeFile : ($_SERVER['REQUEST_URI'] ?? self::$actionPath ?? $defaultIndex);
            $explodePath = explode('?', $value);
            isset($explodePath[1]) ? $value = $explodePath[0] : null;
            $value !== '/' ? : $value.= $defaultIndex;
        
            self::$filePath = $value;
            $this->setCurrentFile();
            $this->setBaseUrl();
        }        

        public final static function Cast($mainObject, $selfObject = self::class): self {
            return Core::Cast($mainObject, $selfObject);
        }
        public function getFileNameUrl(): ?string {
            return self::$fileNameUrl;
        }
        public function getFileBasename(): ?string {
            return self::$fileBasename;
        }
        public function getFilePath(): ?string {
            return substr(self::$filePath, 1);
        }
        public function getRoutingPath(): ?string {
            $subPath = \explode($this->basePath, (string)$this->getFilePath());
            $actionPath = \explode('/', end($subPath));
            self::ActionPath(\implode('_', $actionPath));
            (bool)$this->basePath === true ? : \array_splice($actionPath, 0, 1);
            self::$filePath = \implode('/', $actionPath);
            return \str_replace('/','_', self::$filePath);
        }
        public function getFileName(): ?string {
            return self::$fileName;
        }
        public function getFileType(): ?string {
            return self::$fileType;
        }
        public function setBaseUrl(): void {
            $newUri = self::$filePath ?? 'index';
            $hostIP = getHostByName(getHostName());
            $url  = (isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$hostIP;
            $url .= (isset($_SERVER["SERVER_PORT"]) && ($_SERVER["SERVER_PORT"] !== 80)) ? ":" . $_SERVER["SERVER_PORT"] : "/";
            $url .= isset($newUri) ? $newUri : str_replace('//', '/', $_SERVER["REQUEST_URI"]);
            $baseUrl = \pathinfo($url);
            self::$fileNameUrl = $baseUrl['dirname'] ?? null;
            self::$fileBasename = $baseUrl['basename'] ?? null;
            self::$fileName = $baseUrl['filename'] ?? null;
            self::$fileType = $baseUrl['extension'] ?? null;
        }
        private function setCurrentFile():void {
            $filePath = \pathinfo(self::$filePath);
            $urlToArray = explode('/', substr(($filePath['dirname'] ?? '/'), 1));
            self::$currentFile = $urlToArray[0];
        }
        public function currentFile() {
            return self::$currentFile;
        }
        public static function ActionPath($actionPath = null) {
            self::$actionPath = $actionPath ?? self::$actionPath;
            return self::$actionPath;
        }
    }
}