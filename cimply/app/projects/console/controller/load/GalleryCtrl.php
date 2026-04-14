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
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class GalleryCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $require, $data = array();
        public $templateEngine, $bereiche = array(), $module = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'FileStorage' => new ViewPresenter('FileStorage')
            )));

        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $this->data = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('FileStorage'))->Collection())
                        ->Refresh(1)
                        ->SelectBy("MimeType LIKE 'image/%' AND (Project = 'angebotsportal' OR Project = '".$this->require['UserKey']."')")
                        ->Execute('data', 'gallery');
                    $this->Epilogue();
                }
            }
        }
        
        public function Prologue() {
            return (bool)System::IsReady();
        }

        public function CalculateStorable() {
            return (bool)true;
        }

        public function Epilogue() {
            isset($this->params->webproject['ProjektId']) ? $this->params->sites = $this->data : Template::Show(System::Callback($this->data, 'json'), true);
        }
    }
}