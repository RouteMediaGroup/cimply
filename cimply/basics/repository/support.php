<?php

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
        private $config = null;
        function __construct($config = []) {
            isset($this->config) ? : $this->config = $config;
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
            $result = (bool)RootSettings::isValidValue($keyValue) ? !(empty($this->config[$keyValue])) ? $this->config[$keyValue] : $this->config ?? NULL : RootSettings::GetValueList();
            return $result;
        }

        /**
         * use Project\Enum\SystemSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getSystemSettings(?string $keyValue = null) {
            $result = (bool)SystemSettings::isValidValue($keyValue) ? !(empty($this->config[RootSettings::SYSTEM][$keyValue])) ? $this->config[RootSettings::SYSTEM][$keyValue] : $this->config[RootSettings::SYSTEM] ?? NULL : $this->config[RootSettings::SYSTEM];
            return $result;
        }

        /**
         * use Project\Enum\AppSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getAppSettings(?string $keyValue = null) {
            $result = (bool)AppSettings::isValidValue($keyValue) ? !(empty($this->config[RootSettings::APP][$keyValue])) ? $this->config[RootSettings::APP][$keyValue] : $this->config[RootSettings::APP] ?? NULL : $this->config[RootSettings::APP];
            return $result;
        }

        /**
         * use Project\Enum\ScopeSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getScopeSettings(?string $keyValue = null) {
            $result = $this->config[$keyValue] ?? $this->config;
            return $result;
        }

        /**
         * use Project\Enum\RouteSettings for help
         * @param string $keyValue
         * @return mixed
         */
        public final function getRouteSettings(?string $keyValue) {
            $result = (bool)RouteSettings:: isValidValue($keyValue) ? $this->config[$keyValue] : NULL;
            return $result;
        }

        /**
         * @param string $keyValue
         * @return mixed
         */
        public final function getAssembly() {
            $result = $this->config['Assembly'] ?? null;
            return $result;
        }

        public final function getSettings($args = [], $key = null) {
            $result = \ArrayParser::FlattenArray(array_merge($args, $this->config), false);
            isset($key) ? $result = $result->{$key} ?? null : null;
            return $result;
        }

    }
}