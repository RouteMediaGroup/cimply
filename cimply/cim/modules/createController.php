<?php
/**
 *
 * Copyright (c) 2012 - 2022 Cimply.Work
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Cimply.Work Cim
 * @package    Console
 * @copyright  Copyright (c) 2012 - 2018 Cimply.Work (http://www.cimply.work/v3)
 * @license    http://www.gnu.org/licenses/lgpl.txt  LGPL
 * @version    3.0.0, 2022-03-28
 */

/**
 * Description of
 * @author MikeCorner ModelCtrl
 *
 */

declare(strict_types=1);
namespace Cim\Modules {
    use \Cimply\Core\View\View;
    use \Cim\Modules\Model\ModulModel;
    use Cim\Modules\Model\ModelUpdate;
    class CreateController extends \Cimply\Service\Cli\Base
    {
        static protected $stepCounter = 1;
        static private $message = [];
        static private $validate;
        static private $errorMessage;
        /**
         *
         * @Author Michael Eckebrecht
         * @Step1 Enter the Namespace default(Cimply)
         * @Step2 Declare strict types? 1: yes | 2: no default(no)
         * @Step3 Enter the name of the class
         * @Step4 Set type of class-type 1: class | 2: trait | 3: final class default(class)
         * @Step5 Create a static class? 1: yes | 2: no default(no)
         * @Step6 Enter the name of extended class (skip with enter)
         * @Step7 Enter the name of implement interfaces (comma separated for many or skip with enter)
         * @Step8 Use constructor for initialisation? 1: yes | 2: no default(yes)
         * @Step9 Enter the name of default init-function default(init)
         * @Step10 Set type of function-mode 1: public | 2: private | 3: protect default(public)
         * @Step11 Set method as final function? 1: yes | 2: no default(no)
         * @Step12 Set method as static function? 1: yes | 2: no default(no)
         * @Step13 Set function typehints (skip with enter)
         * @Step14 Use dependent Usings? (use # to separate or skip with enter)
         * @Step15 Use Annotations? (use # to separate or skip with enter)
         * @Step16 Set Output Documenttype for example (xml, json, pdf, jpg, svg) default(html)
         *
         */
        final static function Init(): void {
            print "\n\r";
            self::$validate !== true ? print self::$errorMessage."\n\r": '';
            print View::ParseTplVars("[+Step".self::$stepCounter."+]").":";
            print "\n\r";
            self::$validate = true;
            self::$errorMessage = "";
            self::CalculateStorable(parent::GetMessage());
        }

