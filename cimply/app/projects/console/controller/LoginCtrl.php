<?php
/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply_Cim_App {
    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_System\Cim_System_Config as Config;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core_Entities\Cim_ILoginEntity as ILoginEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_LoginEntity as LoginEntity;
    use \Cimply_Cim_Core\Cim_Logger as Logger;
    
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
/**
 * Description of Save
 *
 * @author MikeCorner
 */
    class LoginCtrl implements IAssembly, IBasics {
        private $viewModel, $params, $context, $collection, $loginEntity;
        public $result, $crypto;

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request, $vm ? $vm : new ViewModel(null, array(
                'Login' => new ViewPresenter('Login', new LoginEntity()),
                'Mitarbeiter' => new ViewPresenter('Mitarbeiter')
            )));
        }
        
        public function Reference() {

        }
        
        public function Init($params = null, $vm = null) {
            $this->crypto = Config::GetConf('Crypto');
            $this->params = Request::Cast($params);
            $this->viewModel = $vm;
            if($this->Prologue()) {
                $this->loginEntity = new ILoginEntity($this->result);
                $mitarbeiter = ViewPresenter::Cast($this->viewModel->GetContext('Mitarbeiter')->Collection());
                $this->dataContext = $this->collection->TableAs('login')->Join()->Combine(
                    array(
                        "Mitarbeiter" => $mitarbeiter->TableAs('mitarbeiter')->Off()->Query()
                    )
                )->On('login.Password = \''.(($this->loginEntity->LoginId > 0) ? $this->loginEntity->Password : \Crypto::Encrypt($this->loginEntity->Password, $this->crypto['salt'], $this->crypto['pepper'])).'\' AND ((login.EMail = \''.$this->loginEntity->Username.'\') OR (login.Username = \''.$this->loginEntity->Username.'\'))')->ChainAnd('login.MitarbeiterId => mitarbeiter.MitarbeiterId')->ChainAnd('mitarbeiter.Status => 1');
                
                $this->context = \Lists::ObjectList($this->collection->Limit(1)->Execute('data'));
                if($this->CalculateStorable() == true) {
                    System::SetSession('UserId', $this->context->MitarbeiterId);
                    System::SetSession('Benutzer', $this->context->Username);
                    System::SetSession('Vorname', $this->context->Vorname);
                    System::SetSession('Nachname', $this->context->Nachname);
                    System::SetSession('EMail', $this->context->EMail);
                    System::SetSession('Rules', $this->context->Rules);
                    Logger::Log("Der Benutzer ".$this->context->Username." wurde geladen.");
                }
            }
            $this->Epilogue();
        }
           
        public function CalculateStorable() {
            if(\Conditions::IsNullOrEmpty(isset($this->context->Username))) {
                //$this->context->Passwort = md5($this->context->Passwort);
                return true;
            } else {
                $this->context->error = true;
                $this->context->message = "Benutzername oder Passwort sind nicht korrekt.";
            }
            return false;
        }
        
        public function Prologue() {
            if(System::IsReady()) {
                $this->collection = ViewPresenter::Cast($this->viewModel->GetContext('Login')->Collection());
                $require = Validator::Cast(Request::Cast($this->params)
                    ->AddSource()
                    ->AddValidationRules(ViewPresenter::Cast($this->collection)
                    ->ValidationRules())->Get());
                $this->result = $require->run()->sanitized;
                return true;
            }
            return false;
        }
        
        public function Epilogue() {
            if(isset($this->context->MitarbeiterId) && $this->context->MitarbeiterId > 0) {
                $contextLogindata = ViewPresenter::Cast($this->viewModel->GetContext('Login')->Collection())->Entity()->Model();
                $contextLogindata->Update(array('LoginId'=>$this->context->LoginId,'LastLogin'=>date("Y-m-d H:i:s")))->Where("MitarbeiterId = ".$this->context->MitarbeiterId)->Save();
            }
            return Template::Show(System::Callback(array('result' => $this->context), 'json'), true);
        }
    }
}