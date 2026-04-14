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
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class UserFilesCtrl implements IAssembly, IBasics {

        private $viewModel, $params;
        public $templateEngine, $bereiche = array(), $module = array(), $data = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'UserFiles' => new ViewPresenter('user_files'),
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $userfilesCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('UserFiles'))->Collection())->SetNamespace('marketing_center_global');
                    $this->data = $userfilesCollection
                        ->SelectBy('user_id = "'.$this->params['UserId'].'"')
                        ->OrderBy('internal_id')
                        ->Asc()
                        ->Execute('data');
                    $this->Epilogue();
                }
            }
        }

        public function Prologue() {
            return (bool)true;
        }

        public function CalculateStorable() {
            return (bool)isset($this->params['UserId']) ? true : false;
        }

        public function Epilogue() {
            return $this->data;
        }
    }
}