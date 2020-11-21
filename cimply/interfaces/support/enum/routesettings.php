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
    abstract class RouteSettings extends \Enum
    {
        const TYPE          = "Type";
        const PARAMS        = "Params";
        const ACTION        = "action";
        const NAMESPACE     = "Namespace";
        const METHOD        = "Method";
        const CONTROLLER    = "Controller";
        const TEMPLATING    = "Templating";
        const TARGET        = "Target";
        const SESSION       = "Session";
        const VALIDATIONS   = "Validations";
        const THEME         = "Theme";
        const CACHING       = "Caching";
        const MARKUP        = "Markup";
        const MARKUPFILE    = "MarkupFile";
        const TPLS          = "Tpls";
        const SCHEME        = "Schema";
        const BINDING       = "Binding";
        const DATABINDING   = "Databinding";
        const REQUEST       = "Request";
        const REQUIRES      = "Requires";
        const NULL          = NULL;
    }
}