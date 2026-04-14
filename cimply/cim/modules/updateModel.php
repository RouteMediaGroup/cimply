<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

declare(strict_types=1);

namespace Cim\Modules {
    use \Cimply\App\Settings;
    use \Cimply\Core\View\{View, Translate, Scope, Markup, Template\Enum\Pattern};
    use \Cimply\Core\{Model\Wrapper, Model\Mapper};

    class UpdateModel extends \Cimply\Service\Cli\Base
    {
        /**
         * 
         * @Author Michael Eckebrecht
         * @Info This Modul updates your Model Entities in your project
         * @Back To leave the program, press Return without any input
         * @Execute "Enter name or number:"
         * 
         */
        final static function Init($project = null): void {
            print "\n\r";
            print View::ParseTplVars("[+Description+]");  
            print "\n\r";
            print "----------------------------------------------------------------------";
            print "\n\r";
            print View::ParseTplVars("[+Info+]");
            print "\n\r";
            print View::ParseTplVars("[+Back+]");
            print "\n\r";
            print "----------------------------------------------------------------------"; 
            print "\n\r";
            self::$projects = self::LoadProject(Settings::AppPath.'Projects');
            print(View::ParseTplVars("[+Execute+]"). " ");
            //var_dump($project);
            self::Calculate(parent::GetMessage());
        }

        private static function Calculate($selected = null) {
            isset(self::$projects[$selected]) ? self::Execute((new \Cimply\Program())->app(self::$projects[$selected] ?? in_array($selected, self::$projects ? $selected : null))->register()) : die();
        }

        private static function Execute($instance = null) {
            new Process\IEntityGenerator(Mapper::Cast($instance->getService()), Wrapper::Cast($instance->getService()));
        }
    }
}