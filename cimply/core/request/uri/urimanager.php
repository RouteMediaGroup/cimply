<?php
namespace Cimply\Core\Request\Uri {
    use \Cimply\Core\{Core};
    class UriManager {
        private static $actionPath, $baseUrl;
        protected static $filePath, $fileName, $fileBasename, $fileType, $fileNameUrl, $currentFile;

        public function __construct($FilePath = null, $defaultIndex = 'index')
        {
            $value = isset($FilePath) ? '/'.$FilePath : $_SERVER['REQUEST_URI'] ?? self::$actionPath;
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
        public function getRoutingPath($setBaseUrl = null): ?string {
            $actionPath = explode('/', $this->getFilePath());
            self::ActionPath(implode('_', $actionPath));
            (bool)$setBaseUrl === true ? : \array_splice($actionPath, 0, 1);
            self::$filePath = implode('/', $actionPath);
            return str_replace('/','_', self::$filePath);
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
            $url  = (isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$hostIP.'/';
            $url .= (isset($_SERVER["SERVER_PORT"]) && ($_SERVER["SERVER_PORT"] !== 80)) ? ":" . $_SERVER["SERVER_PORT"] : "";
            $url .= isset($newUri) ? $newUri : str_replace('//', '/', $_SERVER["REQUEST_URI"]);
            $baseUrl = \pathinfo($url);

            self::$fileNameUrl = $baseUrl['dirname'] ?? null;
            self::$fileBasename = $baseUrl['basename'] ?? null;
            self::$fileName = $baseUrl['filename'] ?? null;
            self::$fileType = $baseUrl['extension'] ?? null;
        }
        private function setCurrentFile():void {
            $filePath = \pathinfo(self::$filePath);
            $urlToArray = explode('/', substr($filePath['dirname'], 1));
            self::$currentFile = $urlToArray[0];
        }
        function currentFile() {
            return self::$currentFile;
        }
        public static function ActionPath($actionPath = null) {
            self::$actionPath = $actionPath ?? self::$actionPath;
            return self::$actionPath;
        }
    }
}