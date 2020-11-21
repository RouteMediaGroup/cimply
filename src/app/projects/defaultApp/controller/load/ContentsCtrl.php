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
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class ContentsCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $require, $data = array();
        public $templateEngine, $bereiche = array(), $module = array();

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
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $projektCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Projekte'))->Collection());
                    $websiteCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Websites'))->Collection());
                    $templateCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Templates'))->Collection());
                    $contentCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel)->GetContext('Contents')->Collection());
                    $this->data = $projektCollection->TableAs('projekt')->Combine(
                        array(
                            "Website" => $websiteCollection->TableAs('website')->Off()->Join()->Refresh(1)->Combine(
                                array(
                                    "Content" => $contentCollection->TableAs('content')->Off()->Join()->Refresh(1)->Query()
                                )
                            )->On()->Query(),
                            "Template" => $templateCollection->TableAs('template')->Off()->Refresh(1)->Query()
                        )
                    )->Select('ProjektId')
                        ->On('projekt.UserKey = \''.$this->params->webproject['UserKey'].'\'')
                        ->FieldVirtualAs('Id', 'content.ContentId', '---')
                        ->FieldVirtualAs('Name', 'template.Name', '---')
                        ->FieldVirtualAs('Bezeichnung', 'template.Bezeichnung', '---')
                        ->FieldVirtualAs('Placeholder', 'template.Placeholder', '---')
                        ->FieldVirtualAs('Route', 'template.Route', '---')
                        ->FieldVirtualAs('Position', 'content.Pos', '---')
                        ->FieldVirtualAs('Markup', 'template.Markup', '---')
                        ->FieldVirtualAs('Data', 'content.Data', '---')
                        ->FieldVirtualAs('DesignId', 'projekt.DesignId', '---')
                        ->ChainAnd('(projekt.ProjektId = website.ProjektId AND (content.WebsiteId = website.WebsiteId))')
                        ->ChainAnd('website.Url = "'.$this->params->webUrl.'"')
                        ->ChainAnd('template.TemplateId = content.TemplateId')
                        ->ChainAnd('content.Deleted = 0')
                        ->Extend('ORDER BY content.Pos')
                        ->Refresh(1)
                        ->Execute('data');
                    if(empty($this->data) && isset($this->params->webproject['UserKey'])) {
                        $websiteCollection = new ViewPresenter('WebWebsites');
                        $getWebsite = $websiteCollection->SelectBy('UserKey = "'.$this->params->webproject['UserKey'].'" AND Url = "'.$this->params->webUrl.'"')->Limit(1)->Execute('data');
                        $this->data = array(array("ProjektId" => $this->params->webproject['ProjektId'], "Id" => null, "WebsiteId" => $getWebsite['WebsiteId'], "Markup" => "default-section.tpl", "Name" => "content", "Position"=>"1", "Data"=>""));
                        System::SetSession('ContentData', $this->data);
                    }
                    $this->Epilogue();
                }
            }
        }

        public function Prologue() {
            if (System::IsReady()) {
                $this->require = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
            }
            return (bool)true;
        }

        public function CalculateStorable() {
            return (bool)isset($this->params->webUrl) ? true : (isset($this->require['url']) ? true : false);
        }

        public function Epilogue() {
            isset($this->params->webUrl) ? $this->params->contents = $this->data : Template::Show(System::Callback($this->data, 'json'), true);
        }
    }
}