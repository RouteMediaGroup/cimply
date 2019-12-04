<?php
namespace Cimply\Core\Validator {
    use Cimply\Core\Core;
    use Cimply\Core\Validator\Types\{Email, Url, Numeric, Floats, Strings, Json, Bits, Ipv4, Ipv6};
    class Validator
    {
        use \Properties, \Cast;
        use Email, Url, Numeric, Floats, Strings, Json, Bits, Ipv4, Ipv6;

        public $sanitized = [], $results = [], $errors = [];
        private $validation_rules = [], $source = [];

        function __construct($params = "") {
            $this->addSource($params, true);
        }

        public final static function Cast($mainObject, $selfObject = self::class): self {
            return static::Cull($mainObject, $selfObject);
        }

         /**
         *
         * @add the source
         *
         * @paccess public
         *
         * @param array $source
         *
         */
        function addSource($source, $trim=false): self
        {
            $this->source = $source;
            return $this;
        }

        /**
         *
         * @run the validation rules
         *
         * @access public
         *
         */
        function run(): self
        {
            if(isset($this->validation_rules)) {
                foreach(new \ArrayIterator($this->validation_rules) as $var => $opt)
                {
                    $opt = (object)$opt;
                    !($opt->required == true) ?? $this->isSet($var);

                    if( array_key_exists('trim', $opt) && $opt->trim == true )
                    {
                        isset($this->source[$var]) && !(empty($this->source[$var])) ? $this->source[$var] = (is_string($this->source[$var]) ? trim( $this->source[$var] ) : $this->source[$var] = \JsonDeEncoder::Encode($this->source[$var])) : $this->source[$var] = null;
                        $this->results[$var] = $this->source[$var];
                    }

                    switch($opt->type)
                    {
                        case 'email':
                            $this->checkEMail($var, $opt);
                            break;

                        case 'url':
                            $this->checkUrl($var, $opt);
                            break;

                        case 'numeric':
                        case 'int':
                        case 'tinyint':
                        case 'bigint':
                        case 'mediumint':
                        case 'smallint':
                            $this->checkNumeric($var, $opt);
                            break;

                        case 'string':
                        case 'char':
                        case 'varchar':
                        case 'tinyblob':
                        case 'smallblob':
                        case 'bigblob':
                        case 'text':
                            $this->checkStrings($var, $opt);
                        break;

                        case 'json':
                            $this->checkJson($var, $opt);
                        break;

                        case 'float': case 'double':
                            $this->checkFloats($var, $opt);
                            break;

                        case 'bool':
                        case 'bit':
                            $this->checkBits($var, $opt);
                            break;

                        case 'ipv4':
                            $this->checkIpv4($var, $opt);
                            break;

                        case 'ipv6':
                            $this->checkIpv6($var, $opt);
                            break;

                        case 'Guid':
                            $this->checkGuid($var, $opt);
                            break;
                    }
                }
            }
            return $this;
        }

        /**
         *
         * @add a rule to the validation rules array
         *
         * @access public
         *
         * @param string $varname The variable name
         *
         * @param string $type The type of variable
         *
         * @param bool $required If the field is required
         *
         * @param int $min The minimum length or range
         *
         * @param int $max the maximum length or range
         *
         */
        function addRule($varname, $type, $required=false, $min=0, $max=0, $trim=false): self
        {
            $newRule = ['type'=>$type, 'required'=>$required, 'min'=>$min, 'max'=>$max, 'trim'=>$trim];
            $this->validation_rules[$varname] = \Lists::ObjectList($newRule);
            return $this;
        }

        /**
         *
         * @add multiple rules to teh validation rules array
         *
         * @access public
         *
         * @param array $rules_array The array of rules to add
         *
         */
        function addRules(array $rules_array): self
        {
            $this->validation_rules = array_merge($this->validation_rules, $rules_array);
            return $this;
        }
        /**
         *
         * @add multiple rules to teh validation rules array
         *
         * @access public
         *
         * @param array $rules_array The array of rules to return
         *
         */
        function getRules(): ?array
        {
            return $this->validation_rules;
        }

        /**
         *
         * @Check if POST variable is set
         *
         * @access private
         *
         * @param string $var The POST variable to check
         *
         */
        private function isSet($var): void
        {
            if(empty($this->source[$var]))
            {
                $this->errors[$var] = "[+%0% is not set|{$var}+]";
            }
        }
    }
}