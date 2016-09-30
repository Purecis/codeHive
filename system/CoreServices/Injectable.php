<?php
namespace App\System;

abstract class Injectable{
    
    protected static $__modules = [];
    
    function __construct()
    {
        $class = get_called_class();
        if(!in_array($class, self::$__modules)){
            array_push(self::$__modules, $class);
            if(is_callable([$this, "__bootstrap"])){
                // using DI to call boostrap or other function
                $this->__bootstrap();
            }
        }
    }

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
}