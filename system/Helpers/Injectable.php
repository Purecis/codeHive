<?php
/**
 * Injectable allow us to use $this->moduleName
 */

namespace App\System;

abstract class Injectable{

    // use magic return as DI Implicit Binding
    function __get($name)
    {
        $class = "App\\";

        $case = Str::extractCase($name);
        
        if(sizeof($case) == 1){
            $class .= "System\\" . ucfirst($name);

        }else{
            // extract for ModuleNameContainer
            $class_path = [];
            $case_copy = $case;
            $container = array_pop($case_copy);
            $module = implode("", $case_copy);
            array_push($class_path, $container);
            array_push($class_path, $module);
            $path = $class . implode("\\", $class_path);
            
            // reversed extract as ContainerModuleName
            if(!class_exists($path)){
                $class_path = [];
                $case_copy = $case;
                $container = array_shift($case_copy);
                $module = implode("", $case_copy);
                array_push($class_path, $container);
                array_push($class_path, $module);
                $path = $class . implode("\\", $class_path);
            }

            $class = $path;
        }
        
        if(!class_exists($class)){
            return false;
        }

        return new $class;
    }

    function __call($callable, $arguments){
        $class = self::__get($callable);
        if($class)return call_user_func_array($class, $arguments);
        else return null;
    }
}