<?php

/*
 * CIMPLY FrameWork V 1.1.0.1
 * Michael Eckebrecht <info@cimply.work>
 * Copyright (c) 2010 - 2017 RouteMedia. All rights reserved.
 */

/**
 * Description of IBasics
 * 
 * 
 * @author Michael Eckebrecht
 */

namespace Cimply\Interfaces {
    interface IBasics {
        function CalculateStorable();
        function Prologue();
        function Epilogue();
        function Execute();
    } 
}