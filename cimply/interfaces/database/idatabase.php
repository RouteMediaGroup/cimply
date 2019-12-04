<?php

/*
 * CIMPLY FrameWork V 1.1.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2017 RouteMedia. All rights reserved.
 */

/**
 * Description of IDatabase
 *
 *
 * @author Michael Eckebrecht
 */

namespace Cimply\Interfaces\Database {
    use Cimply\Core\Database\{DatabaseFactory};
    interface IDatabase {
        public function registerManager(array $connections);
        public function createInstance(string $name, DatabaseFactory $manager);
        public function getInstance(string $name);
    }
}