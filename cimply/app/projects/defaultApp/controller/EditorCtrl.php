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
    use \Cimply_Cim_Core\Cim_Logger_Exception as LoggerException;
    use \Cimply_Cim_Core\Cim_Logger as Logger;
    use \Cimply_Cim_View\Cim_View as View;
    use \Cimply_Cim_View\Cim_ViewTemplate as Template;
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;
  
    class EditorCtrl extends View implements IAssembly, IBasics {

        protected $hashCookie = "PHPSESSID";
        private $tplPattern, $views, $params, $require, $inhaltsverzeichnis = [], $inhalt = "";
        public $viewModel, $templateEngine, $module = array(), $webproject = array(), $sites = null, $contents = null;

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array(
                'Inhalt' => new ViewPresenter('Inhalt')
            )));
        }

        public function Reference() {
            System::InjectController(View . '/Collections/smarty-3.1.30/libs/Smarty.class.php');
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            parent::OverrideCurrentObject(array("type" => "tpl"));
            Template::$fileType = 'html';
            System::Setter('Pattern', $this->tplPattern);
            if ($this->CalculateStorable()) {
                if ($this->Prologue()) {
                    $inhaltCollection = ViewPresenter::Cast(ViewModel::Cast($this->viewModel->GetContext('Inhalt'))->Collection());
                    $data = $inhaltCollection->SelectBy('AngebotId = ' . $this->require['AngebotId'])->OrderBy('Position')->Refresh(true)->Execute('data');
                    $lastId = 0;
                    foreach ($data as $key => $value) {
                        $this->inhaltsverzeichnis[$key] = $value['Title'];
                        $this->inhalt.= $lastId != $value['InhaltId'] ? '<data cnt-id="'.$value['InhaltId'].'">'.($value['Content']).'</data>' : ($value['Content']);//$this->setAttributes($value['Text'], array('data-id' => $value['Bausteinid']), ['body', 'head', 'html', 'link', 'script']);
                        $lastId = $value['InhaltId'];
                    }
                }
                $this->Epilogue();
            }
        }

        public function Prologue() {
            if (System::IsReady()) {               
                $this->require = self::GetParams();
                if (!(isset($this->require['AngebotId']))) {
                    return false;
                }
                if (!($this->tplPattern = Config::GetConf('Tpl/pattern'))) {
                    return false;
                }
            }
            return true;
        }

        public function CalculateStorable() {
            return true;
        }

        public function Epilogue() {
            //\Debug::VarDump($this, false);
            $this->setTemplateEngine(new \Smarty(), System::GetItems('Project'), System::GetItems('CurrentObject'), Config::GetConf('DevMode'));
            return $this;
        }

        /**
         * 
         * @param type $system
         * @param type $debug
         * 
         */
        private function setTemplateEngine(\Smarty $templateEngine = null, $system = null, $params = null, $debug = false) {
            try {
                if (\get_class($templateEngine) == 'Smarty') {
                    $this->templateEngine = $templateEngine;
                    $this->templateEngine->debugging = $debug;
                    $this->templateEngine->caching = false;
                    $this->templateEngine->cache_lifetime = 60000;

                    $this->templateEngine->auto_literal = false;

                    $this->templateEngine->left_delimiter = '[{';
                    $this->templateEngine->right_delimiter = '}]';

                    $this->templateEngine->setPluginsDir('./plugins');
                    $this->templateEngine->setCompileDir($system['Path'] . '/compile')->setCacheDir($system['Cache'])->setTemplateDir(array(
                        'root' => $system['Path'] . '/site',
                        'base' => $system['Path'] . '/site/base',
                        'editor' => $system['Path'] . '/site/base/editor'
                    ));

                    #Main Template                    

                    $this->templateEngine->assign('Inhaltsverzeichnis', $this->inhaltsverzeichnis);
                    $this->templateEngine->assign('Inhalt', $this->inhalt);

                    Template::Show(View::GetLibs($this->templateEngine->fetch($this->templateEngine->getTemplateDir('editor') . 'index.tpl')));

                    isset($this->views) ? $this->templateEngine->assign('View', $this->views) : null;
                    View::SetTemplateArgs(array('Template' => View::ParseVars($this->templateEngine->fetch($this->templateEngine->getTemplateDir($params['theme']) . $this->require['webs'] . '/home.tpl'))));
                    $this->module = isset($params['tpls']) ? $params['tpls'] : null;
                    if (count($this->module) >= 1) {
                        foreach ($this->module as $key => $value) {
                            View::SetTemplateArgs(array($key => View::ParseVars($this->templateEngine->fetch($this->templateEngine->getTemplateDir($params['theme']) . $value))));
                        }
                    }
                    return $this;
                } else {
                    throw new \Exception();
                }
            } catch (LoggerException $ex) {
                Logger::Log($ex->getMessage(), 'ERROR', true);
            }
        }

        private function setAttributes($html, $attrs = array(), $ignored_tags = array()) {
            $dom = new \DOMDocument;
            $dom->loadXML($html);
            foreach ($dom->getElementsByTagName('*') as $tag) {
                // If not a disallowed tag
                if (!in_array($tag->tagName, $ignored_tags)) {
                    $textContent = trim($tag->textContent);
                    // If $textContent matches the format '{foo:bar}'
                    if (preg_match('#{\s*[^>]*:\s*[^>]*\s*[^}]}#', $textContent)) {
                        foreach ($attrs as $attr => $val) {
                            $tag->setAttribute($attr, $val);
                        }
                    }
                }
            }
            return $dom->saveHTML();
        }

    }

}