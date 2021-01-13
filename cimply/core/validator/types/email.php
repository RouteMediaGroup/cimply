<?php

namespace Cimply\Core\Validator\Types
{
	/**
	 * Service of Email validation
	 */
	trait Email
	{
        /**
         * Check E-Mail
         * @param mixed $var
         * @param mixed $opt
         */
        function checkEMail($var, $opt) {
            $this->validateEmail($var, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeEmail($var);
            }
        }

        /**
         *
         * @validate an email address
         *
         * @access private
         *
         * @param string $var The variable name
         *
         * @param bool $required
         *
         */
        private function validateEmail($var, $required=false)
        {
            $state = false;
            ($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ? $state = true : null;
            (filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_VALIDATE_EMAIL) === FALSE) ? $this->errors[$var] =  "[+%0% is not a valid email address|{$var}+]" : null;
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
        public function sanitizeEmail($var)
        {
            $email = preg_replace( '((?:\n|\r|\t|%0A|%0D|%08|%09)+)i' , '', $this->source[$var] );
            $this->sanitized[$var] = (string) filter_var($email, FILTER_SANITIZE_EMAIL);
        }
	}
}