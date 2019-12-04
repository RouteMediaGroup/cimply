<?php
namespace Cimply\Core\Database {
    /**
     * QueryManager short summary.
     *
     * QueryManager description.
     *
     * @version 1.0
     * @author Michael Eckebrecht
     */
    use \Cimply\Core \{
        Core
    };
    use \Cimply\Interfaces\Database \{
        IQuery, Enum\FetchStyleList, Enum\DataTypeList
    };

    use \Cimply\Core\Model\EntityBase;
    use Cimply\System\System;

    class Provider extends DatabaseFactory implements IQuery
    {
        use \Cast;

        public $dbm, $statement;
        protected $params, $fetchStyle = FetchStyleList::NULL;
        private $lastId, $ttl = 0, $cacheFilename, $messageObject = [];

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return static::Cull($mainObject, $selfObject, true);
        }

        #region Cimply\Interfaces\Database\IQuery Members


        function manager() {
            return $this->statement;
        }

        function bindValue($field, $type, $paramType): void {

        }

        function setValues(array $sthArray) : self
        {
            $this->params = $sthArray;
            return $this;
        }

        public static function dbCaching($query = null, $res = '', $type = '')
        {
            $output = array();
            $hash = md5($query);
            self::$cacheFilename = '/' . $hash . '.cache';
            $iCurrentTime = time();
            $iFiletime = is_file(self::$cacheFilename) ? filemtime(self::$cacheFilename) : 0;
            if ($iFiletime < $iCurrentTime - (60 * self::$ttl)) {
                if (isset($res->num_rows) && $res->num_rows > 0) {
                    $output = mysqli_fetch_all($res, $type);
                    System::FileForceContents(self::$cacheFilename, serialize($output));
                }
            } else {
                $output = unserialize(file_get_contents(self::$cacheFilename));
            }
            return array_merge(array('data' => $output), array('hashKey' => $hash));
        }

        function addColumn($table = null, $columnField = null, $columnType = 'varchar', $columnSize = null, $columnOptions = []): void {
            $sth = $this->dbm->prepare("ALTER TABLE {$table} ADD {$columnField} {$columnType}({$columnSize})");
            $sth->execute();
        }

        function dropColumn($table = null, $columnField = null) {
            $sth = $this->dbm->prepare("ALTER TABLE {$table} DROP COLUMN {$columnField}");
            $sth->execute();
        }

        #region Cimply\Interfaces\Database\IQuery Members

        /**
         *
         * @param string $sql
         */
        function dbq(string $sql = null, array $params = null)
        {
            isset($params) ? $this->setParams($params) : null;
            isset($params) ? $this->prepare($sql) : null;
            return $this;
        }

        /**
         *
         * @param  $arr
         * @param  $table
         * @param  $where
         * @param  $db
         */
        function save(EntityBase $arr1 = null, $arr2 = null): bool
        {
            if(!(isset($arr1) && $arr1->saveAble && !isset($arr2))) {
                \Debug::VarDump(['context-error' => $this]);
            }
            return $arr1->saveAble;
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
         *
         * @param string $name
         */
        function getInstance(string $name)
        {
            // TODO: implement the function Cimply\Interfaces\Database\IQuery::getInstance
        }

        /**
         */
        function getLastId()
        {
            // TODO: implement the function Cimply\Interfaces\Database\IQuery::getLastId
        }

        function fetchStyleMode(int $mode)
        {
            $this->fetchStyle = FetchStyleList::isValidValue($mode) ? $mode : FetchStyleList::NULL;
            return $this;
        }

        #endregion

        protected function setParams($params)
        {
            $this->params = $params;
        }

        protected function resetBinding()
        {
            unset($this->params, $this->bindTypes);
            $this->params = array();
            $this->bindTypes = '';
            return $this;
        }

        protected function doBindParam($typeSafe, $bindFunction)
        {
            return call_user_func_array(
                array($this->statement, $bindFunction),
                $this->referenceValues($this->params, $typeSafe)
            );
        }

        protected function referenceValues(array $params, $typeSafe = false)
        {
            $stmtParams = array();
            foreach ($this->params as $k => &$param) {
                $stmtParams[$k] = &$param;
            }

            if ($typeSafe === true) {
                array_unshift($stmtParams, $this->bindTypes);
            } else {
                array_unshift($stmtParams, str_repeat('s', count($params)));
            }

            return $stmtParams;
        }

        protected function typeSafe()
        {
            $this->typeSafe = true;
            return $this;
        }

        /**
         *
         * @param string $sql
         *
         */
        function prepare(string $statement): void
        {
            //parent::dbm()->prepare($statement);
        }

        /**
         * Summary of fetch
         * @return mixed
         */
        function fetch()
        {

        }

        /**
         */
        function execute(): bool
        {
           return false;
        }

        /**
         *
         * @return mixed
         */
        function beginTransaction():Provider
        {
            return $this;
        }

        /**
         *
         * @param int $flag
         * @param string $name
         */
        function commit():Provider
        {
            return $this;
        }

        /**
         */
        function errorHanlder()
        {
            // TODO: implement the function Cimply\Interfaces\Database\IQuery::errorHanlder
        }

        #endregion
    }
}