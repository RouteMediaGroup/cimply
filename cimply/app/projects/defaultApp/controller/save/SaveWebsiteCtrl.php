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
    
    use \Cimply_Cim_Core_Entities\Cim_IWebWebsitesEntity as IWebsitesEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_WebWebsitesEntity as WebsitesEntity;

    class SaveWebsiteCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Projekte' => new ViewPresenter('WebProjekte'),
                'Templates' => new ViewPresenter('WebTemplates'),
                'Websites' => new ViewPresenter('WebWebsites', new WebsitesEntity())
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
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
            $this->context->Where(isset($this->entity->WebsiteId) ? "WebsiteId = ".(int)$this->entity->WebsiteId : null);
            return (bool)$this->context->saveAble;
        }

        public function CalculateStorable() {
            $projectData = System::GetSession('WebProject');
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Websites')->Collection();
            $this->entity = new IWebsitesEntity(
                array(
                    'ProjektId' => $projectData['ProjektId'],
                    'WebsiteId' => isset($this->params['WebsiteId']) ? $this->params['WebsiteId'] : null,
                    'NaviPos' => isset($this->params['NaviPos']) ? $this->params['NaviPos'] : (count(ViewPresenter::Cast($collection)->SelectBy('UserKey = "'.$projectData['UserKey'].'" AND ProjektId = "'.$projectData['ProjektId'].'"')->Execute('data')) + 1),
                    'Depth' => isset($this->params['Depth']) ? $this->params['Depth'] : '0',
                    'NaviPid' => isset($this->params['NaviPid']) ? $this->params['NaviPid'] : null,
                    'Title' => isset($this->params['Title']) ? $this->params['Title'] : null,
                    'Name' => $this->RenderUrl($this->params['Title']),
                    'Url' => $this->RenderUrl($this->params['Title']).'.html',
                    'UserKey' => $projectData['UserKey'],
                    'Finish' => isset($this->params['Finish']) ? $this->params['Finish'] : 0,
                    'Deleted' => isset($this->params['Deleted']) ? $this->params['Deleted'] : 0
                )
            );
            $this->context = ViewPresenter::Cast($collection)->Entity()->Model();           
            if((empty($this->entity->WebsiteId)) && !(empty(ViewPresenter::Cast($collection)->SelectBy('UserKey = "'.$this->entity->UserKey.'" AND Name = "'.$this->entity->Name.'"')->Execute('data')))) {
                $this->context->warning = "Die Seite mit diesen Namen existiert bereits.";
                return false;
            }
            
            if(empty($this->entity->Title)) {
                $this->context->warning = "Sie haben keinen Titel eingeben.";
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
        
        private function RenderUrl($link = null, $hashkey = false) {
            if($hashkey) { 
                $link = md5($link.microtime());
            } else {
                $link = str_replace('Ä', 'ae', $link);
                $link = str_replace('Ö', 'oe', $link);
                $link = str_replace('Ü', 'ue', $link);
                $link = str_replace('ä', 'ae', $link);
                $link = str_replace('ö', 'oe', $link);
                $link = str_replace('ü', 'ue', $link);
                $link = str_replace('ß', 'ss', $link);
                $link = str_replace('_', '-', $link);
                $link = str_replace('&', '', $link);
            }
            return rawurlencode(str_replace(' ', '-', strtolower($link)));
        }
    
    }
}