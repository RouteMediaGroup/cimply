<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\System {
    interface Settings {
        const SystemPath = __DIR__.DIRECTORY_SEPARATOR;
        const FrameworkPath = self::SystemPath.'..';
        const HelperPath = self::SystemPath.'helper';
        const VendorPath = self::SystemPath.'vendor';
        const TempDir = self::SystemPath.'tmp';
        const Assembly = [
            'Framework' => self::FrameworkPath,
            'Cim' => self::FrameworkPath.DIRECTORY_SEPARATOR.'cim',
            'System' => self::HelperPath,
            'Yaml' => self::VendorPath.DIRECTORY_SEPARATOR.'yaml',
            'Linq' => self::VendorPath.DIRECTORY_SEPARATOR.'linq'
        ];
    }
}
