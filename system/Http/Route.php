<?php

namespace App\System;

class Route
{
    private static $routes = [];

    public static function get($route, $invoke)
    {
        $type = "get";
        if (!isset(self::$routes[$route])) {
            self::$routes[$route] = [];
        }
        self::$routes[$route][$type] = $invoke;
    }

    public static function params()
    {
        $request = new Request;
        return $request->routeParams;
    }


    public static function trigger()
    {
        header("Content-Type: text/plain");
        // print_r(self::$routes);
        $request = new Request;
        
        $method = strtolower($request->method);

        foreach(self::$routes as $url => $calback){
            // $url = key(self::$routes);

            $regix = "~:(\w+)~";

            preg_match_all($regix, $url, $original);

            $urlRegix = preg_replace($regix, "(\w+)", $url);
            $urlRegix = str_replace("/", "\/", $urlRegix);
            
            
            if (preg_match('~' . $urlRegix . '~', $request->alias, $matches)) {
                array_shift($matches);
                
                $std = new \stdClass();
                foreach ($matches as $key => $value) {
                    $std->{$original[1][$key]} = $value;
                }
                $request->routeParams = $std;

                $request->route = $url;

                if (isset(self::$routes[$url]['group'])) {
                    Controller::invoke(self::$routes[$url]['group']);
                    break;
                } elseif (isset(self::$routes[$url][$method])) {
                    Controller::invoke(self::$routes[$url][$method]);
                    break;
                }
            }
        }
    }
}
