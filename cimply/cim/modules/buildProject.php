<?php
declare(strict_types=1);
namespace Cim\Modules {
    use \Cimply\App\Settings;
    use \Cimply\Basics\{Basics, ServiceLocator\ServiceLocator, Repository\Support};
    use \Cimply\Core\View\{View, Translate, Scope, Markup, Template\Enum\Pattern};
    use \Cimply\Core\{Model\Wrapper, Model\Mapper};

    class BuildProject extends \Cimply\Service\Cli\Base
    {

        private $currentObject, $services;
        static protected $projects = null;
        
        function __construct(ServiceLocator $services = null)
        {
            $this->services = $services;
            $systemSettings = Support::Cast($this->services->getService())->getSystemSettings();
            $this->currentObject = Scope::Cast($this->services->getService());
            $this->menu();
        }

        /**
         * 
         * @Author Michael Eckebrecht
         * @Info This Modul build your Frontend in your project
         * @Back To leave the program, press Return without any input
         * @Execute "Enter name or number:"
         * 
         */
        final static function Init($services): void {
            new self($services);
            die();
        }

        final private function menu(): void {
            print "\n\r";
            print View::ParseTplVars("[+Description+]");  
            print "\n\r";
            print "-------------------------------------------------------------------------";
            print "\n\r";
            print View::ParseTplVars("[+Info+]");
            print "\n\r";
            print View::ParseTplVars("[+Back+]");
            print "\n\r";
            print "-------------------------------------------------------------------------"; 
            print "\n\r";
            self::$projects = self::LoadProject(Settings::AppPath.'Projects');
            print(View::ParseTplVars("[+Execute+]"). " ");
            $this->calculate(parent::GetMessage());
            print "\n\r";
        } 

        private function calculate($selected = null) {
            isset(self::$projects[$selected]) ? self::Execute(self::$projects[$selected]) : die();
        }

        private function execute($projectName = null) {
            $baseDir = "assets";
            if((bool)(system("cd frontend\\{$projectName} && ng build --prod --base-href ./"))) {
                if($this->recurseCopy(".\\frontend\\{$projectName}\\dist", $baseDir)) {
                    $files = \scandir($baseDir);
                    foreach($files as $file) {
                        $fileDir = explode('.', $file);
                        !empty(end($fileDir)) ? $this->pushFile($baseDir, end($fileDir), $file) : null;
                    }
                }
            }
        }
    }
}