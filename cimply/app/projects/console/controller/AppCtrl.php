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
    use \Cimply_Cim_View\Cim_ViewModel as ViewModel;
    use \Cimply_Cim_View\Cim_View_Markup as ViewMarkup;
    use \Cimply_Cim_Core\Cim_ViewPresenter as ViewPresenter;
    use \Cimply_Cim_Core\Cim_Core_Base_Request as Request;
    use \Cimply_Cim_Core\Cim_Core_Base_Validation as Validator;
    use \Cimply_Cim_Interfaces\Cim_IBasics as IBasics;

    class AppCtrl implements IAssembly, IBasics {

        private $viewModel, $tplPattern, $views, $markupFile = null, $params, $schema = null, $context, $require;
        public $templateEngine, $Bereich, $bereiche = array(), $module = array();

        public function __construct($params = null, $vm = null) {
            $this->Reference();
            $this->Init($params ? $params : new Request(), $vm ? $vm : new ViewModel(null, array()));
        }

        public function Reference() {
            View::SetTemplateArgs(array('Vorname' => System::GetSession('Vorname')));
            View::SetTemplateArgs(array('Nachname' => System::GetSession('Nachname')));
            View::SetTemplateArgs(array('Jahr' => date('Y')));
            System::InjectController('MarkupCtrl');
            System::InjectController('Controller');
            System::InjectController(View . '/Collections/smarty-3.1.30/libs/Smarty.class.php');
        }

        public function Init($params = null, $vm = null) {
            $this->params = $params;
            $this->viewModel = $vm;
            if ($this->Prologue()) {
                if ($this->CalculateStorable()) {
                    if(is_array($this->context)) {
                        /* @var $key type */
                        foreach($this->context as $key => $value) {
                            $this->CreateForm(new ViewMarkup(System::GetItems('Project','Path').'/markup/'.(isset($this->markupFile) ? $this->markupFile : 'formfields.xhtml')), $value, $key);
                        }
                    }
                }                
                $this->Epilogue();
            }
        }

        public function Prologue() {
            if (System::IsReady()) {
                $this->markupFile = System::GetItems('CurrentObject','markupFile', true);
                $this->schema = System::GetItems('CurrentObject','schema', true);
                if (!($this->tplPattern = Config::GetConf('Tpl/pattern'))) {
                    return false;
                }
                $this->require = Validator::Cast(System::GetItems('Project', 'Requires'))->sanitized;
                isset($this->require['design']) ? Template::SetVar('design', $this->require['design']) : null;
                isset($this->require['webs']) ?  View::SetTemplateArgs(array('webs' => $this->require['webs'])) : null;
                if(is_array($this->schema)) {
                    foreach($this->schema as $key => $value) {
                        $this->context[$key] = Config::GetConf('Mapper/entities/'.$value);
                    }
                }
                new MarkupCtrl($this->params, $this->viewModel);
            }
            return (bool)true;
        }

        public function CalculateStorable() {
            $this->schema = isset($this->schema) ? $this->schema : (isset($this->require['Schema']) ? $this->require['Schema'] : $this->schema);
            return (bool)$this->schema;
        }

        public function Epilogue() {
            //\Debug::VarDump((\AnnotationHelper::process(Controller::class, '/')));

            //$this->TemplateEngine(new \Smarty(), System::GetItems('Project'), System::GetItems('CurrentObject'), Config::GetConf('DevMode'));
            return $this;
        }

        /**
         * 
         * @param ViewMarkup $viewForm
         * @param type $context
         * 
         */
        private function CreateForm(ViewMarkup $viewForm, $context = array(), $key = null) {
            $this->views[$key] = $viewForm->BuildHTMLFromContext('div', $context, array("class" => "row"));
        }

        /**
         * 
         * @param type $system
         * @param type $debug
         * 
         */
        private function TemplateEngine(\Smarty $templateEngine = null, $system = null, $params = null, $debug = false) {
            try {
                if(\get_class($templateEngine) == 'Smarty') {
                    $this->templateEngine = $templateEngine;
                    $this->templateEngine->debugging = $debug;
                    $this->templateEngine->caching = false;
                    $this->templateEngine->cache_lifetime = 10;

                    $this->templateEngine->auto_literal = false;

                    $this->templateEngine->left_delimiter = '[{';
                    $this->templateEngine->right_delimiter = '}]';

                    $this->templateEngine->setPluginsDir('./plugins');
                    $this->templateEngine->setCompileDir($system['Path'] . '/compile')->setCacheDir($system['Cache'])->setTemplateDir(array(
                        'root' => $system['Path'] . '/site',
                        'base' => $system['Path'] . '/site/base',
                        'cleaning' => $system['Path'] . '/site/cleaning',
                        'clinic-service' => $system['Path'] . '/site/clinic',
                        'catering' => $system['Path'] . '/site/catering',
                        'security' => $system['Path'] . '/site/security',
                        'airport-service' => $system['Path'] . '/site/airport',
                    ));
                    isset($this->views) ? $this->templateEngine->assign('View', $this->views) : null;
                    
                    if(\JsonDeEncoder::IsJson(System::GetSession('Rules')) ? $rules = \ArrayParser::FlattenArray(\JsonDeEncoder::Decode(System::GetSession('Rules')), null, 0) : false) {
                        foreach ((new ViewPresenter('KatalogEintrag'))->SelectBy('Katalog => "Bereich"')->Select('Name')->Execute('data') as $key => $value) {
                            $this->bereiche[] = $value['Name'];
                        }
                    }
                    $this->templateEngine->assign('Bereiche', 
                        isset($rules) ? (
                            ($rules->Bereich == '*') ? 
                                $this->bereiche 
                                : explode(',', $rules->Bereich)
                            ) 
                        : array()
                    );
                    View::SetTemplateArgs(array('Bereiche' => isset($params['theme']) ? View::ParseVars($this->templateEngine->fetch($this->templateEngine->getTemplateDir($params['theme']) . 'bereiche.tpl')) : ""));
                    isset($this->require['webs']) ? View::SetTemplateArgs(array('Template' => View::ParseVars($this->templateEngine->fetch($this->templateEngine->getTemplateDir($params['theme']) . $this->require['webs'] . '/home.tpl')))) : null;
                    $this->module = isset($params['tpls']) ? $params['tpls'] : array();
                    if(!empty($this->module) && count($this->module) >= 1) {
                        foreach($this->module as $key => $value) {
                            View::SetTemplateArgs(array($key => View::ParseVars($this->templateEngine->fetch($this->templateEngine->getTemplateDir($params['theme']) . $value))));
                        }
                    }
                    return $this;
                    
                } else {
                    throw new \Exception();
                }
            } catch (\ErrorException $ex) {
                Logger::Log($ex->getMessage(), 'ERROR', true);
            }
        }
    }
}