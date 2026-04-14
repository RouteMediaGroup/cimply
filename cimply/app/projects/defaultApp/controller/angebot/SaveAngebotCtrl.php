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
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_AngebotEntity as AngebotEntity;
    use \Cimply_Cim_Core_Entities\Cim_IAngebotEntity as IAngebotEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
        
    class SaveAngebotCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entityAngebot, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Angebot' => new ViewPresenter('Angebot', new AngebotEntity()),
            )));
        }

        public function Reference() {
            System::InjectController('angebot/InhaltCtrl');
            System::InjectController('MailCtrl');
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                $this->entityAngebot = new IAngebotEntity($this->params);
                if($this->Prologue()) {    
                    $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->context->Update((array) $this->entityAngebot);
            $this->context->Where(isset($this->entityAngebot->AngebotId) ? "AngebotId = ".(int)$this->entityAngebot->AngebotId : null);
            
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            if(!(empty($this->params))) {
                $this->context = ViewPresenter::Cast($this->viewModel->GetContext('Angebot')->Collection())->Entity()->Model();
                return (bool)true;
            }
            return false;
        }

        public function Epilogue() {
            if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $ersteller = System::GetSession('Vorname').' '.System::GetSession('Nachname');
                $Betreff = "Neues Angebot für Firma/Kunden \"".$this->params['Firmenname']."\"";
                $Nachricht = "Sehr geehrte Damen und Herren,<br />es wurde ein neues Angebot für die Firma/den Kunden \"".$this->params['Firmenname']."\" von ".$ersteller."  für den Bereich Cleaning angelegt.<br /><br />Vielen Dank für Ihre Aufmerksamkeit.";
                new MailCtrl(array("EmailEmpfaenger"=>"info@wiggedruck.de","Betreff"=> $Betreff, "Nachricht" =>$Nachricht));
                $this->context->query['message'] = "Das Angebot wurde erfolgreich gespeichert.";
            }
            Template::Show(System::Callback($this->context, 'json'));
        }
    }
}