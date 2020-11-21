<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply_Cim_View {

    use \Cimply_Cim_System\Cim_System as System;
    /**
     * Description of Cim_View_Form
     *
     * @author MikeCorner
     */
    class Cim_View_Images {
        public $Image;
        public function __construct() {
            System::ReadDirectory(View.'/Modules/Images');
        }
    }
}