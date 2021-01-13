<?php
namespace {   
    abstract class DateTimeEnhanced extends DateTime {
        public static function ReturnAdd(\DateInterval $interval)
        {
            $dt = clone self;
            $dt->add($interval);
            return $dt;
        }

        public static function ReturnSub(\DateInterval $interval)
        {
            $dt = clone self;
            $dt->sub($interval);
            return $dt;
        }
    }
}