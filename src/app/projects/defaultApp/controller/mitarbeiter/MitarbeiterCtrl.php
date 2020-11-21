<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this katalog file, choose Tools | Templates
 * and open the katalog in the editor.
 */

/**
 * Description of IndexController
 *
 * @author MikeCorner
 */
namespace Cimply_Cim_App {
    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_LoginEntity as LoginEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_MitarbeiterEntity as MitarbeiterEntity;
      
    class MitarbeiterCtrl implements IAssembly, IBasics {
        private $viewModel, $params, $data = array(), $result = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Login' => new ViewPresenter('Login', new LoginEntity()),
                'Mitarbeiter' => new ViewPresenter('Mitarbeiter', new MitarbeiterEntity()),
                'Fachbereich' => new ViewPresenter('KatalogEintrag'),
                'Anrede' => new ViewPresenter('KatalogEintrag'),
                'KatalogEintrag' => new ViewPresenter('KatalogEintrag'),
            )));
        }

        public function Reference() {
            System::InjectController('mitarbeiter/SaveCtrl');
        }
        
        public function Init($params = null, $vm = null) {
            $this->params = Request::Cast($params);
            $this->viewModel = $vm;
            if($this->Prologue()) {
                if($this->CalculateStorable()) {                    
                    $mitarbeiterCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Mitarbeiter'))->Collection());
                    $loginCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel)->GetContext('Login')->Collection());
                    $this->data = $mitarbeiterCollection->TableAs('mitarbeiter')->Combine(
                        array(
                            "login" => $loginCollection->TableAs('login')->Off()->Refresh(1)->Query()
                        )
                    )->Join('LEFT')
                        ->Select('Personalnummer, Abteilung, Position, Standort, Fachbereiche, Anrede, Vorname, Nachname, Avatar, Mobil, EMail, Status')
                        ->On('mitarbeiter.MitarbeiterId = login.MitarbeiterId')
                        ->FieldVirtualAs('MitarbeiterId', 'mitarbeiter.MitarbeiterId')
                        ->FieldSwitchAs('DATE_FORMAT(login.LastLogin, \'%d.%m.%Y %H:%i:%s\')', 'LetzterLogin', '"---"', '---')
                        ->FieldSwitchAs('mitarbeiter.Mobil', 'Mobil', '"---"', '---')
                        ->FieldVirtualAs('Profilbild', 'mitarbeiter.FileKey', 'no-image')
                        ->FieldSwitchAs('login.LoginId', 'LoginId', 'login.LoginId', '')
                        ->FieldVirtualAs('Username', 'mitarbeiter.EMail')
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
            return Template::Show(System::Callback($this->result, 'json'), true);
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