<?php
namespace Cimply\Basics\ServiceLocator
{
	class ServiceLocator
    {
        use \Cast;

        /**
         * @var array
         */
        private $services = [];

        /**
         * @var array
         */
        private $instantiated = [];

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        /**
         * instead of supplying a class here, you could also store a service for an interface
         *
         * @param string $class
         * @param object $service
         */
        public function addInstance($service, $name = null)
        {
            $class = $name ?? get_class($service);
            $this->services[$class] = $service;
            $this->instantiated[$class] = $service;
            return $service;
        }

        public function has(string $interface): bool
        {
            return isset($this->services[$interface]) || isset($this->instantiated[$interface]);
        }

        /**
         * @param string $class
         *
         * @return object
         */
        public function getService(string $className = null)
        {
            return $this->services[$className] ?? $this->services;
        }
    }
}