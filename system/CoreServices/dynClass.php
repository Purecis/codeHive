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
        if (!in_array($key, $this->__container)) {
            throw new Exception("Call to undefined method ".get_class($this)."::".$key."()");
        }
        $subject = $this->{$key};
        return call_user_func_array($subject, $params);
    }

    public function __get($key)
    {
        if (!in_array($key, $this->__container)) {
            return NULL;
        }
        return $this->{$key};
    }
    public function __set($key, $value)
    {
        array_push($this->__container, $key);
        $this->{$key} = $value;
        $this->__event->trigger("setter", [$key, $value]);
    }
    
    public function __unset($key)
    {
        if(($idx = array_search($key, $this->__container)) !== false) {
            unset($this->__container[$idx]);
        }
        unset($this->{$key});
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
            foreach($this->__container as $key){
                $callable($this->{$key}, $key);
            }
        }

        return get_object_vars($this);
    }

    public function get(){
        return get_object_vars($this);
    }
}
