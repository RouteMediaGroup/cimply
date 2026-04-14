<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace {
    trait AsyncOperation
    {
        public $threadId;
        public function __construct($threadId)
        {
            $this->threadId = $threadId;
        }

        public function run()
        {
            printf("T %s: Sleeping 3sec\n", $this->threadId);
            sleep(3);
            printf("T %s: Hello World\n", $this->threadId);
        }
        
        public function test() {
            $test = new Thread();
            $start = microtime(true);
            for ($i = 1; $i <= 5; $i++) {
                $t[$i] = new AsyncOperation($i);
                $t[$i]->start();
            }
            echo microtime(true) - $start . "\n";
            echo "end\n";
        }
        
    }
}