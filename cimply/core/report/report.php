<?php

namespace Cimply\Core\Report
{
	/**
	 * Report short summary.
	 *
	 * Report description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    class Report
    {
        protected $data;

        protected function setData($data) {
            $this->data = $data;
        }

        public function getData()
        {
            return $this->data;
        }
    }
}