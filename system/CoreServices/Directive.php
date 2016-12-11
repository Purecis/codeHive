<?php
namespace App\System;

class Directive extends Invokable
{
    public static $__namespace = "Directive";

    public static $__elements = [];
    public static $__arguments = [];
    
    public static $pattern = "#\<(%s) (\b[^<\>]*+)\>((?:(?:(?!\</?\\1\b).)++| (?R))*+)(?>\</\\1\s*+\>)#six";

    public function __get($name){
        if($name == 'directive')return $this;
        return isset(self::$__arguments[$name]) ? self::$__arguments[$name] : null;
    }

    public static function boot()
    {
        // TODO : dont forget to register __global Folder in globe
        $hive = new Scope('config.hive');
        
        // register directives inside module
        $find = glob($hive->app_path . "/module/*/*/directive/*.php");
        if(sizeof($find)){
            $find = implode("\n", $find);
            preg_match_all("#\/module\/(.*)\/(.*)\/directive\/(.*).php#", $find, $matches);
            foreach($matches[1] as $key => $directive){
                self::register($matches[3][$key] . "@" . $matches[1][$key] . "." . $matches[2][$key]);
            }
        }

        // register default directives, default will overwrite all
        $find = glob($hive->app_path . "/directive/*.php");
        if(sizeof($find)){
            $find = implode("\n", $find);
            preg_match_all("#\/directive\/(.*).php#", $find, $matches);
            foreach($matches[1] as $directive){
                self::register($directive);
            }
        }
    }

    public static function register($element)
    {
        $elm = strtolower(explode("@", $element)[0]);
        self::$__elements[$elm] = $element;
    }

    public static function trigger($content, $scope = "original")
    {
        // TODO : make fake register dirctive and module first and what about assets and things like that 
        // and how to parse them

        $__elements = array_keys(self::$__elements);
        $__elements = implode('|', $__elements);

        $pattern = sprintf(self::$pattern, $__elements);

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        // print_r($matches);

        $keywords = preg_split($pattern, $content, -1, PREG_SPLIT_OFFSET_CAPTURE);
        // print_r($keywords);

        $cache = "";
        foreach($keywords as $k => $word) {
            $cache .= $word[0]; // parse the ${{codes}}
            if($k < sizeof($matches)){
                // TODO : we should parse content, arguments
                // TODO : extract params

                $element = strtolower($matches[$k][1]);
                $arguments = $matches[$k][2];
                
                self::$__arguments = [
                    "content" => $matches[$k][3]
                ];
                
                $callable = explode("@", self::$__elements[$element]);
                
                $cache .= self::invoke($callable[0] . "::handle" . (isset($callable[1]) ? "@" . $callable[1] : ""));
            }
        }

        return $cache;
    }
}
