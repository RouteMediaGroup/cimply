<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author Michael Eckebrecht
 */
namespace Cimply\App\Base {
    use \Cimply\System\System;
    use \Cimply\Core\Core;
    use \Cimply\Core\Validator\Validator;
    use \Cimply\Basics \{
        ServiceLocator\ServiceLocator,
        Repository\Support,
        Repository\Images\Crop,
        Repository\Images\Resize,
        Repository\Images\Convert
    };
    use \Cimply\Core\View \{
        View, Scope, Markup, Template\Enum\Pattern
    };
    use \Cimply\Core\Gui \{
        Gui, GuiFactory, Support\FieldTypeList
    };
    
    class FileCtrl {   
    
        private $fileParams, $fileName, $fileData, $fileSize, $params;
        public $data;
        protected $services;

        function __construct(ServiceLocator $services = null)
        {
            $this->services = $services;
        }

        public final function Cast($mainObject, $selfObject = self::class) : self
        {
            return Core::Cast($mainObject, $selfObject);
        }
        /**
         * 
         * @PageTitle External File
         * @UploadText Dokument vom Computer auswählen und hochladen,<br />oder dirket per drag&drop in das Feld hineinziehen.
         * 
         */
        static function Init($services)
        {
            return (new self($services))->execute();
        }

        private function execute() {
            if($this->Prologue()) {
                if($this->CalculateStorable()) {
                    $systemSettings = Support::Cast($this->services->getService())->getSystemSettings();
                    $currentObject = Scope::Cast(View::Cast($this->services->getService()));
                    $template = Gui::Cast($this->services->getService())->set($systemSettings, true);
                    $valid = Validator::Cast($currentObject)->addRules([
                        'size' => ['type'=>'string', 'required'=>true, 'min'=>1, 'max'=>9, 'trim'=>true],
                        'crop' => ['type'=>'string', 'required'=>true, 'min'=>1, 'max'=>9, 'trim'=>true]]
                    )->addSource($currentObject->get('params'));
                    
                    $this->fileParams = $valid->run()->sanitized;
                    $this->fileData = $template->preparing(View::Create($currentObject->getTarget()));
                }
                $this->Epilogue();
            }
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            if(!empty($this->fileParams['crop'])) {
                $this->Crop();
            }
            else if(!empty($this->fileParams['size'])) {
                $this->Resize();
            } else {
                $this->data = $this->fileData;
            }
            View::Render($this->data);
        }

        public function Prologue() {
            return true;
        }
        
        private function Resize() {
            $this->fileSize = explode('x', $this->fileParams['size']);
            if($this->fileSize[0] > $this->fileSize[1]) {
                $imageSize[0] = (int)($this->fileSize[0] / 100) * $this->fileSize[1];
                $imageSize[1] = (int)($this->fileSize[1] / 100) * $this->fileSize[0]; 
            } else {
                $imageSize[0] = $this->fileParams['size'];
                $imageSize[1] = 0; 
            }
              
            $this->data = new Resize(
                $this->fileName,
                $this->fileData,
                $imageSize[0], 
                $imageSize[1],
                $this->fileSize['mime']
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
            $this->data = new Crop(
                $this->fileData,
                $imageSize[0], 
                $imageSize[1],
                $startX, 
                $startY,
                $this->fileSize['mime']
            );
        }
    }
}