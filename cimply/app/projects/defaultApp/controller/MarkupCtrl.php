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
    use \Cimply_Cim_View\Cim_View_Markup as ViewMarkup;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    
    class MarkupCtrl implements IAssembly, IBasics {

        private $viewModel, $params;
        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params, $vm);
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
            return $this->setMarkup(System::GetItems('CurrentObject'));
        }

        public function Prologue() {
            
        }
        
        private function setMarkup($parameters = null) {
            if(isset($parameters['markupFile']) && isset($parameters['markup']) 
                ? $file = $parameters['markupFile'] 
                : null) 
            {
                $placeholder = key($parameters['markup']);
                $elements = isset($parameters['markup'][$placeholder]) && is_array($parameters['markup'][$placeholder]) ? \JsonDeEncoder::Decode('['.\ArrayParser::ArrayToString($parameters['markup'], $placeholder, ',', false).']', true) : \JsonDeEncoder::Decode('['.\ArrayParser::ToStringImplode($parameters['markup'], ',').']', true);
                (new ViewMarkup(System::GetItems('Project','Path').'/markup/'.$file, null, null, $placeholder))
                    ->BuildHTMLFromContext(
                        '*', $elements, array()
                    );
            }
        }
    }
}