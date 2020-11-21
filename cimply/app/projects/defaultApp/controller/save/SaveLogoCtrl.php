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
    
    use \Cimply_Cim_Core_Entities\Cim_IWebProjekteEntity as IWebProjekteEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_WebProjekteEntity as WebProjekteEntity;

    class SaveLogoCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'WebProjekt' => new ViewPresenter('WebProjekte', new WebProjekteEntity()))
            ));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode($params->GetRequest(), true);
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
            $this->context->Where(isset($this->entity->ProjektId) ? "ProjektId = ".(int)$this->entity->ProjektId : null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $projectData = System::GetSession('WebProject');
            $this->entity = new IWebProjekteEntity(array_merge($projectData, $this->params));
            $projektCollection = ViewModel::Cast($this->viewModel)->GetContext('WebProjekt')->Collection();
            $this->context = ViewPresenter::Cast($projektCollection)->Entity()->Model();
            if(empty($this->entity->ProjektId)) {
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
            return (System::Callback($this->context, 'json'));
        }
    }
}