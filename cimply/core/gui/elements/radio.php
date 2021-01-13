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
	class Radio extends Dom
	{
        public function __construct($domElem, $name = null, $attr = []) {
            foreach($attr as $value) {
                $this->createDomElement("<input type=\"radio\" name = \"{$name}\" ".\ArrayParser::ToStringImplode($value)." />", $domElem);
            }
        }
    }
}