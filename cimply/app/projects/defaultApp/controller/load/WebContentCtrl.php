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
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class WebContentCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $data = null, $require;
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Projekte' => new ViewPresenter('WebProjekte'),
                'Websites' => new ViewPresenter('WebWebsites'),
                'Templates' => new ViewPresenter('WebTemplates'),
                'Contents' => new ViewPresenter('WebContents')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            System::Setter('Theme', Config::GetConf('Themes'))
                    . System::Setter('Collection', Config::GetConf('Collection'))
                    . System::Setter('Parser', array(
                        'TemplateParser' => Config::GetConf('System/useTemplateFor'),
                        'FileParser' => Config::GetConf('System/useParseFiles')
            ));
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $projektCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Projekte'))->Collection());
                    $websiteCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Websites'))->Collection());
                    $templateCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Templates'))->Collection());
                    $contentCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel)->GetContext('Contents')->Collection())->Refresh(true);
                    
                    if(isset($this->require['contentId'])) {
                        $this->GetContent($projektCollection, $websiteCollection, $templateCollection, $contentCollection); 
                    } else {
                        $data = System::GetSession('ContentData');
                        $this->NewContent($data[0]['ProjektId'], 1, $data[0]['WebsiteId'], 1, 1, 1);
                    }
                }
                $this->Epilogue();
            }
        }

        public function Prologue() {
            if (System::IsReady()) {
                $this->require = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
            }
            return (bool)$this->require;
        }

        public function CalculateStorable() {
            return (bool) true;
        }

        public function Epilogue() {
             Template::Show(System::Callback($this->data[0], 'json'));
        }
        
        private function NewContent($ProjektId = null, $Typ = null, $WebsiteId = null, $TemplateId = null, $Spalte = null, $Position = null) {
            $this->data = array(array("ProjektId" => $ProjektId, "Typ" => $Typ, "WebsiteId" => $WebsiteId, "TemplateId" => $TemplateId, "Spalte" => $Spalte, "Position" => $Position, "Data" => "{\"html\":\"\"}"));
        }
        
        private function GetContent($projektCollection = null, $websiteCollection = null, $templateCollection = null, $contentCollection = null) {
            $this->data = $projektCollection->TableAs('projekt')->Combine(
                    array(
                        "Website" => $websiteCollection->TableAs('website')->Off()->Join()->Refresh(1)->Combine(
                                array(
                                    "Content" => $contentCollection->TableAs('content')->Off()->Join()->Query()
                                )
                        )->On()->Query(),
                        "Template" => $templateCollection->TableAs('template')->Off()->Refresh(1)->Query()
                    )
            )->On('content.ContentId = '.$this->require['contentId'])
            ->ChainAnd('(projekt.ProjektId = website.ProjektId AND (content.WebsiteId = website.WebsiteId))')
            ->ChainAnd('template.TemplateId = content.TemplateId')
            ->Extend('ORDER BY content.Pos')
            ->Refresh(true)
            ->Execute('data');
        }
    }
}