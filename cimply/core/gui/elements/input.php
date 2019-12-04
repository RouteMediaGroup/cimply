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
	class Input extends Dom
	{
        public function __construct($domElem, $name = null, $attr = []) {
            $this->createDomElement("<input type=\"text\" name=\"{$name}\" ".\ArrayParser::ToStringImplode($attr)." />", $domElem);
        }
    }
}