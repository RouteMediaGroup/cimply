<?php

/*
 * CIMPLY FrameWork V 1.0.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2016 RouteMedia. All rights reserved.
 */

namespace Cimply\Core\Document {
    use \Cimply\Core\{Core, Document\ObjectModel\NodeManager};

    /**
     * Summary of DomManager
     */
    abstract class DomManager extends \DOMDocument {
        
        final static function Cast($mainObject, $selfObject = self::class): self {
            return Core::Cast($mainObject, $selfObject, true);
        }

        function createDomElement($source, \DOMNode $domDocument = null) {
            libxml_use_internal_errors(true);
            $domDocument ?? $domDocument = \self;
            $tmpDoc = new \DOMDocument();
            $tmpDoc->recover = true;
            $tmpDoc->strictErrorChecking = false;
            $tmpDoc->loadHTML($source, 0);
            foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
                $node = $domDocument->importNode($node, false);
                $domDocument->appendChild($node);
            }
        }

        /**
         * Summary of mergeNodes
         * @param \DOMDocument $viewSource
         * @param \DOMNodeList $sourceNodes
         * @param \DOMNodeList $markupNodes
         * @param mixed $depth
         * @return \null|string
         */
        protected function mergeNodes(\DOMDocument $viewSource, \DOMNodeList $sourceNodes, \DOMNodeList $markupNodes, $depth = 0) {
            $newdoc = new parent();
            $newdoc->formatOutput = false;
            $result = null;
            if($markupNodes->item($depth)) {
                foreach ($sourceNodes as $key => $value) {
                    $element = $markupNodes->item($depth);
                    if ($markupNodes->item($depth)->tagName == $value->tagName) {
                        $this->mergeAttributes($value, $markupNodes, $depth);
                        $node = $newdoc->importNode($value, true);
                        $newdoc->appendChild($node);
                        $result.= $newdoc->saveXML($node, LIBXML_NOEMPTYTAG);
                    } else {
                        $result = "";
                        isset($sourceNodes->item($depth)->textContent) ?
                        $element->getAttribute("show-tag") != null
                        ? $result.= $viewSource->saveHTML($markupNodes->item($depth))
                        : ((bool)(($element->nodeName == "root" || $element->getAttribute("root-tag")) != null)
                        ? $result = str_replace("[+value+]", $sourceNodes->item($depth)->textContent, $this->isRoot($element, $this->MergeNode($markupNodes, $value, $depth), $newdoc)) : null) : null;
                    }
                }
            }
            return $result;
        }

        /**
         * Summary of isRoot
         * @param mixed $element
         * @param mixed $NodeManager
         * @param mixed $newdoc
         * @return string
         */
        protected function isRoot($element = null, $NodeManager = null, $newdoc = null) {
            $result = "";
            if($element->getAttribute("root-tag") != null) {
                $childNode = $newdoc->importNode($NodeManager, true);
                $newdoc->appendChild($childNode);
                $result.= $newdoc->saveXML($childNode, LIBXML_NOEMPTYTAG);
            } else {
                for($i = 0; $i <= $NodeManager->childNodes->length; $i++) {
                    if($NodeManager->childNodes->item($i) != null) {
                        $childNode = $newdoc->importNode($NodeManager->childNodes->item($i), true);
                        $newdoc->appendChild($childNode);
                        $result.= $newdoc->saveHTML($childNode);
                    }
                }
            }
            return $result;
        }

        /**
         * Summary of mergeNode
         * @param \DOMNodeList $markupNodes 
         * @param \DOMElement $sourceNodes 
         * @param mixed $depth 
         * @return \DOMElement
         */
        protected function mergeNode(\DOMNodeList $markupNodes, \DOMElement $sourceNodes, $depth = 0) {
            foreach ($markupNodes->item($depth)->childNodes as $k => $v) {
                if((isset($sourceNodes->parentNode->childNodes->item($k)->tagName) && isset($v->tagName)) && $v->tagName == $sourceNodes->parentNode->childNodes->item($k)->tagName) {
                    $this->mergeAttributes($v, $sourceNodes->parentNode->childNodes, $k);
                    $this->mergeViews($v, $sourceNodes->parentNode->childNodes, $k);
                }
            }
            return $markupNodes->item($depth);
        }

        /**
         * Summary of mergeViews
         * @param \DOMElement $markupNode
         * @param \DOMNodeList $sourceNodes
         * @param mixed $depth
         * @return View
         */
        protected function mergeViews(\DOMElement $markupNode, \DOMNodeList $sourceNodes, $depth = 0) {
            $markupNode->nodeValue = $sourceNodes->item($depth)->nodeValue;
            return $this;
        }

