<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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