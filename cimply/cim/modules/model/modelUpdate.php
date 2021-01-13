<?php
namespace Cim\Modules\Model {
    class ModelUpdate implements \SplObserver{
        public function update(\SplSubject $subject) {
            return $subject;
        }
    }   
}
