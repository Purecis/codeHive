<?php

defined('VERSION') or exit('Direct access to this location is not permitted');

/**
 * codeHive CLI.
 *
 * Command Line Interface.
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/CLI
 * @since      File available since Release 2.1.0
 *
 * @version    V: 2.1.0
 */
class CLI
{
    /**
     * progressbar text.
     * ██▓▒░-
     *
     */
     public static $progressbar = [
         "╢",      // start
         "█",      // fill
         "░",      // empty
         "",      // splitter
         "╟",      // end
         "        "// "in %d sec..." // time message
     ];

     /**
      * cli colors.
      */
     public static $color = [
         "lightgray"    => "\033[97m",
         "green"        => "\033[32m",
         "default"      => "\033[0m",
         "blink"        => "\e[5m"
     ];

     public static $brand = "\033[97m
       ██████╗ ██████╗ ██████╗ ███████╗██╗  ██╗██╗██╗   ██╗███████╗
      ██╔════╝██╔═══██╗██╔══██╗██╔════╝██║  ██║██║██║   ██║██╔════╝
      ██║     ██║   ██║██║  ██║█████╗  ███████║██║██║   ██║█████╗
      ██║     ██║   ██║██║  ██║██╔══╝  ██╔══██║██║╚██╗ ██╔╝██╔══╝
      ╚██████╗╚██████╔╝██████╔╝███████╗██║  ██║██║ ╚████╔╝ ███████╗
       ╚═════╝ ╚═════╝ ╚═════╝ ╚══════╝╚═╝  ╚═╝╚═╝  ╚═══╝  ╚══════╝
     \033[0m\n";

    /**
     * get the .
     *
     * @param	string 	$link
     * @param	string 	$target
     * @param	string 	$progress
     *
     * @return content
     */
    public static function fetch($link, $target=false, $progress=false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_BUFFERSIZE,1280);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($progress){
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $total, $done){
                self::percantageProgress($done, $total, 100);
            });
            curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $content = curl_exec($ch);
        curl_close($ch);

        echo "\n";

        if($target)return file_put_contents($target, $content);
        else return $content;
    }

    /**
     * CLI Percentage Progress Bar.
     *
     * @param	string 	$done     percentage complete
     * @param	string 	$total    total file size
     * @param	string 	$size     progress bar size
     *
     * @return content
     */
    private static function percantageProgress($done, $total, $size=30) {


        static $start_time;

        // if we go over our bound, just ignore it
        if($done > $total || $total == 0) return;


        if(empty($start_time)) $start_time=time();
        $now = time();

        $perc=(double)($done/$total);

        $bar=floor($perc*$size);

        $status_bar="\r" . self::$color['green'] . self::$progressbar[0];
        $status_bar.=str_repeat(self::$progressbar[1], $bar);
        if($bar<$size){
            $status_bar.=self::$progressbar[3];
            $status_bar.=str_repeat(self::$progressbar[2], $size-$bar);
        } else {
            $status_bar.= self::$progressbar[1];
        }

        $disp=number_format($perc*100, 0);

        $donev = File::format($done);
        $totalv = File::format($total);

        $status_bar.= self::$progressbar[4] . self::$color['default'] . " $disp%  $donev/$totalv ";

        $rate = $done == 0 ? 0 : ($now-$start_time)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;


        $status_bar.= sprintf(self::$progressbar[5], number_format($eta), number_format($elapsed));

        echo $status_bar;

        // flush();
    }

    /**
     * install CLI tools to device.
     *
     * @param	string 	$done     percentage complete
     * @param	string 	$total    total file size
     * @param	string 	$size     progress bar size
     *
     * @return content
     */
    public static function install(){

        echo self::$brand;
        echo self::$color['green'];
        echo "CLI tools Installer\n";
        echo self::$color['default'];

        // TODO : check platform if windows or unix
        $php = exec("whereis php");
        $php = self::ask($php, "defining PHP PATH", "OK PHP PATH defined as");
        // TODO : check if file exist

        $index = $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_NAME'];
        $index = self::ask($index, "defining codeHive index PATH", "OK codeHive index PATH is");
        // TODO : check if file exist

        $write = "/usr/local/bin/codehive";
        $write = self::ask($write, "writing codeHive CLI to", "Will Write codeHive to");

        // install codeHive cli to disk
        @unlink($write);
        file_put_contents($write, "#!/bin/sh\n{$php} {$index} _cli $@");
        chmod($write, 0755);

        // install app _cli from internet to apps
        exit;
    }

    public static function ask($default, $question, $OK="", $options="enter|path"){

        echo "{$question} ({$default}) ? ({$options}) \n ";
        $line = trim(fgets(STDIN));
        if($line != "y" and $line != ""){
            $default = $line;
        }
        echo self::$color['green'] . "\r{$OK} ({$default})\n\n\n\n" . self::$color['default'];
        return $default;
    }
}

/* End of file CLI.php */
/* Location: ./system/core/CLI.php */
