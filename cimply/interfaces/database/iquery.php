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
    use \Cimply\Core\Database\Provider;
    use \Cimply\Core\Model\EntityBase;
    interface IQuery {
        function dbq(string $sql, array $params);
        function save(EntityBase $arr1 = null, $arr2 = null);

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