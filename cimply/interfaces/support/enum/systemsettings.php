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
    abstract class SystemSettings extends \Enum
    {
        const USETEMPLATEFOR        = "System:UseTemplateFor";
        const USEPARSEFILES         = "System:UseParseFiles";
        const USENOTTRANSLATIONFOR  = "System:UseNotTranslationFor";
        const USENOTCACHINGFOR      = "System:UseNotCachingFor";
        const PARSINGIMAGEFILES     = "System:ParsingImageFiles";
        const TEMPDIR               = "System:TempDir";
        const NULL                  = NULL;
    }
}