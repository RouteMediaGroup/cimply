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
    use \Cimply_Cim_Core\Cim_Core_Base_Scope as Scope;
    use \Cimply_Cim_Core_Entities\Cim_Core_Entities_FileStorageEntity as FileStorageEntity;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class TextImageCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $requires;
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
            $this->requires = (new Scope)->GetParams();
            return true;//(bool)$this->params;
        }

        public function Epilogue() {
            header('Content-Type: image/png');

            // Create the image
            $im = imagecreatetruecolor(102, 144);

            // Create some colors
            $white = imagecolorallocate($im, 255, 255, 255);
            $grey = imagecolorallocate($im, 210, 210, 210);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 102, 144, $white);
            imagefilledrectangle($im, 5, 3, 97, 131, $grey);

            // The text to draw
            $text = $this->requires['pages']+1;
            
            // Replace path by your own font path
            $font = str_replace('%Project%',Project,Common).'/fonts/1448933/ac4b95cb-49c4-493a-a895-471d763cea38.ttf';
            $count = strlen($text);

            // Add some shadow to the text
            //imagettftext($im, 20, 0, 11, 21, $grey, $font, $text);
            imagettftext($im, 30, 0, 53-($count*(8.5+$count)), 80, $white, $font, $text);

            // Add the text
            //imagettftext($im, 30, 0, 49-($count*(10+$count)), 85, $black, $font, $text);

            // Using imagepng() results in clearer text compared with imagejpeg()
            imagepng($im);
            imagedestroy($im);

        }

        public function Prologue() {
            return;
        }

    }
}