        /**
         * Summary of mergeAttributes
         * @param \DOMDocument $markupNodes
         * @param \DOMNodeList $sourceNodes
         * @param mixed $depth
         * @return View
         */
        protected function mergeAttributes(\DOMElement $markupNodes, \DOMNodeList $sourceNodes, $depth = 0) {
            $isSet = false;
            foreach ($sourceNodes->item($depth)->attributes as $k => $attr) {
                foreach ($markupNodes->attributes as $mk => $mvalue) {
                    if (strtolower($mk) == strtolower($k) && ($attr->value != $mvalue->value)) {
                        $isSet = true;
                        strtolower($k) == "class" ? $markupNodes->setAttribute($k, $attr->value . " " . $mvalue->value) : $markupNodes->setAttribute($k, $mvalue->value);
                    }
                }
                if(!$isSet) {
                    $markupNodes->setAttribute($k, $attr->value);
                }
            }
            return $markupNodes;
        }

        /**
         * Summary of MessageTemplate
         * @param mixed $properties
         * @param mixed $view
         * @return View
         */
        public function MessageTemplate($properties, $view) {
            $prop = [];
            foreach($properties as $key=>$var):
                $prop[$key] = $var;
            endforeach;
            $this->load($view);
            //$this->doc->find("//*[".$prop["template"]."]", 0)->outertext = $prop["message"];
            if(!(empty($prop["message"]))):
                die(" <p>".(isset($prop["message"]) ? $prop["message"] : "error" )."</p>");
            endif;
            return $this;
        }

        /**
         * Summary of setAttributes
         * @param \DOMElement $domElem
         * @param mixed $attrValue
         */
        protected function setAttributes(\DOMElement $domElem = null, $attrValue): void {
            foreach($attrValue as $key => $value) {
                $this->setAttribute($domElem, $key, $value);
            }
        }

        /**
         * Summary of setAttribute
         * @param \DOMElement $domElem
         * @param mixed $attrName
         * @param mixed $attrValue
         * @return View
         */
        protected function setAttribute(\DOMElement $domElem, $attrName, $attrValue): self {
            $domAttribute = $domElem->setAttribute($attrName, $attrValue);
            $domElem->appendChild($domAttribute);
            return $this;
        }

        /**
         * Summary of mergeNodeElements
         * @param mixed $domNodeSource
         * @param mixed $domNodeMarkup
         * @param DomManager $domOutput
         */
        protected function mergeNodeElements($domNodeSource, $domNodeMarkup, parent $domOutput) {
            foreach ($domNodeSource->childNodes as $node)
            {
                if($domNodeMarkup->getElementsByTagName($node->nodeName)->length >= 1) {
                    $markup = $domNodeMarkup->getElementsByTagName($node->nodeName)->item(0);
                    $this->setSingleAttribute("id", $node);
                    $this->setSingleAttribute("name", $node);
                    $this->setMultiAttributes("class", $node, $markup);
                    $newNode = $domOutput->importNode($node, true);
                    $domOutput->appendChild($newNode);
                }
                if(isset($node) && $node->hasChildNodes()) {
                    //$this->mergeNodeElements($node, $domNodeMarkup, $domOutput);
                }
            }
        }

        /**
         * Summary of setElement
         * @param mixed $tagName
         * @param mixed $id
         * @param mixed $classes
         * @param mixed $options
         * @return \DOMElement|null
         */
        public function setElement($tagName = null, $id = null, $classes = [], $options = null): \DOMElement {
            $NodeManager = $this->createElement($tagName) ?? null;
            !(empty($id)) ? $this->createAttribute($NodeManager, "id", $id) : null;
            count($classes) === 0 ? : $this->createAttribute($NodeManager, "class", implode(",", $classes));
            return $NodeManager;
        }

        /**
         * Summary of setMultiAttributes
         * @param mixed $name
         * @param mixed $source
         * @param mixed $markup
         */
        public function setMultiAttributes($name = null, $source = null, $markup = null) {
            if(isset($name)) {
                $newAtrribute = isset($source->attributes->getNamedItem($name)->value) ? " ".$source->attributes->getNamedItem($name)->value : "";
                $source->setAttribute($name,
                    isset($markup->attributes->getNamedItem($name)->value)
                    ? $markup->attributes->getNamedItem($name)->value.$newAtrribute
                    : $newAtrribute);
            }
        }

        /**
         * Summary of setSingleAttribute
         * @param mixed $name
         * @param mixed $source
         */
        public function setSingleAttribute($name = null, $source = null) {
            if(isset($name)) {
                isset($source->attributes->getNamedItem($name)->value)
                ? $source->setAttribute($name, $source->attributes->getNamedItem($name)->value)
                : null;
            }
        }

        /**
         * Summary of setValue
         * @param mixed $val
         * @return mixed
         */
        public function setValue($val)
        {
            //$this->node === the raw NodeManager instance
            $attr = $this->node->getAttribute("value");
            if ($attr === null)://no value attribute yet, create it
                $attr = new \DOMAttr("value", $val);
            else://set new value
                $attr->value = $val;
            endif;
            $this->node->setAttributeNode($attr);
            return $this->node;
        }
    }
}