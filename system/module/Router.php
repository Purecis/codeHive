<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis Router Module.
 *
 * control URL Parameters
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Router
{
    /**
     * List of arrays.
     */
    protected static $callback;
    protected static $router;

    public static function refresh($to = false, $time = 0)
    {
        //$to = Request::domain(true,$to);
        die("<meta http-equiv='refresh' content='{$time}; url={$to}' />");

        return $to;
    }

    public static function get($name = 0)
    {
        $ret = false;
        $url = Request::parser();

        if (isset(self::$router[$url[0]])) {
            $rarr = self::$router[$url[0]];
        } else {
            $rarr = (isset(self::$router['_otherwise'])) ? self::$router['_otherwise'] : array();
        }
        if ($name == 'pagename') {
            $ret = $url[0];
        } else {
            if ($name !== 0 && sizeof($rarr) > 0) {
                $arrSearch = array_search($name, $rarr);
                if ($arrSearch !== false) {
                    if (isset($url[$arrSearch + 1])) {
                        $ret = $url[$arrSearch + 1];
                    }
                }
            }
        }

        return String::escape($ret);
    }

    public static function on($name, $val, $cb = false)
    {
        $name = urlencode($name);

        if ($cb != false) {
            self::$router[$name] = $val;
        } else {
            //self::$router[$name] = array();
            $cb = $val;
        }
        // TODO : parse pattern /url/:id (here and inside api)

        self::$callback[$name] = $cb;

        return self::$router;
    }

    public static function parse($arr)
    {
        if (!is_array($arr)) {
            $arr = array($arr);
        }
        $mod = self::get('pagename');
        self::$router[$mod] = $arr;
        $cls = new stdClass();
        if (isset(self::$router[$mod])) {
            foreach (self::$router[$mod] as $k) {
                $cls->$k = self::get($k);
            }
        }

        return $cls;
    }

    public static function otherwise($val, $cb = false)
    {
        if ($cb != false) {
            self::$router['_otherwise'] = array_merge($val, array('pagename'));
        } else {
            $cb = $val;
        }

        self::$callback['_otherwise'] = $cb;

        return self::$router;
    }

    public static function trigger($mod){

        $_exist = isset(self::$callback[$mod]) ? true : false;
        if($_exist){
            $cls = new stdClass();
            if (isset(self::$router[$mod])) {
                foreach (self::$router[$mod] as $k) {
                    $cls->$k = self::get($k);
                }
            }

            $cb = Controller::trigger(self::$callback[$mod], $cls);
            if (is_array($cb) || is_object($cb)) {
                $cb = json_encode($cb);
            }
            return $cb;
        }
    }

    public static function callback()
    {
        //$mod = Request::get('pagename');
        $mod = Request::parser(0);

        $_exist = isset(self::$callback[$mod]) ? true : false;
        if (!$_exist) {
            global $config;
            $path = APP_PATH."/controller/{$mod}.php";
            if (is_file($path)) {
                require_once $path;
                $_exist = isset(self::$callback[$mod]) ? true : false;
            }
        }

        if (!$_exist) {
            $mod = '_otherwise';
            $_exist = isset(self::$callback[$mod]) ? true : false;
        }

        echo self::trigger($mod);
        /*
        if ($_exist) {
            echo self::trigger();
        } else {
            // 404, 403...
            //post parser
            //get page with that name if not then search for post
            if (Module::used('post')) {
                post::parser();
            }
        }
        */
    }

    // call router emediatly if it the same, its good for directive check
    // to work emediatly without check otherwise again
    public static function is(){
        $args = func_get_args();
        $mod = Request::parser(0);
        if($mod == $args[0]){
            call_user_func_array(array('self','on'), $args);
            return self::trigger($args[0]);
        }
    }

    public static function __shutdown()
    {
        self::callback();
    }
}

/* End of file Router.php */
/* Location: ./system/module/Router.php */
