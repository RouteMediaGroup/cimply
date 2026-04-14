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
    abstract class ScopeSettings extends \Enum
    {
        const PROJECT   = 'Project';
        const ROUTING   = 'Routing';
        const MAPPER    = 'Mapper';
        const COMMON    = 'Common';
        const GLOBALS   = 'Globals';
        const NULL      = null;
    }
}
