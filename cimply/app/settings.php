<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\App {
    interface Settings extends \Cimply\System\Settings {
        const AppPath = __DIR__.DIRECTORY_SEPARATOR;
        const Projects = self::AppPath.'projects'.DIRECTORY_SEPARATOR;
        const ProjectPath = self::Projects.'%project%'.DIRECTORY_SEPARATOR;
    }
}
