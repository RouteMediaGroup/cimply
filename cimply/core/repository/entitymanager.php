<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Repository
{
	/**
	 * EntityManager short summary.
	 *
	 * EntityManager description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
	class EntityManager
	{
        public final static function Cast($mainObject, $selfObject = self::class): self {
            return \Cimply\Core\Core::Cast($mainObject, $selfObject);
        }


	}
}