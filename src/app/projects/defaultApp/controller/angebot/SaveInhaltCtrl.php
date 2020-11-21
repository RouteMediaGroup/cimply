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
    
    use \Cimply_Cim_Core_Entities\Cim_IInhaltEntity as IInhaltEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_InhaltEntity as InhaltEntity;

    class SaveInhaltCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Inhalt' => new ViewPresenter('Inhalt', new InhaltEntity())
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
            $this->context->Update((array) $this->entity);
            $this->context->Where(isset($this->entity->InhaltId) ? "InhaltId = ".(int)$this->entity->InhaltId : null);
            isset($this->entity->InhaltId) ? (new SaveVersionsverwaltungCtrl((new ViewPresenter('Inhalt'))->Select('AngebotId, InhaltId, Title, Content, ParentId, Position, LastUpdates, Status')->SelectById($this->entity->InhaltId)->Limit(1)->Execute('data'))) : null;
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Inhalt')->Collection();
            $this->entity = new IInhaltEntity((array)$this->params);
            $this->entity->Content = \addcslashes($this->entity->Content, '\"');
            $this->entity->LastUpdates = date('Y-m-d H:i:s');
            $this->entity->ParentId = isset($this->entity->ParentId) ? $this->entity->ParentId : 0;
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model();           
            if((isset($this->entity->Title)) && !(empty(ViewPresenter::Cast($collection)->SelectBy('InhaltId <= 0 AND ParentId = "'.$this->entity->ParentId.'" AND Title = "'.$this->entity->Title.'"')->Refresh(true)->Execute('data')))) {
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
            $result = System::Callback(str_replace('\"',"'", $this->context->result[0]), 'json');
            return ($result);
        } 
    }
}