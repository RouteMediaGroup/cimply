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
    use \Cimply_Cim_System\Cim_System_Config as Config;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_MitarbeiterEntity as MitarbeiterEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_LoginEntity as LoginEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_AdressenEntity as AdressenEntity;
  
    use \Cimply_Cim_Core_Entities\Cim_ILoginEntity as ILoginEntity;
    use \Cimply_Cim_Core_Entities\Cim_IAdressenEntity as IAdressenEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

        
    class SaveCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $contextLogindata, $entityLoginMitarbeiter, $entityAdresse, $params;
        public $result, $cyrpto; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Mitarbeiter' => new ViewPresenter('Mitarbeiter', new MitarbeiterEntity()),
                'Login' => new ViewPresenter('Login', new LoginEntity()),
                'Adressen' => new ViewPresenter('Adressen', new AdressenEntity()),
                'Katalog' => new ViewPresenter('KatalogRepository')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            $this->crypto = Config::GetConf('Crypto');
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    $this->result = $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->context->Update((array) $this->entityLoginMitarbeiter);
            $this->context->Where(isset($this->entityLoginMitarbeiter->MitarbeiterId) ? "MitarbeiterId = ".(int)$this->entityLoginMitarbeiter->MitarbeiterId : null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $this->entityLoginMitarbeiter = new ILoginEntity($this->params);
            $this->entityAdresse = new IAdressenEntity($this->params);
            $this->context = ViewPresenter::Cast($this->viewModel->GetContext('Mitarbeiter')->Collection())->Entity()->Model();
            $isLoginDataFalse = empty($this->entityLoginMitarbeiter->MitarbeiterId) ? (bool)$this->CheckLoginData() : false;
            if(empty($this->entityLoginMitarbeiter->EMail)) {
                $this->context->warning = "Das Feld E-Mail-Adresse darf nicht leer sein.";
                return false;
            } else {
                $chkMail = explode('@', $this->entityLoginMitarbeiter->EMail);
                if(!isset($chkMail[1]) || strlen($this->entityLoginMitarbeiter->EMail) <= 4) {
                    $this->context->warning = "Die eingegebnen E-Mail-Adresse ist Fehlerhaft.";
                    return false;
                } else {
                    $this->entityLoginMitarbeiter->Benutzername = $this->entityLoginMitarbeiter->EMail;
                }
            }
            if(empty($this->entityLoginMitarbeiter->Vorname)) {
                $this->context->warning = "Das Feld Vorname darf nicht leer sein.";
                return false;
            }
            if(empty($this->entityLoginMitarbeiter->Nachname)) {
                $this->context->warning = "Das Feld Nachname darf nicht leer sein.";
                return true;
            }
            return (bool)!$isLoginDataFalse;
        }

        public function Epilogue() {
            if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                if($this->SaveLoginData()) {
                    $this->context->query['message'] = "Der Mitarbeiter wurde erfolgreich gespeichert.";
                } else {
                    $this->context->query['error'] = "Der Mitarbeiter konnte nicht gespeichert.";
                }
            }
            return System::Callback($this->context, 'json');
        } 
        
        private function CheckLoginData() {
            if(isset($this->entityLoginMitarbeiter->Password)) {
                if(strlen($this->entityLoginMitarbeiter->Password) <= 4) {
                    $this->context->warning = "Das Passwort muss aus mind. 5 Zeichen bestehen.";
                    return true;
                }
                else if(isset($this->params['PasswordRepeat']) && $this->entityLoginMitarbeiter->Password != $this->params['PasswordRepeat']) {
                    $this->context->warning = "Das Passwort passt nicht mit dem Kontroll-Passwort überein.";
                    return true;
                }
                $this->entityLoginMitarbeiter->Password = md5($this->entityLoginMitarbeiter->Password);
            } else {
                $this->context->warning = "Das Feld Passwort darf nicht leer sein.";
                return true;
            }
        }
        
        private function SaveLoginData() {
            if(!(empty($this->entityLoginMitarbeiter->Password))) {
                $this->entityLoginMitarbeiter->MitarbeiterId = MitarbeiterEntity::Cast($this->result)->result[0]['MitarbeiterId'];
                $this->entityLoginMitarbeiter->Password = \Crypto::Encrypt($this->entityLoginMitarbeiter->Password, $this->crypto['salt'], $this->crypto['pepper']);
                !isset($this->entityLoginMitarbeiter->Rules) ? $this->entityLoginMitarbeiter->Rules = '[{"Bereich":"cleaning", "Rolle":"User"}]' : null;
                $this->contextLogindata = ViewPresenter::Cast($this->viewModel->GetContext('Login')->Collection())->Entity()->Model();
                isset($this->entityLoginMitarbeiter->LoginId) ? $this->entityLoginMitarbeiter->LoginId : 0;
                LoginEntity::Cast($this->contextLogindata)->SetLoginId($this->entityLoginMitarbeiter->LoginId);
                $this->contextLogindata->Update((array) $this->entityLoginMitarbeiter);
                $this->contextLogindata->Where("MitarbeiterId = ".$this->entityLoginMitarbeiter->MitarbeiterId);
                $this->contextLogindata->Save();
            }
            return true;
        }
    }
}