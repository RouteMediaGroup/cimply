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
    abstract class DbConnectSettings extends \Enum
    {
        const MYSQLI       = 'DBConnect:MYSQLI';
        const PDO          = 'DBConnect:PDO';
        const NULL  = null;
    }
}