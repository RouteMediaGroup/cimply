<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Basics\Observer {
    use \Cimply\Basics\Basics;
    class Watch extends Basics implements \SplSubject {
        function __construct(...$args) {
            parent::__construct();
            parent::Reference(end($args), 'System/usings');
        }
        public function attach(\SplObserver $observer) {
            $this->instance[] = $observer;
        }
        public function detach(\SplObserver $observer) {
            $key = array_search($observer, $this->instance, true);
            if($key){
                unset($this->instance[$key]);
            }
        }
        public function notify(): bool {
            $value = null;
            foreach ($this->instance as $value) {
                (var_dump($value));
                $value->update($value);
            }
            return (bool)$value;
        }
    }
}