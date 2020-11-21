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
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_AngebotEntity as AngebotEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_MitarbeiterEntity as MitarbeiterEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_KundenEntity as KundenEntity;

    class AngebotObjectCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $data = array(), $result = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Angebot' => new ViewPresenter('Angebot', new AngebotEntity()),
                'Mitarbeiter' => new ViewPresenter('Mitarbeiter', new MitarbeiterEntity()),
                'Kunden' => new ViewPresenter('Kunden', new KundenEntity()),
                'Adressen' => new ViewPresenter('Adressen'),
                'Bereiche' => new ViewPresenter('KatalogEintrag'),
                'Standort1' => new ViewPresenter('KatalogEintrag'),
                'Standort2' => new ViewPresenter('KatalogEintrag')
            )));
        }

        public function Reference() {
        }

        public function Init($params = null, $vm = null) {
            $this->params = Request::Cast($params);
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $angebotCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Angebot'))->Collection());
                    $mitarbeiterCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Mitarbeiter'))->Collection());
                    $kundenCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Kunden'))->Collection());
                    $adressenCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel)->GetContext('Adressen')->Collection());
                    $bereichCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel)->GetContext('Bereiche')->Collection());
                    $standort1Collection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel)->GetContext('Standort1')->Collection());
                    $this->data = $angebotCollection->TableAs('angebot')->Combine(
                        array(
                            "Kunden" => $kundenCollection->TableAs('kunden')->Off()->Join()->Combine(
                                array(
                                    "Adresse" => $adressenCollection->TableAs('kundenadresse')->Off()->Join()->Query()
                                )
                            )->On()->Query(),
                            "Mitarbeiter" => $mitarbeiterCollection->TableAs('mitarbeiter')->Off()->Join()->Combine(
                                array(
                                    "Standort1" => $standort1Collection->TableAs('standort1')->Off()->Join()->Query()
                                )
                            )->On()->Query(),
                            "Bereich" => $bereichCollection->TableAs('bereich')->Off()->Query()
                        )
                    )->Select('AngebotId, Titel, Kundenangaben, Standorte')
                        ->FieldVirtualAs('Firma', 'kunden.Firmname', '---')
                        ->FieldVirtualAs('ErstelltVon', 'mitarbeiter.Vorname,\' \',mitarbeiter.Nachname', '---')
                        ->FieldVirtualAs('Standort', 'mitarbeiter.Standort', '---')
                        ->FieldVirtualAs('ErstelltAm', 'angebot.CreateOn', '---')
                        ->FieldVirtualAs('UpdateAm', 'angebot.UpdateOn', '---')
                        ->FieldVirtualAs('Profilbild', 'mitarbeiter.FileKey', 'image-not-found')
                        ->FieldVirtualAs('Ansprechpartner', 'kunden.Ansprechpartner', '---')
                        ->FieldVirtualAs('ZuletztBearbeitetVon', 'kunden.Ansprechpartner', '---')
                        ->FieldVirtualAs('ZuletztBearbeitetAm', 'angebot.UpdateOn', '---')
                        ->FieldSwitchAs('standort1.Anzeige', 'Standort1', '"---"', '"---"')
                        ->FieldVirtualAs('Strasse', 'kundenadresse.Strasse', '---')
                        ->FieldVirtualAs('Plz', 'kundenadresse.Plz', '---')
                        ->FieldVirtualAs('Bereich', 'bereich.Anzeige', '---')
                        ->FieldVirtualAs('Status', 'angebot.Status', '---')
                        ->On('angebot.KundenId = kunden.KundenId')
                        ->ChainAnd('(standort1.Katalog = "Standorte" AND (standort1.Wert = mitarbeiter.Standort))')
                        ->ChainAnd('(bereich.ReferenzId = 1 AND (bereich.Wert = angebot.Zielgruppe))')
                        ->ChainAnd('kundenadresse.AdressId = kunden.AdressId')
                        ->GroupBy('AngebotId')
                        ->Refresh(1)
                        ->Execute('data');                    
                }
                $this->Epilogue();
            }
        }

        public function Prologue() {
            if (System::IsReady()) {
                return true;
            }
            return false;
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {            
            return Template::Show(System::Callback($this->data, 'json'), true);
        }

    }

}