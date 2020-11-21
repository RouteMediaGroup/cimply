<?php
namespace Cimply\Core\View\Support\Enum
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class Pattern extends \Enum
    {
        const MODUL = 'Pattern:Modul';
        const LIBS  = 'Pattern:Libs';
        const PARAM = 'Pattern:Param';
        const TRANS = 'Pattern:Trans';
        const NULL  = null;
    }
}