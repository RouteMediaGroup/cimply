<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2018 RouteMedia. All rights reserved.
 */

namespace Cimply\Core\Database {
    use Cimply\Core\Database\DatabaseFactory;
    use Cimply\Core\{Core};
    use Cimply\Interfaces\Database\{IDatabase};
    class Database extends DatabaseFactory implements IDatabase {
        static $managers = [], $connections = [];
        protected $queryString, $result;

        function __construct($manager = array()) {
            $this->registerManager($manager);
        }

        public final static function Cast($mainObject, $selfObject = self::class): ?self {
            return Core::Cast($mainObject, $selfObject);
        }

        #region Cimply\Interfaces\IDatabase Members

        /**
         *
         * @param self $object
         * 
         */
        function registerManager(array $dbConnections)
        {
            foreach($dbConnections as $key => $value) {
                $this->createInstance($key, new DatabaseFactory($value));
            }
        }

        function createInstance(string $name, DatabaseFactory $manager): void
        {
            self::$managers[$name] = $manager;
        }

        function getInstance(string $name): ?DatabaseFactory
        {
            return self::$managers[$name] ?? null;
        }
        #endregion
    }
}
