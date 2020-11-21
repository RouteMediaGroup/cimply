<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply_Cim_View {
    
    //Usings
    use \Cimply_Cim_System\Cim_System as System;
    use \Cimply_Cim_System\Cim_System_Config as Config;
    
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;

    use \Cimply_Cim_View\Cim_View as View;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_View\Cim_ViewData as ViewData;
    
    use \Cimply_Cim_Core\Cim_Core_Invoke_EntityManager as EntityManager;
    use \Cimply_Cim_Interfaces\Cim_ICast as ICast;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
    use \Cimply_Cim_Interfaces\Cim_IViewModel as IViewModel;

    /**
     * Description of Cim_ViewModel
     *
     * @author MikeCorner
     */
    class Cim_ViewModel extends ViewData implements ICast, IBasics, IViewModel {
        
        private $viewPresenter = array(), $collection = array();
        
        public function __construct($dbName = null, $viewPresenter = null) {
            parent::__construct();
            if($this->Prologue()) {
                $this->SetContext($viewPresenter);
                if($this->CalculateStorable()) {
                    //EntityManager::SetMysqliConnect($params['dbName'], System::GetItems('DB'));
                    $dbName ? null : $dbName = System::GetItems('DB', 'Default');
                    $connection = EntityManager::MysqliConnect();
                    parent::Init(isset($connection->thread_id) ? $connection : new EntityManager($dbName, System::GetItems('DB')));
                    $this->Epilogue();
                }
            }
        }
        
        public static function Cast($object = null) {
            if(!isset($object)) {
                return new self();
            }
            return \Classes::Supplements(new self, $object);
        }

        public function CalculateStorable() {
            return (bool)System::IsReady();
        }

        public function Epilogue() {
            return EntityManager::$dbConnect;
        }

        public function Prologue() {
            System::Setter('DB', array(
                'Default' => Config::GetConf('System/database'),
                'Database' => Config::GetConf('DB'),
                'Mapper' => Config::GetConf('Mapper')
            ));
            return true;
        }

        public function Reference() {
            
        }

        public function SetContext($viewPresenter = null) {
            $viewPresenter ? $this->viewPresenter = $viewPresenter : null;
            return $this;
        }
        
        public function GetContext($viewPresenter = null) {
            isset($viewPresenter) ? $this->ViewPresenter($viewPresenter, $this->viewPresenter[$viewPresenter]) : new ViewPresenter();
            return $this;
        }

        public function Collection($value = null) {
            isset($value) ? $this->collection = $value : null;
            return $this->collection;
        }
        
        public function Reset($viewPresenter = null) {
            if(isset($this->viewPresenter[$viewPresenter])) {
                $obj = $this->collection[$viewPresenter];
                unset($this->collection[$viewPresenter]);
                $this->ViewPresenter($viewPresenter, new ViewPresenter($viewPresenter, self::Cast($obj)->entitie)); 
            }
            return $this;
        }
        
        private function ViewPresenter($name = null, ViewPresenter $value) {
            $this->collection = array_merge(array($name => $value), $this->collection);
            return $this;
        }
                
        public function GetMetaInformation($path = null) {
            if (!headers_sent()) {
                header('Content-Type: '.  \Mime::GetMime($path).'');
            }
            $pathInfo = pathinfo($path);
            $result = '';
            $binding = array('Model' => System::GetUnique('Model'));
            $newContext = explode('/', $pathInfo['dirname']);
            if((end($newContext) == 'Context') && (isset($binding))) {
                foreach($bind as $key => $value) {
                    if(isset($value) && ($value != "")) {
                        $result.= 'var '.$key.' = '.System::Callback($value, 'localstorage').';';
                    }
                }
                Template::Show($result);
            } else {
                Template::Show('File not found');
            }
        }
        
        public static function DataBinding($path = null) {
            if (!headers_sent()) {
                header('Content-Type: '.\Mime::GetMime($path).'');
            }
            $pathInfo = pathinfo($path);
            $result = '';
            //$binding = array('Model' => System::GetUnique('Model'));
            $binding = System::GetUnique('CurrentItem');
            $newContext = explode('/', $pathInfo['dirname']);
            if((end($newContext) == 'Model') && (isset($binding))) {
                Template::Show(View::ParseTemplate(System::Callback($binding, '_json')));
            } else {
                if(@is_file($newFile = Common.'/'.System::GetItems('Project','FilePath'))) {
                    Template::Show(file_get_contents($newFile));
                }
                else if(@is_file($newFile = View.'/'.System::GetItems('Project','FilePath'))) {
                    Template::Show(file_get_contents($newFile));
                }
            }
        }

        public function __destruct() {
            System::SetSession('CurrentItem', System::GetItems('CurrentItem'));
        }
    }   
}