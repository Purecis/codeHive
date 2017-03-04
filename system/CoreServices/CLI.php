<?php
/**
 * CLI
 *
 * @category   codeHive Core
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.CLI
 * @since      Class available since Release 3.0
 */
namespace App\System;

class CLI
{

    /**
     * check cli commands and inputs
     *
     * @return boolean
     */
     public static function access()
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
                    echo "\033[32m You should Install CLI first by run (php index.php install) on codeHive root directory. \033[0m \n";
                }

            // install console
            } elseif (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == "install") {
                self::consoleInstall();
                echo "\n";
                
            // keep app runing if not console or install
            } else {
                $is_cli = false;
            }
         }
         return $is_cli;
     }

     /**
     * console install
     *
     * @access public
     * @since release 3.0
     *
     * @param  string    $module
     */
    public static function consoleInstall()
    {
        $errors = [];
        $hive = new Scope('config.hive');
        
        $json = Request::fetch($hive->packager . "Develop.Console");
        $json = json_decode($json);
        $json = $json->latest;
        
        $module_path = $hive->app_path . "/module/Develop/Console";
        
        // create folder if not exists
        FileSystem::mkdirRecursive($module_path);

        // check for zip ..
        if (isset($json->source->zip) && sizeof($json->source->zip) > 0) {
            foreach ($json->source->zip as $zip => $folder) {
                $saveTo = $module_path . "/" . basename($zip);

                echo "Downloading " . $zip . "...\n";

                Request::fetch($zip, $saveTo, function ($current, $total) {
                    $percent = $total == 0 ? 0 : round($current / $total * 100);
                    echo "\rReceiving objects: {$percent}% (". FileSystem::format($current). "/" . FileSystem::format($total) . ") done ...";
                }, function ($e) use (&$errors, $zip) {
                    echo "HTTP Error $e on $zip.";
                    array_push($errors, "HTTP Error {$e} on {$zip}.");
                });
                echo "\n\n";

                if (file_exists($saveTo)) {
                    $extractTo = $module_path . ($folder == "_empty_" ? "" : "/" . $folder);

                    FileSystem::mkdirRecursive($extractTo);

                    if(FileSystem::unzip($saveTo, $extractTo)){
                        unlink($saveTo);
                    }else{
                        array_push($errors, "Can't open zip file, try to extract it manually on {$saveTo}.");
                    }
                }
            }
        }

        if (sizeof($errors)) {
            echo "\n\ncomplete with errors:\n";
            foreach ($errors as $err) {
                echo "\033[31m\t" . $err . "\n\033[0m";
            }
            return false;
        } else {
            if (class_exists("\App\Develop\Console")) {
                \App\Develop\Console::installCLI();
            } else {
                echo "\033[31mUnknown Error while installing Console\n\033[0m";
            }
            return true;
        }
    }
}
