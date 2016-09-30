<?php
// Dinamic Class

namespace App\System;

abstract class dynClusure
{
    private $request;

    public function __get($key)
    {
        return $this->request->{$key};
    }
    
    public function __call($callable, $params)
    {
        return call_user_func_array($this->request->{$callable}, $params);
    }

    public function __apply($request){
        $this->request = $request;
    }
}
