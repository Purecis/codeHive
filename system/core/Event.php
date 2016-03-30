<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis Event Module.
 *
 * This class Control Event Requests
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Event
{
    /**
     * Variables.
     *
     * @var mixen
     */
    private static $events;

    // --------------------------------------------------------------------

    /**
     * event addListener.
     *
     * @param	string
     * @param	mixen
     */
    public static function addListener($event, $callback)
    {
        if (!is_callable($callback)) {
            $callback = function () use ($callback) {
                return $callback;
            };
        }

        if (!isset(self::$events[$event])) {
            self::$events[$event] = [];
        }
        array_push(self::$events[$event], $callback);

        return $callback;
    }

    // --------------------------------------------------------------------

    /**
     * event trigger.
     *
     * @return function
     */
    public static function trigger($event, $args = false)
    {
        $r = '';
        if (isset(self::$events[$event])) {
            foreach (self::$events[$event] as $c) {
                $r .= call_user_func_array($c, array(&Controller::$scope, $args));
            }
        }

        return $r;
    }
}
