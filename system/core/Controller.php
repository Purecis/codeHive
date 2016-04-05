<?php

defined('VERSION') or exit('Direct access to this location is not permitted');

/**
 * codeHive Controller.
 *
 * Controller class provide the framework module based infrastructure
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/Controller
 * @since      File available since Release 2.0.0
 *
 * @version    V: 2.1.0
 */
class Controller
{
    /**
     * List on predefined controllers.
     */
    protected static $controllers = array();

    /**
     * application object (the owner of application variables and functions).
     */
    public static $scope; // TODO:30 : multiple scopes per view with its childs

    /**
     * codeHive Controller class constructor.
     *
     * initialize default controllers
     */
    public static function __bootstrap()
    {
        global $config;
        foreach (glob("{$config['app']}/controller/*.bootstrap.php") as $filename) {
            require_once $filename;
        }
        foreach (glob("{$config['app']}/controller/*.init.php") as $filename) {
            require_once $filename;
        }
    }

    /**
     * append array to the Scope.
     *
     * @param array $arr key:cvalue
     *
     * @return object Scope
     */
    public static function scope($arr = array())
    {
        if (empty(self::$scope)) {
            self::$scope = new stdClass();
        }
        foreach ($arr as $key => $value) {
            self::$scope->$key = $value;
        }

        return self::$scope;
    }

    /**
     * register new controller.
     *
     * @param string $name controller name
     * @param mixen  $fn   callback
     *
     * @return array Controllers
     */
    public static function register($name, $fn)
    {
        $name = urlencode($name);

        self::$controllers[$name] = $fn;

        return self::$controllers;
    }

    /**
     * trigger controller by name and send args to callback.
     *
     * @param string $name controller name
     * @param array  $arg  callback arguments
     */
    public static function trigger($name, $arg = null)
    {
        global $config;

        if (empty(self::$scope)) {
            self::$scope = new stdClass();
        }

        if (is_string($name)) {
            if (array_key_exists($name, self::$controllers)) {
                $name = self::$controllers[$name];
            } else {
                $path = "{$config['app']}/controller/{$name}.php";
                if (is_file($path)) {
                    require_once $path;
                }
            }
            if (array_key_exists($name, self::$controllers)) {
                $name = self::$controllers[$name];
            }
        }
        if (is_array($name)) {
            $path = "{$config['app']}/controller/{$name[0]}.php";
            if (is_file($path)) {
                require_once $path;
            }
            $name[0] = str_replace('.', '\\', $name[0]);
        }

        if (is_callable($name)) {
            return call_user_func_array($name, array(&self::$scope, $arg));
        } else {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Missing Controller', $name);
            } else {
                die("Controller <b>{$name}</b> not Found.");
            }
        }
    }
}

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */
