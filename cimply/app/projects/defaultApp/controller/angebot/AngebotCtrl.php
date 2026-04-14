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
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
      
    class AngebotCtrl implements IAssembly, IBasics {
        private $viewModel, $params, $data = array(), $result = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Angebot' => new ViewPresenter('Angebot'),
                'Login' => new ViewPresenter('Login'),
                'Mitarbeiter' => new ViewPresenter('Mitarbeiter'),
                'Kunden' => new ViewPresenter('Kunden'),
                'Fachbereich' => new ViewPresenter('KatalogEintrag'),
                'Adressen' => new ViewPresenter('Adressen')
            )));
        }

        public function Reference() {

        }
        
        public function Init($params = null, $vm = null) {
            $this->params = Request::Cast($params);
            $this->viewModel = $vm;
            if($this->Prologue()) {
                if($this->CalculateStorable()) {                    
                    $angebotCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Angebot'))->Collection());
                    $mitarbeiterCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Mitarbeiter'))->Collection());
                    $kundeCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Kunden'))->Collection());
                    $this->data = $angebotCollection->TableAs('angebot')->Combine(
                        array(
                            "Mitarbeiter" => $mitarbeiterCollection->TableAs('ma')->Off()->Join('RIGHT')->Query(),
                            "Kunde" => $kundeCollection->TableAs('ku')->Off()->Query()
                        )
                    )
                    ->Select('Titel')
                    ->On('AngebotId > 0')
                    ->FieldSwitchAs('ma.Vorname,\' \',ma.Nachname', 'ErstelltVon')
                    ->FieldVirtualAs('Kunde', 'ku.Firmname')
                    ->ChainAnd('angebot.MitarbeiterId = ma.MitarbeiterId')
                    ->ChainAnd('angebot.KundenId = ku.KundenId')  
                    ->Refresh(1)
                    ->Execute('data');
                }    
            }
        }
                        
        public function Prologue() {
            if(System::IsReady()) {
                return true;
            }
            return false;
        }
        
        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            return Template::Show(System::Callback(array("result" => $this->result), 'json'), true);
        }
        
        public function Load() {
            $this->result = $this->data;
            $this->Epilogue();
        }
        
        public function Save() { 
            $this->result = new SaveCtrl($this->params, $this->viewModel);
            $this->Epilogue();
        }
    }
}