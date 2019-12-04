<?php

namespace Cimply\Core\Database\Manager
{
	/**
	 * PDO short summary.
	 *
	 * PDO description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    use Cimply\Core\Database\{Provider, Enum\FetchStyleList};
    use \Cimply\Core\Model\EntityBase;
    use \Cimply\Interfaces\Database\IProvider;
	class MySqli extends Provider implements IProvider
	{

        private $bindTypes  = '', $typeSafe   = false;

        function __construct(\mysqli $manager, $sth = null) {
            $this->statement = $sth;
            $this->dbm = $manager;
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
            parent::save($arr);
            if ($row = array_intersect_key((array)$arr->storageData() ?? [], (array)$this->fetch()) ?? []) {
                // update
                $result = '';
                foreach ($row as $key => $val) {
                    $result .= $key . "='" . $val . "', ";
                }
                $query = sprintf("UPDATE %s SET %s %s", $table ?? $arr->table, substr($result, 0, -2), trim($where));
            } else {
                $query = sprintf("INSERT INTO %s (%s) VALUES ('%s')", $table ?? $arr->table, implode(',', array_keys((array)$row)), implode("', '", array_values((array)$storageData)));
            }
            $this->beginTransaction() ? $arr->execute($query) : null;
            die(var_dump($query));
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

        function getLastId()
        {
            return $this->dbm->insert_id();
        }

        function errorHanlder() {
            return $this->dbm->error();
        }

        #endregion

        #region Cimply\Interfaces\Database\IQuery Members

        /**
         *
         * @param string $sql
         *
         * @return void
         */
        function prepare($sql): void
        {
            $this->statement = $this->dbm->prepare($sql);
        }

        function fetch()
        {
            $fetchResult = NULL;
            $result = $this->statement->get_result();
            switch ($this->fetchStyle)
            {
                case FetchStyleList::FETCHALL:
                    $fetchResult = $result->fetch_all();
                    break;
                case FetchStyleList::FETCHARRAY:
                    $fetchResult = $result->fetch_array();
                    break;
                case FetchStyleList::FETCHASSOC:
                    $fetchResult = $result->fetch_assoc();
                    break;
                case FetchStyleList::FETCHOBJECT:
                    $fetchResult = $result->fetch_object();
                    break;
                case FetchStyleList::FETCHFIELD:
                    $fetchResult = $result->fetch_field();
                    break;
                default:
                    $fetchResult = $result->fetch_array();
                    break;
            }


            return $fetchResult;
        }

        /**
         */
        function execute(): bool
        {
            empty($this->params) ? : $this->doBindParam($this->typeSafe, 'bind_param');
            $this->statement->execute();
        }

        #endregion

    }
}