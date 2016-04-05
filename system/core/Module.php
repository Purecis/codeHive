<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis Module Class.
 *
 * This class control all the request attrbutes and protect
 * them from known attac and injection
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Module
{
    /**
     * List of paths to load views from.
     *
     * @var array
     */
    protected static $modules = array();

    /**
     * BootStrap.
     */
    public static function __bootstrap()
    {
        global $config;
        self::import($config['modules']);
    }

    // --------------------------------------------------------------------

    /**
     * Use and Import Module.
     *
     * Defines Module and prepare to call bootstrap for each
     *
     * @param	mixen 	module or array of modules
     */
    public static function import($mod)
    {
        if (is_array($mod)) {
            foreach ($mod as $m) {
                if (!isset(self::$modules[$m])) {
                    self::register($m);
                }
            }
        } else {
            if (!isset(self::$modules[$mod])) {
                self::register($mod);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Register Module and initialize it by calling bootstrap.
     *
     * @param	string 	module name
     */
    private static function register($k)
    {
        global $config;

        $path = self::path($k, true);

        if (!$path) {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Missing Module', "{$k}");
            } else {
                die("Module <b>{$k}</b> not Found in {$path}.");
            }
        } else {
            self::$modules[$k] = array('dir' => dirname($path), 'version' => '1');
            require_once $path;
            $k = str_replace('.', '\\', $k);
            if (is_callable(array($k, '__bootstrap'))) {
                call_user_func(array($k, '__bootstrap'));
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Check if Module Used.
     *
     * @param	mixen 	module or array of modules
     *
     * @return bool
     */
    public static function used($mod)
    {
        if (is_array($mod)) {
            foreach ($mod as $m) {
                if (!isset(self::$modules[$m])) {
                    return false;
                }
            }

            return true;
        } else {
            if (isset(self::$modules[$mod])) {
                return true;
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Get Module Path.
     *
     * @param	mixen 	module or array of modules
     *
     * @return bool
     */
    public static function path($mod, $full = false)
    {
        if (isset(self::$modules[$mod])) {
            return self::$modules[$mod]['dir'];
        }

        global $config;
        if (strpos($mod, '.')) {
            $ex = explode('.', $mod);
            $file = end($ex);
        } else {
            $file = $mod;
        }

        $alias = array('module', 'hook', 'plugin', 'extension', 'component', 'element', 'service', 'helper', 'lib', 'kernel', 'space', 'package');
        foreach ($alias as $als) {
            $path = "{$config['app']}/{$als}/{$mod}/{$file}.php";
            if (is_file($path)) {
                break;
            }
            $path = "{$config['assets']}/{$als}/{$mod}/{$file}.php";
            if (is_file($path)) {
                break;
            }
            $path = "{$config['system']}/{$als}/{$mod}/{$file}.php";
            if (is_file($path)) {
                break;
            }
            $path = "{$config['system']}/{$als}/{$mod}.php";
            if (is_file($path)) {
                break;
            }
        }

        /*
        $path = "{$config['app']}/module/{$mod}/{$file}.php";
        if (!is_file($path)) {
            $path = "{$config['app']}/hook/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['app']}/plugin/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['app']}/extension/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['app']}/component/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['app']}/element/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['app']}/service/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['app']}/helper/{$mod}/{$file}.php";
        }

        if (!is_file($path)) {
            $path = "{$config['assets']}/extensions/{$mod}/{$file}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/module/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/hook/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/plugin/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/extension/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/component/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/element/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/service/{$mod}.php";
        }
        if (!is_file($path)) {
            $path = "{$config['system']}/helper/{$mod}.php";
        }
        */

        if (is_file($path)) {
            if ($full) {
                return $path;
            } else {
                return dirname($path);
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    public static function exist($mod, $full = false)
    {
        return (bool) self::path($mod, $full);
    }
    // --------------------------------------------------------------------

    /**
     * Shutdown.
     */
    public static function __shutdown()
    {
        global $config;
        foreach (self::$modules as $k => $v) {
            if (is_callable(array($k, '__shutdown'))) {
                call_user_func(array($k, '__shutdown'));
            }
        }
        if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            foreach (self::$modules as $k => $v) {
                Trace::message('Used Modules', "{$k}");
            }
        }
    }
}

/* End of file Module.php */
/* Location: ./system/core/Module.php */
