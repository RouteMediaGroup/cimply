<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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