<?php
namespace Cimply\Interfaces {
    interface IAssembly {
        function __construct(...$args);
        function Init(\Cimply\Basics\ServiceLocator\ServiceLocator $services = null, $viewModel = null);
        function References($loader = null, $usings = null);
    }
}