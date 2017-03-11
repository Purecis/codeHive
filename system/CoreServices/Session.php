<?php
/**
 * Session Manager.
 *
 * @category   codeHive Core
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.Session
 * @since      Class available since Release 1.0
 */
namespace App\System;

class Session
{
    private static $sessHash = '';
    public static function boot(){
        $hive = new Scope('config.hive');
        
        static::$sessHash = $hive->session ? $hive->session : '_';
    }
    
    /**
     * session get.
     *
     * @param	mixen   string/array/multiple arguments | session name
     * @return  mixen   array/string
     */
    public static function get()
    {
        $arguments = Loader::mergeArguments(func_get_args());
        $sessions = [];

        foreach ($arguments as $session) {
            if (!isset($_SESSION[static::$sessHash.$session])) {
                array_push($sessions, false);
            } else {
                array_push($sessions, $_SESSION[static::$sessHash.$session]);
            }
        }
        return sizeof($sessions) > 1 ? $sessions : $sessions[0];
    }
    
    /**
     * session exists.
     * will check all is exist or none
     *
     * @param	mixen   string/array/multiple arguments | session name
     * @return  bool
     */
    public static function exists()
    {
        $arguments = Loader::mergeArguments(func_get_args());

        foreach ($arguments as $session) {
            if (
                !isset($_SESSION[static::$sessHash.$session]) or
                $_SESSION[static::$sessHash.$session] == false
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * session set.
     * register array of sessions with values
     *
     * @param	array   key,value array to set
     * @return  void
     */
    public static function set()
    {
        $arguments = func_get_args();
        if(is_array($arguments[0])){
            foreach ($arguments[0] as $session => $value) {
                $_SESSION[static::$sessHash.$session] = $value;
            }
        }else{
            $_SESSION[static::$sessHash.$arguments[0]] = $arguments[1];
        }
    }

    /**
     * session remove.
     * remove all sessions
     *
     * @param	array 	str or array key value
     */
    public static function remove()
    {
        $arguments = func_get_args();
        $sessions = $arguments[0];
        $clear = isset($arguments[1]) ? true : false;

        if(is_array($sessions)){
            foreach ($sessions as $session) {
                unset($_SESSION[static::$sessHash.$session]);
            }
        }

        if($clear){
            @session_unset();
            @session_destroy();
        }
    }

    public static function id(){
        $sessId = func_get_arg(0);
        if($sessId){
            session_id($sessId);
        }
        return session_id();
    }

    // TODO : remote, will end a remote user session
    // TODO : drivers support (http://php.net/manual/en/function.session-set-save-handler.php)
}

/* End of file Session.php */
