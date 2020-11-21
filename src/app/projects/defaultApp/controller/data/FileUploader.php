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
    
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Invoke_FileManager as FileManager;
    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_FileStorageEntity as FileStorageEntity;
    use \Cimply_Cim_Interfaces\Cim_FileCollection as IFileCollection;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class FileUploader implements IAssembly, IBasics {

        private $viewModel, $params;
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init(isset($params) && !(empty($params)) ? $params : (new Request())->GetRequest(), $vm ? $vm : new ViewModel(null, array(
                'FileStorageEntity' => new ViewPresenter('FileStorageEntity', new FileStorageEntity()),
            )));
        }

        public function Reference() {}
        
        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Epilogue()) {
                    $this->Prologue();
                }
            }
        }
        
        public function CalculateStorable() {
            return (bool)$this->params;
        }

        public function Epilogue() {
            $files = \unserialize($this->params['files']);
            (new FileManager(new IFileCollection($files), $this->viewModel))->FileUploader();
            return true;
        }

        public function Prologue() {
            
        }

    }
}