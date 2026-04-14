<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Basics\Repository
{
	/**
	 * Project short summary.
	 *
	 * Project description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    use \Cimply\Core\{Core};
    use \Cimply\Interfaces\Support\Enum\{RootSettings, AppSettings, SystemSettings, ScopeSettings, RouteSettings};

	class Support
	{
        use \Cast;
        private mixed $config = null;

        public function __construct($config = []) {
            if ($this->config === null) {
                $this->config = $config;
            }
        }

        public final static function Cast($mainObject, $selfObject = self::class): self {
            return static::Cull($mainObject, $selfObject);
        }

        /**
         * use Project\Enum\RootSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getRootSettings(?string $keyValue = null) {
            if ($keyValue === null) {
                return $this->config;
            }

            return $this->getValue((array)$this->config, $keyValue);
        }

        /**
         * use Project\Enum\SystemSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getSystemSettings(?string $keyValue = null) {
            $config = (array)$this->getValue((array)$this->config, RootSettings::SYSTEM);
            return $keyValue !== null ? $this->getValue($config, $keyValue) : $config;
        }

        /**
         * use Project\Enum\AppSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getAppSettings(?string $keyValue = null) {
            $config = (array)$this->getValue((array)$this->config, RootSettings::APP);
            return $keyValue !== null ? $this->getValue($config, $keyValue) : $config;
        }

        /**
         * use Project\Enum\ScopeSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getScopeSettings(?string $keyValue = null) {
            return $keyValue !== null ? $this->getValue((array)$this->config, $keyValue) : $this->config;
        }

        /**
         * use Project\Enum\RouteSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getRouteSettings(?string $keyValue) {
            return $keyValue !== null ? $this->getValue((array)$this->config, $keyValue) : null;
        }

        /**
         * @param string $keyValue
         * @return mixed
         */
        public final function getAssembly() {
            return $this->getValue((array)$this->config, 'Assembly');
        }

        public final function getSettings($args = [], $key = null) {
            $result = \ArrayParser::FlattenArray(array_merge((array)$args, (array)$this->config), false);
            return $key !== null ? $this->getValue($result, $key) : $result;
        }

        private function getValue(array|object|null $source, string $key): mixed
        {
            $source = $this->normalizeSource($source);

            if (array_key_exists($key, $source)) {
                return $source[$key];
            }

            $normalizedNeedle = $this->normalizeKey($key);
            foreach ($source as $candidateKey => $value) {
                if ($this->normalizeKey((string)$candidateKey) === $normalizedNeedle) {
                    return $value;
                }
            }

            return null;
        }

        private function normalizeSource(array|object|null $source): array
        {
            if (\is_array($source)) {
                return $source;
            }

            if (\is_object($source)) {
                return \get_object_vars($source);
            }

            return [];
        }

        private function normalizeKey(string $key): string
        {
            $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower($key));
            return $normalized !== null ? $normalized : strtolower($key);
        }
    }
}
