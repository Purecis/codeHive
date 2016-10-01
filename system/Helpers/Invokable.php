<?php
namespace App\System;

abstract class Invokable
{
    public static $__namespace = "Invokable";

    public static function inject()
    {
        $args = func_get_args();
        $cls = new \stdClass;

        foreach ($args as $argument) {
            // init class namespace
            $class = "\App\\";
            
            // extracting module if exists

            
            if (Str::contains($argument, '@')) {
                list($invokable, $module) = explode("@", $argument);
                $class .= str_replace(".", "\\", $module) . "\\";
            } else {
                $invokable = $argument;
            }
            
            // complete injecting class namespace
            $class .= static::$__namespace . "\\" . $invokable;

            // call injected class
            $cls->{$invokable} = (new $class);

            // Use DI -> check module __get as DI + Implicit Binding here
        }
        
        if (sizeof($args) == 1) {
            return $cls->{key($cls)};
        }

        return $cls;
    }

    // Explicit Binding here
    public static function invoke()
    {
        // TODO : fix clusure callback

        $args = func_get_args();
        $cls = new \stdClass;

        foreach ($args as $argument) {
            $class = "\App\\";
            
            list($invokable, $method) = explode("::", $argument);
            if (Str::contains($method, '@')) {
                list($method, $module) = explode("@", $method);
                
                $class .= str_replace(".", "\\", $module) . "\\";
            }
            self::inject($invokable . (isset($module) ? "@" . $module : ""));

            $class .= static::$__namespace . "\\" . $invokable;

            $params = self::extractParams($class, $method);
            $params = array_map(function ($e) {
                return $e->type ? new $e->type : $e->value;
            }, $params);

            $cls->{$method} = call_user_func_array(array((new $class), $method), $params);
        }

        if (sizeof($args) == 1) {
            return $cls->{key($cls)};
        }
        
        return $cls;
    }

    public static function extractParams($class, $method)
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
