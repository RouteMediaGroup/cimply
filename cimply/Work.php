<?php
namespace Cimply {
    class Work extends \Exception {
        public $error = false;
        private $projectName = null;
        static $loader;
        function __construct($assembly = []) {
            self::autoLoader(function ($usings = []) use($assembly) {
                set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, array_merge((array)$usings ?? [], (array)$assembly ?? [])));
				spl_autoload_extensions('.php');
				spl_autoload_register(function($clsName) use($assembly) {
					!is_readable($caseSensitiveFile = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $clsName).'.php')) ?
                    spl_autoload(strtolower(str_replace(__NAMESPACE__.'\\', '', $clsName))) : require_once($caseSensitiveFile);    
                });
			}, $assembly);
        }
        function app($projectName = null): self {
            if(!($projectName)) {
                throw new \Exception("Error: load non-project.");
            }
            $this->projectName = $projectName;
            return $this;
        }
        function run($extends = []): ?App\Run {
            return new App\Run($this->projectName, self::$loader, ['extends' => $extends]) ?? null;
        }
        private static function autoLoader($loader, $assembly = []): void {
			if(!is_callable(self::$loader ?? self::$loader = $loader)) {
				throw new \Exception('classLoader error.');
            }
			//die(var_dump(array_merge((array)System\Settings::Assembly, (array)$assembly)));
            (self::$loader)($assembly).($loader)(array_merge((array)System\Settings::Assembly, (array)$assembly));
        }

        function __destruct() {
            self::$loader = null;
        }
    }
}