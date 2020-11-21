<?php
declare(strict_types=1);
namespace Cim\Modules\Templates {
    use \Cimply\Core\View\View;
    class Markup
    {
        public function render($data):string {
            $result = "";
            if(!empty($data)) {
               $result = View::ParseTplVars('
declare(strict_types=[+stricttypes+]);
namespace Cimply\App\[+modulname+] {
    use \Cimply\Core\Core;
    use \Cimply\Core\View \{
        View, Scope, Markup, Template\Enum\Pattern
    };
    use \Cimply\Core\Gui \{
        Gui, GuiFactory, Support\FieldTypeList
    };
    use \Cimply\Basics \{
        ServiceLocator\ServiceLocator,
        Repository\Support
    };
    [+usings+]
    class [+cls_name+][+extends+][+interfaces+]
    {
        protected $services, $dbCon;
        function __construct(ServiceLocator $services = null, $dbCon = null)
        {
            $this->services = $services;
            $this->dbCon = $dbCon;
        }

        public final function Cast($mainObject, $selfObject = self::class) : self
        {
            return Core::Cast($mainObject, $selfObject);
        }
        
        /**
        *
        *[+annotations+]
        *
        */
        static function [+init_func+]($services)
        {
            return (new self($services, null))->execute();
        }

        private function execute(): void
        {
            $systemSettings = Support::Cast($this->services->getService())->getSystemSettings();
            $crypto = (Support::Cast($this->services->getService())->getScopeSettings("Crypto"));
            
            $currentObject = Scope::Cast($this->services->getService());
            $template = Gui::Cast($this->services->getService())->set($systemSettings, true);

            $outputHtml = $template->preparing(View::Create($currentObject->getTarget()));
        
            View::Assign([]);       
            View::Render($outputHtml);
        }
    }
}', $data);
            }
            return $result;
        }
        
    }
}