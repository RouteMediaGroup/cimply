<?php

namespace Cimply\Core\Validator\Types
{
	/**
     * Service of Numeric validate
     */
	trait Numeric
	{
        /**
         * Check Numeric
         * @param mixed $var
         * @param mixed $opt
         */
        function checkNumeric($var, $opt) {
            $this->validateNumeric($var, $opt->min, $opt->max, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeNumeric($var);
            }
        }

        /**
         *
         * @validate an number
         *
         * @access private
         *
         * @param string $var the variable name
         *
         * @param int $min The minimum number range
         *
         * @param int $max The maximum number range
         *
         * @param bool $required
         *
         */
        private function validateNumeric($var, $min=0, $max=0, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ? $state = true : null;
            (filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max)))===FALSE) ? $this->errors[$var] = "[+%0% is an invalid number|{$var}+]" : null;
            return $state;
        }

        /**
         *
         * @sanitize a numeric value
         *
         * @access private
         *
         * @param string $var The variable name
         *
         */
        private function sanitizeNumeric($var)
        {
            $this->sanitized[$var] = (int) filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_SANITIZE_NUMBER_INT);
        }
	}
}