<?php
namespace Cimply\Base\Mediator {
    trait Data {
        private static $instance;
        function __construct($mediatorClass) {
            if (!(self::$instance instanceof self)) {
                self::$instance = new $mediatorClass();
            }
            return self::$instance;
        }
    }
}