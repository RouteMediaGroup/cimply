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
    class App
    {
        public bool $error = false;
        private ?App\Run $app = null;

        public function __construct(?string $project = null)
        {
            $frameworkRoot = __DIR__ . DIRECTORY_SEPARATOR . 'cimply';
            $this->app = (new Work([$frameworkRoot]))->app($project)->run();
        }

        public function run(): App\Run
        {
            if ($this->app === null) {
                throw new \RuntimeException('App has not been initialized.');
            }

            return $this->app;
        }
    }
}
