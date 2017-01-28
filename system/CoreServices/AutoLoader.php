<?php
/**
 * AutoLoader
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

require_once "system/Helpers/Str.php";

class AutoLoader
{
    /**
     * register new PSR-4 Autoloader
     *
     * @access public
     * @since release 3.0
     *
     * @param  string           $prefix
     * @param  string|array     $patterns
     * @return void
     */
    public static function register($prefix, $patterns)
    {

        // register the autoloader
        spl_autoload_register(function ($class) use ($prefix, $patterns) {

            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // no, move to the next registered autoloader
                return;
            }
            
            // get the relative class name
            $relative_class = substr($class, $len);
            $relative_class = str_replace('\\', '/', $relative_class);

            // get class name
            $explode = explode("/", $relative_class);
            $class_name = end($explode);
            array_pop($explode);
            $relative_path = implode("/", $explode);

            $hive = new Scope('config.hive');

            // convert patterns to array
            if (is_string($patterns)) {
                $patterns = [$patterns];
            }
            
            // loop and load patterns
            foreach ($patterns as $pattern) {
                $pattern = Str::bindSyntax($pattern, [
                    "path"      => $relative_path,
                    "all"       => $relative_class,
                    "class"     => $class_name,
                    "system"    => $hive->system,
                    "container" => $hive->container,
                    "assets"    => $hive->assets,
                    "app"       => $hive->app,
                    "app_path"  => $hive->app_path
                ], 'colon', 'same');

                $pattern = str_replace(
                    ["Model", "Controller", "Interface", "Middleware", "Directive"],
                    ['model', "controller", "interface", "middleware", "directive"],
                    $pattern);
                
                $classes = glob($pattern);
                if (sizeof($classes)) {
                    foreach ($classes as $class) {

                        // checking for interface
                        $interface = str_replace('.class.php', ".interface.php", $class);
                        if (file_exists($interface)) {
                            require_once $interface;
                        }

                        require_once $class;
                        // TODO: register class to shutdown it later
                        // search in __global folder 
                    }
                }
            }
        });
    }

    /**
     * load app config
     * load all files extensions .boot.php and .router.php
     *
     * @access public
     * @since release 3.0
     *
     * @return void
     */
    public static function boot()
    {
        $hive = new Scope('config.hive');
        $scope = new Scope('config');

        $config = glob($hive->app_path . "/config/*.php");
        foreach ($config as $class) {
            $scope->set("_" . basename($class, ".php"), require_once $class);
        }

        // boot files
        $boot = array_merge(
            glob($hive->app_path . "/controller/*.boot.php"),
            glob($hive->app_path . "/module/*/*/*.boot.php"),
            glob($hive->app_path . "/module/*/*/*.router.php")
            // adding __global check here too
        );
        foreach ($boot as $class) {
            require_once $class;
        }
    }
}

// if (version_compare(PHP_VERSION, '7', '<')) {
//     set_error_handler(function ($ErrLevel, $ErrMessage) {
//         if ($ErrLevel == E_RECOVERABLE_ERROR) {
//             if(preg_match('/^Argument \d+ passed to (?:\w+::)?\w+\(\) must be an instance of (\w+), (\w+) given/', $ErrMessage, $matches)){
//                 print_r($matches);
//                 if(!isset($matches[1]))return true;
//                 if($matches[1] == "int")$matches[1] = "integer";
//                 if($matches[1] == "bool")$matches[1] = "boolean";
//                 return strtolower($matches[1]) == ($matches[2] == 'double' ? 'float' : $matches[2]);
//             }
//         }
//     });
// }
