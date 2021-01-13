<?php

namespace Cimply\Core\Model
{
	/**
	 * Router short summary.
	 *
	 * Router description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    use \Cimply\Core\{Core};
    use \Cimply\Interfaces\ICast;
	class Wrapper implements ICast
	{
        use \Properties, \Cast;
        
        protected $entities = [];

        function __construct() {}
        
        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        /**
         * Get the value of model
         */ 
        public function getModel($model = null): ?array
        {
            return $this->entities[$model] ?? $this->entities ?? null;
        }
    }
}