<?php

/*
 * set current working directory to the main index path
 */
define('__CWD__', getcwd());
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));


// register global functions here



// function str_contains($haystack, $needles){
//     App\System\Str::contains($haystack, $needles);
// }
