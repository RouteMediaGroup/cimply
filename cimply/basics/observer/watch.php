<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\Basics\Observer {
    use \Cimply\Basics\Basics;
    class Watch extends Basics implements \SplSubject {
        function __construct(...$args) {
            parent::__construct();
            parent::Reference(end($args), 'System/usings');
        }
        public function attach(\SplObserver $observer) {
            $this->instance[] = $observer;
        }
        public function detach(\SplObserver $observer) {
            $key = array_search($observer, $this->instance, true);
            if($key){
                unset($this->instance[$key]);
            }
        }
        public function notify(): bool {
            $value = null;
            foreach ($this->instance as $value) {
                (var_dump($value));
                $value->update($value);
            }
            return (bool)$value;
        }
    }
}