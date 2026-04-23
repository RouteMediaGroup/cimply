<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Interfaces {
    interface IAssembly {
        function __construct(...$args);
        function Init(?\Cimply\Basics\ServiceLocator\ServiceLocator $services = null, $viewModel = null);
        function References($loader = null, $usings = null);
    }
}
