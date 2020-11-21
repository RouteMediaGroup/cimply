<?php

namespace Cimply\Interfaces\Database\Enum
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