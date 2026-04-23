<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Database\Enum
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class FetchStyleList extends \Enum
    {
        const FETCHOBJECT = 1;
        const FETCHASSOC = 2;
        const FETCH = 3;
        const FETCHARRAY = 4;
        const FETCHCOLUMN = 5;
        const FETCHFIELD = 6;
        const FETCHFIELD_DIRECT = 7;
        const FETCHALL = 8;
        const NULL = 0;
    }
}