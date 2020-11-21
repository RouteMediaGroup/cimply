<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply\Core\View {

    /**
     * Description of Cim_View_Form
     *
     * @author MikeCorner
     */
    use Cimply\Core\Document\{Dom};
    class Markup extends Dom {

        protected $dom;
        private $node, $markup = null, $source = null, $namespace = null, $instanceName = null;

        public $root = null;
        public $nodes = array();
        public $callback = null;
        public $lowercase = false;
        protected $pos;
        protected $doc;
        protected $char;
        protected $size;
        protected $cursor;
        public $parent;
        protected $noise = array();
        protected $token_blank = " \t\r\n";
        protected $token_equal = " =/>";
        protected $token_slash = " />\r\n\t";
        protected $token_attr = " >";
        protected $self_closing_tags = array("img"=>1, "br"=>1, "input"=>1, "meta"=>1, "link"=>1, "hr"=>1, "base"=>1, "embed"=>1, "spacer"=>1);
        protected $block_tags = array("root"=>1, "body"=>1, "form"=>1, "div"=>1, "span"=>1, "table"=>1);
        protected $optional_closing_tags = array(
            "tr"=>array("tr"=>1, "td"=>1, "th"=>1),
            "th"=>array("th"=>1),
            "td"=>array("td"=>1),
            "li"=>array("li"=>1),
            "dt"=>array("dt"=>1, "dd"=>1),
            "dd"=>array("dd"=>1, "dt"=>1),
            "dl"=>array("dd"=>1, "dt"=>1),
            "p"=>array("p"=>1),
            "nobr"=>array("nobr"=>1),
        );

        public function __construct($markup = null, $source = null, $namespace = null, $instanceName = null) {
            isset($markup) && is_file($markup) ? $this->markup = \File::GetFile($markup) : $this->markup = $markup;
            isset($source) && is_file($source) ? $this->source = \File::GetFile($source) : $this->source = $source;
            isset($namespace) ? $this->namespace = $namespace : null;
            isset($instanceName) ? $this->instanceName = $instanceName : $this->instanceName = "Markup";
            parent::__construct();
        }

        public function buildHTMLFromContext($element = "div", array $fields, $attr = "", $row = 1)
        {
            $hidden = array();
            $inputfields = array();
            $block = array();
            $i = 1;
            $html = "";
            $field = "";
            $count = 1;
            $index = 1;

            foreach($fields as $key => $value) {
                if(is_array($value)) {
                    $tagName = isset($this->namespace) ? $this->namespace : (isset($value["markup"]) ? "markup" : key($value));
                    //die(var_dump($tagName));
                    $i == 1 ? $field .= "<".$tagName." ". (isset($value[$tagName]["attr"]) ? \ArrayParser::ToStringImplode($value[$tagName]["attr"]) : "").">" : "";
                    $inputfields[$key] = $this->buildField(
                        empty($value[$tagName]["field"]) ? "input" : $value[$tagName]["field"],
                        isset($value[$tagName]["type"]) ? $value[$tagName]["type"] : (isset($value["markup"]) ? "text" : "hidden"),
                        empty($value[$tagName]["name"]) ? Translate::WordTranslation($key) : Translate::WordTranslation($value[$tagName]["name"]),
                        empty($value[$tagName]["id"]) ? "field".Translate::WordTranslation($key) : "field".Translate::WordTranslation($value[$tagName]["id"]),
                        empty($value[$tagName]["label"]) ? $key : $value[$tagName]["label"],
                        empty($value[$tagName]["properties"]) ? "" : $value[$tagName]["properties"],
                        empty($value[$tagName]["class"]) ? "" : $value[$tagName]["class"],
                        empty($value[$tagName]["placeholder"]) ? Translate::WordTranslation($key) : Translate::WordTranslation($value[$tagName]["placeholder"]),
                        empty($value[$tagName]["style"]) ? "" : $value[$tagName]["style"],
                        empty($value[$tagName]["disabled"]) ? "" : $value[$tagName]["disabled"],
                        empty($value[$tagName]["hide"]) ? "" : $value[$tagName]["hide"],
                        empty($value[$tagName]["options"]) ? "" : $value[$tagName]["options"],
                        empty($value[$tagName]["value"]) ? "" : $value[$tagName]["value"],
                        empty($value[$tagName]["model"]) ? "" : $value[$tagName]["model"],
                        empty($value[$tagName]["namespace"]) ? "" : $value[$tagName]["namespace"],
                        isset($value[$tagName]["markup-tag"]) ? $value[$tagName]["markup-tag"] : null,
                        " tabindex=\"".$index++."\""
                    );
                    $field .= $inputfields[$key];
                    if($i == $row) {
                        $field .= "</".$tagName.">";
                        $html .= $field;
                        View::SetVars(array("-".($count++) => $field), "Block");
                        View::SetVars(array("".$key => $field), "Field");
                        $block[$key] = $field;
                        $field = "";
                        $i = 1;
                    } else {
                        $i++;
                    }
                } else {
                    $hidden[$key] = $this->buildField(
                        empty($value[$tagName]["field"]) ? "input" : $value[$tagName]["field"],
                        isset($value[$tagName]["type"]) ? $value[$tagName]["type"] : (isset($value["autoincrement"]) && $value["autoincrement"] || isset($value["index"]) && $value["index"]) ? "hidden" : "text",
                        empty($value[$tagName]["name"]) ? Translate::WordTranslation($key) : Translate::WordTranslation($value[$tagName]["name"]),
                        empty($value[$tagName]["id"]) ? "field".Translate::WordTranslation($key) : "field".Translate::WordTranslation($value[$tagName]["id"]),
                        empty($value[$tagName]["label"]) ? $key : $value[$tagName]["label"],
                        empty($value[$tagName]["properties"]) ? "" : $value[$tagName]["properties"],
                        empty($value[$tagName]["class"]) ? "" : $value[$tagName]["class"],
                        empty($value[$tagName]["placeholder"]) ? Translate::WordTranslation($key) : Translate::WordTranslation($value[$tagName]["placeholder"]),
                        empty($value[$tagName]["style"]) ? "" : $value[$tagName]["style"],
                        empty($value[$tagName]["disabled"]) ? "" : $value[$tagName]["disabled"],
                        empty($value[$tagName]["hide"]) ? "" : $value[$tagName]["hide"],
                        empty($value[$tagName]["options"]) ? "" : $value[$tagName]["options"],
                        empty($value[$tagName]["value"]) ? "" : $value[$tagName]["value"],
                        empty($value[$tagName]["model"]) ? "" : $value[$tagName]["model"],
                        empty($value[$tagName]["namespace"]) ? "" : $value[$tagName]["namespace"],
                        isset($value[$tagName]["markup-tag"]) ? $value[$tagName]["markup-tag"] : null
                    );
                }
            }
            View::SetVars($inputfields ? $inputfields : null, "Field");
            View::SetVars($hidden ? $hidden : null, "Hide");
            View::SetVars($block, "Block");
            //Template::SetVars($html, "Form");
            if($element != "*") {
                $domElement = $this->setElement($element);
                isset($attr) ? $this->setAttributes($domElement, $attr) : null;
                $this->appendChild($domElement);
                $this->preserveWhiteSpace = true;
                $html = self::mergeMarkup($html, $this, "*");
            }

            View::SetVars(array($this->instanceName => $html));
            return array($this->instanceName => $html, "Block" => $block, "Field" => $inputfields, "Hidden" => $hidden);
        }

        public static function mergeMarkup($docSource, $dom = null, $xpath = "*")
        {
            !empty($docSource) ? : $docSource = "<div></div>";
            $doc = new Dom();
            $doc->formatOutput = true;
            if(isset($dom)) {
                $doc->loadXML($dom->saveXML());
                $docFragment = $doc->createDocumentFragment();
                $docFragment->appendXML($docSource);
                isset($docSource) ? $doc->documentElement->appendChild($docFragment) : null;
                return $doc->saveHTML();
            }
            return $docSource;
        }

        public function buildField($field = "form", $type, $name, $id, $label = array(), $properties = null, $class = null, $placeholder = null, $style = null, $disabled = null, $hide = false, $options = null, $value = null, $model = null, $namespace = null, $markupTag = null, $tabindex = "")
        {
            if( isset($label["attr"]) && is_array($label["attr"]) ) {
                $label["attr"] = \ArrayParser::ArrayToString($label, "attr");
            }
            $namespace = isset($namespace) ? $namespace.".".$name : $name;
            $result = "";

            switch($field):
                case "input":
                    $fieldHTML  = $this->CreateInput($type, $id, $name, $value, $class, $style, $placeholder, $disabled, $tabindex, $model, $namespace);
                    break;

                case "textarea":
                    $fieldHTML  = $this->CreateTextarea($id, $name, $value, $class, $style, $placeholder, $disabled, $tabindex, $model, $namespace);
                    break;

                case "select":
                    $fieldHTML  = $this->CreateDropdown($id, $name, $value, $options, $class, $style, $disabled, $tabindex, $model, $namespace);
                    break;

                case "submit":
                    $fieldHTML  = $this->CreateInput($type, $id, $name, $value, $class, $style, $placeholder, $disabled, $tabindex, $model, $namespace);
                    break;

                default:
                    if($type == "single"):
                        $fieldHTML = $this->CustomSingleTag($field, $id, $name, $value, $class, $style, $options, $tabindex);
                    else:
                        $fieldHTML = $this->CustomMultiTag($field, $id, $name, $value, $class, $style, $options, $tabindex);
                    endif;
                    break;
            endswitch;

            if(isset($label["attr"])):
                $hide = isset($label["hide"]) ? true : false;
                $fieldHTML = $this->CreateLabel($label["name"], $id, $label["attr"], $hide).$fieldHTML;
            endif;
            if(is_array($properties)):
                $fieldHTML = $this->MessageTemplate($properties, $fieldHTML);
            endif;
            $fieldHTML .= "\r";

            if(isset($this->markup)) {
                $field = isset($markupTag) ? $markupTag : $field;
                //markup-".$field = "div"
                $result = $this->execute("markup-{$field}", $fieldHTML);
            } else {
                $result = $fieldHTML;
            }

            return $result;
        }

        public function buildHTMLTag($tag, $type = "", $name = "", $id = "", $class, $style = null, $attr = null, $value = null, $model = null, $namespace = null, $index = 0)
        {
            if($type == "single"):
                $fieldHTML = $this->CustomSingleTag($tag, $id, $name, $value, $class, $style, $attr, $index);
            else:
                $fieldHTML = $this->CustomMultiTag($tag, $id, $name, $value, $class, $style, $attr, $index);
            endif;
            $fieldHTML .= "\r";
            return $fieldHTML;
        }

        private function execute($field = "markup-default", $source = "") {
            $output = '';

            //$docSourceHTML = \mb_convert_encoding('<'.$field.'>'.$source.'</'.$field.'>', 'HTML-ENTITIES', 'UTF-8');
            $docSourceHTML = ('<'.$field.'>'.$source.'</'.$field.'>');
            $docSource = self::GetCurrentMarkup($docSourceHTML);
            $sourceXpath = new \DOMXPath($docSource);
            $sourceNodes = $sourceXpath->query('//'.$field.'/*');

            //$docMarkupHTML = mb_convert_encoding($this->markup, 'HTML-ENTITIES', 'UTF-8');
            $docMarkupHTML = ($this->markup);
            $docMarkup = self::GetCurrentMarkup($docMarkupHTML);
            $markupXpath = new \DOMXPath($docMarkup);
            $markupNodes = $markupXpath->query('//'.$field.'/*');

            for ($i = 0; $i <= $sourceNodes->length; $i++) {
                $output.= $this->mergeNodes($docMarkup, $sourceNodes, $markupNodes, $i);
            }
            return $output;
        }

        public function render($source = "", $isRoot = false) {
            $result = "";
            preg_match_all("/<markup-(.*?)>(.*?)<\/markup-(.*?)>/", $source, $allTags);
            if(count($allTags[2])>=1) {
                foreach($allTags[2] as $key => $value) {
                    $result.= $this->execute("markup-".$allTags[1][$key], $allTags[2][$key]);
                }
            }
            return $result;
        }

        public function customSingleTag($tag = "div", $id = "", $name = null, $html = "", $class = "", $style = "", $placeholder = "", $attr = "", $index = "", $model = "", $namespace = "") {
            $fieldHTML  = " <{$tag}";
            $fieldHTML .= empty($name) ? "" : " name=\"{$name}\"";
            $fieldHTML .= empty($html) ? "" : " value=\"{$html}\"";
            $fieldHTML .= empty($model) ? "" : " {$model}=\"{$namespace}\"";
            $fieldHTML .= empty($placeholder) ? "" : " placeholder=\"{$placeholder}\"";
            $fieldHTML .= empty($id) ? "" : " id=\"{$id}\"";
            $fieldHTML .= empty($class) ? "" : " class=\"{$class}\"";
            $fieldHTML .= empty($style) ? "" : " style=\"{$style}\"";
            $fieldHTML .= empty($attr) ? "" : $attr;
            $fieldHTML .= " index=\"{$index}\" />";
            return $fieldHTML;
        }

        public function customMultiTag($tag = "div", $id = "", $name = null, $html = "", $class = "", $style = "", $placeholder = "", $attr = "", $index = "", $model = "", $namespace = "") {
            $fieldHTML  = " <{$tag}";
            $fieldHTML .= empty($name) ? "" : " name=\"{$name}\"";
            $fieldHTML .= empty($html) ? "" : " value=\"{$html}\"";
            $fieldHTML .= empty($model) ? "" : " {$model}=\"{$namespace}\"";
            $fieldHTML .= empty($placeholder) ? "" : " placeholder=\"{$placeholder}\"";
            $fieldHTML .= empty($id) ? "" : " id=\"{$id}\"";
            $fieldHTML .= empty($class) ? "" : " class=\"{$class}\"";
            $fieldHTML .= empty($style) ? "" : " style=\"{$style}\"";
            $fieldHTML .= empty($attr) ? "" : $attr;
            $fieldHTML .= " index=\"{$index}\">".$html."</".$tag.">";
            return $fieldHTML;
        }

        public function createTextarea($id = "", $name = "", $value = "", $class = "", $style = "", $placeholder = "", $disabled = "", $tabindex = "", $model = "", $namespace = "") {
            $fieldHTML  = " <textbox name=\"{$name}\"";
            $fieldHTML .= empty($model) ? "" : " $model=\"{$namespace}\"";
            $fieldHTML .= empty($placeholder) ? "" : " placeholder=\"{$placeholder}\"";
            $fieldHTML .= empty($id) ? "" : " id=\"{$id}\"";
            $fieldHTML .= empty($class) ? "" : " class=\"{$class}\"";
            $fieldHTML .= empty($style) ? "" : " style=\"{$style}\"";
            $fieldHTML .= empty($disabled) ? "" : " disabled=\"{$disabled}\"";
            $fieldHTML .= " \"{$tabindex}\">".$value."</textbox>";
            return $fieldHTML;
        }

        public function createInput($type = "", $id = "", $name = "", $value = "", $class = "", $style = "", $placeholder = "", $disabled = "", $tabindex = "", $model = "", $namespace = "") {
            $fieldHTML  = " <input type=\"{$type}\" name=\"{$name}\"";
            $fieldHTML .= empty($value) ? "" : " value=\"{$value}\"";
            $fieldHTML .= empty($model) ? "" : " $model=\"{$namespace}\"";
            $fieldHTML .= empty($placeholder) ? "" : " placeholder=\"{$placeholder}\"";
            $fieldHTML .= empty($id) ? "" : " id=\"{$id}\"";
            $fieldHTML .= empty($class) ? "" : " class=\"{$class}\"";
            $fieldHTML .= empty($style) ? "" : " style=\"{$style}\"";
            $fieldHTML .= empty($disabled) ? "" : " disabled=\"{$disabled}\"";
            $fieldHTML .= "{$tabindex}";
            $fieldHTML .= " />";
            return $fieldHTML;
        }

        public function createDropdown($id = "", $name = "", $value = "", $options = "", $class = "", $style = "", $disabled = "", $tabindex = "", $model = "", $namespace = "") {
            $fieldHTML  = " <select ";
            $fieldHTML .= empty($id) ? "" : " id=\"{$id}\"";
            $fieldHTML .= empty($id) ? "" : " name=\"{$id}\"";
            $fieldHTML .= empty($class) ? "" : " class=\"{$class}\"";
            $fieldHTML .= empty($style) ? "" : " style=\"{$style}\"";
            $fieldHTML .= empty($disabled) ? "" : " disabled=\"{$disabled}\"";
            $fieldHTML .= "{$tabindex}";
            $fieldHTML .= empty($model) ? "" : " {$model}=\"{$namespace}\"";
            $fieldHTML .= empty($options) ? "" : " ng-options=\"{$options}\"";
            $fieldHTML .= " ng-change=\"hasChanged()\"></select>";
            return $fieldHTML;
        }

        public function createLabel($name, $for, $attr=null, $hide = false) {
            return $hide ? "<label ".$attr." for=\"{$for}\" />" : "<label {$attr} for=\"{$for}\">{$name}</label>";
        }

        public function renderMarkup($source = null, $template = null, $selector = null, $path = "markup/") {
            $explName = explode(".", $template);
            isset($selector) ? $selector : $selector = "markup-".$explName[0];
            //ToDo: Lade neue Konfig und nicht �ber System
            $filePath = \File::GetFile(System::GetItems("Project","Path")."/".$path.$template);
            $markup =  $filePath != false ? $filePath : $template;
            $domMarkup = self::GetCurrentMarkup(mb_convert_encoding($markup, "HTML-ENTITIES", "UTF-8"));

            $resultDom = new \DOMDocument("1.0", "utf-8");
            $resultDom->formatOutput = true;

            foreach($source as $key => $value) {
                $domDoc = self::GetCurrentMarkup(mb_convert_encoding("<".$selector.">".$value."</".$selector.">", "HTML-ENTITIES", "UTF-8"));
                $this->mergeNodeElements($domDoc->getElementsByTagName($selector)->item(0), $domMarkup, $resultDom);
            }

            return $resultDom->saveHTML();
        }
    }
}