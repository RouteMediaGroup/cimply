<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Interfaces\Database {
    use Cimply\Core\Database\{DatabaseFactory};
    interface IDatabase {
        public function registerManager(array $connections);
        public function createInstance(string $name, DatabaseFactory $manager);
        public function getInstance(string $name);
    }
}