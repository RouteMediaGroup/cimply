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

    use Cimply\Core\Database\{Provider};
    use Cimply\Interfaces\Database\{Enum\FetchStyleList, IQuery};
	class MSSql extends Provider implements IQuery
	{
        function __construct(\PDO $manager, \PDOStatement $sth = null) {
            $this->dbm = $manager;
            $this->statement = $sth;
        }

        #region Cimply\Interfaces\Database\IQuery Members

         /**
         *
         * @param  $arr
         * @param  $table
         * @param  $where
         * @param  $db
         */
        function dbw($arr = array(), $table = null, $where = null, $db = null)
        {
            // TODO: implement the function Cimply\Interfaces\Database\IQuery::dbw
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

        function beginTransaction()
        {
            $this->dbm->beginTransaction();
            return $this->dbm;
        }

        function commit(int $flag, string $name)
        {
            $this->dbm->commit($flag, $name);
            return $this->dbm;
        }

        /**
         */
        function getLastId()
        {
            return ($this->dbm->lastInsertId());
        }

        function errorHanlder() {
            return $this->dbm->errorCode();
        }

        #endregion

        #region Cimply\Interfaces\Database\IQuery Members

        /**
         *
         * @param  $sql
         */
        function prepare($sql): void
        {
            $this->statement = $this->dbm->prepare($sql);
        }

        /**
         */
        function fetch()
        {
            return $this->statement->fetch($this->fetchStyle);
        }

        /*
        function fetchStyleMode(int $mode)
        {
            $this->statement->setFetchMode(\PDO::FETCH_INTO, 1);
            return $this;
        }
        */

        function execute()
        {
            $this->statement->execute($this->params);
        }

        #endregion
    }
}