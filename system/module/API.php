<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis API Module.
 *
 * This class Control API Requests
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class API
{
    /**
     * Variables.
     *
     * @var mixen
     */
    private static $apis;
    public static $route = 'api';

    // --------------------------------------------------------------------

    /**
     * api bootstrap.
     */
    public static function __bootstrap()
    {
        Module::import('Router');
        // todo .. create function to change the api main url
        Router::on(static::$route, array('fire', 'do', 'token'), function (&$scope, $router) {
            global $config;
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                $config['ENVIRONMENT'] = 'development';
            }

            $name = $router->fire;
            $path = APP_PATH."/controller/api.{$name}.php";
            if (!is_file($path)) {
                $path = APP_PATH."/controller/{$name}.php";
            }
            if (!is_file($path)) {
                $path = APP_PATH."/controller/api.php";
            }

            // search in module controller
            if (!isset(self::$apis[$router->fire]) && !is_file($path)) {
                $mod = Module::path($router->fire);

                $name = $router->do;
                $path = "{$mod}/controller/api.{$name}.php";
                if (!is_file($path)) {
                    $path = "{$mod}/controller/{$name}.php";
                }
                if (!is_file($path)) {
                    $a = explode('.', $router->fire);
                    $name = end($a);
                    $path = "{$mod}/controller/api.{$name}.php";
                }
                if (!is_file($path)) {
                    $path = "{$mod}/controller/{$name}.php";
                }
                if (!is_file($path)) {
                    $path = "{$mod}/controller/api.php";
                }
            }


            // search in module controller
            if (!isset(self::$apis[$router->fire]) && !is_file($path)) {
                $mod = Module::path($router->fire);
                $name = $router->do;
                $path = "{$mod}/controller/api.{$name}.php";
                if (!is_file($path)) {
                    $path = "{$mod}/controller/{$name}.php";
                }
                if (!is_file($path)) {
                    $path = "{$mod}/controller/api.php";
                }
                if (is_file($path)) {
                    ++self::$current;
                }
            }

            if (is_file($path)) {
                require_once $path;
            }
            if (isset(self::$apis[$name])) {
                //check is callable
                return call_user_func_array(self::$apis[$name], array(&$scope, $router));
            }
        });
    }

    // --------------------------------------------------------------------

    /**
     * api register.
     *
     * @return function
     */
    public static function register($api, $callback)
    {
        self::$apis[$api] = $callback;

        return $callback;
    }

    // --------------------------------------------------------------------

    /**
     * api router.
     *
     * @return array
     */
    public static function router($args, $minus = false)
    {
        if (!is_array($args)) {
            $args = array($args);
        }
        if (!$minus) {
            --self::$current;
        }

        $arr = array();
        for ($i = 1; $i <= self::$current; ++$i) {
            array_push($arr, "api-{$i}");
        }
        foreach ($args as $key) {
            array_push($arr, $key);
        }

        return Router::parse($arr);
    }

    /**
     * api then.
     *
     * @return array
     *
     * @todo  creating scope, router, multi call in same scope
     */
    private static $current = 1;
    public static function then($a, $b = false, $c = false, $method = true)
    {
        if (is_object($c)) {
            $callback = $c;
            $args = $b;
            $text = $a;
        }
        if (is_object($b)) {
            $callback = $b;
            if (is_array($a)) {
                $args = $a;
                $text = false;
            } else {
                // todo : split string and check for :id arguments
                $args = array();
                $text = $a;
            }
        }
        if (is_object($a)) {
            $callback = $a;
            $args = array();
            $text = false;
        }else{
            // TODO : fix pattern /url/:id pattern
            // if ( preg_match_all("(:\w+)", $text, $matches) ) {
            //     $temp = [];
            //     foreach($matches[0] as $match){
            //         array_push($temp, ltrim($match, ':'));
            //     }
            // }
            // array_merge($temp, $args);
            // $text = explode("/", $text);
            // $text = $text[0];
        }

        if ($text == false) {
            --self::$current;
        }

        $router = self::router($args, true);
        $me = 'api-'.(self::$current);

        if (
            (($router->$me == $text) || ($text === false)) && $method
        ) {
            ++self::$current;
            $cb = call_user_func_array($callback, array(&Controller::$scope, $router));
            if (is_array($cb) || is_object($cb)) {
                $cb = json_encode($cb, JSON_NUMERIC_CHECK);
            }
            echo $cb;
            exit;
        } else {
            if ($text == false) {
                ++self::$current;
            }
        }
    }

    public static function group($text, $args = false, $callback = false)
    {
        return self::then($text, $args, $callback);
    }

    public static function get($text, $args = false, $callback = false)
    {
        return self::then($text, $args, $callback, Request::method() == 'GET');
    }

    public static function post($text, $args = false, $callback = false)
    {
        return self::then($text, $args, $callback, Request::method() == 'POST');
    }

    public static function delete($text, $args = false, $callback = false)
    {
        return self::then($text, $args, $callback, Request::method() == 'DELETE');
    }

    public static function put($text, $args = false, $callback = false)
    {
        return self::then($text, $args, $callback, Request::method() == 'PUT');
    }

    // --------------------------------------------------------------------

    /**
     * api access : check token if send to make query.
     *
     * @return bool
     */
    public static function access($name = 'token')
    {
        global $config;
        $token = Request::get($name);

        return $token == md5($config['license']['key'].$config['license']['secret']);
    }

    // --------------------------------------------------------------------

    /**
     * accessDenied.
     *
     * @param 	string
     *
     * @return string
     */
    public static function accessDenied($err = 'Access Denied', $code = false)
    {
        $cls = new stdClass();
        $cls->status = false;
        $cls->error = $err;
        if ($code) {
            $cls->code = $code;
        }

        return $cls;
    }
}
