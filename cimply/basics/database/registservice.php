<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Basics\Database {
    use \Cimply\Core\{
        Core,
        Database\Database,
        Database\Presenter\Presenter,
        Database\Presenter\RegistEntity,
        Database\DatabaseFactory
    };

    use \Cimply\Core\Request\Request;
    
    class RegistService extends Database
    {
        private $dbManager = [], $services = null;
        public function registManager(string $model): void {
            $this->dbManager[$model] = (parent::Cast($this->services))->getInstance($model);
        }

        function registEntities(string $model, array $entities = []): RegistEntity {
            return (new RegistEntity($this->getManager($model)->dbm(), $entities));
        }

        function getManager($name= null): ?DatabaseFactory {
            return $this->dbManager[$name];
        }
    } 
}
