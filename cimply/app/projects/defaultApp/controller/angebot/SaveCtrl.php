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
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_AdressenEntity as AdressenEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_KundenEntity as KundenEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_InhaltEntity as InhaltEntity;
    use \Cimply_Cim_Core_Entities\Cim_IAngebotEntity as IAngebotEntity;
    use \Cimply_Cim_Core_Entities\Cim_IAdressenEntity as IAdressenEntity;
    use \Cimply_Cim_Core_Entities\Cim_IKundenEntity as IKundenEntity;
    use \Cimply_Cim_Core_Entities\Cim_IInhaltEntity as IInhaltEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
        
    class SaveCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $contextKunde, $entityAngebot, $entityInhalt, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Angebot' => new ViewPresenter('Angebot', new AngebotEntity()),
                'Kunden' => new ViewPresenter('Kunden', new KundenEntity()),
                'Adressen' => new ViewPresenter('Adressen', new AdressenEntity()),
                'Inhalt' => new ViewPresenter('Inhalt', new InhaltEntity()),
                'Textbausteine' => new ViewPresenter('Textbausteine'),
                'Katalog' => new ViewPresenter('KatalogRepository')
            )));
        }

        public function Reference() {
            //System::InjectController('angebot/InhaltAngebotCtrl');
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                $this->entityAngebot = new IAngebotEntity($this->params);
                $this->entityAngebot->KundenId = $this->contextKunde->result[0]['KundenId'];
                $this->entityAngebot->Status = !empty($this->entityAngebot->Status) ? $this->entityAngebot->Status : '1';
                if($this->Prologue() && $this->entityAngebot->KundenId >= 1) {    
                    $this->saveInhalt($this->result = $this->context->Save());
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
                $entityAdresse = new IAdressenEntity($this->params);
                $contextAdresse = ViewPresenter::Cast($this->viewModel->GetContext('Adressen')->Collection())->Entity()->Model();
                $contextAdresse->Update((array)$entityAdresse);
                $contextAdresse->Where(isset($entityAdresse->AdressId) ? "AdressId = ".(int)$this->entityAngebot->AdressId : null);
                if($contextAdresse->Save()) {
                    $entityKunde = new IKundenEntity($this->params);
                    $entityKunde->AdressId = $contextAdresse->result[0]['AdressId'];
                    $this->contextKunde = ViewPresenter::Cast($this->viewModel->GetContext('Kunden')->Collection())->Entity()->Model();
                    $this->contextKunde->Update((array)$entityKunde);
                    $this->contextKunde->Where(isset($entityKunde->KundenId) ? "KundenId = ".(int)$entityKunde->KundenId : null);
                    $this->contextKunde->Save();
                }
                return (bool)$this->contextKunde->saveAble;
            }
            return false;
        }

        public function Epilogue() {
            if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Das Angebot wurde erfolgreich gespeichert.";
            }
            Template::Show(System::Callback($this->context, 'json'));
        }
        
        private function saveInhalt(AngebotEntity $angebot):bool {
            $angebot->Update(end($angebot->result));
            $textbausteinCollection = ViewPresenter::Cast($this->viewModel->GetContext('Textbausteine')->Collection());
            $textbausteinData = $textbausteinCollection->SelectBy('Status = "1"')->FieldVirtualAs('AngebotId', $angebot->AngebotId)->Refresh(true)->Execute('data');
            $contextInhalt = ViewPresenter::Cast($this->viewModel->GetContext('Inhalt')->Collection())->Entity()->Model();
            foreach($textbausteinData as $value) {
                $entities = new IInhaltEntity($value);
                $entities->ParentId <= 0 ? $entities->ParentId = 'NULL' : null;
                $contextInhalt->Update((array)$entities);
                $contextInhalt->Where();
                $contextInhalt->Save();
            }
            return true;
        }
    }
}