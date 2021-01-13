<?php

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
	class HtmlReport extends Report implements IReport
	{

        #region Cimply\Basics\Interfaces\IReport Members

        /**
         *
         * @param $data
         */
        function formatData(...$data): void
        {
            $this->setData(htmlentities($data[0]));
        }

        #endregion
    }
}