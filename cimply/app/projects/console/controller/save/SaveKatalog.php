<?php

/* 
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply_Cim_App {
    
    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Core_Entities\Cim_IKatalogEintragEntity as IKatalogEintragEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_KatalogEintragEntity as KatalogEintragEntity;
    
    class SaveKatalog implements IAssembly, IBasics {

        private $viewModel, $params, $context, $entity;
        public $result; 
        
        public function __construct($params = null, $vm = null) {
            $this->Reference();            
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'KatalogEintrag' => new ViewPresenter('KatalogEintrag', new KatalogEintragEntity())
            )));
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

        public function Reference() {
            
        }
        
        public function CalculateStorable() {
            $this->entity = new IKatalogEintragEntity((array)$this->params);
            $collection = ViewModel::Cast($this->viewModel)->GetContext('KatalogEintrag')->Collection();
            //$this->context = ViewPresenter::Cast($this->viewModel->GetContext('KatalogEintrag')->Collection());
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model();
            
            return true;
        }
        
        public function Epilogue() {
            if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Katalogeintrag wurde gespeichert.";     
            }
            Template::Show(System::Callback($this->context, 'json'));
        }
        
        public function Prologue() {
            $this->context->Update((array) $this->entity);
            $this->context->Where((isset($this->entity->ReferenzId) && isset($this->entity->Wert)) ? "ReferenzId = ".(int)$this->entity->ReferenzId." AND Wert = '".$this->entity->Wert."'"  : null);
            return (bool)$this->context->saveAble;
            
            
        }
    }
}