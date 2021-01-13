<?php

namespace Cimply\Core\Gui
{
	/**
     * Connect short summary.
     *
     * Connect description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    use Cimply\Core\Document\Dom;
	class GuiFactory
	{
        /**
         * Summary of $guiElements
         * @var mixed
         */
        protected $guiElements = [];

        /**
         * Summary of __construct
         */
        function __construct() {}

        /**
         * Summary of set
         * @param mixed $name
         * @param mixed $guiType
         * @param mixed $args
         * @return GuiFactory
         */
        function set($name = null, $guiType = Support\FieldTypeList::NULL, $args = null): self {
            $domElement = new Dom();
            $domElement->formatOutput = true;
            //$domElement->createElement(\mb_strtolower($guiType));
            $domElement->createElement(\strtolower($guiType));
            $newClass = __NAMESPACE__.'\\Elements\\'.$guiType;
            new $newClass($domElement ,$name, $args);

            Support\FieldTypeList::isValidName($guiType) ? $this->guiElements[$name] = $domElement : null;
            return $this;
        }
        /**
         * Summary of get
         * @param mixed $name
         * @return \Cimply\Core\Document\Dom|null
         */
        function get($name = null): ?Dom
        {
            return $this->guiElements[$name] ?? null;
        }
        /**
         * Summary of getHTML
         * @param mixed $name
         * @return \null|string
         */
        function getHTML($name = null): ?String
        {
            return Dom::Cast($this->guiElements[$name])->saveHTML() ?? null;
        }
        function allToHTML(): ?String
        {
            $resultHtml = "";
            foreach($this->guiElements as $item) {
                $resultHtml.=Dom::Cast($item)->saveHTML();
            }
            return $resultHtml;
        }
        function markupToHTML($markup = []): ?String
        {
            $resultHtml = "";
            foreach($markup as $item) {
                $resultHtml.=Dom::Cast($this->guiElements[$item])->saveHTML();
            }
            return $resultHtml;
        }
        function getHTML5($name = null): ?String
        {
            return Dom::Cast($this->guiElements[$name])->saveXML() ?? null;
        }
        function allToHTML5(): ?String
        {
            $resultHtml = "";
            foreach($this->guiElements as $item) {
                $resultHtml.=Dom::Cast($item)->saveXML();
            }
            return $resultHtml;
        }
        function markupToHTML5($markup = []): ?String
        {
            $resultHtml = "";
            foreach($markup as $item) {
                $resultHtml.=Dom::Cast($this->guiElements[$item])->saveXML();
            }
            return $resultHtml;
        }
    }
}