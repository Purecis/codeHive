<?php
namespace App\System;

class Directive extends Invokable
{
    public static $__namespace = "Directive";

    public static $__elements = [];
    public static $__arguments = [];
    
    public static $__pattern = "#\<(%s) \s*(?>\#(\S+))?\s (\b[^<\>]*+)\>((?:(?:(?!\</?\\1\b).)++| (?R))*+) (?>\</\\1\s*+\>)#six";

    // default priority value
    public $priority = 100;

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

    public static function register($element, $level=0)
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

        $pattern = sprintf(self::$__pattern, $__elements);

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        // print_r($matches);

        $keywords = preg_split($pattern, $content, -1, PREG_SPLIT_OFFSET_CAPTURE);
        // print_r($keywords);

        // prepare temp array to call by priority
        $temp = [];
        $temp_priority = [];
        $index = 0;
        foreach($keywords as $k => $word) {
            array_push($temp, $word[0]); // parse the ${{codes}}
            array_push($temp_priority, [-1, $index]);
            $index++;
            if($k < sizeof($matches)){
                $cls = new \stdClass();
                $cls->element   = strtolower($matches[$k][1]);
                $cls->scope     = $matches[$k][2]; // TODO : scope parsing #scopeName
                $cls->arguments = $matches[$k][3]; // TODO : extract params
                $cls->content   = $matches[$k][4]; // TODO : we should parse content, arguments
                $cls->priority  = self::inject(self::$__elements[$cls->element])->priority;
                array_push($temp, $cls);
                array_push($temp_priority, [$cls->priority, $index]);
                $index++;
            }
        }
        return $temp;

        $temp_priority = array_filter($temp_priority, function($e){
            return $e[0] != -1;
        });

        // sort temp_priority array to run asc
        usort($temp_priority, function($a, $b) {
            return $a[0] == $b[0] ? ($a[1] > $b[1]) : ($a[0] > $b[0] ? 1 : -1);
        });
        
        foreach($temp_priority as $idx){
            $item = $temp[$idx[1]];
            if(is_object($item)){
                $callable = explode("@", self::$__elements[$item->element]);
                // parse all
                $temp[$idx[1]] = self::invoke($callable[0] . "::handle" . (isset($callable[1]) ? "@" . $callable[1] : ""));
            }
        }
        
        return implode("",$temp);
    }
}
