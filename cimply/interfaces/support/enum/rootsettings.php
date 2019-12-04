<?php
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