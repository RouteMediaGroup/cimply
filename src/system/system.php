<?php
namespace Cimply\System {
    /**
     * 
     * @Author Michael Eckebrecht
     * 
     */
    use \Cimply\System\Config;
    class System extends Helpers {
        protected static $conf = [], $configHelper;
        function __construct($config = null, $configFile = null) {
            if(isset($config)) {
                self::$configHelper = $config;
            }
            self::$conf = is_file($sysConfig = Settings::SystemPath.$configFile) ? self::$configHelper::loader([$sysConfig], self::$conf) : self::$conf;
        }
        private static function GetUsings($searchPatttern): ?array {
            return self::$configHelper::getConf(self::$conf, $searchPatttern);
        }
        protected function Reference($loader = null, $usings = null): void {
            $loader(self::GetUsings($usings));
        }
        public static function GetConfig(): ?Config {
            return self::$configHelper ?? null;
        }
    }
}
