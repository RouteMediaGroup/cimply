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
    abstract class AppSettings extends \Enum
    {
        const BASEURL               = "App:BaseUrl";
        const BASENAME              = "App:BaseName";
        const PROJECTNAME           = "App:Project";
        const PROJECTPATH           = "App:ProjectPath";
        const PROJECTNAMESPACE      = "App:Namespace";
        const INDEX                 = "App:Index";
        const PARAMS                = "App:Params";
        const USINGS                = "App:Usings";
        const ASSETS                = "App:Assets";
        const CLIENTFILESALLOW      = "App:ClientFiles";
        const DATABASE              = "App:Database";
        const DEFAULTS              = "App:Default";
        const COMMONDIR             = "App:CommonDir";
        const CACHEDIR              = "App:CacheDir";
        const NULL                  = NULL;
    }
}