<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * codeHive Hooks class is an alias for Events.
 *
 * This class Control Event Requests
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Hook
{
    // --------------------------------------------------------------------

    /**
     * event addListener.
     *
     * @param	string (event name)
     * @param	mixen
     */
    public static function on($event, $callback)
    {
        return Event::addListener($event, $callback);
    }

    // --------------------------------------------------------------------

    /**
     * event trigger.
     *
     * @return function
     */
    public static function trigger($event, $args = false)
    {
        return Event::trigger($event, $args);
    }
}
