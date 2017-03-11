<?php

namespace App\System;

class Route
{
    private static $routes = [];
    private static $currentRoute = null;
    private static $currentMethod = null;

    // TODO : define methods
    public static function get($route, $invoke)
    {
        $method = "get";
        if (!isset(self::$routes[$route])) {
            self::$routes[$route] = [];
        }
        self::$routes[$route][$method] = [
            "invoke" => $invoke,
            "middleware" => [
                "before" => [],
                "after" => []
            ],
            "pipes" => []
        ];
        self::$currentRoute = $route;
        self::$currentMethod = $method;

        return new self;
    }

    public static function params()
    {
        $request = new Request;
        return $request->router;
    }

    public function middleware()
    {
        $args = func_get_args();
        foreach($args as $middleware){
            array_push(self::$routes[self::$currentRoute][self::$currentMethod]['middleware']['before'], $middleware);
        }
        
        return $this;
    }
    public function middlewareAfter()
    {
        $args = func_get_args();
        foreach($args as $middleware){
            array_push(self::$routes[self::$currentRoute][self::$currentMethod]['middleware']['after'], $middleware);
        }

        return $this;
    }


    public static function trigger()
    {
        $request = new Request;        
        $method = strtolower($request->method);

        // rearrange routes before looping to call tallest path first if in same url
        uksort(self::$routes, function($a, $b) {
            $a = strlen($a);
            $b = strlen($b);
            if ($a == $b) return 0;
            else return ($a < $b) ? 1: -1;
        });

        foreach(self::$routes as $url => $calback){
            // $url = key(self::$routes);
            $request->router = new \stdClass;

            $regix = "~:(\w+)~";

            preg_match_all($regix, $url, $original);

            $urlRegix = preg_replace($regix, "(\w+)", $url);
            $urlRegix = str_replace("/", "\/", $urlRegix);
            
            
            if (preg_match('~' . $urlRegix . '~', $request->alias, $matches)) {
                array_shift($matches);
                
                foreach ($matches as $key => $value) {
                    $request->router->{$original[1][$key]} = $value;
                }
                
                $request->route = $url;
                
                $callable = isset(self::$routes[$url][$method]) ? self::$routes[$url][$method] : null;
                $callable = isset(self::$routes[$url]['group']) ? self::$routes[$url]['group'] : $callable;

                if(!is_null($callable)){
                    if(sizeof($callable['middleware']['before'])){
                        $middleware = (new \App\System\Middleware($callable['middleware']['before']))->beginQueue();
                        if($middleware instanceof Response){
                            $middleware->spread();
                            break;
                        }
                    }

                    // TODO : send invoked value to pipe .. 
                    $invoke = Controller::invoke($callable['invoke']);
                    
                    if(sizeof($callable['middleware']['after'])){
                        $middleware = (new \App\System\Middleware($callable['middleware']['after']))->beginQueue();
                        if($middleware instanceof Response){
                            $middleware->spread();
                            break;
                        }
                    }
                    break;
                }
            }
        }
        if(isset($invoke)){
            if(is_callable($invoke)){
                $invoke = $invoke();
            }
            if($invoke instanceof Response) {
                $invoke->spread();

            } else {
                $response = new Response();
                $response->body($invoke);
                $response->spread();
                
            }
        }
    }
}
