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
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Scope as Scope;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class LoadDiffAngeboteCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Versionsverwaltung' => new ViewPresenter('Versionsverwaltung')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = Scope::GetParams();//\JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    
                }
                $this->Epilogue();
            }
        }

        public function Prologue() {
            //ToDo: SelectAll ändern in SelectBy -> Rollen und Bereiche
            $versionenCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Versionsverwaltung'))->Collection());
            $this->result = $versionenCollection->SelectBy('AngebotId = '.$this->params['id'])->ChainAnd('InhaltId > 0')->ChainAnd('Status = "1"')->Limit(10)->Execute('data');
            return !(empty($this->result)) ? true : false;
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            Template::Show(System::Callback($this->result, '_json'));
        }
    }
}