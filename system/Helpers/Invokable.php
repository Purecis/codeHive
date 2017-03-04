<?php
namespace App\System;

abstract class Invokable extends Injectable
{
    public static $__namespace = "Invokable";

    private static $__invokable = [];

    function __construct()
    {
        $class = get_called_class();
        if(!in_array($class, self::$__modules)){
            array_push(self::$__modules, $class);
            if(is_callable([$this, "__bootstrap"])){
                // $this->__bootstrap();
                // using DI to call boostrap or other function
                self::__directInvoke($this, "__bootstrap");
            }
        }
    }

    // this will only inject the class to the system without calling any function
    public static function inject()
    {
        $args = func_get_args();
        $cls = new \stdClass;

        foreach ($args as $invokable) {
            // init class namespace
            $class = "\App\\";
            
            // when method exists then reparse path string (ClassName::Function@Container.Module)
            Str::contains($invokable, '::') && list($invokable, $method) = explode("::", $invokable);
                            
            if (isset($method) && Str::contains($method, '@')) {
                list($method, $module) = explode("@", $method);
            }

            // extracting module if exists (ClassName@Container.Module)
            else if (Str::contains($invokable, '@')) {
                list($invokable, $module) = explode("@", $invokable);                
            }

            // replace Module.Name to psr-4 path Module\\Name for autoloader
            isset($module) && $class .= str_replace(".", "\\", $module) . "\\";
            
            // complete injecting class namespace
            if(empty(static::$__namespace)){
                $class .= str_replace(".", "\\", $invokable);
            }else{
                $class .= static::$__namespace . "\\" . $invokable;
            }
                        
            // make (ClassName@Container.Module) to register it inside a invokable class
            $path = $invokable . (isset($module) ? "@" . $module : "");

            // check if class injected before or intialize it
            if(!isset(self::$__invokable[$path])){
                self::$__invokable[$path] = (new $class);
            }

            // call injected class
            $cls->{$invokable} = self::$__invokable[$path];

            // set __invokable_method if method exists
            isset($method) && $cls->{$invokable}->__invokable_method = $method;

            // Use DI -> check module __get as DI + Implicit Binding here
        }
        
        // of only one just return it
        if (sizeof($args) == 1) {
            return $cls->{key($cls)};
        }

        return $cls;
    }

    // Explicit Binding here ( this will call function inside a class in pattern ) ClassName::Function@Container.Module
    public static function invoke()
    {
        // TODO : fix clusure callback
        $args = func_get_args();
        $cls = new \stdClass;

        foreach ($args as $argument) {
            $injectable = self::inject($argument);
            $method = $injectable->__invokable_method;

            $cls->{$method} = self::__directInvoke($injectable, $method);
        }

        if (sizeof($args) == 1) {
            return $cls->{key($cls)};
        }
        
        return $cls;
    }

    public static function __directInvoke()
    {
        list($injectable, $method) = func_get_args();
        
        $params = self::__extractFuncParams($injectable, $method);
        $params = array_map(function ($e) {
            return $e->type ? new $e->type : $e->value;
        }, $params);
        
        return call_user_func_array(array($injectable, $method), $params);
    }


    // extract function arguments
    public static function __extractFuncParams($class, $method)
    {
        $arr = [];
        $parameters = (new \ReflectionMethod($class, $method))->getParameters();
        foreach ($parameters as $param) {
            $std = new \stdClass;
            $std->name = $param->getName();
            $std->isOptional = $param->isOptional();
            $std->value = $param->isOptional() ? $param->getDefaultValue() : null;

            $export = \ReflectionParameter::export([$class, $method], $std->name, true);
            preg_match_all('/.+\[.+\>\s+(\S+).+\$.+\]/', $export, $matches);
            $std->type = isset($matches[1][0]) ? $matches[1][0] : null;
            array_push($arr, $std);
        }
        return $arr;
    }
}
