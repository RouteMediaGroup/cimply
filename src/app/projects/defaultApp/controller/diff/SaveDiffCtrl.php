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
    use \Cimply_Cim_Core_Entities\Cim_IVersionsverwaltungEntity as IVersionsverwaltungEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_VersionsverwaltungEntity as VersionsverwaltungEntity;

    class SaveVersionsverwaltungCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Versionsverwaltung' => new ViewPresenter('Versionsverwaltung', new VersionsverwaltungEntity())
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->params['MitarbeiterId'] = System::GetSession('UserId');
            $this->params['CreateFrom'] = System::GetSession('Vorname').' '.System::GetSession('Nachname');
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->context->Update((array) $this->params);
            $this->context->Where(null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Versionsverwaltung')->Collection();
            $this->entity = new IVersionsverwaltungEntity((array)$this->params);
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model();                     
            return true;
        }

        public function Epilogue() {
             if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Erfolgreich gespeichert.";     
            }
            Logger::Log($this->context->query['message']);
        } 
    }
}