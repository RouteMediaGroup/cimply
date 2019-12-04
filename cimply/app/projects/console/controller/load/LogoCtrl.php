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
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_FileStorageEntity as FileStorageEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class LogoCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $userFiles, $userLogo, $designPath;
        public $webproject;
        
        public function __construct($params = null, $vm = null) {
            $config = (object)System::$conf;
            $this->designPath = \File::GetFilePath(Root, PATHINFO_DIRNAME).$config->directories['design'];
            $this->Reference();
            $this->Init(isset($params) && !(empty($params)) ? $params : System::GetItems('Project', 'Requires'), $vm ? $vm : new ViewModel(null, array(
                'FileStorage' => new ViewPresenter('FileStorage', new FileStorageEntity()),
                'ImageData' => new ViewPresenter('ImageData')
            )));
        }

        public function Reference() {
            System::InjectController('load/UserMainCtrl');
            System::InjectController('load/UserFilesCtrl');
            System::ReadDirectory(View.'/Modules/Images');
            System::InjectController(View.'/Modules/CIM.View.Image.Crop.php', true);
        }
        
        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Epilogue()) {
                    $this->Prologue();
                }
            }
            return false;
        }
        
        public function HasLogo() {
            return true;
        } 
        
        public function CalculateStorable() {
            System::IsReady() ? $this->webproject = System::GetSession('WebProject') : $this->webproject = null;
            return (bool)$this->webproject;
        }

        public function Epilogue() {
            $currentUser = \ArrayParser::FlattenArray((new UserMainCtrl($this))->data);
            $this->userFiles = (new UserFilesCtrl($currentUser))->data;
            $this->userLogo = isset($this->getLogo()->file) ? $this->designPath.'/user_'.$currentUser['UserId'].'/'.$this->getLogo()->file : false;
            return (bool)is_file($this->userLogo);
        }

        public function Prologue() {
            $hasLogo = ((object)$this->params);
            if(isset($hasLogo->hasLogo)) {
                return true;
            }
            header('Content-type:' . \Mime::GetMime($this->userLogo));
            Template::Show(\File::GetFile($this->userLogo));
        }
        
        private function getLogo() {
            $currentLogo = null;
            foreach ($this->userFiles as $item) {
                ($item['special_type'] == "logo_main") ? $currentLogo = $item : null;
            }
            return (object)$currentLogo;
        }
    }
}