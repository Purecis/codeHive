<?php
// Dinamic Class

namespace App\System;

abstract class dynable
{
    private static $__dynable;
    function __construct(){
        static::$__dynable = new dynClass;
    }
    public function __get($key)
    {
        return static::$__dynable->{$key};
    }
    public function __set($key, $value)
    {
        static::$__dynable->{$key} = $value;
    }
    
    public function __call($callable, $params)
    {
        return call_user_func_array(static::$__dynable->{$callable}, $params);
    }
}
