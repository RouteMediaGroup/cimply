<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author MikeCorner
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