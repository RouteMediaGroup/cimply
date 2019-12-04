<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply\Core\Document\ObjectModel {

    class NodeManager {

        private $node;

        public function __construct($node) {
            $this->node = $node;
        }

        public function __call($name, $args) {
            if($name !== 'appendChild') {
                return $this->node->$name(...$args);
            } else {
                $this->node->appendChild(...$args);
                return $this->node;
            }
        }
    }
}