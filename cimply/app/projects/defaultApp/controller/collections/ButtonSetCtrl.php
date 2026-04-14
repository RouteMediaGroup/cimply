<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply_Cim_App {
    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_View\Cim_View_Markup as ViewMarkup;

    class ModalCtrl implements IAssembly {

        private $viewModel, $params;
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params, $vm);
        }

        public function Reference() {
            System::InjectController('IndexCtrl');
        }
        
        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
        }
        
        public function Buttons(string ...$parameters) {
            $this->Create(new ViewMarkup(System::GetItems('Project','Path').'/markup/'.(isset($this->markup) ? $this->markup : 'formfields.xhtml')), \JsonDeEncoder::Decode('['.implode(',', $parameters).']', true), 'div');
            (new IndexCtrl($this->params, $this->viewModel))->Init();
        }
        
        private function Create(ViewMarkup $viewForm, $context = array(), $element = 'div', $attr = array("class" => "row", "ng-model" => "formData")) {
            $viewForm->BuildHTMLFromContext($element, $context, $attr);
        }
    }
}