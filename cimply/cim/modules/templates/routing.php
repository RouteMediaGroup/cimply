<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

declare(strict_types=1);

namespace Cim\Modules\Templates {
    use \Cimply\Core\View\View;
    class Routing
    {
        public function render($data):string {
            $result = "";
            if(!empty($data)) {
               $result = View::ParseTplVars('
[+modulname+]:
    type:         [+extention+]
    params:       [+params+]
    action:       Cimply\App\[+modulname+]\[+cls_name+]::[+init_func+]
    templating:
        header:id=page-header:
            tpl: \'{->page_~header.html}\'
        main:id=page-content:
            tpl: \'{->page_~content.html}\'
        footer:id=page-footer:
            tpl: \'{->page_~footer.html}\'
    target: \'{->base.html}\'
    markupFile: formfields.xhtml
    caching:      true
               ', $data);
            }
            return $result;
        }
        
    }
}