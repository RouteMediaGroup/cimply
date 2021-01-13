<?php

namespace Cimply\Core\Gui\Elements
{
	/**
	 * Button short summary.
	 *
	 * Button description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    use Cimply\Core\Document\Dom;
	class Label extends Dom
	{
        public function __construct($domElem, $name = null, $attr = []) {
            $this->createDomElement("<label ".\ArrayParser::ToStringImplode($attr).">{$name}</label>", $domElem);
        }
    }
}