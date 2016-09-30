<?php
// $_GET, $_POST, $_REQUEST, $_COOKIE, $_FILES, $_SERVER

namespace App\System;

class Request extends dynClusure
{
    private static $request = null;
    public function __construct()
    {
        $request                      = new dynClass();
        
        // define get, post, request, cookie
        $request->get                 = new dynClass($_GET,     ["App\\System\\Str", "escape"]);
        $request->post                = new dynClass($_POST,    ["App\\System\\Str", "escape"]);
        $request->all                 = new dynClass($_REQUEST, ["App\\System\\Str", "escape"]);

        // define cookie
        $request->cookie              = new dynClass($_COOKIE,  ["App\\System\\Str", "escape"]);
        $request->cookie->onSet(function ($key, $value) {
            $value = json_encode($value);
            setcookie($key, $value);
        });
        $request->cookie->onDelete(function ($key) {
            $value = json_decode($value);
            setcookie($key, "", time() - 3600);
        });

        // define http header
        $request->http                = new dynClass();
        $request->http->host          = isset($_SERVER['HTTP_HOST'])            ? $_SERVER['HTTP_HOST']                     : null;
        $request->http->agent         = isset($_SERVER['HTTP_USER_AGENT'])      ? $_SERVER['HTTP_USER_AGENT']               : null;
        $request->http->accept        = isset($_SERVER['HTTP_ACCEPT'])          ? $_SERVER['HTTP_ACCEPT']                   : null;
        $request->http->encoding      = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING']          : null;
        $request->http->language      = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE']          : null;
        $request->http->referer       = isset($_SERVER['HTTP_REFERER'])         ? $_SERVER['HTTP_REFERER']                  : null;

        // define server
        $request->server              = new dynClass();
        $request->server->name        = isset($_SERVER['SERVER_NAME'])          ? $_SERVER['SERVER_NAME']                   : null;
        $request->server->ip          = isset($_SERVER['SERVER_ADDR'])          ? $_SERVER['SERVER_ADDR']                   : null;
        $request->server->port        = isset($_SERVER['SERVER_PORT'])          ? $_SERVER['SERVER_PORT']                   : null;
        $request->server->protocol    = isset($_SERVER['SERVER_PROTOCOL'])      ?
            strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')))                      : null;
            
        $request->server->gateway     = isset($_SERVER['GATEWAY_INTERFACE'])    ? $_SERVER['GATEWAY_INTERFACE']             : null;
        $request->server->handler     = php_sapi_name();

        // define remote
        $request->remote              = new dynClass();
        $request->remote->name        = isset($_SERVER['REMOTE_ADDR'])          ? gethostbyaddr($_SERVER['REMOTE_ADDR'])    : null;
        $request->remote->ip          = isset($_SERVER['REMOTE_ADDR'])          ? $_SERVER['REMOTE_ADDR']                   : null;
        $request->remote->port        = isset($_SERVER['REMOTE_PORT'])          ? $_SERVER['REMOTE_PORT']                   : null;

        // defaults
        $request->method              = isset($_SERVER['REQUEST_METHOD'])       ? $_SERVER['REQUEST_METHOD']                : null;
        $request->time                = isset($_SERVER['REQUEST_TIME'])         ? $_SERVER['REQUEST_TIME']                  : null;
        $request->self                = isset($_SERVER['PHP_SELF'])             ? $_SERVER['PHP_SELF']                      : null;
        $request->uri                 = isset($_SERVER['REQUEST_URI'])          ? $_SERVER['REQUEST_URI']                   : null;
        $request->script              = isset($_SERVER['SCRIPT_NAME'])          ? $_SERVER['SCRIPT_NAME']                   : null;
        $request->index               = $request->script                        ? basename($request->script)                : null;
        $request->base                = $request->script                        ? rtrim($request->script, $request->index)  : null;
        $request->query               = isset($_SERVER['QUERY_STRING'])         ? $_SERVER['QUERY_STRING']                  : null;
        $request->domain              = $request->http->host                    ?
            $request->server->protocol . "://" . $request->http->host .
            ($request->server->port == 80 ? "" : ":" . $request->server->port)                                            : null;
 
        list($bareURI) = explode("?", $request->uri);
        $request->alias               = $request->uri                           ? ltrim($bareURI, $request->base)          : null;
        $request->segments            = $request->alias                         ? explode("/", $request->alias)          : null;
        $request->is                  = function () {
            echo 123;
        };
        
        // add session managment
        parent::__apply($request);

        return $request;
    }


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
