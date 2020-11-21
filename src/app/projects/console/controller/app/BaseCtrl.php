<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author MikeCorner
 */

namespace Cimply\App {

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

    class basectrl extends Init
    {
        protected $services;
        private $dbManager1, $dbManager2;
        function __construct(ServiceLocator $services = null) {
            $this->services = $services;
        }

        public final function Cast($mainObject, $selfObject = self::class): self {
            return Core::Cast($mainObject, $selfObject);
        }

        /**
         * 
         * @PageTitle Cimply.Work rock´s
         * @params {"Name1":"Value1", "Name2":10}
         * @redirect '{"conditions":{"BenutzerId":false},"fallback":"\login"}'
         * 
         */
        function Init($services) {
            parent::Init($app = new self($services)) ? $app->execute() : null;
        }
        
        function makeDesign() {
            $systemSettings = Support::Cast($this->services->getService())->getSystemSettings();
            $appSettings = Support::Cast($this->services->getService())->getAppSettings();
    
            $currentDirectories = Assets::Cast($this->services->getService());
            $currentObject = Scope::Cast($this->services->getService());
            //Template::Cast($this->services->getService())->set($appSettings);
            
            $html = Gui::Cast($this->services->getService())->set($systemSettings, true);
            $elements = (new GuiFactory())
                ->set('Vorname', FieldTypeList::INPUT, ["id" => "Vorname", "value"=>"Hans", "class" => "text"])
                ->set('Nachname', FieldTypeList::INPUT, ["id" => "Nachname", "value"=>"Meiser", "class" => "text"])
                ->set('LabelRadio', FieldTypeList::LABEL, ["value" => "Auswahl", "for" => "sel", "class" => "label"])
                ->set('RadioButton', FieldTypeList::RADIO, ["radioBtn1" => ["id" => "sel", "value"=>"A", "class" => "radio"], "radioBtn2" => ["id" => "sel", "value"=>"B", "class" => "radio"]]);
            $markup = new Markup('<markup-input><label class="text">[+value+]</label><input type="text" class="form-control" /><div show-tag="true">Test</div></markup-input>', $currentObject->getMarkupFile());
            //die(var_dump(\JsonDeEncoder::Decode($obj['Control']['save'], true)));
            return $markup;
            //$this->createForm('form', $markup, ['markup' => ['label'=>['type'=>'text', 'class'=>'text', 'placeholder'=>'Code Eingabe'],'input'=>['type'=>'text', 'value'=>'ok', 'class'=>'text', 'placeholder'=>'Code Eingabe']], ['div'=>['type'=>'text', 'name'=>'supidupi', 'value'=>'ok', 'class'=>'text', 'placeholder'=>'Code Eingabe']]], 'Formular');  
        }

        function execute() {
            
            $systemSettings = Support::Cast($this->services->getService())->getSystemSettings();
            $appSettings = Support::Cast($this->services->getService())->getAppSettings();
    
            $currentDirectories = Assets::Cast($this->services->getService());
            $currentObject = Scope::Cast($this->services->getService());
            //Template::Cast($this->services->getService())->set($appSettings);
            
            $html = Gui::Cast($this->services->getService())->set($systemSettings, true);
            $elements = (new GuiFactory())
                ->set('Vorname', FieldTypeList::INPUT, ["id" => "Vorname", "value"=>"Hans", "class" => "text"])
                ->set('Nachname', FieldTypeList::INPUT, ["id" => "Nachname", "value"=>"Meiser", "class" => "text"])
                ->set('LabelRadio', FieldTypeList::LABEL, ["value" => "Auswahl", "for" => "sel", "class" => "label"])
                ->set('RadioButton', FieldTypeList::RADIO, ["radioBtn1" => ["id" => "sel", "value"=>"A", "class" => "radio"], "radioBtn2" => ["id" => "sel", "value"=>"B", "class" => "radio"]]);
            $markup = new Markup('<markup-input><label class="text">[+value+]</label><input type="text" class="form-control" /><div show-tag="true">Test</div></markup-input>', $currentObject->getMarkupFile());
            //die(var_dump(\JsonDeEncoder::Decode($obj['Control']['save'], true)));
            
            $this->createForm('form', $markup, ['markup' => ['label'=>['type'=>'text', 'class'=>'text', 'placeholder'=>'Code Eingabe'],'input'=>['type'=>'text', 'value'=>'ok', 'class'=>'text', 'placeholder'=>'Code Eingabe']], ['div'=>['type'=>'text', 'name'=>'supidupi', 'value'=>'ok', 'class'=>'text', 'placeholder'=>'Code Eingabe']]], 'Formular');

            $template = $html->preparing(View::Create($currentObject->getTarget()));
            View::Assign(['Title' => '<translate>no file selected.</translate>', 'LogoText' => 'Logo Text', 'Fields' => $elements->allToHTML5(), 'Form' => $this->views['Formular']['Markup']]);
            View::Render($template);

            $scope = Support::Cast($this->services->getService());
            $dbConnect = (Database::Cast($this->services));
            $this->dbManager1 = $dbConnect->getInstance('PDO')->manager;
            $this->dbManager2 = $dbConnect->getInstance('MYSQLI')->manager; 

            $viewPresenter = new Presenter($this->dbManager1, 'city');
            $result = $viewPresenter
                ->selectBy('ID => ?')
                ->chainAnd('Name = ?')
                ->setParams([9, 'Eindhoven'])
                ->styleMode(FetchStyleList::FETCHOBJECT);
            \Debug::VarDump($result->execute(), false);

            $viewPresenter = new Presenter($this->dbManager2, 'city');
            $result = $viewPresenter
                ->selectBy('ID => ?')
                ->chainAnd('Name = ?')
                ->setParams([9, 'Eindhoven'])
                ->styleMode(FetchStyleList::FETCHOBJECT);
            \Debug::VarDump($result->execute(), false);  
        }

        /**
         * @param Markup $viewForm
         * @param mixed $context
         * @param mixed $key
         */
        private function createForm($type = 'form', Markup $markup, $context = [], $key = 'FormElement') {
            $this->views[$key] = $markup->buildHTMLFromContext($type, $context, ["class" => "text"]);
        }
    }
}