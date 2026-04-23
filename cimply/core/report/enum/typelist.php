<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Repository\Report\Enum
{
	/**
	 * OperatorList short summary.
	 *
	 * OperatorList description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
	abstract class TypeList extends \Enum
	{
        const text = 0;
        const html = 1;
        const csv = 2;
        const xml = 3;
        const pdf = 4;
        const json = 5;
    }
}