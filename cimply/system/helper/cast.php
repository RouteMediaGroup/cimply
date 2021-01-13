<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CIM
 *
 * @author MikeCorner
 */
namespace {

    trait Cast {
        /**
         * Summary of Cull
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @param mixed $abstractClass
         * @return mixed
         */
        public static function Cull($mainObject, $selfObject = null, $abstractClass = false) {
            $newObject = null;
            try {
                is_array($mainObject) ? null : ( ($mainObject instanceof $selfObject) ? $newObject = $mainObject :
                    ( (new ReflectionClass($selfObject))->isAbstract() ? $abstractClass = true : $newObject = new $selfObject ) );

                /*$newObject = static::recursiveWalker($newObject, $mainObject, $selfObject, $abstractClass);*/
                foreach((array)$mainObject as $value) {
                    ($value instanceof $selfObject) ?
                        $newObject = ($abstractClass === true) ? self::iterateAbstractClass($value, $selfObject) : $value : null;
                }
            }
            catch(\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }
            return $newObject;
        }

        /**
         * Summary of iterateAbstractClass
         * @param mixed $abstractClassClass
         * @param mixed $selfObject
         * @return mixed
         */
        private static function iterateAbstractClass($abstractClassClass, $selfObject) {
            $result = null;
            foreach($abstractClassClass as $key => $value) {
                (key($selfObject) == $key) ? $result = $value : null;
            }
            return $result;
        }

        /**
         * Summary of recursiveWalker
         * @param mixed $newObject
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @param mixed $abstractClass
         * @return mixed
         */
        private static function recursiveWalker(&$newObject, $mainObject, $selfObject, $abstractClass, $count = 0) {
            /*foreach((array)$mainObject as $value) {
            ($value instanceof $selfObject) ?
                $newObject = ($abstractClass === true) ? self::iterateAbstractClass($value, $selfObject) : $value : null;
            }*/
            try {
               foreach((array)$mainObject as $value) {
                    ($value instanceof $selfObject) ? 
                        ( $newObject = ($abstractClass === true) ? self::iterateAbstractClass($value, $selfObject) : $value )
                        : ( ( is_object($value) && $count<= 0 ) ? $newObject = self::recursiveWalker($newObject, $value, $selfObject, $abstractClass, $count++) : $value );
               }
            } catch(\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }

            return $newObject;
        }

    }
}
