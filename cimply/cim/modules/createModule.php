<?php
/**
 *
 * Copyright (c) 2012 - 2018 Cimply.Work
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
 * @version    3.0.0, 2018-01-21
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
    use \Cim\Modules\Model\ModelUpdate;
    use \Cim\Modules\Templates\Markup;
    use \Cim\Modules\Templates\Routing;
    class CreateModule extends \Cimply\Service\Cli\Base
    {
        static protected $stepCounter = 1;
        static private $message = [];
        static private $validate;
        static private $errorMessage;
        /**
         *
         * @Author Michael Eckebrecht
         * @Step1 Enter name of modul (*)
         * @Step2 Declare strict types? 1: yes | 2: no default(no)
         * @Step3 Enter name of base class (*)
         * @Step4 Enter name of extended class (skip with enter)
         * @Step5 Enter name of implement interfaces (comma separated for many or skip with enter)
         * @Step6 Enter name of static init-function default(Init)
         * @Step7 Use dependent Usings? (use # to separate or skip with enter)
         * @Step8 Use Annotations? (use # to separate or skip with enter)
         * @Step9 Set Output Documenttype for example (xml, json, pdf, jpg, svg) default(html)
         *
         */
        final static function Init(): void {
            print "\n\r";
            self::$validate !== true ? print self::$errorMessage."\n\r": '';
            print View::ParseTplVars("[+Step".self::$stepCounter."+]").":";
            print "\n\r";
            self::$validate = true;
            self::$errorMessage = "";
            self::CalculateStorable(parent::GetMessage(false));
        }

        private static function CalculateStorable($selected) {
            switch (self::$stepCounter)
            {
                case '1':
                    (bool)strstr($selected, '(OFF)') === true ? $selected = '' : null;
                    ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 2 ? self::SendErrorMsg("enter min. 3 characters") : self::Execute('modulname', !empty($selected) ? \ucwords($selected, '-') : $selected);
                    break;
                case '2':
                    (bool)strstr($selected, '(OFF)') === true || $selected == '2' ? $selected = '0' : $selected = '1';
                    !ctype_digit(substr($selected, 0, 1)) && (\strlen($selected) === 1) ? self::SendErrorMsg("only option 1 or 2 allow") : null;
                    self::Execute('stricttypes', $selected);
                    break;
                case '3':
                    (bool)strstr($selected, '(OFF)') === true ? self::SendErrorMsg("no value - only letter and digits allow") : null;
                    ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 2 ? self::SendErrorMsg("Enter min. 3 characters") : self::Execute('cls_name', \ucwords($selected, '-'));
                    break;
                case '4':
                    if((bool)strstr($selected, '(OFF)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                        ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                        strlen($selected) <= 2 && !empty($selected) ? self::SendErrorMsg("enter min. 3 characters") : $selected = ' extends '.($selected);
                    }
                    self::Execute('extends', $selected);
                    break;
                case '5':
                    if((bool)strstr($selected, '(OFF)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : $selected = ' implements '.($selected);
                    }
                    (strlen($selected) <= 2 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : self::Execute('interfaces', $selected);
                    break;
                case '6':
                    (bool)strstr($selected, '(OFF)') === true ? $selected = 'Init' : null;
                    ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 2 ? self::SendErrorMsg("enter min. 3 characters") : self::Execute('init_func', $selected);
                    break;
                case '7':
                    $usings = [];
                    if((bool)strstr($selected, '(OFF)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    }
                    (strlen($selected) <= 3 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : !empty($selected) ? $usings = \explode('#', $selected) : null;
                    self::Execute('usings', $usings);
                    break;
                case '8':
                    $annotations = [];
                    if((bool)strstr($selected, '(OFF)') === true) {
                        $selected = "";
                    } else {
                        ctype_digit(substr($selected, 0, 1)) ? self::SendErrorMsg("the first char must be a letter") : null;
                    }
                    (strlen($selected) <= 2 && !empty($selected)) ? self::SendErrorMsg("enter min. 3 characters") : !empty($selected) ? $annotations = \explode('#', $selected) : null;
                    self::Execute('annotations', $annotations);
                    break;
                case '9':
                    (bool)strstr($selected, '(OFF)') === true ? $selected = 'html' : null;
                    ctype_alnum($selected) ? : self::SendErrorMsg("only letter and digits allow");
                    strlen($selected) <= 1 ? self::SendErrorMsg("enter min. 2 characters") : self::Execute('extention', $selected);
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
                self::$message['params'] = self::$message['annotations'];
                $data = (array)self::PrepairData(self::$message);
                $baseDir = ".\\app\\".\strtolower(View::ParseTplVars('[+modulname+]', $data));
                if(!\file_exists($baseDir)) {
                    \mkdir($baseDir);
                }
                $baseFile = \strtolower(View::ParseTplVars('[+cls_name+]', $data));
                $path = str_replace(" ", "", $baseDir."\\".$baseFile.".php");
                $markup = (new Markup)->render($data);
                $routing = (new Routing)->render($data);
                $markup = str_replace('**', '*', $markup);
                if(\file_put_contents('.\\routing.yml', $routing, FILE_APPEND)) {
                    \file_put_contents($path, "<?php\r".str_replace('@@', '@', $markup)); 
                    printf("complete.\r");
                } else {
                    printf("compile error.\r");    
                }
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