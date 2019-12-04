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
    abstract class ControllerSettings extends \Enum
    {
        const PRIVATES = 'Controller:Private';
        const PUBLICS  = 'Controller:Public';
        const NULL  = null;
    }
}