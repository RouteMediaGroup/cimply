<?php

namespace
{
	/**
	 * ToDo: Beschreibung von Properties fehlt!
	 */
	trait Properties
	{
        /**
         * Summary of set
         * @param mixed $properties
         * @return Properties
         */

        protected static $staticProperties = null;

        final function set($properties = null, $hasCheckProperties = false) {
            foreach ((array)$properties as $attribute => $value)
            {
                property_exists(self::class, $attribute) || $hasCheckProperties ? $this->{$attribute} = $value : null;
            }
            self::onPropertyChanged();
            return $this;
        }

        final function setValue(string $key = null, $value = ''): void {
            isset($key) ? $this->{$key} = $value : null;
        }

        final function get($key = null) {
            return $this->{$key} ?? null;
        }
        
        final function getAttrByName(string $name) {
            return $this->get($name);
        }
        
        function onPropertyChanged() {}
        static function GetStaticProperty($key = null) {
            return self::$staticProperties->{$key} ?? self::$staticProperties;
        }
	}
}