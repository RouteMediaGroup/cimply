<?php

namespace Cimply\Core\Validator\Types
{
	/**
     * Service of Guid validation
     */
	trait Guid
	{
        /**
         * Check E-Mail
         * @param mixed $var
         * @param mixed $opt
         */
        function checkGuid($var, $opt) {
            $this->validateGuid($var, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeGuid($var);
            }
        }

        /**
         *
         * @validate an GUID
         *
         * @access private
         *
         * @param string $var The variable name
         *
         * @param bool $required
         *
         */
        private function validateGuid($var, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ? $state = true : null;
            (isset($this->source[$var]) ? $this->source[$var] : null) === FALSE ? $this->errors[$var] =  "[+%0% is not a valid Guid|{$var}+]" : null;
            return $state;
        }

        /**
         *
         * @santize and email
         *
         * @access private
         *
         * @param string $var The variable name
         *
         * @return string
         *
         */
        public function sanitizeGuid($var)
        {
            $guid = preg_replace( '/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/' , '', $this->source[$var] );
            $this->sanitized[$var] = (string) filter_var($guid, FILTER_SANITIZE_STRING);
        }
	}
}