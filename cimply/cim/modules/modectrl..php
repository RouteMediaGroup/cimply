<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of
 *
 * @author MikeCorner ModelCtrl
 */

declare(strict_types=1);
namespace Cim\Modules {
    use \Cimply\App\Settings;
    use \Cimply\App\Repository\{Assets};
    use \Cimply\Basics\{
        ServiceLocator\ServiceLocator,
        Repository\Support
    };
    use \Cimply\Core\{
        Core,
        Database\Database,
        Database\Presenter,
        Database\Enum\FetchStyleList
    };

    use \Cimply\Core\Gui\{Gui, GuiFactory, Support\FieldTypeList};
    use \Cimply\Core\View\{View, Scope, Markup, Template\Enum\Pattern};

    class ModelCtrl extends \Cimply\Service\Cli\Base
    {
        protected static $services, $currentSelect = null;
        private static $mocker = null;
        function __construct($services = null) {
            self::$services = $services;
        }

        /**
         *
         * @Author Michael Eckebrecht
         * @Options 1: Clear Cache123 | 2: Update Model | ^ Return
         * @Execute "Please enter your Choice?"
         *
         */
        final static function Init($services = null, $menu = true): void {
            if($menu === false) {
                exec('.\execute.bat', $result);
                $select = strtolower(end($result));
                $services->mainMenu($select);
            }
            print "\n\r";
            print View::ParseTplVars("[+Description+]");
            print "\n\r";
            print "---------------------------------------------------------------";
            print "\n\r";
            print View::ParseTplVars("[+Options+]");
            print "\n\r";
            print "---------------------------------------------------------------";
            print "\n\r";
            print(View::ParseTplVars("[+Execute+]"). " ");
            self::Execute();
        }
        private static function Execute($args = null): void {
            exec('.\execute.bat', $result);
            $select = strtolower(end($result));
            isset($args[$select]) && !(empty($args[$select])) ? self::Debug((new \Cimply\Program())->app($args[$select])) : self::MainMenu($select);
        }

        private static function MainMenu($select = '1') {
            switch(self::$currentSelect ?? $select) {
                case 1:
                case 'create project':
                    self::Init();
                    break;
                case 2:
                case 'load project':
                    self::$currentSelect = 2;
                    $project = self::LoadProject(Settings::AppPath.'Projects');
                    print("Enter name or number: ");
                    self::Execute($project);
                    break;
                case 3:
                case 'help':
                    print(passthru("CHOICE /?"));
                    print "\n\r";
                    (new \App\WelcomeCtrl())->Init();
                    break;
                case 4:
                case 'exit':
                    die(passthru("PAUSE & EXIT"));
                    break;
                default:
                    print("invalid value - try again: ");
                    self::Execute();
            }
        }

        static function Debug($object) {
            self::$currentSelect = null;
            (new \App\WelcomeCtrl())->Init($object->ext(), false);
        }
    }
}