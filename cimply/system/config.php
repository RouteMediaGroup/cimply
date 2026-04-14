<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\System {

    class Config {
        protected static $result = [], $conf, $hasValues = false;

        public function __construct($conf = [], $arr = []) {
            self::$conf = !empty($conf) ? \ArrayParser::MergeArrays($conf, $arr) : $arr;
        }

        public static function getConf($conf = [], $filter = null, $needle = null): ?array {
            if(isset($filter)) {
                $filters = explode("/", $filter);
                if(isset($filters[1])) {
                    self::$result = \ArrayParser::SearchArrayRecursive($filters, $conf);
                    self::$hasValues = isset($needle) ? \in_array($needle, (array)self::$result, true) : self::$result;
                    return self::$result;
                }
                return isset($conf[$filter]) ? $conf[$filter] : null;
            }
            return $conf;
        }

        public static function loader($configFile = [], $conf = []): array {
            try {
                if(!is_array($configFile)) {
                    throw new \Exception('Type of configFile is no array');
                }
                foreach($configFile as $newConf) {
                    if(is_file($newConf)) {
                        $conf = \YamlParser::AddYaml($conf, $newConf) ?? $conf;
                    }
                }
            } catch (\Throwable $ex) {
                //$this->logger->log(sprintf('Set Project: %s', $this->SystemConfig['project']), 'NOTICE');
                //$this->logger->log(sprintf('Message: "%s" Error on Line %s', $ex->getMessage(), $ex->getLine()), 'ERROR');
                \Debug::VarDump($ex);
            }
            return $conf;
        }
    }
}
