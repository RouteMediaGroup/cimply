<?php
namespace Cimply\Core\Model {
    class EntityBase
    {
        use \Properties, \Cast;
        public $table, $infoMessage = array(), $saveAble = true, $refresh = false;

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        static function Cast($mainObject, $selfObject = self::class): self {
            return static::Cull($mainObject, $selfObject, true);
        }
    }
}
