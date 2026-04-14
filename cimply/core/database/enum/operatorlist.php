<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Database\Enum
{
	/**
	 * OperatorList short summary.
	 *
	 * OperatorList description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
	abstract class OperatorList extends \Enum
	{
        const ON = 1;
        const SELECT = 2;
        const UPDATE = 3;
        const DELETE = 4;
        const FROM = 5;
        const WHERE = 6;
        const NULL = 0;
    }
}