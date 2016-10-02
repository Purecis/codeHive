<?php 
namespace App\System;

class Event
{
    /**
     * Variables.
     *
     * @var mixen
     */
    protected static $events = [
        "global" => []
    ];

    protected $current = "global";

    public function __construct($name = null)
    {
        if ($name) {
            $this->current = $name;
            self::$events[$name] = [];
        }
    }

    /**
     * event addListener.
     *
     * @param	string
     * @param	mixen
     */
    public function addListener($event, $callback)
    {
        if (!is_callable($callback)) {
            $callback = function () use ($callback) {
                return $callback;
            };
        }
        if (!isset(self::$events[$this->current][$event])) {
            self::$events[$this->current][$event] = [];
        }
        array_push(self::$events[$this->current][$event], $callback);

        return $callback;
    }

    // --------------------------------------------------------------------

    /**
     * event trigger.
     *
     * @return function
     */
    public function trigger($event, $args = array())
    {
        // TODO : need to do generators here
        $return = '';
        if (isset(self::$events[$this->current]) && isset(self::$events[$this->current][$event])) {
            foreach (self::$events[$this->current][$event] as $c) {
                $return .= call_user_func_array($c, $args);
            }
        }

        return $return;
    }
}