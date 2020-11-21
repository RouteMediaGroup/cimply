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

    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class UserMainCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $require;
        public $templateEngine, $bereiche = array(), $module = array(), $data = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $dbConnects = System::GetItems('DB','Database');
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'UserMain' => new ViewPresenter('user_main'),
                'WebProjekt' => new ViewPresenter('WebProjekte')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $userKey = isset($this->params->webproject['UserKey']) ? $this->params->webproject['UserKey'] : $this->require['UserKey'];
                    $userdataCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('UserMain'))->Collection())->SetNamespace('marketing_center_global');
                    $webProjektCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('WebProjekt'))->Collection());
                    $this->data = $userdataCollection->TableAs('userdata')->Combine(
                        array(
                            "Projekt" => $webProjektCollection->TableAs('projekt')->Off()->Query()
                        )
                    )->Select('Username')
                        ->On('hashcode = "'.$userKey.'"')
                        ->FieldSwitchAs('id', 'UserId')
                        ->FieldVirtualAs('ProjektId', 'projekt.ProjektId')
                        ->FieldSwitchAs('username','Username')
                        ->FieldSwitchAs('email','EMail')
                        ->FieldSwitchAs('type_id', 'TypeId')
                        ->FieldSwitchAs('superadmin', 'SuperAdmin')
                        ->FieldSwitchAs('status', 'Status')
                        ->FieldVirtualAs('DesignId', 'projekt.DesignId')
                        ->FieldVirtualAs('Paket', 'projekt.Paket')
                        ->FieldVirtualAs('Berufsgruppe', 'projekt.Berufsgruppe')
                        ->FieldVirtualAs('Title', 'projekt.Title')
                        ->FieldVirtualAs('Logo', 'projekt.Logo')
                        ->FieldVirtualAs('Color', 'projekt.Color')
                        ->FieldVirtualAs('Title', 'projekt.Title')
                        ->FieldVirtualAs('VerbandImg', 'projekt.VerbandImg')
                        ->FieldVirtualAs('VerbandTitle', 'projekt.VerbandTitle')
                        ->FieldVirtualAs('VerbandUrl', 'projekt.VerbandTitle')
                        ->FieldVirtualAs('UserKey', 'projekt.UserKey')
                        ->ChainAnd('userdata.hashcode = projekt.UserKey')
                        ->Extend('COLLATE utf8_unicode_ci')
                        ->Execute('data');
                    $this->Epilogue();
                }
            }
        }

        public function Prologue() {

            if (System::IsReady()) {
                $this->require = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
            }
            return (bool)true;
        }

        public function CalculateStorable() {
            return (bool)isset($this->params->webproject['ProjektId']) ? true : (isset($this->require['projectId']) ? true : $this->getSession());
        }

        public function Epilogue() {
            isset($this->params->webproject['ProjektId']) ? $this->params->userdata = $this->data : Template::Show(System::Callback($this->data, 'json'), true);
        }
        
        private function getSession() {
            $this->require = System::GetSession('WebProject');
            return (bool)$this->require['UserKey'];
        }
    
    }
}