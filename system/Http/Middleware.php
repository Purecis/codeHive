<?php
namespace App\System;

class Middleware extends Invokable
{
    public static $__namespace = "Middleware";
    
    public static $middlewares = [];
    public static $arguments = [];
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

    /**
     * middleware queue
     * Usage: Mid@My.Module(some, arguments)
     *        Mid(some, arguments)
     *
     * @return Response | Middleware
     */
    private function queue()
    {
        if (isset($this->stack[$this->index])) {
            $re = '/([^@\n\(]+)(?:@)*([^\(\n]*)(?:\((.+)\))?/';
            preg_match($re, $this->stack[$this->index], $matches);
            $class     = Str::contains($matches[1], '::') ? $matches[1] : $matches[1] . '::handle';
            $module   = !empty($matches[2]) ? $matches[2] : null;
            self::$arguments = isset($matches[3]) ? array_map(function ($e) {
                return trim($e);
            }, explode(',', $matches[3])) : [];

            $response = self::invoke($class . ($module ? "@" . $module : ""));
            if ($response instanceof Middleware) {
                $this->index += 1;
                $response = $this->queue();
            }
            return $response;
        }
        return $this;
    }

    public function next()
    {
        return $this;
    }
    
    public function arguments()
    {
        return self::$arguments;
    }
}
