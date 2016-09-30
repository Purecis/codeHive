<?php
/**
 * Scope is a Special IoC (Inversion of Control) Container, it help’s us to share data between classes and views in codeHive.
 *
 * @category   codeHive Core
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.Scope
 * @since      Class available since Release 3.0
 */
namespace App\System;

class Scope
{
    /**
     * global scope data
     *
     * @static
     * @var    array
     * @access private
     */
    public static $__scope_data = array();
    
    /**
     * current scope base
     *
     * @var    string
     * @access private
     */
    private $__scope_base = "global";

    /**
     * type of return value, Array when true and stdObject when false.
     *
     * @var    boolean
     * @access private
     */
    private $__scope_arrayonly = false;

    /**
     * Construct Scope Class
     *
     * @static
     * @access public
     * @since release 3.0
     *
     * @param  boolean  $scope  current base
     * @return void
     */
    public function __construct($scope = null)
    {
        if (!empty($scope)) {
            $this->__scope_base = $scope;
        }
    }

    /**
     * magic method setter
     *
     * @access public
     * @since release 3.0
     *
     * @param  string   $key
     * @param  mixed    $value
     * @return void
     */
    public function __set($key, $value)
    {
        self::set($key, $value);
    }

    /**
     * magic method getter
     *
     * @access public
     * @since release 3.0
     *
     * @param  string    $key
     * @return mixed value
     */
    public function __get($key)
    {
        return self::get($key);
    }

    /**
     * set scope variable
     *
     * @access public
     * @since release 3.0
     *
     * @param  string   $key
     * @param  mixed    $value
     * @return void
     */
    public function set($key, $value = null)
    {
        is_array($key) ? array_walk($key, function ($v, $k) {
            $this->set($k, $v);
        }) : $this->setArrayDeep(self::$__scope_data, $this->__scope_base . "." . $key, $value);
    }

    /**
     * get scope value
     *
     * @access public
     * @since release 3.0
     *
     * @param  string    $key
     * @return mixed value
     */
    public function get($key)
    {
        $get = $this->getArrayDeep(self::$__scope_data, $this->__scope_base . "." . $key);
        if (is_array($get)) {
            $json = json_encode($get);
            $get = json_decode($json, $this->__scope_arrayonly);
        }
        return $get;
    }

    /**
     * set or get scope base values as Array or stdObject
     *
     * @access public
     * @since release 3.0
     *
     * @param  string    $scope
     * @return mixed values
     */
    public function base($scope = null)
    {
        if ($scope) {
            $this->__scope_base = $scope;
        }
        return $this->getArrayDeep(self::$__scope_data, $this->__scope_base, true);
    }

    /**
     * get all registered scopes
     *
     * @access public
     * @since release 3.0
     *
     * @return mixed values
     */
    public function scopes()
    {
        return array_keys(self::$__scope_data);
    }

    /**
     * set return mode for getters, Array when true and stdObject when false.
     *
     * @access public
     * @since release 3.0
     *
     * @param  boolean    $state default true
     * @return void
     */
    public function arrayOnly($state = true)
    {
        $this->__scope_arrayonly = $state;
    }

    /**
     * real array deep setter by dots in key
     *
     * @access private
     * @since release 3.0
     *
     * @param  array     $array
     * @param  string    $keys
     * @param  mixed     $value
     * @return void
     */
    private function setArrayDeep(&$array, $keys, $value)
    {
        $keys = explode(".", $keys);
        $current =& $array;
        foreach ($keys as $idx => $key) {
            if (sizeof($keys) - 1 != $idx && $idx != 0) {
                $key = "_" . $key;
            }
            $current =& $current[$key];
        }
        if (is_array($current) && is_array($value)) {
            $current = array_merge($current, $value);
        } else {
            $current = $value;
        }
    }

    /**
     * real array deep getter by dots in key
     *
     * @access private
     * @since release 3.0
     *
     * @param  array     $array
     * @param  string    $key
     * @param  boolean   $base
     * @return mixed
     */
    private function getArrayDeep($array, $keys, $base = false)
    {
        $keys = explode(".", $keys);
        $current = $array;
        foreach ($keys as $idx => $key) {
            if (sizeof($keys) - 1 != $idx && $idx != 0 || ($base && (sizeof($keys) - 1 == $idx && sizeof($keys) != 1))) {
                $key = "_" . $key;
            }
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                return false;
            }
        }
        return $current;
    }
}

/* End of file Scope.php */
/* Location: ./system/code/Scope.php */