        private static function CalculateStorable($selected) {
            switch (self::$stepCounter)
            {
                case '1':
                    (bool)strstr($selected, '(off)') === true ? $selected = 'Cimply' : null;
                    ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 2 ? self::SendErrorMsg("enter min. 3 characters") : self::Execute('namespace', \ucwords($selected, '-'));
                    break;
                case '2':
                    (bool)strstr($selected, '(off)') === true || $selected == '2' ? $selected = '0' : $selected = '1';
                    self::Execute('stricttypes', $selected);
                    break;
                case '3':
                    (bool)strstr($selected, '(off)') === true ? self::SendErrorMsg("no value - only letter and digits allow") : null;
                    ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 2 ? self::SendErrorMsg("Enter min. 3 characters") : self::Execute('cls_name', \ucwords($selected.' ', '-'));
                    break;
                case '4':
                    if((bool)strstr($selected, '(off)') !== true && ($selected == '2' || strstr($selected, 'trait'))) {
                        $selected = 'trait ';
                        self::$stepCounter = 6;
                    }
                    else if((bool)strstr($selected, '(off)') !== true && ($selected == '3' || strstr($selected, 'final class'))) {
                        $selected = 'final class ';
                    } else {
                        $selected = 'class ';
                    }
                    self::Execute('cls_type', $selected);
                    break;
                case '5':
                    (bool)strstr($selected, '(off)') !== true && ($selected == '2' || \strtolower($selected) == 'yes') ? $selected = 'static ' : $selected = '';
                    self::Execute('cls_declare', $selected);
                    break;
                case '6':
                    if((bool)strstr($selected, '(off)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                        ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                        strlen($selected) <= 2 && !empty($selected) ? self::SendErrorMsg("enter min. 3 characters") : $selected = 'extends '.($selected).' ';
                    }
                    self::Execute('extends', $selected);
                    break;
                case '7':
                    if((bool)strstr($selected, '(off)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : $selected = 'implements '.($selected);
                    }
                    (strlen($selected) <= 2 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : self::Execute('interfaces', $selected);
                    break;
                case '8':
                    (bool)(strstr($selected, '(off)')) === true || $selected == '2' ? $selected = '' : $selected = 'true';
                    self::Execute('constructor', $selected);
                    break;
                case '9':
                    (bool)(strstr($selected, '(off)')) === true ? $selected = 'init' : null;
                    ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 2 ? self::SendErrorMsg("enter min. 3 characters") : self::Execute('func_name', $selected);
                    break;
                case '10':
                    if((bool)(strstr($selected, '(off)')) !== true || $selected == '2' || \strtolower($selected) === 'private') {
                        $selected = 'private';
                    } else if((bool)(strstr($selected, '(off)')) !== true || $selected == '3' || \strtolower($selected) === 'protected') {
                        $selected = 'protected';
                    } else {
                        $selected = 'public';
                    }
                    self::Execute('func_type', $selected.' ');
                    break;
                case '11':
                    (bool)(strstr($selected, '(off)') === true || $selected == '2') ? $selected = '' : $selected = 'final';
                    self::Execute('func_final', $selected.' ');
                    break;
                case '12':
                    (bool)(strstr($selected, '(off)') === true || $selected == '2') ? $selected = '' : $selected = 'static';
                    self::Execute('func_declare', $selected.' ');
                    break;
                case '13':
                    if((bool)strstr($selected, '(off)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                        ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                        (strlen($selected) <= 2 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : $selected = ':'.$selected;
                    }
                    self::Execute('typehints', $selected);
                    break;
                case '14':
                    $usings = [];
                    if((bool)strstr($selected, '(off)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    }
                    ((strlen($selected) <= 3 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : !empty($selected)) ? $usings = \explode('#', $selected) : null;
                    self::Execute('usings', $usings);
                    break;
                case '15':
                    $annotations = [];
                    if((bool)strstr($selected, '(off)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    }
                    ((strlen($selected) <= 2 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : !empty($selected)) ? $annotations = \explode('#', $selected) : null;
                    self::Execute('annotations', $annotations);
                    break;
            	default:
                    self::Execute('complete', 'complete');
            }
        }

        private static function Execute($key, $value) {
            $key !== 'no' ? self::$stepCounter++ : null;
            if($value !== 'complete') {
                self::$message[$key] = $value;
                self::Init();
            } else {
                $result = View::ParseTplVars('
                    declare(strict_types=[+stricttypes+]);
                    namespace [+namespace+] {
                        [+usings+]
                        [+cls_declare+][+cls_type+][+cls_name+][+extends+][+interfaces+]
                        {
                            /**
                            *
                            *[+annotations+]
                            *
                            */

                            [+constructor+]

                            [+func_type+][+func_final+][+func_declare+]function [+func_name+]()[+typehints+] {
                                //content of function here
                            }
                        }
                    }->route("[+path+]", function() use ($app) {
                        $app->type = "[+app_type+]";
                        $app->action("[+action+]::[+func_name+]");
                        $app->validates([]);
                        $app->target = "~";
                        $app->caching = false;
                        return $app;
                    });', (array)self::PrepairData(self::$message));
                printf($result);
            }
        }

        private static function SendErrorMsg(string $message): void {
            self::$validate = false;
            self::$errorMessage = 'incorrect value! ('.$message.')';
            self::Init();
        }

        private static function PrepairData(array $data):?ModulModel  {
            $model = new ModulModel((object)$data);
            $model->attach(new ModelUpdate());
            return $model->getContent();
        }
    }
}