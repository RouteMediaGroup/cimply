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
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Logger as Logger;

    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    use \Cimply_Cim_Core_Entities\Cim_IWebContentsEntity as IWebContent;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_WebContentsEntity as WebContent;

    class SavePositionCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params, $require, $data = array();
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                        'Contents' => new ViewPresenter('WebContents', new WebContent())
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode($params->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                   $this->Epilogue(); 
                }
            }
        }

        public function Prologue() {
                        
            foreach ($this->data as $newOrderObject) {
                $this->entity = new IWebContent((array)$newOrderObject);
                $this->context->Update((array)$this->entity);
                $this->context->Where(isset($this->entity->ContentId) ? "ContentId = ".(int)$this->entity->ContentId : null);
                $this->context->Save();
            }
            
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $this->require = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;         
          
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Contents')->Collection();
            $currentObject = ViewPresenter::Cast($collection)->SelectById($this->require['ContentId'])->Limit(1)->Execute('data');
            
            $newCurrentPos = ($currentObject['Pos'] + $this->require['upordown']);

            $changeObject = ViewPresenter::Cast($collection)->SelectBy('ProjektId = "'.$currentObject['ProjektId'].'" AND WebsiteId = "'.$currentObject['WebsiteId'].'" AND Pos = "'.($newCurrentPos).'"')->Limit(1)->Execute();       
            $newChangePos = ($this->require['upordown'] >= 1) ? ($changeObject['Pos']-1) : $changeObject['Pos']+1;
            
            $currentObject['Pos'] = $newCurrentPos;
            $changeObject['Pos'] = $newChangePos;
            
            $this->data = array($currentObject, $changeObject);
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model(); 
            
            return (bool)true;
        }

        public function Epilogue() {
            
            $this->context->query['message'] = "Erfolgreich gespeichert.";     

            Logger::Log($this->context->query['message']); 
            return (System::Callback($this->context, 'json'));
        }
    }
}