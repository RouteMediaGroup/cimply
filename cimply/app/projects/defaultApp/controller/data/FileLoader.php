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
    use \Cimply_Cim_Core\Cim_Core_Invoke_FileManager as FileManager;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;   
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_FileStorageEntity as FileStorageEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class FileLoader extends FileManager implements IAssembly, IBasics {

        private $viewModel, $params, $require;
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init(isset($params) && !(empty($params)) ? $params : System::GetItems('Project', 'Requires'), $vm ? $vm : new ViewModel(null, array(
                'FileStorage' => new ViewPresenter('FileStorage', new FileStorageEntity()),
                'ImageData' => new ViewPresenter('ImageData')
            )));
        }

        public function Reference() {
            System::ReadDirectory(View.'/Modules/Images');
            System::InjectController(View.'/Modules/CIM.View.Image.Crop.php', true);
        }
        
        public function Init($params = null, $vm = null) {
            $this->params = Validator::Cast($params, true)->results;
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Epilogue()) {
                    $this->Prologue();
                }
            }
            parent::Init($params, $vm);
        }
        
        public function CalculateStorable() {
            parent::Init();
            return (bool)true;
        }

        public function Epilogue() {
            $data = array();
            $fileStorageCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('FileStorage'))->Collection());
            $imageDataCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('ImageData'))->Collection());
            $imageKey = md5(\ArrayParser::ArrayToString((new ViewPresenter('FileStorage'))->Select('FileKey, Project, Label')->SelectBy('FileKey => "'.$this->params['file'].'"')->Execute('data'), 0, '', false));
            if(!(isset($this->params['size']))) {
                $data = $fileStorageCollection->TableAs('files')->Combine(
                    array(
                        'ImageData' => $imageDataCollection->TableAs('image')->Off()->Query()
                    )
                )->Select('*')
                    ->On('files.FileKey = "'.$this->params['file'].'"')
                    ->ChainAnd('files.FileKey = image.FileKey')   
                    ->ChainAnd('image.ImageKey => "'.$imageKey.'"')
                    ->FieldSwitchAs('image.Coords', 'Coords', 'files.Coords', 'image.Coords')
                    ->Limit(1)->Execute('data');
            }
            !(empty($data)) ? $this->GetFile(array($data)) : (new FileManager())->LoadFile();
        }
        
        public function GetFile($data) {
            $result = (object)$data[0];
            if(isset($result->Project)) {
                header('Content-type:' . $result->MimeType);
                $coords = !(empty($result->Coords)) ? \JsonDeEncoder::Decode($result->Coords) : null;
                if(isset($coords)) {
                    (new \Cimply_Cim_View\Cim_View_Images_Crop)->Crop($result->FilePath, $coords->width + 75, $coords->height + 75, $coords->x1+70, $coords->y1+70, $result->MimeType);
                }
                return $this;
            } else {
                $this->FileNotFound();
            }
        }

        public function Prologue() {
            return false;
        }
    }
}