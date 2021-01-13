<?php

namespace Cimply\Core\Document
{
	/**
     * ViewManager short summary.
     *
     * ViewManager description.
     *
     * @version 1.0
     * @author MikeCorner
     */
	class Dom extends DomManager
	{
        public function __construct(...$args) {
            parent::__construct(...$args);
        }

        /**
         * Summary of GetCurrentMarkup
         * @param mixed $source
         * @return Dom
         */
        static function GetCurrentMarkup($source = null): parent {
            \libxml_use_internal_errors(true);
            $dom = new self();
            $dom->formatOutput = true;
            $dom->encoding='UTF-8';
            $dom->preserveWhiteSpace=false;
            if(isset($source)) {
                $dom->loadXML($source);
            }
            return $dom;
        }

        /**
         * Summary of ReplaceContentByView
         * @param mixed $element
         * @param mixed $tag
         * @param mixed $attr
         * @param mixed $name
         * @param mixed $source
         * @param mixed $params
         * @param mixed $model
         * @return string
         */
        public static function ReplaceContentByView($element = null, $tag = null, $attr = null, $name = null, $source = null, $params = null, $model = null) {
            $domDoc = self::GetCurrentMarkup($element);
            $markupElements = isset($tag) ? $domDoc->getElementsByTagName($tag) : $domDoc->getElementsByTagName("*")->item(0);

            if(!(empty($source))) {
                $tmpDoc = self::GetCurrentMarkup($source);
                $model = isset($model) ? json_encode($model) : $model;
                $htmlDoc = new \DOMDocument();
                $htmlDoc->formatOutput = true;
                $htmlDoc->encoding='UTF-8';
                $htmlDoc->preserveWhiteSpace=false;
                for ($i = 0; $i < $markupElements->length; $i++) {
                    $el = $markupElements->item($i);
                    $asName = explode(" as ", $name);
                    if(isset($attr) && $el->getAttribute($attr) === $asName[0]) {
                        $tmpNode = $tmpDoc->getElementsByTagName($tag)->item(0) ?? $tmpDoc->childNodes->item(0) ?? $htmlDoc->createElement($tag, $source);
                        $fragment = $domDoc->importNode($tmpNode, true);
                        $elAttr = $el->attributes;
                        $fragment->setAttribute($attr, isset($asName[1]) ? $asName[1] : $asName[0]);
                        foreach($elAttr as $key=>$value) {
                            isset($value->value) ? $fragment->setAttribute($key, $value->value) : null;
                        }
                        if(isset($model->result)) {
                            $fragment->setAttribute("model", $model->result);
                        }
                        if((isset($params["attr"])) && (is_array($params["attr"]))) {
                            foreach($params["attr"] as $key => $val) {
                                $fragment->setAttribute($key, $val);
                            }
                        }
                        $el->parentNode->replaceChild($fragment, $el);
                    }
                }
            }
            $output = "";
            foreach ($domDoc->childNodes as $node) {
                $output.=$domDoc->saveXML($node)."\n";
            }
            return $output;
        }

        public static function SetAttrFromArray($content = null, $attributes = []) {
            //$content = trim(\html_entity_decode($content));
            $dom = self::GetCurrentMarkup($content);
            $root = $dom->documentElement;
            foreach($attributes as $key => $attr) {
                !is_array($attr) ? $root->setAttribute($key, $attr) : null;
            }
            $output = "";
            foreach ($dom->childNodes as $node) {
                $output.= $dom->saveXML($node);
            }
            return $output;
        }

        /*public function __call($name, $args) {
            if($name !== 'createElement') {
                return (new self())->$name(...$args);
            } else {
                //return new NodeManager($this->dom->createElement(...$args));
            }
        }*/
	}
}