<?php

namespace Cimply\Core\Validator\Types
{
	/**
	 * Service of Bits validate
	 */
	trait Bits
	{
        /**
         * Check Bits
         * @param mixed $var
         * @param mixed $opt
         */
        function checkBits($var, $opt) {
            $this->validateBit($var, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitized[$var] = (bool)$this->source[$var] ?? false;
            }
        }

        /**
         * @validate a boolean
         *
         * @access private
         *
         * @param string $var the variable name
         *
         * @param bool $required
         *
         */
        private function validateBit($var, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ? $state = true : null;
            filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_VALIDATE_BOOLEAN) ? $this->errors[$var] = "[+%0% is Invalid bit-type+|{$var}]" : null;
            return $state;
        }
	}
}