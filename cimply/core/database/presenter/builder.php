<?php

namespace Cimply\Core\Database\Presenter
{
        /**
         * QueryBuilder short summary.
         *
         * QueryBuilder description.
         *
         * @version 1.0
         * @author MikeCorner
         */
        //use \Cimply\Core\Database\Enum\OperatorList
        use \Cimply\Core\Database\Provider;
        use \Cimply\Interfaces\Database\Enum\OperatorList;
        class Builder
        {
                public $tables = array(), $query, $result = [], $tableAs = null, $tableExt = null, $table = null, $onlyExt = null, $on = null, $operator = 'SELECT', $where = ' WHERE ', $selector = ' * ', $from = ' FROM ', $selectBy = null, $groupBy = null, $orderBy = null, $top = null, $limit = null, $once = false, $distinct = null, $join = null, $chain = [], $colsas = null, $combine = null, $union = null, $extend = null, $subquery = null, $entity = null, $model = null, $infoMessage = true, $refresh = false, $namespace = null, $dbconnect = null;
                protected static $sort = ' ASC ';
                public $params = [];

                function __construct($manager, $entityObject = null, $alias = null) {
                    $this->setManager($manager);
                    (isset($entityObject) && is_object($entityObject)) ? (function($entityObject) use($alias) {
                        $this->setTable($entityObject->table);
                        $this->tableAs($alias !== $entityObject->table ? $alias : null);
                        $this->setEntity($entityObject);
                    })($entityObject) : $this->setTable($entityObject);
                }

                public function setNamespace(string $namespace = null) {
                    isset($namespace) ? $this->namespace = $namespace.'.' : $this->namespace;
                    return $this;
                }

                public function setManager($manager = null) {
                    $this->manager = Provider::Cast($manager ? $manager : $this->manager);
                    return $this;
                }

                public function setTable(string $table = null) {
                    $this->table = $table ? $table : $this->table;
                    return $this;
                }

                public function setParams(array $params = []) {
                    $this->params = $params ? $params : $this->$params;
                    return $this;
                }

                public function tableAs($string = null) {
                    if(isset($string)) {
                        $this->tableAs = ' AS '.$string;
                        $this->tableExt = $string.'.';
                        $this->onlyExt = $string;
                    }
                    return $this;
                }

                public function off() {
                    $this->where = null;
                    $this->operator = null;
                    return $this;
                }

                /**
                 * set string name of table
                 * @param string $string
                 * @return Presenter
                 */
                public function on(string $string = null, string $operator = 'NULL') {
                    //$this->where = null;
                    isset($string) ? $this->on = ''.($this->tables[$operator] ?? NULL).' ON '.$string.' ' : $this->on = ' '.($this->tables[$operator] ?? null);

                    return $this;
                }

                public function operate($string = null) {
                    (empty($this->on) && (isset($string) && (self::isValidName($string)))) ? $this->operator = ' '.$string.' ' : $string;
                    return $this;
                }

                public function select($string = null) {
                    if(isset($string)) {
                        $expSelect = explode(',', str_replace(' ','',$string));
                        $part = array();
                        foreach($expSelect as $value) {
                            $part[] = ' '.$this->tableExt.$value.'';
                        }
                        $this->selector = implode(',', $part);
                    }
                    return $this;
                }

                public function selectById($id = null) {
                    if(isset($id)) {
                        $indexField = $this->manager->getIndexField($this->table)->name;
                        if($indexField) {
                            $this->selectBy = ' '.$this->where.' '. $this->tableExt.$indexField . '  = '.$id.' ';
                        }
                    }
                    return $this;
                }

                public function selectAll() {
                    //$indexField = $this->manager->getIndexField($this->table)->name;
                    //if($indexField) {
                    $this->selectBy = null;
                    //}
                    return $this;
                }

                public function selectBy($lambdaExpression) {
                    if(isset($lambdaExpression)) {
                        $expression = explode("=>", $lambdaExpression);
                        if(isset($expression[1])) {
                            $this->selectBy = $this->where.' '.$this->tableExt.implode('=', $expression) .' ';
                        } else {
                            $this->selectBy = $this->where.' '. $lambdaExpression;
                        }
                    }
                    return $this;
                }

                public function groupBy($string) {
                    isset($string) ? $this->groupBy = ' GROUP BY '.$this->tableExt.$string.' ' : null;
                    return $this;
                }

                public function orderBy($string) {
                    isset($string) ? $this->orderBy = ' ORDER BY '.$this->tableExt.$string.' ' : null;
                    return $this;
                }

                public function asc() {
                    self::$sort = ' ASC ';
                    return $this;
                }

                public function desc() {
                    self::$sort = ' DESC ';
                    return $this;
                }

                public function top(int $value) {
                    $this->top = " TOP({$value})";
                    return $this;
                }

                public function limit($limit) {
                    if(isset($limit)) {
                        $limiter = explode(',', $limit);
                        isset($limiter[1]) ? $this->limit = ' LIMIT '.$limit : $this->limit = ' LIMIT 0, '.$limit;
                        if(($limit == "0,1") || ($limit == "1")) {
                            $this->once = true;
                        }
                    }
                    return $this;
                }

