<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Repository\Report\Types
{
    /**
	 * CsvReport short summary.
	 *
	 * CsvReport description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
	use Cimply\Basics\Composition\Report\Report;
    use Cimply\Interfaces\IReport;
	class JsonReport extends Report implements IReport
	{

        #region Cimply\Basics\Interfaces\IReport Members

        /**
         *
         * @param $data
         */
        function formatData(...$data): void
        {
            $this->setData(json_encode($data[0]));
        }

        #endregion
    }
}