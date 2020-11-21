<?php

namespace Cimply\Core\Database
{
	/**
	 * ViewPresenter short summary.
	 *
	 * ViewPresenter description.
	 *
	 * @version 1.0
	 * @author Michael Eckebrecht
	 */
    use Cimply\Interfaces\Database\Enum\FetchStyleList;
    class Presenter extends Presenter\Builder {

        public function query(): ?string {
            $this->query = isset($this->query) ? $this->query : $this->operator
            .$this->top
            .$this->selector
            .$this->colsas
            .$this->from
            .$this->namespace.$this->table
            .$this->tableAs
            .$this->selectBy
            .$this->join
            .$this->combine
            .$this->on
            .implode('', $this->chain)
            .$this->union
            .$this->distinct
            .$this->subquery
            .(isset($this->orderBy) ? $this->orderBy.self::$sort : $this->orderBy)
            .$this->groupBy
            .$this->limit
            .$this->extend;
            return preg_replace('/\s+/', ' ', $this->query);
        }

        public function execute($name = null, $value = null): Provider  {
      
            //Provider::Cast($this->manager)->dbm->prepare($this->query());

            return Provider::Cast($this->manager)->dbq($this->query(), $this->params);
            //die(var_dump($stmt->fetchStyleMode(FetchStyleList::FETCH)->execute()));
            
            //$result = Provider::Cast($this->manager)->dbq($this->query(), $this->params);
            //$result = $this->manager->dbq($this->query(), $this->params);
            /*if(isset($value)) {
                isset($name) ? $result = array($name => array($value => $result[$name])) : $result = array($value => $result);
                return $result;
            }
            (isset($name) && isset($result[$name])) ? $result = $result[$name] : null;
            */
         
        }

        public function update($entity) {
            if((bool)$this->model) {
                $this->model->update($entity ? $entity : $this->execute());
                return $this;
            }
        }

        public function externalApi() {
            $dataSelector = null;
            $stripTags = false;
            $this->parameter = str_replace("�", "'", $this->parameter);
            isset($this->parameter['Table']) ? $this->setTable($this->parameter['Table']) : null;
            isset($this->parameter['Select']) ? $this->select($this->parameter['Select']) : null;
            isset($this->parameter['SelectBy']) ? $this->selectBy($this->parameter['SelectBy'], $this->parameter['Params']) : null;
            isset($this->parameter['Params']) ? $this->setParams($this->parameter['Params']) : null;
            isset($this->parameter['SelectById']) ? $this->selectById($this->parameter['SelectById']) : null;
            isset($this->parameter['SelectAll']) ? $this->selectAll() : null;
            isset($this->parameter['From']) ? $this->from($this->parameter['From']) : null;
            isset($this->parameter['OrderBy']) ? $this->orderBy($this->parameter['OrderBy']) : null;
            isset($this->parameter['GroupBy']) ? $this->groupBy($this->parameter['GroupBy']) : null;
            isset($this->parameter['Message']) ? $this->message($this->parameter['Message']) : null;
            isset($this->parameter['Asc']) ? $this->asc() : null;
            isset($this->parameter['Desc']) ? $this->sesc() : null;
            isset($this->parameter['Limit']) ? $this->limit($this->parameter['Limit']) : null;
            isset($this->parameter['Refresh']) ? $this->refresh($this->parameter['Refresh']) : null;
            isset($this->parameter['Data']) ? $dataSelector = $this->parameter['Data'] : null;
            isset($this->parameter['StripTags']) ? $stripTags = true : $stripTags = false;
            if(isset($this->parameter['Export'])) {
                $data = isset($this->parameter['Execute']) ? ($this->canExecute() ? $this->execute($dataSelector) : $this->query()) : $this->query();
                $this->ExportCsv($data);
            } else {
                $result = array((isset($this->parameter['Execute']) ? ($this->canExecute() ? $stripTags == true ? ($this->execute($dataSelector)) : $this->execute($dataSelector) : $this->query()) : $this->query()));
                return (isset($this->parameter['Straight'])) ? $result[0] : array("result" => $result[0]);
            }
        }
    }
}