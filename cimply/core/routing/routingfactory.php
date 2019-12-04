<?php

namespace Cimply\Core\Routing
{
	/**
     * Connect short summary.
     *
     * Connect description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    use Cimply\Core\Routing\Manager;
	class RoutingFactory
	{
        private $provider;
        public $manager;
        function __construct($con) {
            $this->provider = $con['routing'] ?? 'default';
            switch ($this->provider)
            {
                case 'yaml':
                    try
                    {
                        new Manager\Yaml();
                    }
                    catch (\Exception $exception)
                    {
                        throw new \Exception($exception->getMessage());
                    }

                    break;

                case 'closure':
                    try
                    {
                        new Manager\Closure();
                    }
                    catch (\Exception $exception)
                    {
                        throw new \Exception($exception->getMessage());
                    }
                    break;

                case 'json':
                    try
                    {
                        new Manager\Json();
                    }
                    catch (\Exception $exception)
                    {
                        throw new \Exception($exception->getMessage());
                    }
                    break;

                case 'xml':
                    try
                    {
                        new Manager\Xml();
                    }
                    catch (\Exception $exception)
                    {
                        throw new \Exception($exception->getMessage());
                    }
                    break;

                case 'annotation':
                    throw new \Exception(printf("Data Provider \"%s\" not found.", $this->provider));

            	default:
                    try
                    {
                        new Manager\Yaml();
                    }
                    catch (\Exception $exception)
                    {
                        throw new \Exception($exception->getMessage());
                    }
            }
        }
    }
}