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
        // TODO : pass argument to middleware (split by : )
        if (isset($this->stack[$this->index])) {
            $callable = explode("@", $this->stack[$this->index]);
            $response = self::invoke($callable[0] . "::handle" . (isset($callable[1]) ? "@" . $callable[1] : ""));
            
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
