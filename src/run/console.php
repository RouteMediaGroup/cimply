<?php
namespace Cimply {
    use Cimply\System\Settings;
    use Cimply\Core\Request\Uri\UriManager;
    class Console extends \Exception {
        static $loader;
        function __construct($assembly = []) {
            self::autoLoader(function ($usings = []) use($assembly) {
                set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, array_merge($usings ?? [], $assembly)));
                spl_autoload_register(function($clsName) {
					is_callable($clsName) ? : spl_autoload(str_replace(__NAMESPACE__.'\\', '', $clsName));
				});
			});
        }
        function app($projectName = null): App\Run {
            if(!($projectName)) {
                throw new \Exception("Error: load non-project.");
            }
            return new App\Run($projectName, self::$loader, Settings::Assembly);
        }

        static function Console(&$args): bool {
            if($args === null) {
                die("no access.");
            }
            array_shift($args);
            $path = implode('_', $args);
            $root = (__FUNCTION__);
            UriManager::ActionPath("/{$root}/{$path}");
            return (bool)(php_sapi_name() === 'cli');
        }

        private static function autoLoader($loader = null, $assembly = []): void {
            isset(self::$loader) ? : self::$loader = $loader;
            ((string)self::$loader)($assembly).($loader)(Settings::Assembly);
        }

        function __destruct() {
            self::$loader = null;
        } 
    }
}