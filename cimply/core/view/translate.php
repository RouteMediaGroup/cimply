<?php

namespace Cimply\Core\View {

    /**
     * Description of Cimply.Work
     *
     * @author Michael Eckebrecht
     */
    use \Cimply\System\Helpers as Helper;
    class Translate
    {
        use \Properties, \Cast;

        protected static $pattern = null; 
        function __construct($pattern = null) {
            self::$pattern = $pattern;
        }

        public final function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        function OnPropertyChanged() {
            self::$staticProperties = $this;
        }

        //Get Translation
        static function GetTranslastion(string $s = "", $langCode = 'de_DE') {
            $matches = [];
            @preg_match_all(self::$pattern['Trans'], $s, $matches);
            if(isset($matches[1])) {
                $i = 0;
                foreach($matches[1] as $key => $value) {
                    if($trans = self::WordTranslation($matches[1][$i], $langCode)) {
                        $s = str_replace($matches[0][$i], $trans, $s);
                    }
                    $i++;
                }
            }
            return $s;
        }

        static function WordTranslation($value, $langCode = 'de_DE') {
            $translation = (array)self::GetStaticProperty('Translations');
            $checkString = $translation[$value] ?? $translation;
            return $checkString[$langCode] ?? $value;
        }

        static function Translation($value, $trans = null) : String {
            !(isset($trans)) ? $trans = self::GetStaticProperty('Translastions') : null;
            $translation = (array)self::GetTranslastion($value);
            count($translation) === 0 ? $translation = $value : null;
            
            $trimSting = explode(" ", $translation);
            $counts = strlen($trimSting[0]);
            $replacesWord = [];
            foreach($checkString = $trans as $key => $val) {
                if(substr($key, 0, $counts) === $trimSting[0]) {
                    if(similar_text($key, $translation, $percent)) {
                        if($percent >= 50) {
                            $trimResult = explode(" ", $key);
                            foreach ($trimResult as $k => $v) {
                                if(isset($trimSting[$k]) && ($v != $trimSting[$k])) {
                                    $i = str_replace('%', '', $v);
                                    $replacesWord[$i] = self::WordTranslation($trimSting[$k]);
                                }
                            }
                            $translation = vsprintf($val, $replacesWord);
                        }
                    }
                }
            }
            return $translation;
        }
    }
}
