<?php
/**
 * Description of
 *
 * @author MikeCorner BaseCtrl
 */

declare(strict_types=1);
namespace App {
    use \Cimply\Core\View\{View};
    use \Cimply\App\Settings;
    class MainCtrl extends \Cimply\Service\Cli\Base
    {
        protected static $app, $currentSelect = null;
        /**
         *
         * @Author Michael Eckebrecht
         * @Menu 1: Create Module | 2: Create Controller | 3: Create Entity 
         * @Options1 4: Init Project | 5: Build Project | 6: Update Project
         * @Options2 7: Settings | 8: Clear Cache | 9: Help | 10: Exit
         * @Execute "Please enter your Choice?"
         *
         */
        final static function Init($app = null, $menu = true): void {
            if($menu) {
                print "\n\r";
                print View::GetVar("Title");
                print "\n\r";
                print "--------------------------------------------------------------------------------";
                print "\n\r";
                print View::GetVar("Menu");
                print "\n\r";
                print View::GetVar("Options1");
                print "\n\r";
                print View::GetVar("Options2");
                print "\n\r";
                print "--------------------------------------------------------------------------------";
                print "\n\r";
                print(self::GetSession('Project') != '' ? '@'.self::GetSession('Project').':' : '');
                print(View::GetVar("Execute"). " ");
            }
            self::MainMenu(parent::GetMessage());
        }

        private static function MainMenu($select = '1') {
            $close = false;
            $goto = null;
            switch(self::$currentSelect ?? $select) {
                case 1:
                case 'create module':
                    $goto = 'create module';
                    break;
                case 2:
                case 'create controller':
                    $goto = 'create controller';
                    break;
                case 3:
                case 'create entity':
                    $goto = 'create entity';
                    break;
                case 4:
                case 'init project':
                    $goto = 'init project';
                    break;
                case 5:
                case 'build project':
                    $goto = 'build project';
                    break;
                case 6:
                case 'update project':
                    $goto = 'update project';
                    break;
                case 7:
                case 'Settings':
                    $goto = 'settings';
                    break;
                case 8:
                case 'clear cache':
                    self::ClearSession('Project');
                    print('clear cache success.');
                    break;
                case 9:
                case 'help':
                    print(passthru("CHOICE /?"));
                    print "\n\r";
                    break;
                case 10:
                case 'exit':
                    $close = true;
                    break;
                default:
                    print("invalid value - try again: ");
                    self::Init(self::$app, false);
            }
            ($close !== true) ? passthru('.\cim.bat '.$goto) : die(passthru("EXIT"));
            self::Init();
        }
    }
}