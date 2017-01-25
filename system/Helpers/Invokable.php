<?php
namespace App\System;

abstract class Invokable extends Injectable
{
    public static $__namespace = "Invokable";

    private static $__invokable = [];


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
            $class .= static::$__namespace . "\\" . $invokable;

            // make (ClassName@Container.Module) to register it inside a invokable class
            $path = $invokable . (isset($module) ? "@" . $module : "");

            // check if class injected before or intialize it
            if(!isset($__invokable[$path])){
                $__invokable[$path] = (new $class);
            }

            // call injected class
            $cls->{$invokable} = $__invokable[$path];

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

            $params = self::extractFuncParams($injectable, $method);
            $params = array_map(function ($e) {
                return $e->type ? new $e->type : $e->value;
            }, $params);

            $cls->{$method} = call_user_func_array(array($injectable, $method), $params);
        }

        if (sizeof($args) == 1) {
            return $cls->{key($cls)};
        }
        
        return $cls;
    }


    // extract function arguments
    public static function extractFuncParams($class, $method)
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
