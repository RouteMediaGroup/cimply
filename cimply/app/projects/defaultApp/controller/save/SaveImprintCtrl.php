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
    use \Cimply_Cim_Core\Cim_Logger as Logger;

    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    use \Cimply_Cim_Core_Entities\Cim_IWebImpressumEntity as IWebImpressumEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_WebImpressumEntity as WebImpressumEntity;

    class SaveImprintCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'WebImpressum' => new ViewPresenter('WebImpressum', new WebImpressumEntity()))
            ));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $requestData = Request::Cast($params)->GetRequest();
            $this->params = !(empty($requestData)) ? (\JsonDeEncoder::Decode($requestData->GetRequest(), true)) : $params;
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->context->Update((array) $this->entity);
            $this->context->Where(isset($this->entity->Projekt) ? "Projekt = ".(int)$this->entity->Projekt : null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $projectData = System::GetSession('WebProject');
            $this->entity = new IWebImpressumEntity((!is_object($this->params)) ? array_merge($projectData, $this->params) : (array)$this->params);
            $impressumCollection = ViewModel::Cast($this->viewModel)->GetContext('WebImpressum')->Collection();
            $this->context = ViewPresenter::Cast($impressumCollection)->Entity()->Model();
            if(empty($this->entity->Projekt)) {
                $this->context->warning = "Fehler-Code: 10";
                return false;
            }         
            return true;
        }

        public function Epilogue() {
             if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Erfolgreich gespeichert.";     
            }
            Logger::Log($this->context->query['message']); 
            return is_object($this->params) ? true : (System::Callback($this->context, 'json'));
        }
    }
}