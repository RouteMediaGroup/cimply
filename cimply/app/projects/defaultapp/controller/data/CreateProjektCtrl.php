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
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Logger as Logger;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_Core_Invoke_EntityManager as EntityManager;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    class CreateProjektCtrl implements IAssembly, IBasics {

        private $viewModel, $tplPattern, $params, $crypto;
        public $webproject = array();
        private $fieldsWebsites = array(
            "WebsiteId",
            "NaviPos",
            "NaviPid",
            "Depth",
            "Hidden",
            "Url",
            "Name",
            "'###PRJ_ID###' ProjektId",
            "Header",
            "Title",
            "Robots",
            "Description",
            "'###USR_KEY###' UserKey",
            "Deleted",
            "Finish"
        ), $fieldsProjekte = array(
            "ProjektId",
            "Title",
            "Paket",
            "Berufsgruppe",
            "VerbandImg",
            "VerbandTitle",
            "VerbandUrl",
            "DesignId",
            "Logo",
            "Header",
            "Color",
            "PiwikId",
            "Likebox",
            "'###USR_KEY###' UserKey",
            "Status"
        ), $fieldsContents = array(
            "ContentId",
            "Typ",
            "Data",
            "TemplateId",
            "Spalte",
            "'###WEB_ID###' WebsiteId",
            "'###PRJ_ID###' ProjektId",
            "Pos",
            "Hidden",
            "Deleted"
        );
        
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params, $vm ? $vm : new ViewModel(null, 
                array(
                    'WebProjekt' => new ViewPresenter('WebProjekte'),
                    'WebWebsite' => new ViewPresenter('WebWebsites'),
                    'WebContent' => new ViewPresenter('WebContents')
                )
            ));
        }

        public function Reference() {
   
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            $this->crypto = Config::GetConf('crypto');
            System::Setter('Pattern', $this->tplPattern)
                    . System::Setter('Theme', Config::GetConf('Themes'))
                    . System::Setter('Collection', Config::GetConf('Collection'))
                    . System::Setter('Parser', array(
                        'TemplateParser' => Config::GetConf('System/useTemplateFor'),
                        'FileParser' => Config::GetConf('System/useParseFiles')
            ));
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {

                }
                $this->Epilogue();
            }
        }

        public function Prologue() {
            return (bool)true;
        }

        public function CalculateStorable() {
            return (bool)true;
        }

        public function Epilogue() {
            return $this;
        }

        /*
         * exports project from WebProjekt
         * @param $ProjektId Integer project-id
         * @param $encrypt Boolean 
         * @return String|Array depends on param $encrypt               
         */

        public function ExportProject($ProjektId, $encrypt = true) {
            // projekt exportieren:
            $webProjektCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('WebProjekt'))->Collection());
            $webWebsiteCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('WebWebsite'))->Collection());
            $webProjekt = $webProjektCollection->Select(implode(',', $this->fieldsProjekte))->FieldVirtualAs('ProjektId', 'NULL')->SelectBy("ProjektId => " . $ProjektId)->Limit(1)->Execute('data');       
            if (!$webProjekt) {
                Logger::Log('err: project '.$ProjektId.' not found.');
            }
  
            // websites exportieren:
            $websites_array = $webWebsiteCollection->Select(implode(',', $this->fieldsWebsites))->SelectBy("ProjektId LIKE " . $ProjektId . " AND Deleted != 0")->Execute('data');
            
            $webWebsites = array();
            foreach ($websites_array as $website) {
                $akt = array();
                $akt['values'] = (new ViewPresenter('WebWebsites'))->Select(implode(',', $this->fieldsWebsites))->FieldVirtualAs('WebsiteId', 'NULL')->FieldSwitchAs('WebsiteId', 'NaviMid', 'WebsiteId')->FieldVirtualAs('NaviPid', 'NULL')->FieldSwitchAs('NaviPid', 'Subs', 'NaviPid')->SelectBy("ProjektId = " . $ProjektId . " AND WebsiteId = " . $website['WebsiteId'])->Limit(1)->Execute('data');
                $akt['webContents'] = array();
                // website content exportieren:
                $content_array = (new ViewPresenter('WebContents'))->SelectBy("WebsiteId = " . $website['WebsiteId'] . " AND Deleted = 0")->Execute('data');
                if (!(empty($content_array))) {
                    foreach ($content_array as $webContent) {
                        $akt['webContents'][] = array(
                            'values' => (new ViewPresenter('WebContents'))->Select(implode(',', $this->fieldsContents))->FieldVirtualAs('ContentId', 'NULL')->SelectBy("ContentId = " . $webContent['ContentId'] . " AND Deleted = 0")->Limit(1)->Execute('data')
                        );
                    }
                }
                $webWebsites[] = $akt;
            }            
            $project = array(
                'webProjekt' => $webProjekt,
                'webWebsites' => $webWebsites
            );
            return ($encrypt) ? \Crypto::Encrypt($project, $this->crypto['salt'], $this->crypto['pepper']) : $project;
        }

        /*
         *  inserts projekt for user
         *   
         * @param $defaultProject Array decoded *.web file
         * @param $userKey Array one entity of table "tw_user"
         * @param $webProjekt_overwriteFields Array set of fieldnames and values to overwrite from webProjekt
         * @return Integer project id                          
         */

        public function InsertProject($defaultProject, $userKey, $webProjekt_overwriteFields = array()) {
       
            $defaultProject['webProjekt'] = \ArrayParser::MergeArrays($defaultProject['webProjekt'], $webProjekt_overwriteFields);

            // projekt einfügen
            $webProjekt_keys = array_keys($defaultProject['webProjekt']);
            $defaultProject['webProjekt']['UserKey'] = $userKey;
            $q = 'INSERT INTO WebProjekte (' . implode(',', $webProjekt_keys) . ') VALUES (\'' . implode('\',\'', $defaultProject['webProjekt']) . '\')';

            if (!$ProjektId = EntityManager::dbq($q)) {
                return false;
            }

            //return $ProjektId;
            //$defaultProject = arrayWalker($defaultProject,"str_replace('###PRJ_ID###','".$ProjektId."',this)");
            //websites und content anlegen:
            $subArray = array();
            foreach ($defaultProject['webWebsites'] as $website) {
                $subs = array("Subs" => $website['values']['Subs'], "NaviMid" => $website['values']['NaviMid']);
                unset($website['values']['Subs']);
                unset($website['values']['NaviMid']);
                $tw_website_keys = array_keys($website['values']);
                $website['values']['UserKey'] = $userKey;
                $website['values']['ProjektId'] = $ProjektId;
                //Has Subs
                if($subs['Subs'] > 0) {
                    $website['values']['NaviPid'] = $subArray[$subs['Subs']];
                }
                $q = 'INSERT INTO WebWebsites (' . implode(',', $tw_website_keys) . ') VALUES (\'' . implode('\',\'', $website['values']) . '\')';
                if (!$WebsiteId = EntityManager::dbq($q)) {
                    return false;
                }
                //Set MainId
                if($subs['NaviMid'] > 0) {
                    $subArray[$subs['NaviMid']] = $WebsiteId;
                }
                
                //$webContents = arrayWalker($website['webContents'],"str_replace('###WEB_ID###','".$WebsiteId."',this)");
                foreach ($website['webContents'] as $webContent) {
                    $webContent_keys = array_keys($webContent['values']);
                    $webContent['values']['WebsiteId'] = $WebsiteId;
                    $webContent['values']['ProjektId'] = $ProjektId;
                    $q = 'INSERT INTO WebContents(' . implode(',', $webContent_keys) . ') VALUES (\'' . implode('\',\'', $webContent['values']) . '\')';
                    if (!$ContentId = EntityManager::dbq($q)) {
                        return false;
                    }
                }
            }
            return $ProjektId;
        }
    }
}