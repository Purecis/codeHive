<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis Session Class.
 *
 * control Sessions With Advance Tecnique
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */

//if (!defined('SESSION_PERFIX'))define(SESSION_PERFIX,"");

class Session
{
    public static $SESSION_PERFIX;

    public function __construct()
    {
        self::$SESSION_PERFIX = defined(SESSION_PERFIX) ? SESSION_PERFIX : '';
    }
    /**
     * Session get.
     *
     * @param	mixen 	str or array keys only
     *
     * @return mixen
     */
    public static function get($sess)
    {
        if (!is_array($sess)) {
            $arr = explode(',', $sess);
        } else {
            $arr = $sess;
        }

        $cls = new stdClass();
        if (sizeof($arr) > 1) {
            foreach ($arr as $key => $val) {
                if (!isset($_SESSION[static::$SESSION_PERFIX.$val])) {
                    $cls->$val = false;
                } else {
                    $cls->$val = $_SESSION[static::$SESSION_PERFIX.$val];
                }
            }

            return $cls;
        } else {
            return isset($_SESSION[static::$SESSION_PERFIX.$arr[0]]) ? $_SESSION[static::$SESSION_PERFIX.$arr[0]] : false;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Session exist.
     *
     * will check all is exist or none
     *
     * @param	mixen 	str or array keys only
     *
     * @return bool
     */
    public static function exist($arr)
    {
        if (!is_array($arr)) {
            $arr = explode(',', $arr);
        }

        foreach ($arr as $key) {
            if (
                !isset($_SESSION[static::$SESSION_PERFIX.$key]) or
                $_SESSION[static::$SESSION_PERFIX.$key] == false
            ) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Session set.
     *
     * register array of sessions with values
     *
     * @param	array 	str or array key value
     */
    public static function set($arr)
    {
        foreach ($arr as $key => $val) {
            $_SESSION[static::$SESSION_PERFIX.$key] = $val;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Session remove.
     *
     * remove all sessions
     *
     * @param	array 	str or array key value
     */
    public static function remove($arr = array(), $all = false)
    {
        if (sizeof($arr) < 1 || $all) {
            @session_unset();
            @session_destroy();
        } else {
            foreach ($arr as $val) {
                unset($_SESSION[static::$SESSION_PERFIX.$val]);
            }
        }
    }
}

/* End of file Session.php */
/* Location: ./system/core/Session.php */
