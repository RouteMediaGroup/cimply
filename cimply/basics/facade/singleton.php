<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Basics\Facade {
    trait Singleton {
        protected static $instance;
        private static $protectInstance = false;
        function __construct($instanceName, $instanceClasses) 
        {
            if ( (!(self::$instance instanceof self)) && !(self::$protectInstance) ) {
                self::$instance = $instanceClasses();
                self::$instance[$instanceName] = $this;
                self::$protectInstance = true;
            }
            return (object)self::$instance;
        }

        public function getInstance($app = null) {
            return self::$instance['currentObject'] = (function() use ($app){
                $instance = (object)static::$instance;
                return $app;
            });
        }

    }
}