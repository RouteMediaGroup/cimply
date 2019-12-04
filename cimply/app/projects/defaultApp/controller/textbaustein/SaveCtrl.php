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
    
    use \Cimply_Cim_Core_Entities\Cim_ITextbausteineEntity as ITextbausteinEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_TextbausteineEntity as TextbausteinEntity;

    class SaveCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Textbaustein' => new ViewPresenter('Textbausteine', new TextbausteinEntity())
            )));
        }

        public function Reference() {
            System::InjectController('diff/SaveDiffCtrl');
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->entity->CreateFrom = System::GetSession('Vorname').' '.System::GetSession('Nachname');
            isset($this->entity->BausteinId) ?: ($this->entity->CreateDate = date("Y-m-d H:i:s")).($this->entity->Status = '1');
            $this->context->Update((array) $this->entity);
            $this->context->Where(isset($this->entity->BausteinId) ? "BausteinId = ".(int)$this->entity->BausteinId : null);
            isset($this->entity->BausteinId) ? (new SaveVersionsverwaltungCtrl((new ViewPresenter('Textbausteine'))->Select('BausteinId, Title, Content, ParentId, Position, LastUpdates, Status')->SelectById($this->entity->BausteinId)->Limit(1)->Execute('data'))) : null;
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Textbaustein')->Collection();
            $this->entity = new ITextbausteinEntity((array)$this->params);
            $this->entity->Content = \addcslashes($this->entity->Content, '\"');
            $this->entity->LastUpdates = date('Y-m-d H:i:s');
            $this->entity->ParentId = isset($this->entity->ParentId) ? $this->entity->ParentId : 0;
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model();           
        
            if((isset($this->entity->Title)) && !(empty(ViewPresenter::Cast($collection)->SelectBy('BausteinId <= 0 AND ParentId = "'.$this->entity->ParentId.'" AND Title = "'.$this->entity->Title.'"')->Execute('data')))) {
                $this->context->warning = "Ein Baustein mit diesem Namen existiert bereits.";
                return false;
            }
            
            if(empty($this->entity->Title)) {
                $this->context->warning = "Sie haben keinen Titel eingeben.";
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