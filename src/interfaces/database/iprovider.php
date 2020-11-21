<?php

/**
 * iprovider short summary.
 *
 * iprovider description.
 *
 * @version 1.0
 * @author Eckebrecht
 */
namespace Cimply\Interfaces\Database
{
    use \Cimply\Core\Database\Provider;
    interface IProvider
    {
        public function dbm(): ?Provider;
    }
}
