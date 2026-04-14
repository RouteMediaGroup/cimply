<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply_Cim_App {

    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Logger as Logger;

    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Core_Entities\Cim_ITextbausteineEntity as ITextbausteineEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_TextbausteineEntity as TextbausteineEntity;

    class SavePositionCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params, $results = array();
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Textbausteine' => new ViewPresenter('Textbausteine', new TextbausteineEntity())
            )));
        }

        public function Reference() {

        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                   $this->Epilogue(); 
                }
            }
        }

        public function Prologue() {
            $i = 0;
            foreach ($this->params as $newOrderObject) {
                $this->entity = new ITextbausteineEntity($newOrderObject);
                $this->entity->ParentId <= 0 ? $this->entity->ParentId = 'NULL' : null;
                $this->entity->Status = 1;
                $this->context->Update((array)$this->entity);
                $this->context->Where("BausteinId = ".$this->entity->BausteinId);
                $this->context->Save();
                if($i >= count($this->params)) {
                    $this->results[] = $this->context;
                }
                $i++;
            }

            return (bool)true;
        }

        public function CalculateStorable() {
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Textbausteine')->Collection();
            $this->context = TextbausteineEntity::Cast(ViewPresenter::Cast($collection)->Entity()->Model()); 
            
            return (bool)true;
        }

        public function Epilogue() {
            
            $this->context->query['message'] = "Erfolgreich gespeichert.";     

            Logger::Log($this->context->query['message']); 
            System::Callback($this->context, 'json');
        }
    }
}