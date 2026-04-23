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

    class Debug extends Work {
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
    }
}
