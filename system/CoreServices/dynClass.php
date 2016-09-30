<?php
// Dinamic Class

namespace App\System;

class dynClass extends \stdClass
{
    // const to create from array, secound arg is filter function if exist

    private $__event;
    private $__container = [];

    public function __construct($array = NULL, $filter = NULL){
        $this->__event = new Event(Str::random());
        if($array){
            foreach($array as $key => $value){
                $this->{$key} = ($filter) ? call_user_func_array($filter, [$value, $key]) : $value;
            }
        }
    }

    public function __call($key, $params)
    {
        echo "calling..";
        if (!isset($this->__container[$key])) {
            throw new Exception("Call to undefined method ".get_class($this)."::".$key."()");
        }
        $subject = $this->__container[$key];
        return call_user_func_array($subject, $params);
    }

    public function __get($key)
    {
        if (!isset($this->__container[$key])) {
            return NULL;
        }
        return $this->__container[$key];
    }
    public function __set($key, $value)
    {
        $this->__container[$key] = $value;
        $this->__event->trigger("setter", [$key, $value]);
    }
    
    public function __unset($key)
    {
        unset($this->__container[$key]);
        $this->__event->trigger("remover", [$key]);
    }
    
    public function onSet($e){
        $this->__event->addListener("setter", $e);
    }
    public function onGet($e){
        $this->__event->addListener("getter", $e);
    }
    public function onDelete($e){
        $this->__event->addListener("remover", $e);
    }

    public function each($callable){
        if(is_callable($callable)){
            foreach($this->__container as $k => $v){
                $callable($v, $k);
            }
        }

        return $this->__container;
    }

    // register getter fillter
    // register setter caller EVENT
}
