<?php
namespace Cimply\Core {
    use \Cimply\Interfaces\{ICast};

    abstract class Core implements ICast {
        public static $fillable;

        /**
         * Cast a Object to Current Class-Object to itself
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        public static function Cast($mainObject, $selfObject = self::class) {
            $Cast = (ICast::Cull);
            return $Cast($mainObject, $selfObject);
        }

        /**
         * Summary of FillObjectFromStdClass
         * @param mixed $std
         * @param mixed $selfObject
         * @return object
         */
        public static function FillObjectFromStdClass($std, $selfObject = self::class)
        {
            $instance = clone($selfObject);
            foreach ( (array) $std as $attribute => $value)
            {
                self::fillableIsSetAndContainsAttribute($attribute) || self::fillableNotSet($selfObject) ? $instance->{$attribute} = $value : null;
            }
            return $instance;
        }

        /**
         * Returns if the fillable array exists and contains
         * the attributes requested.
         *
         * @param $attribute
         * @return bool
         */
        public static function fillableIsSetAndContainsAttribute($attribute): bool
        {
            return (isset(static::$fillable) && count(static::$fillable) > 0 && in_array($attribute, static::$fillable));
        }

        /**
         * Returns whether fillable attribute is not set.
         *
         * @return bool
         */
        public static function fillableNotSet($selfObject): bool
        {
            return ! isset($selfObject::$fillable);
        }

    }
}