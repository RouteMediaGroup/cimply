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
    use \Cimply_Cim_View\Cim_View as View;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
  
    class WebsitesCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $require, $data = array();
        public $templateEngine, $bereiche = array(), $module = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Websites' => new ViewPresenter('WebWebsites')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    $this->params->webUrl = isset($this->params->webUrl) ? $this->params->webUrl : View::GetFileBaseName();
                    $filter = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
                    $userKey = isset($this->params->webproject['UserKey']) ? $this->params->webproject['UserKey'] : $this->require['UserKey'];
                    $websiteCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Websites'))->Collection());
                    $websiteCollection->SelectBy('UserKey = "'.$userKey.'" ')
                            ->ChainAnd('Deleted <= 0');
                    //isset($filter['subs']) ? $websiteCollection
                    //        ->ChainAnd('t1.Name = "'.$filter['subs'].'"')
                    //         : null;
                    $this->data = $websiteCollection->OrderBy('NaviPos')->Execute('data');                
                    $NaviPid = '';
                    foreach($this->data as $key => $value) {
                        if(isset($filter['subs'])) {
                            if($value['Name'] == $filter['subs']) {
                                $NaviPid = $value['WebsiteId'];
                            }
                            if(isset($value['NaviPid']) && ($NaviPid == $value['NaviPid'])) {
                                $data[] = $value;
                            }
                            isset($data) ? $this->data = $data : null;
                        } else {
                            System::SetSession('CurrentWebsite', $this->params->webUrl);
                            isset($this->params->webUrl) && ($this->data[$key]['Url'] == $this->params->webUrl) ? $this->data[$key]['Active'] = 'active' : $this->data[$key]['Active'] = '';
                        }
                    }
                    $this->Epilogue();
                }
            }
        }

        public function Prologue() {
            if (System::IsReady()) {
                $this->require = System::GetSession('WebProject');//Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
            }
            return (bool)true;
        }

        public function CalculateStorable() {
            return (bool)isset($this->require['ProjektId']) ? true : false;
        }

        public function Epilogue() {
            isset($this->params->webproject['ProjektId']) ? $this->params->sites = $this->data : Template::Show(System::Callback($this->data, 'json'), true);
        }
    }
}