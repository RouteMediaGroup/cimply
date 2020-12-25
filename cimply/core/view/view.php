<?php
namespace Cimply\Core\View {

    /**
     * Description of CIMPLY.WORK
     *
     * @author Michael Eckebrecht
     */
    use \Cimply\System\Helpers as Helper;
    use \Cimply\Core \{
        Document\Dom, Request\Uri\UriManager
    };
    use \Cimply\Interfaces\Support\Enum \{
        PatternSettings, CryptoSettings, RootSettings, SystemSettings, AppSettings
    };
    use \Cimply\Interfaces\IProperty;

    class View implements IProperty
    {
        use \Properties, \Cast, \Files;

        private static $vars = [], $ttl = 1, $view = "";
        protected $scope;

        public static $mimeType = 'x-conference/x-cooltalk', $externalFile = true;

        function __construct(Scope $scope = null)
        {
            $this->scope = $scope;
        }

        final static function Cast($mainObject, $selfObject = self::class) : self
        {
            return self::Cull($mainObject, $selfObject);
        }

        function OnPropertyChanged()
        {
            self::$staticProperties = $this;
        }

        static function GetTemplateArgs($tpl = null) : ? array
        {
            $result = null;
            $path = [];
            if ($modul = self::GetModules($tpl)) {
                foreach ($modul as $key => $value) {
                    $path = \is_string($key) ? ['file' => $key, 'attr' => $value] : ['file' => $value];
                }
                $fileInfo = new UriManager($path['file']);
                $extension = $fileInfo->getFileType();
                $basename = \str_replace('_', DIRECTORY_SEPARATOR, $fileInfo->getFileBasename());
                $baseFile = self::GetStaticProperty(AppSettings::ASSETS) . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $basename;
                $result = \is_file($baseFile) && (self::GetStaticProperty(AppSettings::CLIENTFILESALLOW) == true) 
                ? $baseFile
                : self::GetStaticProperty(AppSettings::PROJECTPATH) . DIRECTORY_SEPARATOR . $baseFile;
                self::$externalFile = false;
            }
            return \array_merge(['filePath' => $result], $path);
        }

