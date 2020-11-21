<?php

namespace Cimply\Core\Database\Presenter
{
	/**
	 * ViewPresenter\RegistEntity short summary.
	 *
	 * ViewPresenter\RegistEntity description.
	 *
	 * @version 1.0
	 * @author Michael Eckebrecht
	 */
    use Cimply\Core\Database\Presenter;
    use Cimply\Core\Database\Provider;
    use Cimply\Core\Model\EntityBase;
    class RegistEntity {
        protected $entities = [];
        private $manager, $entity, $alias;
        public function __construct() {}
        protected function manager():Provider {
            return Provider::Cast($this->manager);
        }
        protected function entity():EntityBase {
            return EntityBase::Cast($this->entity);
        }
        protected function alias():string {
            return $this->alias;
        }
        public function add(object $manager, EntityBase $entity, string $alias = null):self {
            $this->manager = $manager;
            $this->entity = $entity;
            $this->alias = $alias ?? $entity->table;
            $this->entities[$this->alias()] = new Presenter($this->manager(), $this->entity(), $this->alias());
            return $this;
        }
        public function get($name = null):?Presenter {
            $result = "";
            isset($this->entities[$name]) ? $result = $this->entities[$name] : (function($name) {
                throw new \Exception("Entity '{$name}' could not found");
            })($name);
            return $result;
        }
        public function execute(): self {
            return $this;
        }
    }
}