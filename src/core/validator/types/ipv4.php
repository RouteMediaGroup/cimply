<?php

namespace Cimply\Core\Validator\Types
{
	/**
	 * Service of Ipv4 validate
	 */
	trait Ipv4
	{
        /**
         * Check Ipv4
         * @param mixed $var
         * @param mixed $opt
         */
        function checkIpv4($var, $opt) {
            $this->validateIpv4($var, $opt->required);
            if(!array_key_exists($var, $this->errors))
            {
                $this->sanitizeIpv4($var);
            }
        }


        /**
         *
         * @validate an ipv4 IP address
         *
         * @access private
         *
         * @param string $var The variable name
         *
         * @param bool $required
         *
         */
        private function validateIpv4($var, $required=false)
        {
            $state = false;
            !($required==false && strlen(isset($this->source[$var]) ? $this->source[$var] : null) == 0) ?? $state = true;
            !(filter_var(isset($this->source[$var]) ? $this->source[$var] : null, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE) ?? $this->errors[$var] = "[+%0% is not a valid IPv4+|{$var}]";
            return $state;
        }

        /**
         *
         * @sanitize a Ipv4-String
         *
         * @access private
         *
         * @param string $var The variable name
         *
         */
        private function sanitizeIpv4($var)
        {
            $this->sanitized[$var] = isset($this->source[$var]) ? $this->source[$var] : null;
        }
	}
}