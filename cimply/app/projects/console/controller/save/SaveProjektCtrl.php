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
    use \Cimply_Cim_Core\Cim_Logger as Logger;

    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
        
    use \Cimply_Cim_Core_Entities\Cim_IWebContentsEntity as IWebContent;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_WebContentsEntity as WebContent;

    class SaveProjektCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Projekte' => new ViewPresenter('WebProjekte'),
                'Websites' => new ViewPresenter('WebWebsites'),
                'Templates' => new ViewPresenter('WebTemplates'),
                'Contents' => new ViewPresenter('WebContents', new WebContent())
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode($params->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    $this->context->Save();
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            $this->context->Update((array) $this->entity);
            $this->context->Where(isset($this->entity->ContentId) ? "ContentId = ".(int)$this->entity->ContentId : null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $projectData = System::GetSession('WebProject');
            $this->webUrl = System::GetSession('CurrentWebsite');
     
            isset($this->params['Data']) ? $this->params['Data'] = $this->prittyContent($this->params['Data']) : null;

            $this->entity = new IWebContent(array_merge($projectData, $this->params));
            $websiteCollection = ViewModel::Cast($this->viewModel)->GetContext('Websites')->Collection();
            $this->entity->WebsiteId = ViewPresenter::Cast($websiteCollection)->Select('WebsiteId')->SelectBy("ProjektId = '".$projectData['ProjektId']."' AND UserKey = '".$projectData['UserKey']."' AND Url = '".$this->webUrl."'")->Limit(1)->Execute('data');
            $contentCollection = ViewModel::Cast($this->viewModel)->GetContext('Contents')->Collection();
            $this->context = ViewPresenter::Cast($contentCollection)->Entity()->Model();
            /*if(empty($this->entity->TemplateId) || empty($this->entity->WebsiteId)) {
                $this->context->warning = "Fehler-Code: 10";
                return false;
            }*/
            if(empty($this->entity->Data)) {
                $this->context->warning = "Sie haben keinen Inhalt eingegeben.";
                return false;
            }
          
            return true;
        }

        public function Epilogue() {
             if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Erfolgreich gespeichert.";     
            }
            Logger::Log($this->context->query['message']); 
            return (System::Callback($this->context, 'json'));
        }
        
        private function prittyContent($data = null) {
            $result = array();
            foreach (\JsonDeEncoder::Decode($data, true) as $key => $value) {
                if(is_array($value)) {
                    $result[$key] = $value;
                } else {
                    $result[$key] = preg_replace('/\r?\n|\r/', '', str_replace('	', '', str_replace('"', '\"', \htmlentities($value))));
                }
            }
            return \html_entity_decode(\JsonDeEncoder::Encode($result), JSON_PRETTY_PRINT);
        }
        
    }
}