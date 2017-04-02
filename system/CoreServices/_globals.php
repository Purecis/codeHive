<?php
namespace {

    /*
    * set current working directory to the main index path
    */
    define('__CWD__', getcwd());
    chdir(dirname($_SERVER["SCRIPT_FILENAME"]));


    // alias for some functions
    function random($length, $chars = false)
    {
        return App\System\Str::random($length, $chars);
    }

    function str_contains($haystack, $needles)
    {
        return App\System\Str::contains($haystack, $needles);
    }
}
