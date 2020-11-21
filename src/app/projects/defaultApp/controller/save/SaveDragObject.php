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
    use \Cimply_Cim_Core_Entities\Cim_IDragObjectsEntity as IDragObjectsEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_DragObjectsEntity as DragObjectsEntity;
    
    class SaveDragObject implements IAssembly, IBasics {

        private $viewModel, $params, $context, $entity;
        public $result; 
        
        public function __construct($params = null, $vm = null) {
            $this->Reference();            
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'DragObjects' => new ViewPresenter('DragObjects', new DragObjectsEntity())
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
            System::InjectController('MailCtrl');
        }
        
        public function CalculateStorable() {
            $collection = ViewModel::Cast($this->viewModel)->GetContext('DragObjects')->Collection();
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model();
            if(!(empty(System::GetSession('UserId')))) {
                $this->params['MitarbeiterId'] = System::GetSession('UserId');
                $this->params['CreateFrom'] = System::GetSession('Vorname').' '.System::GetSession('Nachname');
                $this->params['CreateDate'] = date("Y-m-d H:i:s");
                $this->params['Coords'] = \JsonDeEncoder::Encode($this->params['Coords']);
                $Comment = '[{"Message":"'.$this->params['Comment'].'","Ersteller":"'.$this->params['CreateFrom'].'","ErstelltAm":"'.date("d.m.Y H:i:s").'"}]';
                $result = (new ViewPresenter('DragObjects'))->SelectBy('ObjectId => "'.$this->params['ObjectId'].'"')->Select('Comments')->Limit(1)->Execute('data');
                $this->params['Comments'] = trim(\JsonDeEncoder::Encode(($result) ? $this->updateCurrentObject(array_merge(\JsonDeEncoder::Decode($result, true), \JsonDeEncoder::Decode($Comment, true))) : $Comment), '"');
                $this->entity = new IDragObjectsEntity((array)$this->params);
            } else {
                $this->context->error = true;
                $this->context->message = "Ihre Session wurde aus Sicherheitsgründen beendet. Bitte melden Sie sich erneut an.";
                return false;
            }
            return true;
        }
        
        public function Epilogue() {
            if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $Betreff = "Neuer Kommentar von \"".$this->params['CreateFrom']."\"";
                $Nachricht = "Sehr geehrte Damen und Herren,<br />Sie haben einen Kommentar zum Angebot Firma/Kunden {Name der Firma} von ".$this->params['CreateFrom']." erhalten.<br />Kommentar:<br />\"".$this->params['Comment']."\"<br /><br />Vielen Dank für Ihre Aufmerksamkeit.";
                new MailCtrl(array("EmailEmpfaenger"=>"info@wiggedruck.de","Betreff"=> $Betreff, "Nachricht" =>$Nachricht));
                
                $this->context->query['message'] = "Objekt wurde gespeichert.";     
            }
            Template::Show(System::Callback($this->context, 'json'));
        }
        
        public function Prologue() {
            $this->context->Update((array) $this->entity);
            $this->context->Where(isset($this->entity->ObjectId) ? "ObjectId = '".$this->entity->ObjectId."'"  : null);
            return (bool)$this->context->saveAble;            
        }
        
        private function updateCurrentObject($value = []):array{
            $this->params['MitarbeiterId'] = null;
            $this->params['CreateFrom'] = null;
            $this->params['CreateDate'] = null;
            $this->params['Coords'] = null;
            return $value;
        }
    }
}