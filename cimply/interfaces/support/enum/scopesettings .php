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
    abstract class ScopeSettings extends \Enum
    {
        const PROJECT   = "Project";
        const ROUTING   = "Routing";
        const MAPPER    = "Mapper";
        const COMMON    = "Common";
        const GLOBALS   = "Globals";
        const NULL      = NULL;
    }
}