<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Traits {
    trait Cast {
        /**
         * Cast
         * @param mixed $mainObject Das Hauptobjekt, das "gecastet" werden soll.
         * @param mixed $selfObject Die Klasse, zu der das Objekt "gecastet" werden soll.
         * @return mixed Ein neues Objekt der Zielklasse, das die Eigenschaften des Hauptobjekts übernimmt.
         */
        public static function Cull($mainObject, $selfObject) {
            $instance = new $selfObject();
            foreach ($mainObject as $key => $value) {
                $instance->$key = $value;
            }
            return $instance;
        }
    }
}