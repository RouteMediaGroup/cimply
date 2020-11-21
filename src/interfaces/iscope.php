<?php
namespace Cimply\Interfaces {
    interface IScope {
        function setScope($key = null, $var = null, $filter = null, $overwrite = false);
        function getScope($key);
    }
}