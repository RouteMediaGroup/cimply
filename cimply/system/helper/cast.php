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

    trait Cast {
        public static function Cull($mainObject, $selfObject = null, $searchNestedObjects = false) {
            $selfObject = $selfObject ?? static::class;

            if (!\is_string($selfObject) || $selfObject === '') {
                return null;
            }

            if ($mainObject instanceof $selfObject) {
                return $mainObject;
            }

            $values = [];
            if (\is_array($mainObject) || $mainObject instanceof \Traversable) {
                $values = (array)$mainObject;
            } elseif (\is_object($mainObject)) {
                $values = \get_object_vars($mainObject);
            }

            foreach ($values as $value) {
                if ($value instanceof $selfObject) {
                    return $value;
                }

                if ($searchNestedObjects && (\is_array($value) || \is_object($value) || $value instanceof \Traversable)) {
                    $nestedValue = static::Cull($value, $selfObject, true);
                    if ($nestedValue instanceof $selfObject) {
                        return $nestedValue;
                    }
                }
            }

            try {
                $reflection = new \ReflectionClass($selfObject);
                return $reflection->isInstantiable() ? $reflection->newInstance() : null;
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
