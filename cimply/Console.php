<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply {
    use Cimply\Core\Request\Uri\UriManager;

    class Console extends Work {
        public function __construct(array $assembly = [])
        {
            parent::__construct($assembly);
        }

        public function app(?string $projectName = null): App\Run
        {
            $run = parent::app($projectName)->run();
            if ($run === null) {
                throw new \RuntimeException('Error: load non-project.');
            }

            return $run;
        }

        public static function Console(&$args): bool
        {
            if ($args === null) {
                throw new \RuntimeException('no access.');
            }

            array_shift($args);
            $path = implode('_', (array)$args);
            $root = __FUNCTION__;
            UriManager::ActionPath("/{$root}/{$path}");

            return PHP_SAPI === 'cli';
        }
    }
}
