<?php

namespace Cimply\Core\Validator\Types
{
	/**
	 * Service of Json validate
	 */
	trait Json
	{
        /**
         * Check Json
         * @param mixed $var
         * @param mixed $opt
         */
        function checkJson($var, $opt) {
            $this->validateJson($var, $opt->min, $opt->max, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeJson($var);
            }
        }


        /**
         *
         * @validate a string
         *
         * @access private
         *
         * @param string $var The variable name
         *
         * @param int $min the minimum string length
         *
         * @param int $max The maximum string length
         *
         * @param bool $required
         *
         */
        private function validateJson($var, $min=0, $max=0, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : 0) == 0) ? $state = true : null;

            if(isset($this->source[$var]))
            {
                (strlen(isset($this->source[$var]) ? $this->source[$var] : null) < $min) ?
                    $this->errors[$var] = $var . ' [+is too short+]'
                : (strlen(isset($this->source[$var]) ? $this->source[$var] : null) > $max) ?
                    $this->errors[$var] = $var . ' [+is too long+]'
                : (is_string($this->source[$var])) ? :
                    $this->errors[$var] = $var . ' [+is invalid+]';
            }
            return $state;
        }

        /**
         *
         * @sanitize a Json-String
         *
         * @access private
         *
         * @param string $var The variable name
         *
         */
        private function sanitizeJson($var)
        {
            $this->sanitized[$var] = isset($this->source[$var]) ? $this->source[$var] : '{}';
        }
	}
}