<?php
namespace Cimply {
    use Cimply\Core\Request\Uri\UriManager;
    class CLI extends \Exception {
        static function Console(&$args, $app): bool {
            if($args === null) {
                die("no access.");
            }
            array_shift($args);
            $path = implode('_', $args);
            UriManager::ActionPath( DIRECTORY_SEPARATOR.(($path) ? __FUNCTION__.'/'.$path : DIRECTORY_SEPARATOR.__FUNCTION__) );
            return (bool)(php_sapi_name() === 'cli');
        }
    }
}