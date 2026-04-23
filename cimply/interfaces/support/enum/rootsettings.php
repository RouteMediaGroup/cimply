<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Interfaces\Support\Enum
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class RootSettings extends \Enum
    {
        const CROSSING              = "Crossing";
        const COMPRESS              = "Compress";
        const DEVMODE               = "DevMode";
        const DEBUG                 = "Debug";
        const DISPLAY               = "Display";
        const DECRYPT               = "Decrypt";
        const CRYPTO                = "Crypto";
        const LOCALE                = "Locale";
        const SYMLINK               = "Symlink";
        const APP                   = "App";
        const SYSTEM                = "System";
        const MAINTENANCE           = "Maintenance";
        const DIRECTORIES           = "Directories";
        const MVC                   = "MVC";
        const TPL                   = "Tpl";
        const PATTERN               = "Pattern";
        const CONTROLLER            = "Controller";
        const COLLECTIONS           = "Collections";
        const THEMES                = "Themes";
        const TRANSLATIONS          = "Translations";
        const DBCONNECT             = "DBConnect";
        const SSL                   = "SSL";
        const ALLOWSCTAGS           = "AllowSCTags";
        const NULL                  = null;
    }
}