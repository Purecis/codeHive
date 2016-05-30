<?php
/*
 * auto load class when it called (Core Classes Only).
 */
spl_autoload_register(function ($class) {
    global $config;
    $class = str_replace('\\', '/', $class);
    if (is_file("{$config['system']}/core/{$class}.php")) {
        require_once "{$class}.php";
        if (is_callable(array($class, '__bootstrap'))) {
            call_user_func(array($class, '__bootstrap'));
        }
        if (isset($config['ENVIRONMENT']) && strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            Trace::message('Used System Cores', $class);
        }
    } else {
        if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            Trace::error('System Cores Faild', $class);
        }
    }
});

/**
 * Locale Alias __.
 *
 * @param	mixen 	str or array keys only
 *
 * @return mixen
 */
function __($e, $a = array(), $space = false)
{
    return Localization::translate($e, $a, $space);
}
