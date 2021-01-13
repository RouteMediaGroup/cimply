<?php

namespace Cimply\Interfaces\Support
{
	/**
     * Description of IConnect
     *
     *
     * @author Michael Eckebrecht
     */
	interface IFactory {
        function execute($stringElement = null): void;
    }
}