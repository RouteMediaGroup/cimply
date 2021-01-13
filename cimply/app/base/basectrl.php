<?php
/**
 * Description of BaseCtrl
 *
 * @author Michael Eckebrecht
 */

namespace Cimply\App\Base {
    use \Cimply\Core\Core;
    use \Cimply\Basics \{
        ServiceLocator\ServiceLocator,
        Repository\Support
    };
    use \Cimply\Core\View \{
        View, Scope, Markup, Template\Enum\Pattern
    };
    use \Cimply\Core\Gui \{
        Gui, GuiFactory, Support\FieldTypeList
    };

    class BaseCtrl
    {
        protected $services, $passthru = false, $encode = false;
        function __construct(ServiceLocator $services = null)
        {
            $this->services = $services;
        }

        public final function Cast($mainObject, $selfObject = self::class) : self
        {
            return Core::Cast($mainObject, $selfObject);
        }

        /**
         *
         * @PageTitle %project%
         * @Version "V %version%"
         * @Author Developed by %author% %date%
         *
         */
        static function Init($services)
        {
            return (new self($services))->execute();
        }

        protected function execute(): void
        {
            $systemSettings = Support::Cast($this->services->getService())->getSystemSettings();
            //$crypto = (Support::Cast($this->services->getService())->getScopeSettings('Crypto'));

            $currentObject = Scope::Cast($this->services->getService());
            $template = Gui::Cast($this->services->getService())->set($systemSettings, true);

            $outputHtml = $template->preparing(View::Create($currentObject->getTarget()));

            $author = View::ParseTplVars('Author');
            $version = View::ParseTplVars('Version');

            View::Assign([ 'author' => $author, 'version' => $version, 'date' => date('Y-m-d\TH:i:s\Z'), 'addr' => $_SERVER['HTTP_HOST'] ]);
            View::Show($outputHtml, $this->passthru, $this->encode, 'auto');
        }
    }
}