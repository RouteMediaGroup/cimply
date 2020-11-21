<?php

namespace Cim\Modules\Model
{
	/**
	 * ModulModel short summary.
	 *
	 * ModulModel description.
	 *
	 * @version 1.0
	 * @author Eckebrecht
	 */
	class ModulModel implements \SplSubject
	{
        private $observers = array();

        use \Properties;
        function __construct(object $data) {
            $this->setValue('description', '---');
            $this->setValue('stricttypes', $data->stricttypes ?? '');
            $this->setValue('modulname', $data->modulname ?? '');
            $this->setValue('namespace', $data->namespace ?? '');
            $this->setValue('cls_declare', $data->cls_declare ?? '');
            $this->setValue('cls_type', $data->cls_type ?? '');
            $this->setValue('cls_name', $data->cls_name ?? '');
            $this->setValue('extends', $data->extends ?? '');
            $this->setValue('interfaces', $data->interfaces ?? ''); 
            $this->setValue('extention', $data->extention ?? ''); 
            $this->setValue('constructor', (function($constructor) {
                $result = "";
                if((bool)$constructor) {
                    $result = '__construct function(...$arg) {}';
                }
                return $result;
            })($data->constructor ?? null) ?? '');
            $this->setValue('func_type', $data->func_type ?? '');
            $this->setValue('func_final', $data->func_final ?? '');
            $this->setValue('func_declare', $data->func_declare ?? '');
            $this->setValue('init_func', $data->init_func ?? '');
            $this->setValue('func_name', $data->func_name ?? '');
            $this->setValue('typehints', $data->typehints ?? '');
            $this->setValue('usings', (function($usings) {
                $result = "";
                foreach($usings as $using) {
                    $using = \ucwords($using.' ', '-');
                    $result.= "use {$using};
                        ";
                }
                return $result;
            })($data->usings) ?? '');
            $this->setValue('annotations', (function($annotations) {
                $result = "";
                foreach($annotations as $annotation) {
                    $annotation = \ucwords($annotation.' ', '-');
                    $result.= "
                * @{$annotation}
                    ";
                }
                return $result;
            })($data->annotations) ?? '* description of [+cls_name+]');
            $this->setValue('params', (function($params) {
                $result = [];
                foreach($params as $param) {
                    $explParams = explode(" ", $param);
                    $key = array_shift($explParams);
                    $result[$key] = implode(" ", $explParams); 
                }
                return (string)json_encode($result);
            })($data->annotations) ?? '{}');
        }

        //add observer
        public function attach(\SplObserver $observer) {
            $this->observers[] = $observer;
        }

        //remove observer
        public function detach(\SplObserver $observer) {
            $key = array_search($observer,$this->observers, true);
            if($key){
                unset($this->observers[$key]);
            }
        }

        public function getContent():self {
            $this->notify();
            return $this;
        }

        //notify observers(or some of them)
        public function notify() {
            foreach ($this->observers as $value) {
                $value->update($this);
            }
        }
	}
}