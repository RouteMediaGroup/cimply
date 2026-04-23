<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace {   
    use Cimply\Core\Annotation\Annotation as Annotate;
    trait Annotation {

        public static function GetAnnotations($classObject = null): Annotate {
            $objectExpl = explode("::", $classObject);
            $className = $objectExpl[0] ?? null;
            $method = $objectExpl[1] ?? null;;
            try {
                $annotate = new Annotate($className, $method);
            } catch (\Throwable $e) {
                \Debug::VarDump($e->getMessage(), false);
                $annotate = new Annotate(static::class);
            }		
            return $annotate;
        }
    }
}
