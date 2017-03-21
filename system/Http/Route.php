<?php

namespace App\System;

class Route
{
    private static $routes = [];
    private static $currentRoute = null;
    private static $currentMethod = null;

    public static function group($route, $class){
        // get all users
        Route::get($route, $class . '::index');
        // get user
        Route::get($route . '/:id', $class . '::view');
        // add new user
        Route::post($route, $class . '::store');
        // edit user ( all accept one or all fields )
        Route::post($route . '/:id', $class . '::store');
        Route::put($route . '/:id', $class . '::store');
        Route::patch($route . '/:id', $class . '::store');
        // delete user
        Route::delete($route . '/:id', $class . '::remove');
    }
    public static function get($route, $invoke){
        return self::register($route, $invoke, 'GET');
    }
    public static function post($route, $invoke){
        return self::register($route, $invoke, 'POST');
    }
    public static function put($route, $invoke){
        return self::register($route, $invoke, 'PUT');
    }
    public static function patch($route, $invoke){
        return self::register($route, $invoke, 'PATCH');
    }
    public static function delete($route, $invoke){
        return self::register($route, $invoke, 'DELETE');
    }
    public static function options($route, $invoke){
        return self::register($route, $invoke, 'OPTIONS');
    }

    public static function register($route, $invoke, $method = 'GET')
    {
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
        $arguments = Loader::mergeArguments(func_get_args());
        $before = &self::$routes[self::$currentRoute][self::$currentMethod]['middleware']['before'];
        $before = array_merge($before, $arguments);

        return $this;
    }
    public function middlewareAfter()
    {
        $arguments = Loader::mergeArguments(func_get_args());
        $before = &self::$routes[self::$currentRoute][self::$currentMethod]['middleware']['after'];
        $before = array_merge($before, $arguments);

        return $this;
    }


    public static function trigger()
    {
        $request = new Request;        
        $method = strtoupper($request->method);

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
                    $inject = Controller::inject($callable['invoke']);
                    if(isset($inject['instance']->middlewares)){
                        if(isset($inject['instance']->middlewares[$inject['__invokable_method']])){
                            $middleware = $inject['instance']->middlewares[$inject['__invokable_method']];
                            if(!is_array($middleware)){
                                $middleware = [$middleware];
                            }
                            $callable['middleware']['before'] = array_merge($callable['middleware']['before'], $middleware);
                        }
                    }
                    if(sizeof($callable['middleware']['before'])){
                        $middleware = (new Middleware($callable['middleware']['before']))->beginQueue();
                        if($middleware instanceof Response){
                            $middleware->spread();
                            break;
                        }
                    }

                    // TODO : send invoked value to pipe .. 
                    $invoke = Controller::invoke($inject);
                    
                    if(sizeof($callable['middleware']['after'])){
                        $middleware = (new Middleware($callable['middleware']['after']))->beginQueue();
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
            if(is_callable($invoke) & !$invoke instanceof Response){
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
