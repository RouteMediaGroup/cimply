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
				spl_autoload_register(function($clsName) {
					!is_readable($caseSensitive = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $clsName).'.php')) ?
                    spl_autoload(strtolower(str_replace(__NAMESPACE__.'\\', '', $clsName))) : require_once($caseSensitive);
				});
			});
        }
        function app($projectName = null): self {
            if(!($projectName)) {
                throw new \Exception("Error: load non-project.");
            }
            $this->projectName = $projectName;
            return $this;
        }
        function run(): ?App\Run {
            return new App\Run($this->projectName, self::$loader, System\Settings::Assembly) ?? null;
        }
        private static function autoLoader($loader, $assembly = []): void {
			if(!is_callable(self::$loader ?? self::$loader = $loader)) {
				throw new \Exception('classLoader error.');
            }
            (self::$loader)($assembly).($loader)(System\Settings::Assembly);
        }

        function __destruct() {
            self::$loader = null;
        }
    }
}
