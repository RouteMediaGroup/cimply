<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

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
	class Mapper implements ICast
	{
        use \Properties, \Cast;
        
        protected $default, $name, $engine, $collation, $datatype = [], $phptypes = [], $index = [], $mappingFiles;

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
         * Get the value of default
         */ 
        public function getDefault()
        {
            return $this->default;
        }

        /**
         * Get the value of name
         */ 
        public function getName()
        {
            return $this->name;
        }

        /**
         * Get the value of engine
         */ 
        public function getEngine()
        {
            return $this->engine;
        }

        /**
         * Get the value of collation
         */ 
        public function getCollation()
        {
            return $this->collation;
        }

        /**
         * Get the value of datatype
         */ 
        public function getDatatype()
        {
            return $this->datatype;
        }

        /**
         * Get the value of phptypes
         */ 
        public function getPhptypes()
        {
            return $this->phptypes;
        }

        /**
         * Get the value of index
         */ 
        public function getIndex()
        {
            return $this->index;
        }
        
        /**
         * Get the array of mapping files
         */ 
        public function getMappers()
        {
            return $this->mappingFiles;
        }
    }
}