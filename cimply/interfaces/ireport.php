<?php

/**
 * IReport short summary.
 *
 * IReport description.
 *
 * @version 1.0
 * @author MikeCorner
 */
namespace Cimply\Interfaces {
    interface IReport
    {
        public function formatData(...$data);
    }
}