<?php
namespace Cimply\Interfaces\Database\Enum
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class BindTypeList extends \Enum
    {
        const INTEGER = 'i';
        const DOUBLE  = 'd';
        const STRING  = 's';
        const BLOB    = 'b';
        const NULL    = null;
    }
}