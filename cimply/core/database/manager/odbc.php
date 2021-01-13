<?php

namespace Cimply\Core\Database\Manager
{
	/**
	 * MSSql short summary.
	 *
	 * MSSql description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    use \Cimply\Core\Model\EntityBase;
    use \Cimply\Core\Database\Provider;
    use \Cimply\Interfaces\Database\IProvider;
	class ODBC extends Provider implements IProvider
    {
        function __construct(\PDO $manager, \PDOStatement $sth = null) {
            $this->statement = $sth;
            $this->dbm = $manager;
        }

        function bindValue($field, $type, $paramType): void {
            $this->statement->bindValue($field, $type, $paramType);
        }

        #region Cimply\Interfaces\Database\IQuery Members

         /**
         *
         * @param  $arr
         * @param  $table
         * @param  $where
         * @param  $db
         */
        function save(EntityBase $arr1 = null,  $arr2 = null, $where = null, $table = null, $db = null): bool
        {
            $sql = "";
            parent::save($arr1);
            if ($data = array_intersect_key((array)$arr1->storageData() ?? [], (array)$arr2) ?? []) {
                $identKeyValue = $arr1->identKeyValue();
                $rows = array_diff_key($data, $identKeyValue);
                $result = [];
                foreach ($rows as $key => $val) {
                    key($identKeyValue) !== $key . " = :{$key}" ? $result[] = $key . " = :{$key}" : null;
                }
                $keys = [];
                foreach ($identKeyValue as $key => $val) {
                    $keys[] = count($identKeyValue) > 1 ? key($val) : key($identKeyValue);
                }
                $sql = sprintf("UPDATE %s SET %s %s", $table ?? $arr1->table, implode(', ',$result), trim($where ?? $where = 'WHERE '. implode(' AND ', $keys)));
            } else {
                $sql = sprintf("INSERT INTO %s (%s) VALUES (:%s)", $table ?? $arr1->table, implode(', ', array_keys((array)$arr1->storageData())), implode(', :', array_keys((array)$arr1->storageData())));
            }
            return $this->stmtCreator($arr1->storageData(), $sql) ? $this->dbm->beginTransaction() : \Debug::VarDump($this->dbm->errorInfo());
        }

        /**
         *
         * @param string $schema
         * @param  $db
         */
        function getIndexField(string $schema, $db = null)
        {
            // TODO: implement the function Cimply\Interfaces\Database\IQuery::getIndexField
        }

        /**
         * Summary of beginTransaction
         */
        function beginTransaction():Provider
        {
            $this->dbm->beginTransaction();
            return parent::beginTransaction();
        }

        /**
         * Summary of commit
         */
        function commit():Provider
        {
            $this->dbm->commit();
            return parent::commit();
        }

        private function stmtCreator($arr = null, $stmt = null): bool {
            $data = [];
            foreach ($arr as $key => $val) {
                $data[':'.$key] = $val;
            }
            $this->dbm->query($stmt);
            $this->statement = $this->dbm->prepare($stmt);
            return $this->statement->execute($data);
        }

        /**
         * Summary of lastIndexId
         * @param mixed $query
         * @return mixed
         */
        function lastIndexId($query = null)
        {
            $sth = $this->dbm->prepare($query);
            $sth->execute();
            return $sth->lastInsertId();
        }

        function fetchRow($query) {
            die($this->dbs($query));
        }

        function fetchAll($query = null, $options = []) {
            $sth = $this->dbm->prepare($query);
            $sth->execute();
            $optionList = !empty($options) ? implode(',', $options) : \PDO::FETCH_ASSOC;
            return $sth->fetchAll($optionList);
        }

        function errorHanlder() {
            return $this->dbm->errorCode();
        }

        #endregion

        #region Cimply\Interfaces\Database\IQuery Members

        /**
         *
         * @param string $sql
         *
         */
        function prepare($statement = null):void
        {
            $this->statement = $statement ? $this->dbm->prepare($statement) : null;
        }

        /**
         * Summary of fetch
         * @return mixed
         */
        function fetch()
        {
            return $this->statement ? $this->statement->fetch($this->fetchStyle) : null;
        }

        /*
        function fetchStyleMode(int $mode)
        {
            $this->statement->setFetchMode(\PDO::FETCH_INTO, 1);
            return $this;
        }
        */

        function execute():bool
        {
            return $this->statement ? $this->statement->execute($this->params) : false;
        }

        #endregion

        #region Cimply\Interfaces\Database\IProvider Members

        #endregion

        #region Cimply\Interfaces\Database\IProvider Members

        /**
         *
         * @return Provider
         */
        function dbm():Provider
        {
            return static::Cast($this);
        }

        #endregion
    }
}