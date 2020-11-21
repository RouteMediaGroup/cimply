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
    use \Cimply_Cim_View\Cim_View as View;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class FileCtrl implements IAssembly, IBasics {   
        
        private $fileParams, $fileName, $fileData, $fileSize, $params, $require;
        public $data;
         
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params, $vm);
        }
        
        public function Reference() {
            System::InjectController('PageCtrl');
            System::ReadDirectory(View.'/Modules/Images');
        }
        
        public function Init($params = null, $vm = null) {
            $this->params = isset($params) && count($params) > 0 ? $params : System::GetItems('Project', 'Requires');
            if($this->Prologue()) {
                if($this->CalculateStorable()) {
                    if(Config::GetConf('System/parsingImageFiles', View::GetFileType())) {
                        $this->fileParams = Validator::Cast($this->params)
                            ->addRule('size', 'string', true, 1, 255, true)
                            ->addRule('crop', 'string', true, 1, 255, true)
                            ->run()->sanitized;
                        
                        $this->fileName = str_replace('_', '/', View::GetFileBaseName());
                        $this->fileData = is_file(View::GetBasePath().$this->fileName) ? View::GetBasePath().$this->fileName : View::GetFileBasePath().$this->fileName;
                        $this->fileSize = is_file($this->fileData) ? getimagesize($this->fileData) : System::Callback(Template::Show('no file.'), 'json');
                    }
                }
                
                $this->Epilogue();
            }
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            if(View::GetFileType() == 'html') {
                (new PageCtrl())->Init($this);
            } else {
                if(isset($this->fileParams['crop'])) {
                    $this->Crop();
                }
                elseif(isset($this->fileParams['size'])) {
                    $this->Resize();
                }
            }
        }

        public function Prologue() {
            $this->require = Validator::Cast($this->params)
                            ->addRule('editmode', 'string', true, 1, 255, true)
                            ->addRule('site', 'string', true, 1, 255, true)
                            ->addRule('userkey', 'string', true, 1, 255, true)
                            ->run()->sanitized;
            return System::IsReady();
        }
        
        private function Resize() {
            $imageSize = explode('x', $this->fileParams['size']);
            if($this->fileSize[0] > $this->fileSize[1]) {
                $imageSize[0] = ($this->fileSize[0] / 100) * $this->fileParams['size'];
                $imageSize[1] = ($this->fileSize[1] / 100) * $this->fileParams['size']; 
            } else {
                $imageSize[0] = $this->fileParams['size'];
                $imageSize[1] = 0; 
            }            
            $this->data = System::Invoke (
                'ImageCrop', 
                'Cimply_Cim_View\Cim_View_Images_Resize',
                'Resize', 
                array(
                    'Name' => $this->fileName,
                    'FilePath' => $this->fileData,
                    'ImageWidth' => $imageSize[0], 
                    'ImageHeight' => $imageSize[1],
                    'MimeType' => $this->fileSize['mime']
                )
            );
        }
        
        private function Crop() {
            $imageCrop = explode('x', $this->fileParams['crop']);
            $imageSize = explode('x', $this->fileParams['size']);
            
            $startX = $imageCrop[0];
            $startY = $imageCrop[1];

            if(!(isset($imageSize[1]))) {
                $imageSize = explode('x', $this->context->message->FileFormat);
                if($this->fileSize[0] > $this->fileSize[1]) {
                    $imageSize[0] = ($imageSize[0] / 100) * $this->fileParams['size'];
                    $imageSize[1] = ($imageSize[1] / 100) * $this->fileParams['size']; 
                } else {
                    $imageSize[0] = $this->fileParams['size'];
                    $imageSize[1] = 0; 
                } 
            }
            $this->data = System::Invoke (
                'ImageCrop', 
                'Cimply_Cim_View\Cim_View_Images_Crop',
                'Crop', 
                array(
                    'FilePath' => $this->fileData,
                    'ImageWidth' => $imageSize[0], 
                    'ImageHeight' => $imageSize[1],
                    'StartX' => $startX, 
                    'StartY' => $startY,
                    'MimeType' => $this->fileSize['mime']
                )
            );
        }
    }
}