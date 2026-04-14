<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
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