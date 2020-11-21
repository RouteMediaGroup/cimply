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
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Base_Scope as Scope;
    class InhaltCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $require = array();
        public $result = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Inhalt' => new ViewPresenter('Inhalt'),
                'Bausteine' => new ViewPresenter('Textbausteine')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            
            $this->params = \JsonDeEncoder::Encode(Scope::GetParams());
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $data = [];
                    $resultString = "";
                    $inhaltsverzeichnisCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Inhalt'))->Collection());
                    if(isset($this->require->InhaltId)) {
                        $this->result = $inhaltsverzeichnisCollection->Select('*')->SelectBy('InhaltId = '.$this->require->InhaltId)->ChainAnd('Status=1')->Execute('data');
                    }
                    $this->Epilogue();
                }
            }
        }

        public function Prologue() {
            if (System::IsReady()) {
                $this->require = \JsonDeEncoder::Decode($this->params);
                return true;
            }
            return false;
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            return Template::Show(System::Callback($this->result, '_json'));
        }
    }
}