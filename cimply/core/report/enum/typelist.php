<?php

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