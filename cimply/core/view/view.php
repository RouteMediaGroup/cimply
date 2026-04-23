<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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

        public static $mimeType = '', $externalFile = true;

        function __construct(?Scope $scope = null)
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

        /*static function GetTemplateArgs($tpl = null) : ? array { 
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
                : ((\is_string(self::GetStaticProperty(AppSettings::PROJECTPATH))) && \is_file(self::GetStaticProperty(AppSettings::PROJECTPATH). DIRECTORY_SEPARATOR . $baseFile) ? self::GetStaticProperty(AppSettings::PROJECTPATH). DIRECTORY_SEPARATOR . $baseFile : self::GetStaticProperty(AppSettings::MODULE). DIRECTORY_SEPARATOR . $baseFile); self::$externalFile = false; 
            }
            return \array_merge(['filePath' => $result], $path);
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
				: ((\is_string(self::GetStaticProperty(AppSettings::PROJECTPATH))) && \is_file(self::GetStaticProperty(AppSettings::PROJECTPATH). DIRECTORY_SEPARATOR . $baseFile) ? self::GetStaticProperty(AppSettings::PROJECTPATH). DIRECTORY_SEPARATOR . $baseFile : (\is_string(self::GetStaticProperty(AppSettings::MODULE)) ? self::GetStaticProperty(AppSettings::MODULE). DIRECTORY_SEPARATOR . $baseFile : null));
                self::$externalFile = false;
            }
            return \array_merge(['filePath' => $result], $path);
        }
        */
        static function GetTemplateArgs($tpl = null) : ?array
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
                
                if ($baseFile && \is_file($baseFile) && (self::GetStaticProperty(AppSettings::CLIENTFILESALLOW) == true)) {
                    $result = $baseFile;
                } else if ($baseFile && \is_string(self::GetStaticProperty(AppSettings::PROJECTPATH)) && \is_file(self::GetStaticProperty(AppSettings::PROJECTPATH) . DIRECTORY_SEPARATOR . $baseFile)) {
                    $result = self::GetStaticProperty(AppSettings::PROJECTPATH) . DIRECTORY_SEPARATOR . $baseFile;
                } else if ($baseFile && \is_string(self::GetStaticProperty(AppSettings::MODULE))) {
                    $result = self::GetStaticProperty(AppSettings::MODULE) . DIRECTORY_SEPARATOR . $baseFile;
                } else {
                    $result = null;
                }

                self::$externalFile = false;
            }
            return \array_merge(['filePath' => $result], $path);
        }

        public static function GetTemplatePath($tpl = null, $unused = null): ?string
        {
            return self::GetTemplateArgs($tpl)['filePath'] ?? null;
        }

        public static function GetFileBaseName(): ?string
        {
            return (new UriManager())->getFileBasename();
        }

        public static function GetBasePath(): string
        {
            $projectPath = (string)(self::GetStaticProperty(AppSettings::PROJECTPATH) ?? '');
            return $projectPath !== '' ? rtrim($projectPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '';
        }

        public static function GetFileBasePath(): string
        {
            $assets = (string)(self::GetStaticProperty(AppSettings::ASSETS) ?? '');
            return self::GetBasePath() . ($assets !== '' ? trim($assets, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '');
        }

        //Set Parameters
        public function setTemplateParams($params)
        {
            $msg = [];
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
                    if ($v !== null) {
                        self::$vars[$key . $k] = $v;
                    }
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
            $filePath = !(empty($tplArgs['filePath'])) ? $tplArgs['filePath'] : '';
            if (\is_file($filePath)) {
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
            $fileType = $scope?->getType() ?? 'html';
            $mimeType = (\is_string($mime) && trim($mime) !== '')
                ? trim($mime)
                : (\Mime::GetMime((string)$fileType, true) ?? self::$mimeType);
            $disabledCaching = self::GetStaticProperty(SystemSettings::USENOTCACHINGFOR);
            $caching = !($scope?->getCaching()) ? ($scope?->getCaching()) : !\in_array($fileType, (array)$disabledCaching, true);

            $toTranslation = self::GetStaticProperty(SystemSettings::USENOTTRANSLATIONFOR);
            !((isset($toTranslation)) && \in_array($fileType, (array)$toTranslation, true)) || (empty($toTranslation)) ? $body = Translate::GetTranslastion($body ?? '') : null;
            $body = View::ParseTplVars($body);
            ($secure === 1 || $secure === true) ? $body = (function($body) use($scope, $fileType, $mimeType, $caching) {
                $salt = self::GetStaticProperty(CryptoSettings::SALT);
                $pepper = md5(time().self::GetStaticProperty(CryptoSettings::PEPPER));
                return \JsonDeEncoder::Encode(["scope" => serialize($scope), "fileTyp" => $fileType, "mimeTyp" => $mimeType, 'hash' => $pepper, "caching" => $caching, "content" => (\Crypto::Encrypt(\JsonDeEncoder::Encode(self::$vars), $salt, $pepper)), "data" => (\Crypto::Encrypt($body, $salt, $pepper))]);
            })($body) : null;

            if (!headers_sent() && \is_string($mimeType) && trim($mimeType) !== '') {
                header("Content-Type: {$mimeType}");
            }

            $output = $body;
            if (!($passthru)) {
                die($output);
            }
            return $output;
        }

        public static function ParseTplVars($template = null, $vars = null) : ?string
        {
            if($template !== null) {
                $pattern = (string)self::GetStaticProperty(PatternSettings::PARAM);
                if ($pattern === '') {
                    return $template;
                }

                preg_match_all($pattern, (string)$template, $matches);
                $lookup = \is_array($vars) ? $vars : self::$vars;
                $replacements = [];

                if (isset($matches[1]) && count($matches[1]) > 0) {
                    foreach ($matches[1] as $key => $value) {
                        if (\array_key_exists($value, $lookup)) {
                            $translated = Translate::WordTranslation($lookup[$value]);
                            $replacements[$matches[0][$key]] = self::normalizeTemplateValue($translated ?? $lookup[$value]);
                        }
                    }
                }

                if ($replacements !== []) {
                    $template = strtr((string)$template, $replacements);
                }
            }
            return $template;
        }

        public static function ParseTplView($template = null) : string
        {
            if($template !== null) {
                $template = self::ParseTplVars($template);
                $pattern = (string)self::GetStaticProperty(PatternSettings::MODUL);
                if ($pattern === '') {
                    return (string)$template;
                }

                preg_match_all($pattern, (string)$template, $matches);
                $replacements = [];

                if (isset($matches[1]) && count($matches[1]) > 0) {
                    foreach ($matches[1] as $key => $value) {
                        $replacements[$matches[0][$key]] = self::Create($matches[0][$key]) ?? '';
                    }
                }

                if ($replacements !== []) {
                    $template = strtr((string)$template, $replacements);
                }
            }
            return $template;
        }

        private static function normalizeTemplateValue(mixed $value): string
        {
            if (\is_string($value)) {
                return $value;
            }

            if (\is_scalar($value) || $value === null) {
                return (string)$value;
            }

            if (\is_array($value)) {
                return (string)(\JsonDeEncoder::Encode($value) ?? '');
            }

            return \is_object($value) && \method_exists($value, '__toString') ? (string)$value : '';
        }

        public static function ParseTplAttr($element = null) : ? array
        {
            $result = null;
            if($element !== null) {
                preg_match_all((string)self::GetStaticProperty(PatternSettings::ATTR), (string)$element, $matches);
                $result = $matches[0] ?? [];
                if (!empty($matches['name'][0]) && !empty($matches[1])) {
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
            if (!empty($matches[1])) {
                $result = self::ParseTplAttr($matches[1][0]);
            }
            return $result;
        }
    }
}
