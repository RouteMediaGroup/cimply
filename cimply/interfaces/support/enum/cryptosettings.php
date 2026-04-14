<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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