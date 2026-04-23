<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\App\Basics {
    trait Routing {
        
        protected static $Path, $Name, $FilePath, $FileName, $FileType, $BasePath, $BaseName, $CurrentFile, $CurrentViewModel, $Controller, $Requires, $Cache; 
        
        public function __construct() { }

        public static function GetProject(): ?string {
            return static::$Name;
        }
        public static function GetCommonPath() {}
        public static function GetCurrentObject() {}
        public static function GetRequires() {}
        public static function GetBaseName() {}
        public static function GetBasePath() {}
        public static function GetValidations() {}
        public static function GetRedirect() {}
        public static function GetLocale() {}

        public static function SetProject($projectName = ""): void {
            static::$Name = $projectName;
        }
        public static function SetCommonPath() {}
        public static function SetCurrentObject() {}
        public static function SetRequires() {}
        public static function SetBaseName() {}
        public static function SetBasePath() {}
        public static function SetRedirect() {}
        public static function SetLocale() {}
        public static function SetValidations() {}
        
        public static function CurrentViewModel() { return static::$CurrentViewModel; }
        
    }
}