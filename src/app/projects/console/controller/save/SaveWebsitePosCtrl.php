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
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Logger as Logger;

    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    use \Cimply_Cim_Core_Entities\Cim_IWebWebsitesEntity as IWebsitesEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_WebWebsitesEntity as WebsitesEntity;

    class SaveWebsitePosCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $entity, $params, $require, $results = array(), $data = array();
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                        'Websites' => new ViewPresenter('WebWebsites', new WebsitesEntity())
            )));
        }

        public function Reference() {

        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode($params->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                   $this->Epilogue(); 
                }
            }
        }

        public function Prologue() { 
            foreach ($this->params as $newOrderObject) {
                $this->entity = new IWebsitesEntity((array)array_merge((new ViewPresenter('WebWebsites'))->SelectBy('WebsiteId = "'.$newOrderObject['WebsiteId'].'"')->Limit(1)->Refresh(true)->Execute('data'), $newOrderObject));
                $this->context->Update((array)$this->entity);
                $this->context->Where("WebsiteId = ".$this->entity->WebsiteId);
                $this->context->Save();
                $this->results[] = $this->context;
            }
            return (bool)true;
        }

        public function CalculateStorable() {
            $collection = ViewModel::Cast($this->viewModel)->GetContext('Websites')->Collection();
            $this->context = WebsitesEntity::Cast(ViewPresenter::Cast($collection)->Entity()->Model()); 
            
            return (bool)true;
        }

        public function Epilogue() {
            
            $this->context->query['message'] = "Erfolgreich gespeichert.";     

            Logger::Log($this->context->query['message']); 
            return (System::Callback($this->results, 'json'));
        }
    }
}