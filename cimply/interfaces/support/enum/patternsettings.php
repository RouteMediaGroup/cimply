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
    abstract class PatternSettings extends \Enum
    {
        const MODUL = 'Pattern:Modul';
        const LIBS  = 'Pattern:Libs';
        const PARAM = 'Pattern:Param';
        const TRANS = 'Pattern:Trans';
        const ATTR =  'Pattern:Attributes';
        const NULL  =  null;
    }
}