<?php
/**
 * codeHive Core Initializer.
 *
 * @category   codeHive Core
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.codeHive
 * @since      Class available since Release 1.0
 */
namespace App\System;

/*
 * Register framework AutoLoaders
 */
require "_globals.php";
require "Scope.php";
require "Loader.php";

/*
 * class codeHive
 */
class codeHive
{

    /**
     * bootstrap codeHive framework.
     *
     * @return void
     */
    public static function boot()
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            throw new \Exception('The codeHive Framework v3.0 requires PHP version 5.4 or higher, (5.6) is Recomonded.');
        }
        
        // initialize sessions
        session_start();

        // defining config.version scope
        $version = new Scope('config.version');
        $version->major = '3';
        $version->minor = '0';
        $version->patch = '00';
        $version->code  = 'BETA';
        
        // defining config.app scope
        $hive = new Scope('config.hive');
        $hive->app          = "sample";
        $hive->glob          = "__global";
        $hive->container    = "apps";
        $hive->assets       = "assets";
        $hive->system       = "system";
        $hive->bootstrap    = "bootstrap.php";
        
        // overwrite hive defaults by sended arguments
        $hive->set(func_get_arg(0));
        
        $hive->app_path     = $hive->container . "/" . $hive->app;
        $hive->glob_path    = $hive->container . "/" . $hive->glob;
        $hive->version      = 'v.' . $version->major . '.' . $version->minor . '.' . $version->patch . ' ' . $version->code;
        $hive->packager     = 'http://codehive.purecis.com/package/';

        // Register System AutoLoaders
        Loader::register('App\\System\\', ':system/*/:class.php');

        // Register Model & Controller AutoLoaders
        Loader::register('App\\Model\\', [
            ':app_path/model/:class.php',
            ':glob_path/model/:class.php'
        ]);
        Loader::register('App\\Controller\\', [
            ':app_path/controller/:class.php',
            ':glob_path/controller/:class.php'
        ]);
        Loader::register('App\\Middleware\\', [
            ':app_path/middleware/:class.php',
            ':glob_path/middleware/:class.php'
        ]);
        Loader::register('App\\Directive\\', [
            ':app_path/directive/:class.php',
            ':glob_path/directive/:class.php'
        ]);
        Loader::register('App\\Driver\\', [
            ':app_path/driver/:class.php',
            ':glob_path/driver/:class.php'
        ]);

        // Register Modules autoloader
        Loader::register('App\\', [
            ':app_path/module/:path/:class/:class.:suffix.php',
            ':app_path/module/:path/:class.:suffix.php',
            ':app_path/module/:path/:class.php',
            ':glob_path/module/:path/:class/:class.:suffix.php',
            ':glob_path/module/:path/:class.:suffix.php',
            ':glob_path/module/:path/:class.php'
        ]);

        Loader::config();

        // check if cli and bootstrap app
        if (!CLI::access()) {
            if(!file_exists($hive->app_path. '/' . $hive->bootstrap)){
                // TODO : error triggers from codehive it self
                echo "<b>Error:</b> Application file (<b>" . $hive->app_path . "/" . $hive->bootstrap . "</b>) not exists.";
                exit;
            }
            Session::boot();
            Directive::boot();
            Loader::boot();
            require_once $hive->app_path. '/' . $hive->bootstrap;

            Route::trigger();
        }
    }
}

/* End of file codeHive.php */
/* Location: ./system/core/codeHive.php */
