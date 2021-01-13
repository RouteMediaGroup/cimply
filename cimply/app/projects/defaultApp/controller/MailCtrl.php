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
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class MailCtrl implements IAssembly, IBasics {
        
        private $viewModel, $emailEmpfaenger;
        public $params = null; 
         
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params, $vm ? $vm : new ViewModel());
        }
        
        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if($this->Prologue()) {
                $this->emailEmpfaenger = isset($this->params['EmailEmpfaenger']) ? $this->params['EmailEmpfaenger'] : 'info@wiggedruck.de';
                if($this->CalculateStorable()) {
                    $this->Epilogue();
                    return "E-Mail wurde versendet";   
                } else {
                    return "Fehler";
                }
            }
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            $empfaenger = $this->emailEmpfaenger ? $this->emailEmpfaenger : 'info@wiggedruck.de';
            $betreff = $this->params['Betreff'];
            $emailBody = $this->params['Nachricht'];
            
            $header  = 'MIME-Version: 1.0' . "\r\n";
            $header.= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $header.= "From: Klüh Angebotsportal <info@Cimply.de>\n";
            $header.= "Reply-To: info@wiggedruck.de\n";
            //$header.= "Reply-To: ".$this->entity->EMail."\n";
            $header.= "Return-Path: info@wiggedruck.de\n";
            $header.= "X-Mailer: PHP/" . phpversion() . "\n";
            $header.= "X-Sender-IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
            $header.= "Content-type: text/html\n";

            //send email:
            mb_language('uni');
            if (mb_send_mail($empfaenger, $betreff, $emailBody, $header, "")) {
                // redirect if everything is okay:
                return true;
            } else {
                if (mail($empfaenger, $betreff, $emailBody, $header)) {
                   return true;
                   // $this->redirect($c_formmailer->conf['url-okay']);
                } else {
                    return false;
                }
            }
        }

        public function Prologue() {
            if(System::IsReady()) {
                return (bool)System::IsReady();
            }
            return false;
        }

        public function Reference() {
            
        }
    }
}