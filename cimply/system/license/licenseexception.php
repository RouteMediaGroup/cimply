<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\System\License {
    class LicenseException extends \RuntimeException
    {
        public function __construct(string $message = '', int $code = 403, ?\Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }
}
