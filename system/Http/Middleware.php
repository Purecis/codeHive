<?php
namespace App\System;

class Middleware extends Invokable
{
    public static $__namespace = "Middleware";
    
    public static $middlewares = [];
    private $stack = [];
    private $index = null;

    public function __construct($stack = null)
    {
        $this->stack = $stack;
    }
    public function beginQueue()
    {
        if (sizeof($this->stack)) {
            $this->index = 0;
            return $this->queue();
        }
        return $this;
    }
    private function queue($a = null)
    {
        // echo $this->index . " : ";
        if (isset($this->stack[$this->index])) {
            $response = self::invoke($this->stack[$this->index] . "::handle");
            
            if ($response instanceof Middleware) {
                $this->index += 1;
                $response = $this->queue();
            }
            return $response;
        }
    }
    public function next()
    {
        return $this;
    }
}
