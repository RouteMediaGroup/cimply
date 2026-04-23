<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Database\Manager
{
    use Cimply\Core\Database\{Provider, Enum\FetchStyleList};
    use Cimply\Core\Model\EntityBase;
    use Cimply\Interfaces\Database\IProvider;
    use PDO;
    use PDOStatement;

    class SqlSrv extends Provider implements IProvider
    {
        private string $bindTypes = '';
        private bool $typeSafe = false;
        public $dbm = null;
        public $statement = null;

        public function __construct(PDO $manager, ?PDOStatement $sth = null)
        {
            $this->dbm = $manager;
            $this->statement = $sth;
        }

        #region Cimply\Interfaces\Database\IQuery Members

        public function save(?EntityBase $arr1 = null, $arr2 = null, $where = null, $table = null, $db = null): bool
        {
            parent::save($arr1);
            $row = array_intersect_key(
                (array)$arr1->storageData() ?? [],
                (array)$this->fetch() ?? []
            );
            
            if ($row) {
                // Update
                $setClause = implode(', ', array_map(fn($key, $val) => "$key = :$key", array_keys($row), array_values($row)));
                $query = sprintf("UPDATE %s SET %s %s", $table ?? $arr1->table, $setClause, trim($where));
            } else {
                // Insert
                $columns = implode(',', array_keys($row));
                $placeholders = implode(',', array_map(fn($key) => ":$key", array_keys($row)));
                $query = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table ?? $arr1->table, $columns, $placeholders);
            }

            $this->beginTransaction();
            $stmt = $this->dbm->prepare($query);

            foreach ($row as $key => $val) {
                $stmt->bindValue(":$key", $val);
            }

            $success = $stmt->execute();
            $success ? $this->commit() : $this->dbm->rollBack();
            
            return $success;
        }

        public function getIndexField(string $schema, $db = null)
        {
            // TODO: Implement the function Cimply\Interfaces\Database\IQuery::getIndexField
        }

        public function beginTransaction(): Provider
        {
            $this->dbm->beginTransaction();
            return parent::beginTransaction();
        }

        public function commit(): Provider
        {
            $this->dbm->commit();
            return parent::commit();
        }

        public function getLastId(): int
        {
            return (int) $this->dbm->lastInsertId();
        }

        public function errorHandler(): array
        {
            return $this->dbm->errorInfo();
        }

        #endregion

        #region Cimply\Interfaces\Database\IQuery Members

        public function prepare(string $sql): void
        {
            $this->statement = $this->dbm->prepare($sql);
        }

        public function fetch(): mixed
        {
            $fetchResult = null;

            if ($this->statement) {
                switch ($this->fetchStyle) {
                    case FetchStyleList::FETCHALL:
                        $fetchResult = $this->statement->fetchAll();
                        break;
                    case FetchStyleList::FETCHARRAY:
                        $fetchResult = $this->statement->fetch(PDO::FETCH_BOTH);
                        break;
                    case FetchStyleList::FETCHASSOC:
                        $fetchResult = $this->statement->fetch(PDO::FETCH_ASSOC);
                        break;
                    case FetchStyleList::FETCHOBJECT:
                        $fetchResult = $this->statement->fetch(PDO::FETCH_OBJ);
                        break;
                    case FetchStyleList::FETCHFIELD:
                        $fetchResult = $this->statement->fetch(PDO::FETCH_COLUMN);
                        break;
                    default:
                        $fetchResult = $this->statement->fetch(PDO::FETCH_BOTH);
                        break;
                }
            }

            return $fetchResult;
        }

        public function execute(): bool
        {
            return $this->statement ? $this->statement->execute() : false;
        }

        #endregion
    }
}
