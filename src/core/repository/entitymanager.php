<?php

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