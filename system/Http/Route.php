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
        return $request->routeParams;
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
        return $this;
    }


    public static function trigger()
    {
        $request = new Request;        
        $method = strtolower($request->method);

        // TODO : rearrange routes before looping

        foreach(self::$routes as $url => $calback){
            // $url = key(self::$routes);
            $request->routeParams = new \stdClass;

            $regix = "~:(\w+)~";

            preg_match_all($regix, $url, $original);

            $urlRegix = preg_replace($regix, "(\w+)", $url);
            $urlRegix = str_replace("/", "\/", $urlRegix);
            
            
            if (preg_match('~' . $urlRegix . '~', $request->alias, $matches)) {
                array_shift($matches);
                
                foreach ($matches as $key => $value) {
                    $request->routeParams->{$original[1][$key]} = $value;
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
            if($invoke instanceof Response){
                $invoke->spread();

            }else if(is_string($invoke) || is_numeric($invoke)){
                $response = new Response();
                $response->body($invoke);
                $response->spread();

            }else if(is_array($invoke) || is_object($invoke)){
                $response = new Response();
                $response->json();
                $response->body($invoke);
                $response->spread();
            }
        }
    }
}
