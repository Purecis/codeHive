<?php
/**
 * Loader
 *
 * @category   codeHive Core
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.Loader
 * @since      Class available since Release 3.0
 */
namespace App\System;

class Loader
{
    /**
     * install
     *
     * @access public
     * @since release 3.0
     *
     * @param  string    $module
     */
    public static function packageInstall($package, $force = false)
    {
        $errors = [];
        $hive = new Scope('config.hive');

        $json = Request::fetch($hive->packager . $package);
        $json = json_decode($json);
        
        list($container, $module) = explode(".", $package);

        $module_path = $hive->app_path . "/module/" . $container . "/" . $module;
        
        if (file_exists($module_path)) {
            if (!$force) {
                echo "Already Exists. if you want to update try \033[32mhive update " . $package . "\033[0m or \033[32mhive reinstall " . $package . "\033[0m\n";
                return;
            }
            File::rmdirRecursive($module_path);
        }

        // create folder if not exists
        if (!file_exists($module_path)) {
            mkdir($module_path, 0755, true);
        }

        // check for git ..

        // check for zip ..
        if (isset($json->source->zip) && sizeof($json->source->zip) > 0) {
            foreach ($json->source->zip as $zip => $folder) {
                $saveTo = $module_path . "/" . basename($zip);

                echo "Downloading " . $zip . "...\n";

                Request::fetch($zip, $saveTo, function ($current, $total) {
                    $percent = $total == 0 ? 0 : round($current / $total * 100);
                    echo "\rReceiving objects: {$percent}% (". File::format($current). "/" . File::format($total) . ") done ...";
                }, function ($e) use (&$errors, $zip) {
                    echo "HTTP Error $e on $zip.";
                    array_push($errors, "HTTP Error {$e} on {$zip}.");
                });
                echo "\n\n";
                // return;

                if (file_exists($saveTo)) {
                    $extractTo = $module_path . ($folder == "_empty_" ? "" : "/" . $folder);

                    if (!file_exists($extractTo)) {
                        mkdir($extractTo, 0755, true);
                    }

                    // unzip the file
                    $zip = new \ZipArchive;
                    if ($zip->open($saveTo) === true) {
                        $zip->extractTo($extractTo);
                        $zip->close();
                        unlink($saveTo);
                    } else {
                        array_push($errors, "Can't open zip file, try to extract it manually on {$saveTo}.");
                    }
                }
            }
        }

        echo "\033[32m Module Located at : " . $module_path . "\033[0m";
        if (sizeof($errors)) {
            echo "\n\ncomplete with errors:\n";
            foreach ($errors as $err) {
                echo "\033[31m\t" . $err . "\n\033[0m";
            }
        } else {
            echo "\n\ncomplete .. \n";
            // now loader hive install
            // run package installer
        }
    }

    /**
     * check for dependencies and install them
     *
     * @param  string   $package
     * @return boolean
     */
    public static function packageDependencies($package = NULL) {

        // install dependancies and check if installed before
        // 
        
        if(!is_null($package)) {
            echo "hello dependency";
        }
    }

    // packageConfigure

    /**
     * check cli commands and inputs
     *
     * @return boolean
     */
     public static function CLI()
     {
         
         // check is php running from command line
         $is_cli = (substr(php_sapi_name(), 0, 3) == 'cli');
         if ($is_cli) {

            // check is php arguments
            if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == "_cli") {
                
                // remove first 2 arguments
                $_SERVER['argv'] = array_slice($_SERVER['argv'], 2);

                // run console
                if (class_exists("\App\Develop\Console")) {
                    new \App\Develop\Console;
                    echo "\n";
                } else {
                    echo "\033[32m you should install CLI first by run (php index.php install) on codeHive root \033[0m \n";
                }

            // install console
            } elseif (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == "install") {
                self::packageInstall("Develop.Console", true);
                echo "\n";
                
            // keep app runing if not console or install
            } else {
                $is_cli = false;
            }
         }
         return $is_cli;
     }
}
