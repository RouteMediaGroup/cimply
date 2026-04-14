<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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
	class Checkbox extends Dom
	{
        public function __construct($domElem, $name = null, $attr = []) {
            foreach($attr as $value) {
                $this->createDomElement("<input type=\"checkbox\" name = \"{$name}\" ".\ArrayParser::ToStringImplode($value)." />", $domElem);
            }
        }
    }
}