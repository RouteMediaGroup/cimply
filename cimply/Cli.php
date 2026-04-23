<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply {
    use Cimply\Core\Request\Uri\UriManager;

    class CLI {
        public static function Console(&$args, $app = null): bool
        {
            if ($args === null) {
                throw new \RuntimeException('no access.');
            }

            array_shift($args);
            $path = implode('_', (array)$args);
            UriManager::ActionPath('/' . ($path !== '' ? __FUNCTION__ . '/' . $path : __FUNCTION__));

            return PHP_SAPI === 'cli';
        }
    }
}
