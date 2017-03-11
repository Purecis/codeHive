<?php
// $_GET, $_POST, $_REQUEST, $_COOKIE, $_FILES, $_SERVER

namespace App\System;

class Request extends Dynable
{
    private static $request = null;
    public static $__dynable = null;
    
    public function __construct()
    {
        if(self::$__dynable)return self::$__dynable;
        
        self::$__dynable                      = new dynClass();

        // define input
        $_INPUT = [];
        try{
            $formData = json_decode(file_get_contents('php://input'));
            if($formData)foreach ($formData as $key => $value) {
                $_INPUT[$key]   = $value;
                $_REQUEST[$key] = $value;
            }
        }catch(Exception $e){}
        self::$__dynable->input               = new dynClass($_INPUT, ["App\\System\\Str", "escape"]);

        // define get, post, request, cookie
        self::$__dynable->get                 = new dynClass($_GET,     ["App\\System\\Str", "escape"]);
        self::$__dynable->post                = new dynClass($_POST,    ["App\\System\\Str", "escape"]);
        self::$__dynable->all                 = new dynClass($_REQUEST, ["App\\System\\Str", "escape"]);

        // define cookie
        self::$__dynable->cookie              = new dynClass($_COOKIE,  ["App\\System\\Str", "escape"]);
        self::$__dynable->cookie->onSet(function ($key, $value) {
            $value = json_encode($value);
            setcookie($key, $value);
        });
        self::$__dynable->cookie->onDelete(function ($key) {
            $value = json_decode($value);
            setcookie($key, "", time() - 3600);
        });

        // define sessions
        self::$__dynable->session             = new dynClass();
        self::$__dynable->session->onGet(function ($key) {
            return Session::get($key);
        });
        self::$__dynable->session->onSet(function ($key, $value) {
            Session::set($key, $value);
        });
        self::$__dynable->session->onDelete(function ($key) {
            Session::remove($key);
        });

        // define http header
        self::$__dynable->http                = new dynClass();
        self::$__dynable->http->host          = isset($_SERVER['HTTP_HOST'])            ? $_SERVER['HTTP_HOST']                     : null;
        self::$__dynable->http->agent         = isset($_SERVER['HTTP_USER_AGENT'])      ? $_SERVER['HTTP_USER_AGENT']               : null;
        self::$__dynable->http->accept        = isset($_SERVER['HTTP_ACCEPT'])          ? $_SERVER['HTTP_ACCEPT']                   : null;
        self::$__dynable->http->encoding      = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING']          : null;
        self::$__dynable->http->language      = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE']          : null;
        self::$__dynable->http->referer       = isset($_SERVER['HTTP_REFERER'])         ? $_SERVER['HTTP_REFERER']                  : null;

        // define server
        self::$__dynable->server              = new dynClass();
        self::$__dynable->server->name        = isset($_SERVER['SERVER_NAME'])          ? $_SERVER['SERVER_NAME']                   : null;
        self::$__dynable->server->ip          = isset($_SERVER['SERVER_ADDR'])          ? $_SERVER['SERVER_ADDR']                   : null;
        self::$__dynable->server->port        = isset($_SERVER['SERVER_PORT'])          ? $_SERVER['SERVER_PORT']                   : null;
        self::$__dynable->server->protocol    = isset($_SERVER['SERVER_PROTOCOL'])      ?
            strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')))                      : null;
            
        self::$__dynable->server->gateway     = isset($_SERVER['GATEWAY_INTERFACE'])    ? $_SERVER['GATEWAY_INTERFACE']             : null;
        self::$__dynable->server->handler     = php_sapi_name();

        // define remote
        self::$__dynable->remote              = new dynClass();
        self::$__dynable->remote->name        = isset($_SERVER['REMOTE_ADDR'])          ? gethostbyaddr($_SERVER['REMOTE_ADDR'])    : null;
        self::$__dynable->remote->ip          = isset($_SERVER['REMOTE_ADDR'])          ? $_SERVER['REMOTE_ADDR']                   : null;
        self::$__dynable->remote->port        = isset($_SERVER['REMOTE_PORT'])          ? $_SERVER['REMOTE_PORT']                   : null;

        // defaults
        self::$__dynable->method              = isset($_SERVER['REQUEST_METHOD'])       ? $_SERVER['REQUEST_METHOD']                : null;
        self::$__dynable->time                = isset($_SERVER['REQUEST_TIME'])         ? $_SERVER['REQUEST_TIME']                  : null;
        self::$__dynable->self                = isset($_SERVER['PHP_SELF'])             ? $_SERVER['PHP_SELF']                      : null;
        self::$__dynable->uri                 = isset($_SERVER['REQUEST_URI'])          ? $_SERVER['REQUEST_URI']                   : null;
        self::$__dynable->script              = isset($_SERVER['SCRIPT_NAME'])          ? $_SERVER['SCRIPT_NAME']                   : null;
        self::$__dynable->index               = self::$__dynable->script                ? basename(self::$__dynable->script)        : null;
        self::$__dynable->base                = self::$__dynable->script                ? rtrim(self::$__dynable->script, self::$__dynable->index)  : null;
        self::$__dynable->query               = isset($_SERVER['QUERY_STRING'])         ? $_SERVER['QUERY_STRING']                  : null;
        self::$__dynable->domain              = self::$__dynable->http->host                    ?
            self::$__dynable->server->protocol . "://" . self::$__dynable->http->host .
            (self::$__dynable->server->port == 80 ? "" : ":" . self::$__dynable->server->port)                                              : null;
 
        list($bareURI) = explode("?", self::$__dynable->uri);
        $alias = substr($bareURI, strlen(self::$__dynable->base));
        self::$__dynable->alias               = self::$__dynable->uri                           ? ( $alias ? $alias : 'index' )             : null;
        self::$__dynable->segments            = self::$__dynable->alias                         ? explode("/", self::$__dynable->alias)             : null;
        self::$__dynable->is                  = function () {
            echo 123; // TODO: check method type
        };
        
        // self::self::$__dynable = self::$__dynable;
        // add session managment
        // parent::__apply(self::$__dynable);

        return self::$__dynable;
    }


    // TODO : use observable to callback
    public static function fetch($link, $target = null, callable $progress = null, $error = false)
    {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $link);
        curl_setopt($handle, CURLOPT_BUFFERSIZE, 1280);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        if (is_callable($progress)) {
            curl_setopt($handle, CURLOPT_PROGRESSFUNCTION, function ($resource, $total, $done) use ($progress) {
                if ($done > $total) {
                    $total = $done;
                }
                call_user_func($progress, $done, $total);
            });
            // needed to make progress function work
            curl_setopt($handle, CURLOPT_NOPROGRESS, false);
        }
        curl_setopt($handle, CURLOPT_HEADER, 0);
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        } else {
            curl_setopt($handle, CURLOPT_USERAGENT, "CURL Fetch, codeHive Framework Hive/3.0");
        }
        $content = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            // TODO: handle errors by events and broadcasters
            if ($error) {
                $error($httpCode);
            }
        }
        curl_close($handle);
        if ($target) {
            return file_put_contents($target, $content);
        } else {
            return $content;
        }
    }
}
