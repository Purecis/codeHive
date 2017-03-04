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
            // check 5 or greater means the module name has more than one capital letter
            $prefix = [];
            $suffix = [];
            if(sizeof($case) >= 5){
                array_push($suffix, array_pop($case));
                array_push($suffix, array_pop($case));
                $suffix = array_reverse($suffix);
            }
            array_push($prefix, array_shift($case));
            array_push($prefix, implode("", $case));

            $case = array_merge($prefix, $suffix);

            $class .= implode("\\", $case);
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