        //Set Parameters
        public function setTemplateParams($params)
        {
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $msg[$key][$k] = $v;
                    }
                } else {
                    $msg[$key] = $val;
                }
            }
            return $msg;
        }

        //Get Parameter
        static function GetVar($s, $key = '')
        {
            return static::$vars[$key . $s] ?? null;
        }

        //Set Value
        static function SetVar($key = null, $value = '')
        {
            isset($key) ? self::$vars[$key] = $value : null;
        }

        //Set Parameters
        static function SetVars($params = null, $key = '')
        {
            if (is_array($params)) {
                foreach ($params as $k => $v) {
                    !empty($v) ? self::$vars[$key . $k] = $v : null;
                }
            }
        }

        //Get Parameters
        static function GetVars(): ?array
        {
            return self::$vars ?? null;
        }

        //Set Assign Params
        public static function Assign($value) : void
        {
            $vars = [];
            foreach ($value as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $vars[$key][$k] = $v;
                    }
                } else {
                    $vars[$key] = $val;
                }
            }
            //self::$vars = [self::$vars, ...$vars];
            self::$vars = array_merge(self::$vars, $vars);
        }

        function isExternalFile() : bool
        {
            return $this->externalFile;
        }

        static function GetPattern(string $key) : ? string
        {
            return PatternSettings::isValidValue($key) ? self::GetStaticProperty($key) : null;
        }

        /**
         *
         * Create
         *
         */
        public static function Create($template = null) : ? string
        {
            $tplArgs = self::GetTemplateArgs($template);
            $filePath = $tplArgs['filePath'];
            if (is_file($filePath)) {
                self::$mimeType = \Mime::GetMime($tplArgs['file']);
                $cacheFile = self::GetStaticProperty(AppSettings::CACHEDIR) . DIRECTORY_SEPARATOR . 'tmp_' . md5($filePath);
                $iFiletime = self::HasFileCached($cacheFile) ? \filemtime($cacheFile) : 0;
                $template = ($iFiletime > (\time() - (10 * self::$ttl))) ? self::GetFileContent($cacheFile) : self::$staticProperties->cacheFile($filePath, $cacheFile);
                isset($tplArgs['attr']) ? $template = Dom::SetAttrFromArray($template, $tplArgs['attr']) : null;
            }
            return $template ?? null;
        }

        /**
         *
         * Render View
         *
         */
        public static function Render($template = null, $passthru = false, $secure = null): void
        {
            if (!headers_sent() && isset(self::$mimeType)) {
                header('Content-type:' . self::$mimeType);
            }
            $template = is_array($template) ? self::EncodeBeforeRendering($template) : self::ParseTplVars($template);
            self::Show($template ?? self::$view, $passthru, $secure);
        }

        /**
         *Convert ViewParsing
         * @param [type] $template
         * @return String
         */
        static function EncodeBeforeRendering($template = []): String
        {
            $template[key($template)] = self::ParseTplVars($template[key($template)]);
            return \JsonDeEncoder::Encode($template);
        }

        static function Show($body = "", $passthru = false, $secure = null, $mime = null) : string
        {
            $scope = Scope::Cast(self::GetStaticProperty('scope'));
            $fileType = $scope->getType();
            $mimeType = \Mime::GetMime('.' . $fileType, true) ?? self::$mimeType;
            $caching = !$scope->getCaching() ? $scope->getCaching() : !\in_array($fileType, self::GetStaticProperty(SystemSettings::USENOTCACHINGFOR));

            $toTranslation = self::GetStaticProperty(SystemSettings::USENOTTRANSLATIONFOR);
            !((isset($toTranslation)) && in_array($scope->getType(), $toTranslation)) || (empty($toTranslation)) ? $body = Translate::GetTranslastion($body) : null;
            $body = View::ParseTplVars($body);
            ($secure === 1 || $secure === true) ? $body = (function($body) use($scope, $fileType, $mimeType, $caching) {
                $salt = self::GetStaticProperty(CryptoSettings::SALT);
                $pepper = md5(time().self::GetStaticProperty(CryptoSettings::PEPPER));
                return \JsonDeEncoder::Encode(["scope" => serialize($scope), "fileTyp" => $fileType, "mimeTyp" => $mimeType, 'hash' => $pepper, "caching" => $caching, "vars" => (\Crypto::Encrypt(\JsonDeEncoder::Encode(self::$vars), $salt, $pepper)), "data" => (\Crypto::Encrypt($body, $salt, $pepper))]);
            })($body) : null;
            
            $mime ? header("Content-Type: {$mime}") : $mime;
            $output = $body;
            if (!($passthru)) {
                die($output);
            }
            return $output;
        }

        public static function ParseTplVars($template = null, $vars = null) : ?string
        {
            if($template !== null) {
                @preg_match_all(self::GetStaticProperty(PatternSettings::PARAM), $template, $matches);
                if (isset($matches[1]) && count($matches[1]) > 0) {
                    foreach ($matches[1] as $key => $value) {
                        if (array_key_exists($value, (is_array($vars) ? $vars : self::$vars) ?? self::$vars)) {
                            $template = str_replace($matches[0][$key], Translate::WordTranslation($vars[$value] ?? self::$vars[$value]) ?? null, $template);
                        }
                    }
                }
            }
            return $template;
        }

        public static function ParseTplView($template = null) : string
        {
            if($template !== null) {
                $template = self::ParseTplVars($template);
                @preg_match_all(self::GetStaticProperty(PatternSettings::MODUL), $template, $matches);
                if (isset($matches[1]) && count($matches[1]) > 0) {
                    //$explAttr = explode(':', $matches[1][0]);
                    foreach ($matches[1] as $key => $value) {
                        $template = str_replace($matches[0][$key], self::Create($matches[0][$key]) ?? null, $template);
                    }
                }
            }
            return $template;
        }

        public static function ParseTplAttr($element = null) : ? array
        {
            $result = null;
            if($element !== null) {
                @preg_match_all(self::GetStaticProperty(PatternSettings::ATTR), $element, $matches);
                $result = $matches[0];
                if (($matches['name'][0] !== '') && count($matches[1]) > 0) {
                    $explAttr = explode(',', $matches['value'][0]);
                    $remove = array("\"", "'");
                    foreach ($explAttr as $key => $value) {
                        $v = explode('=', \ltrim($value));
                        $result[$matches['name'][0]][$v[0]] = str_replace($remove, '', $v[1]);
                    }
                }
            }
            return $result;
        }

        public static function GetModules($s) : ? array
        {
            $result = null;
            if (!self::GetPattern(PatternSettings::MODUL))
                throw new \Exception("Modul-Pattern " . PatternSettings::MODUL . " not found.");

            preg_match_all(self::GetPattern(PatternSettings::MODUL), (string)$s, $matches);
            if ($matches[1]) {
                $result = self::ParseTplAttr($matches[1][0]);
            }
            return $result;
        }
    }
}