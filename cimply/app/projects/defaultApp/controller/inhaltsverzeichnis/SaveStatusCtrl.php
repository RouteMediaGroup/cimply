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
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_InhaltEntity as InhaltEntity;
    use \Cimply_Cim_Core_Entities\Cim_IInhaltEntity as IInhaltEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
        
    class SaveStatusCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entityInhalt, $params;
        public $result;

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Inhalt' => new ViewPresenter('Inhalt', new InhaltEntity())
            )));
        }

        public function Reference() {
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                $this->entityInhalt = new IInhaltEntity($this->params);
                if($this->Prologue()) {    
                    $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->context->Update((array) $this->entityInhalt);
            $this->context->Where(isset($this->entityInhalt->InhaltId) ? "InhaltId = ".(int)$this->entityInhalt->InhaltId : null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            if(!(empty($this->params))) {
                $this->context = ViewPresenter::Cast($this->viewModel->GetContext('Inhalt')->Collection())->Entity()->Model();
                return (bool)true;
            }
            return false;
        }

        public function Epilogue() {
            if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Der Status wurde geändert.";
            }
            //$this->context->result = ViewPresenter::Cast($this->viewModel->GetContext('Inhalt')->Collection())->SelectById((int)$this->entityInhalt->InhaltId)->Limit(1)->Refresh(true)->Execute();
            Template::Show(System::Callback($this->context, 'json'));
        }
    }
}