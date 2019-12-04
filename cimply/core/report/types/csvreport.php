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
	class CsvReport extends Report implements IReport
	{
        #region Cimply\Basics\Interfaces\IReport Members

        /**
         *
         * @param  $data
         */
        function formatData(...$data): void
        {
            $lines = [];

            foreach($data[0] as $row) {
                $lines = implode(",", $row);
            }

            $this->setData(implode("\n", $lines));
        }

        #endregion
    }
}