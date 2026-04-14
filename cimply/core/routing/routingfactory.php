<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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