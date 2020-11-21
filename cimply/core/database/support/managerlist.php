<?php
namespace Cimply\Core\Database\Support
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class ManagerList extends \Enum
    {
        const MYSQLI    = 'MySqli';
        const PDO       = 'PDO';
        const NULL      = null;
    }
}