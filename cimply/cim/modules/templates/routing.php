<?php
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