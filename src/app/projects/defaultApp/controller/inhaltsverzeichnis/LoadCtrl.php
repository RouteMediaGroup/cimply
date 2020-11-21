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
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class LoadCtrl implements IAssembly, IBasics {

        private $viewModel, $params, $result; 

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Inhalt' => new ViewPresenter('Inhalt')
            )));
        }

        public function Reference() {
            
        }

        public function Init($params = null, $vm = null) {
            $this->params = \JsonDeEncoder::Decode(Request::Cast($params)->GetRequest(), true);
            $this->viewModel = $vm;
            if($this->CalculateStorable()) {
                if($this->Prologue()) {
                    
                }
                $this->Epilogue();
            }
        }

        public function Prologue() {
            //ToDo: SelectAll ändern in SelectBy -> Rollen und Bereiche
            $bausteineCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Inhalt'))->Collection());
            if(isset($this->params['AngebotId'])) {
                $result = $bausteineCollection->SelectBy('ParentId IS NULL')->ChainOR('ParentId = 0')->ChainAnd('AngebotId ='.$this->params['AngebotId'])->ChainAnd('Status >= "1"')->OrderBy('Position')->Refresh(1)->Execute('data');
                foreach ($result as $value) {
                    $node = array('Nodes' => (new ViewPresenter('Inhalt'))->Select('*')->SelectBy('ParentId = '.$value['BausteinId'])->ChainAnd('AngebotId ='.$this->params['AngebotId'])->ChainAnd('Status >= "1"')->FieldVirtualAs('Nodes', '""')->OrderBy('Position')->Refresh(1)->Execute('data'));
                    $this->result[] = array_merge($value, $node);
                }
            }
            return !(empty($this->result)) ? true : false;
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            Template::Show(System::Callback($this->result, '_json'));
        }
    }
}