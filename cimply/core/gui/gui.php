<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply\Core\Gui {

    use \Cimply\System\System;
    use \Cimply\System\Config;
    use \Cimply\Core\{View\View, View\Scope, Annotation\Annotation as Annotate, Document\Dom};
    use \Cimply\Interfaces\Support\Enum\{SystemSettings, PatternSettings, AppSettings, ScopeSettings};
    class Gui {

        use \Properties, \Cast;

        protected $params = null, $Pattern = [], $Collections = [], $view = null, $useTemplateFor;
        private static $viewModel, $currentObject = array(), $annotations;

        function __construct(View $view = null) {
            $this->view = $view;
        }

        static final function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        /*private static function fetch() {
            $includeFlag = false;
            $templateFile = View::GetTemplatePath(View::$Target ?? isset(self::$annotations['target']) ? : die());
            if ($templateFile == '~') {
                return View::$vars;
            } else {
                if (System::GetItems('CurrentObject', 'include') == 'true') {
                    $includeFlag = false;
                }
                View::$view = self::GetLibs(self::PreparingView($templateFile), $includeFlag);
            }
            View::SetTemplateArgs(self::$annotations['args'] ?? null);
            View::Display();
        }*/

        /**
         *
         * @param type $tpl
         * @param type $unparse
         * @return type
         *
         */
        /*
        public static function PreparingView($tpl = null, $unparse = false) {
            //Variablen Parsen
            if ($unparse == false) {
                View::$args = self::ParseParameter(View::$Params, $act = true);
            }

            //Templates Parsen
            $templateFile = isset($tpl) ? $tpl : System::GetItems('Project', 'CurrentFile');
            $tmpFile = self::parseFile($templateFile);
            if (View::$Module) {
                foreach (View::$Module as $modul) {
                    require_once(Modules . '/' . $modul);
                }
            }
            if (isset($routing['redirect']['native'])) {
                #callback zb. registrierung / db speicherung
                System::SetSession('currentRouting', $routing['redirect']['url']);
                header('location: ' . $routing['redirect']['url']);
                exit;
            }

            if (is_file($templateFile)) {
                $tmpFile = Translate::Translation($tmpFile);
            }

            return self::preparing($tmpFile);
        }
        */

        /**
         *
         * @param type $templateFile
         * @return type
         *
         */
        private static function parseFile($templateFile = null) {
            $externalFile = "";
            if (is_file($templateFile = self::ExternalFile($templateFile)) ? $templateFile : (is_file($templateFile = str_replace('_', '/', $templateFile)) ? $templateFile : false)) {
                $externalFile = \file_get_contents($templateFile);
            } else {
                if (@is_file($templateFile = View . '/' . ucfirst(System::GetItems('Project', 'FilePath')))) {
                    $externalFile = \file_get_contents($templateFile);
                } else {
                    //Memory Storage Solution
                    $externalFile = null; //self::$viewModel->DataBinding($templateFile);
                }
            }
            return $externalFile;
        }

        /**
         *
         * @param type $templateFile
         * @return type
         *
         */
        private static function ExternalFile($templateFile = null) {
            if (Scope::GetCurrentObject('external') && (!(is_file($templateFile)))) {
                $tmp = explode("_", Scope::GetFileBaseName());
                $fileName = array_pop($tmp);
                $files = array();
                $addValue = "";
                foreach ($tmp as $key => $value) {
                    $addValue.= $value . '/';
                    is_file(Scope::GetBasePath() . $addValue . $fileName) ? $files[] = Scope::GetBasePath() . $addValue . $fileName : null;
                }
                return end($files);
            } else {
                return $templateFile;
            }
        }

        /**
         *
         * @param type $template
         * @return type
         *
         */
        public function preparing($template = null) : ?String {
            $params = array();
            $model = array();
            $getAttr = array();
            $getParams = array();
            if($templating = Scope::Cast($this->view)->getTemplating()) {
                foreach ($templating as $key => $value) {
                    $tplAttr = View::ParseTplAttr($key);
                    $attr = end($tplAttr);
                    $attrName = array_keys($attr)[0];
                    $attrValue = $attr[$attrName];
                    $tagName = key($tplAttr);

                    $globalParams = \JsonDeEncoder::Decode(Scope::Cast(View::GetStaticProperty('scope'))->getParams(), true) ?? [];
                    //$tpl = is_array($templating[$key]['tpl']) ?? null ? array_merge($templating[$key]['tpl'] ?? [], $value) : [];
                    $getAttr = isset($value['attr']) ? ["attr" => \JsonDeEncoder::Decode($value['attr'], true)] : [];
                    $getParams = isset($value['params']) ? \JsonDeEncoder::Decode($value['params'], true) : [];
                    isset($value['model']) ? $getModel = self::$viewModel->GetDataModel($value['model']) : null;
                    $params = \array_merge($globalParams, $getAttr, $getParams);
                    $template = Dom::ReplaceContentByView(
                        $this->getLibs($template, false), $tagName, $attrName, $attrValue, View::ParseTplVars(View::ParseTplView($this->newTemplate($value['tpl'], true)), $params), $params, $model
                    );
                }
            }
            return $this->getLibs($template);
        }

        /**
         *
         * @param type $tplPath
         * @param type $target
         * @return type
         *
         */
        public function newTemplate($tplPath = null, $target = true) {
            $path = View::GetStaticProperty(AppSettings::PROJECTPATH).'\\'.\str_replace('_','\\', $tplPath);
            $parsingTpl = str_replace('&gt;', '>', $tplPath);
            if ($target) {
                if ($view = View::Create($parsingTpl)) {
                    $tpl = $view;// preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $view);
                } else {
                    $tpl = $this->newTemplate(\file_get_contents($path), false);
                }
            } else {
                $tplFile = array();
                $matches = array();
                $tpl = null;
                preg_match_all(View::GetStaticProperty(PatternSettings::MODUL), (string)$parsingTpl, $matches);
                ob_start();
                foreach ($matches[1] as $key => $value) {
                    $tplFile[$key] = str_replace('_', '/', View::GetTemplatePath($value, $matches[0][$key]));
                    print(str_replace($matches[0][$key], \file_get_contents($filePath), $tpl));
                    $tpl = $this->newTemplate(ob_get_contents(), false);
                }
                ob_end_clean();
            }
            return $tpl;
        }

        /**
         *
         * @param type $params
         * @param type $act
         * @param type $once
         * @return type
         *
         */
        public static function ParseParameter($params, $act = false, $once = false) {
            $paramsData = (isset($params) && $params != "") ? $params : array();
            if (!(is_array($paramsData))) {
                $paramsData = \JsonDeEncoder::Decode($paramsData, $act);
            }
            if ($once) {
                return isset($paramsData[$once]) ? $paramsData[$once] : array();
            }
            return array_merge($paramsData ?? array(), View::$vars ?? array());
        }

        public function getLibs($s, $include = false) {
            $search = array();
            $library = array();
            $directory = array();
            $libsPattern = View::GetStaticProperty(PatternSettings::LIBS) ?? NULL;
            $scope = Scope::Cast($this->view);
            if(isset($libsPattern) && in_array($scope->getType(), is_array($this->useTemplateFor) ? $this->useTemplateFor : [])) {
                preg_match_all($libsPattern, (string) $s, $matches);
                if (!(empty($matches[1]))) {
                    $search = $matches[0];
                    $library = str_replace('/',':', $matches[1]);
                    $directory = $matches[2];
                    foreach ($search as $key => $value) {
                        if ($include === true) {
                            $s = str_replace($search[$key], View::Render(View . '/' . $library[$key] . '/' . str_replace('%transaction%', time(), $directory[$key])), $s);
                        } else {
                            if ($confParam = View::GetStaticProperty($library[$key]) ?? null) {
                                is_object($confParam) ? : $library[$key] = $confParam;
                            }
                            if ($library[$key] === "@") {
                                $s = $this->ForeachContent($directory, $key, $search, $s, true);
                            }
                            else if ($library[$key] === "Include") {
                                $s = $this->ForeachContent($directory, $key, $search, $s);
                            } else {
                                $checkFirstDir = explode('/', $matches[1][$key]);
                                $path = ($_SERVER['REQUEST_SCHEME'] ?? 'http') .'://'.$_SERVER['HTTP_HOST'].'/'.\Conditions::IfThanElse($checkFirstDir[0] != $library[$key], $library[$key], $checkFirstDir[0].'/'.$library[$key]);
                                $s = str_replace($search[$key], $path. '/' . str_replace('%transaction%', time(), $directory[$key]), $s);
                            }
                        }
                    }
                }
            }
            return $s;
        }

        public function foreachContent($input = "", $key = "", $search = "", $output = "", $import = false) {
            $dom = new Dom();
            $produceString = "";
            $explodeString = explode("|", $input[$key]);
            $lastExplodeString = end($explodeString) != $explodeString[1] ? end($explodeString) : '';
            $param = explode('=', $explodeString[1]);
            $path = explode("*", $param[1]);
            $excepted = explode(",", $lastExplodeString);
            $projectPath = View::GetStaticProperty(AppSettings::PROJECTPATH).DIRECTORY_SEPARATOR;
            if ($files = \glob($projectPath.$param[1], GLOB_BRACE)) {
                foreach ($files as $file) {
                    $fileInfos = pathinfo($file);
                    $currentFileName = $path[0] . $fileInfos['basename'];
                    if (($excepted[0] == "*") || $excepted[0] == "") {
                        $newArray[] = 'http://'.$_SERVER['SERVER_NAME'].'/'.$currentFileName;
                    } else if (in_array($fileInfos["filename"], $excepted)) {
                        $newKey = (array_keys($excepted, $fileInfos["filename"]));
                        $newArray[$newKey[0]] = 'http://'.$_SERVER['SERVER_NAME'].'/'.$currentFileName;
                    }
$produceString.=<<<Inhalt
@{$explodeString[0]} {$param[0]}("{$currentFileName}"){$lastExplodeString};

Inhalt;
                }
                isset($newArray) && is_array($newArray) ? ksort($newArray) : $newArray = array();
                foreach ($newArray as $fileName) {
                    $newnode = $dom->appendChild($dom->createElement($explodeString[0]));
                    $newnode->setAttribute($param[0], $fileName);
                }
            }
            if($import) {
                return str_replace($search[$key], $produceString, $output);
            } else {
                $dom->preserveWhiteSpace = true;
                $dom->formatOutput = true;
                return str_replace($search[$key], $dom->saveHTML(), $output);
            }
        }
    }
}
