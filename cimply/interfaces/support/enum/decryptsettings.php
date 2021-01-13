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
    abstract class DecryptSettings extends \Enum
    {
        const STATE = 'Decrypt:State';
        const SALT  = 'Decrypt:Salt';
        const SECURECODE = 'Decrypt:Securecode';
        const NULL  = null;
    }
}