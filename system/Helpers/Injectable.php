<?php
/**
 * Injectable allow us to use $this->moduleName
 */

namespace App\System;

abstract class Injectable{
    
    protected static $__modules = [];

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
        
        return new $class;
    }

    function __call($callable, $arguments){
        if($callable == '__bootstrap'){
            return null; // this added to remove ERROR .. need more test to figerout what the real error was.
        }
        $class = self::__get($callable);
        if($class)return call_user_func_array($class, $arguments);
        else return null;
    }
}