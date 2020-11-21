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
    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_AngebotEntity as AngebotEntity;
    use \Cimply_Cim_Core_Entities\Cim_IAngebotEntity as IAngebotEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
        
    class SaveStatusCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $contextKunde, $entityAngebot, $entityInhalt, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Angebot' => new ViewPresenter('Angebot', new AngebotEntity()),
                'Katalog' => new ViewPresenter('KatalogEintrag'),
            )));
        }

        public function Reference() {
            System::InjectController('angebot/InhaltCtrl');
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
                $katalogContext = ViewPresenter::Cast($this->viewModel->GetContext('Katalog')->Collection())->Select('Anzeige')->SelectBy('Wert = "'.$this->entityAngebot->Status.'"')->ChainAnd('ReferenzId = 8')->Limit(1)->Execute('data'); 
                $this->context->query['message'] = "Der Status \"".$katalogContext."\" wurde gesetzt.";
            }
            Template::Show(System::Callback($this->context, 'json'));
        }
    }
}