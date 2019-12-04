<?php

/*
 * CIMPLY FrameWork V 1.1.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2017 RouteMedia. All rights reserved.
 */

/**
 * Description of IBase
 * 
 * 
 * @author Michael Eckebrecht
 */

namespace Cimply\Interfaces {
    abstract class IBase {
        protected $capsule;
        function getCapsule() {
            return $this->capsule;
        }
    } 
}