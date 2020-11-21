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
    use \Cimply\Basics\{Basics, ServiceLocator\ServiceLocator};
    use \Cimply\Core\Gui\{Gui, GuiFactory, Support\FieldTypeList};
    use \Cimply\Core\View\{View, Translate, Scope, Markup, Template\Enum\Pattern};

    class NewInstance extends \Cimply\Service\Cli\Base
    {
        /**
         *
         * @Author Michael Eckebrecht
         * @Info Create a new Node.js Instance.
         * @Back To leave the program, press Return without any input
         * @Execute "Please choice (Y)es/(N)o"
         *
         */
        final static function Init(): void {
            print "\n\r";
            print View::ParseTplVars("[+Description+]");
            print "\n\r";
            print "--------------------------------------------------------------------";
            print "\n\r";
            print View::ParseTplVars("[+Info+]");
            print "\n\r";
            print View::ParseTplVars("[+Back+]");
            print "\n\r";
            print "--------------------------------------------------------------------";
            print "\n\r";
            print(View::ParseTplVars("[+Execute+]"). " ");
            self::Calculate(parent::GetMessage());
            print "\n\r";
        }

        private static function Calculate($selected = null) {
            \strtolower(substr($selected,0,1)) === "y" ? self::Execute($selected) : die();
        }

        private static function Execute($project) {
            print(system("npm install angular --g --save"));
            print(system("npm install -g @angular/cli --save"));
            print(system("npm upgrade --g"));
            print(" - success.");
        }
    }
}