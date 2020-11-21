<?php

/**
 * Interface1 short summary.
 *
 * Interface1 description.
 *
 * @version 1.0
 * @author MikeCorner
 */
namespace Cimply\Interfaces {
    use \Cimply\App\Repository\Project\Enum\RootSettings;
    interface IProject
    {
        function getBaseDir();
        function getProject();
        function getNamespace();
        function getIndex();
        function getParams();
    }

}
