<?php
namespace Cimply\Base\Singleton {
    trait View {
        private static $instance;
        function __construct($mediatorClass) {
            if (!(self::$instance instanceof self)) {
                self::$instance = new $mediatorClass();
            }
            return self::$instance;
        }      
    }
}