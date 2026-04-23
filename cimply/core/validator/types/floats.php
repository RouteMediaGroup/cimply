<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Validator\Types
{
	/**
	 * Service of Floats validate
	 */
	trait Floats
	{
        /**
         * Check Floats
         * @param mixed $var
         * @param mixed $opt
         */
        function checkFloats($var, $opt) {
            $this->validateFloat($var, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeFloat($var);
            }
        }

        /**
         *
         * @validate a floating point number
         *
         * @access private
         *
         * @param $var The variable name
         *
         * @param bool $required
         */
        private function validateFloat($var, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ? $state = true : null;
            (filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_VALIDATE_FLOAT) === false) ? $this->errors[$var] = "[+%0% is an invalid float+|{$var}]" : null;
            return $state;
        }


        /**
         *
         * @sanitize a float value
         *
         * @access private
         *
         * @param string $var The variable name
         *
         */
        private function sanitizeFloat($var)
        {
            $this->sanitized[$var] = (int) filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_SANITIZE_NUMBER_FLOAT);
        }
	}
}