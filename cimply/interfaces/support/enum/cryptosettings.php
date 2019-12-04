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
    abstract class CryptoSettings extends \Enum
    {
        const SALT       = 'Crypto:Salt';
        const PEPPER     = 'Crypto:Pepper';
        const PASSPHRASE = 'Crypto:Passphrase';
        const NULL  = null;
    }
}