                public function pushed($starts = 0, $limit = 1000) {
                    $this->limit = ' LIMIT '.$starts.', '.$limit;
                    return $this;
                }

                public function counts($name = 'counts') {
                    return $this->manager->dbq('SELECT count(*) '.$name.' FROM '.$this->table, []);
                }

                public function distinct() {
                    $this->distinct = ' DISTINCT ';
                    return $this;
                }

                public function all() {
                    $this->distinct = ' ALL ';
                    return $this;
                }

                public function combine($arrayOfQueries = array()) {
                    if(!(empty($arrayOfQueries))) {
                        $this->tables = array();
                        $output = array();
                        $enumList = OperatorList::GetValueList();
                        $i = 1;
                        $k = "";
                        foreach($arrayOfQueries as $query) {
                            foreach($enumList as $k => $item) {
                                $r = explode($k, $query);
                                isset($r[1]) ? $output = $r : null;
                            }
                            isset($output[$i]) ? $this->tables[$k] = $output[1] : $this->tables[$k].= $output[1] ;
                            $i++;
                        }
                        $this->selector = $output[0];
                        !isset($this->join) ? $this->join() : null;
                        //$this->combine = $this->selectBy;
                    }
                    return $this;
                }

                public function union($arrayOfQueries = null) {
                    if(is_array($arrayOfQueries)) {
                        $part = null;
                        foreach($arrayOfQueries as $query) {
                            $part.= ' UNION ('.$query.') ';
                        }
                        $this->union = $part;
                    } else {
                        $this->union = 'UNION '. $arrayOfQueries;
                    }
                    return $this;
                }

                public function from($string) {
                    isset($string) ? $this->from.= ' '.$string.' ' : null;
                    return $this;
                }

                public function join($join = null) {
                    $this->join = ' '.$join.' JOIN ';
                    return $this;
                }

                public function chain($lambdaExpression = null, $glue = null) {
                    if(isset($lambdaExpression)) {
                        $expression = explode("=>", $lambdaExpression);
                        if(isset($expression[1])) {
                            $this->chain[] = $glue.' ('.implode('=', $expression) .') ';
                        } else {
                            $this->chain[] = $glue. ' ' .$lambdaExpression .' ';
                        }
                    }
                    return $this;
                }

                public function chainAnd($expression = null) {
                    $this->chain($expression, ' AND');
                    return $this;
                }

                public function chainOr($expression = null) {
                    $this->chain($expression, ' OR');
                    return $this;
                }
                
                public function switchCase($fieldAs = null, $value = '') {
                    if(isset($fieldAs)) {
                        $this->colsas = $this->colsas.', CASE '.$value.' AS '.$fieldAs;
                    }
                    return $this;
                }

                public function fieldSwitchAs($from = null, $to = '', $value = null, $notnull = null) {
                    if(isset($from)) {
                        $hasNamespace = explode('.', $from);
                        !(isset($hasNamespace[1])) ? $from = $this->tableExt.$from : null;
                        if(isset($value)) {
                            isset($notnull)
                                ? $from = 'IF('.$from.' IS NULL OR '.$from.' = "", CONCAT('.$value.'), '.$from.')'
                                : $from = 'IF('.$from.' IS NULL, CONCAT('.$value.'), CONCAT('.$value.'))';
                        }
                        $this->colsas = $this->colsas.', '.$from.' AS '.$to;
                    }
                    return $this;
                }
                
                public function fieldVirtualAs($fieldAs = null, $value = '') {
                    if(isset($fieldAs)) {
                        $this->colsas = $this->colsas.', CONCAT('.$value.') AS '.$fieldAs;
                    }
                    return $this;
                }

                public function extend($string = '') {
                    isset($string) ? $this->extend = $string.' '.$this->extend : $this->extend;
                    return $this;
                }

                public function subQuery($table = '', $query = '', $where = null) {
                    isset($query) ? $this->subquery = ' = (SELECT '.$query.' FROM '.$table.(isset($where) ? ' WHERE '.$where : $where).') '.$this->subquery : $this->subquery;
                    return $this;
                }

                public function styleMode(int $mode) {
                    $this->manager->fetchStyleMode($mode);
                    return $this;
                }

                public function setEntity($value = null) {
                    $value ? $this->entity = $value : null;
                    return $this;
                }

                public function message($value) {
                    $this->infoMessage = $value;
                    return $this;
                }

                public function entity() {
                    if(isset(Core::$Factory[$this->table]) ? false : true) {
                        Core::addFactory(array($this->table => $this->entity));
                        $this->model = Core::$Factory[$this->table];
                    }
                    return $this;
                }

                public function canExecute() {
                    return (bool)isset($this->table);
                }

                public function model() {
                    return $this->model;
                }

                public function validationRules() {
                    return $this->manager->WrapperRules($this->table);
                }

                public function refresh($value = "true") {
                    $this->refresh = $value;
                    $this->manager->refresh($value);
                    return $this;
                }

                public function setQuery($query = null): self {
                        isset($query) ? $this->query = $query : null;
                        return $this;
                }

                public function getManager(): Provider {
                    return $this->manager;
                }

        }
}