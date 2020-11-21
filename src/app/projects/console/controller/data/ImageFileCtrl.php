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
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Core\Cim_Logger as Logger;    
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_FileStorageEntity as FileStorageEntity;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_ImageDataEntity as ImageDataEntity;
    use \Cimply_Cim_Core_Entities\Cim_IFileStorageEntity as IFileStorageEntity;
    use \Cimply_Cim_Core_Entities\Cim_IImageDataEntity as IImageDataEntity;
    use \Cimply_Cim_Core_Entities\Cim_IWebProjekteEntity as IWebProjekteEntity;
    
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class ImageFileCtrl implements IAssembly, IBasics {

        private $viewModel, $context, $projektEntity, $fileEntity, $imageDataEntity, $require, $params, $modus;
        public $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null,
                array(
                    'FileStorage' => new ViewPresenter('FileStorageEntity', new FileStorageEntity()),
                    'ImageData' => new ViewPresenter('ImageDataEntity', new ImageDataEntity()),
                    'WebProjekt' => new ViewPresenter('WebProjekte')
                )
            ));
        }

        public function Reference() {
 
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode($params->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    $this->modus == 'remove' ? 
                            (FileStorageEntity::Cast($this->context)->Delete($this->fileEntity->FileId) ? 
                            \File::Remove($this->fileEntity->FilePath) : 
                        $this->context->error = "Beim löschen der Datei ist ein Fehler aufgetreten.") :
                        ImageDataEntity::Cast($this->context)->Save();
                    
                }
            }
            $this->Epilogue();
        }

        public function Prologue() {
            return (bool)isset($this->context->saveAble) ? true : false;
        }

        public function CalculateStorable() {
            $projectData = System::GetSession('WebProject') != null ? System::GetSession('WebProject') : [];
            $this->projektEntity = new IWebProjekteEntity($projectData);
            $this->fileEntity = new IFileStorageEntity($this->params);
            $this->imageDataEntity = new IImageDataEntity($this->params);

            if (System::IsReady()) {
                $this->require = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
                $this->modus = $this->require['modus'];

                switch ($this->modus) {
                    case 'remove':
                        $collection = ViewModel::Cast($this->viewModel)->GetContext('FileStorage')->Collection();
                        $this->context = ViewPresenter::Cast($collection)->Entity()->Model();
                        if($this->fileEntity->Project != $this->projektEntity->UserKey) {
                            //$this->context->warning = "Sie haben keine Berechtigung diese Datei zu löschen.";
                            //return false;
                        }
                        break;

                    case 'edit':
                        !(isset($this->imageDataEntity->ImageKey)) ? $this->imageDataEntity->ImageKey = md5($this->fileEntity->FileKey.$this->fileEntity->Project.$this->fileEntity->Label) : null;
                        $collection = ViewModel::Cast($this->viewModel)->GetContext('ImageData')->Collection();
                        $this->context = ViewPresenter::Cast($collection)->Entity()->Model();
                        $this->context->Update((array) $this->imageDataEntity);
                        $this->context->Where(isset($this->imageDataEntity->ImageKey) ? "ImageKey = '".$this->imageDataEntity->ImageKey."'" : null);
                        break;
                    
                    default:
                        $this->context = null;
                        break;
                }
            }
            if(empty($this->fileEntity->Project)) {
                $this->context->warning = "Ihre Session isr abgelaufen, bitte melden Sie sich erneut an.";
                return false;
            }
            
            if(empty($this->fileEntity->FileId)) {
                $this->context->warning = "Sie haben keine Datei gewählt.";
                return false;
            }  
            return true;
        }

        public function Epilogue() {
             if( isset($this->context->error) || isset($this->context->warning) ) {
                $this->context->query['message'] = Template::Translation($this->context->error ? $this->context->error : $this->context->warning);
            } else {
                $this->context->query['message'] = "Erfolgreich gelöscht.";     
            }
            Logger::Log($this->context->query['message']); 
            return (System::Callback($this->context, 'json'));
        }
    }
}