<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace
{
    trait Properties
    {
        protected static $staticProperties = null;
        private array $propertyBag = [];

        final public function set($properties = null, $hasCheckProperties = false): static
        {
            foreach ((array)$properties as $attribute => $value) {
                $attribute = (string)$attribute;
                if ($attribute === '') {
                    continue;
                }

                $declaredProperty = $this->resolveDeclaredProperty($attribute);
                if ($declaredProperty !== null) {
                    $this->{$declaredProperty} = $value;
                    continue;
                }

                if ($hasCheckProperties) {
                    $this->storePropertyValue($attribute, $value);
                }
            }

            $this->onPropertyChanged();

            return $this;
        }

        final public function setValue(?string $key = null, $value = ''): void
        {
            if ($key === null || $key === '') {
                return;
            }

            $declaredProperty = $this->resolveDeclaredProperty($key);
            if ($declaredProperty !== null) {
                $this->{$declaredProperty} = $value;
                return;
            }

            $this->storePropertyValue($key, $value);
        }

        final public function get($key = null)
        {
            if ($key === null || $key === '') {
                return $this;
            }

            $declaredProperty = $this->resolveDeclaredProperty((string)$key);
            if ($declaredProperty !== null) {
                return $this->{$declaredProperty} ?? null;
            }

            return $this->propertyBag[(string)$key] ?? $this->propertyBag[self::normalizePropertyKey((string)$key)] ?? null;
        }

        final public function getAttrByName(string $name)
        {
            return $this->get($name);
        }

        public function __set(string $name, mixed $value): void
        {
            $this->setValue($name, $value);
        }

        public function __get(string $name): mixed
        {
            return $this->get($name);
        }

        public function __isset(string $name): bool
        {
            $declaredProperty = $this->resolveDeclaredProperty($name);
            if ($declaredProperty !== null) {
                return isset($this->{$declaredProperty});
            }

            $normalized = self::normalizePropertyKey($name);

            return array_key_exists($name, $this->propertyBag) || array_key_exists($normalized, $this->propertyBag);
        }

        public function __unset(string $name): void
        {
            unset($this->propertyBag[$name], $this->propertyBag[self::normalizePropertyKey($name)]);
        }

        public function onPropertyChanged(): void
        {
        }

        public static function GetStaticProperty($key = null)
        {
            $staticProperties = static::$staticProperties;
            if ($key === null) {
                return $staticProperties;
            }

            if ($staticProperties === null) {
                return null;
            }

            if (\is_object($staticProperties) && \method_exists($staticProperties, 'get')) {
                return $staticProperties->get($key);
            }

            if (\is_object($staticProperties)) {
                return $staticProperties->{$key} ?? $staticProperties->{self::normalizePropertyKey((string)$key)} ?? null;
            }

            if (\is_array($staticProperties)) {
                return $staticProperties[$key] ?? $staticProperties[self::normalizePropertyKey((string)$key)] ?? null;
            }

            return null;
        }

        private function resolveDeclaredProperty(string $attribute): ?string
        {
            if (property_exists($this, $attribute)) {
                return $attribute;
            }

            $normalized = self::normalizePropertyKey($attribute);
            foreach (array_keys(get_class_vars(static::class)) as $property) {
                if (self::normalizePropertyKey((string)$property) === $normalized) {
                    return (string)$property;
                }
            }

            return null;
        }

        private function storePropertyValue(string $key, mixed $value): void
        {
            $this->propertyBag[$key] = $value;
            $this->propertyBag[self::normalizePropertyKey($key)] = $value;
        }

        private static function normalizePropertyKey(string $key): string
        {
            $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower($key));
            return $normalized !== null ? $normalized : strtolower($key);
        }
    }
}
