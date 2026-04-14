<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Interfaces\Database {
    use \Cimply\Core\Database\Provider;
    use \Cimply\Core\Model\EntityBase;
    interface IQuery {
        function dbq(string $sql, array $params);
        function save(?EntityBase $arr1 = null, $arr2 = null);

        function prepare(string $sql): void;
        function fetch();
        function fetchStyleMode(int $mode);
        function execute(): bool;
        function beginTransaction(): Provider;
        function commit(): Provider;

        function getIndexField(string $schema, $db = null);
        function getLastId();

        function errorHanlder();

    }
}
