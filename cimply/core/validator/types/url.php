<?php

namespace Cimply\Core\Validator\Types
{
	/**
	 * Service of Url validate
	 */
	trait Url
	{
        /**
         * Check URL
         * @param mixed $var
         * @param mixed $opt
         */
        function checkUrl($var, $opt) {
            $this->validateUrl($var);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeUrl($var);
            }
        }

        /**
         *
         * @validate a url
         *
         * @access private
         *
         * @param string $var The variable name
         *
         * @param bool $required
         *
         */
        private function validateUrl($var, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ? $state = true : null;
            (filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_VALIDATE_URL) === FALSE) ? $this->errors[$var] =  "[+%0% is not a valid URL|{$var}+]" : null;
            return $state;
        }

        /**
         *
         * @sanitize a url
         *
         * @access private
         *
         * @param string $var The variable name
         *
         */
        private function sanitizeUrl($var)
        {
            $this->sanitized[$var] = (string) filter_var(isset($this->source[$var]) ? $this->source[$var] : null,  FILTER_SANITIZE_URL);
        }
	}
}