<?php
/**
 * codeHive Core.
 *
 * Core class init codeHive Framework
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/Benchmark
 * @since      File available since Release 2.0.0
 *
 * @version    V: 2.1.0
 */
class codeHive
{
    public static function start($start = array('app' => 'app'))
    {
        global $config;

        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            throw new Exception('The codeHive Framework v2.1 requires PHP version 5.3 or higher, (5.4) is Recomonded.');
        }

        define('VersionMajor', '2');
        define('VersionMinor', '1');
        define('VersionPatch', '00');
        define('VersionCode', 'Beta');
        define('VersionBuild', '3000');

        define('VERSION', 'v.'.VersionMajor.'.'.VersionMinor.'.'.VersionPatch.' '.VersionCode.', Build '.VersionBuild);

        $config = array();
        $config['app'] = $start['app'] ?: 'app';
        $config['assets'] = $start['assets'] ?: 'assets';
        $config['system'] = $start['system'] ?: 'system';

        $config = array_merge($config, self::config($config['app']));

        if (!isset($config['settings'])) {
            $config['settings'] = array();
        }

        // defines
        define('INDEX_FILE', isset($config['index']) ? $config['index'] : 'index.php');
        define('SESSION_PERFIX', isset($config['session']) ? $config['session'] : '');

        if (isset($config['license'])) {
            if (isset($config['license']['hash'])) {
                define('SECURITY_HASH', $config['license']['hash']);
            }
        }
        if (isset($config['timezone'])) {
            date_default_timezone_set($config['timezone']);
        }

        if (isset($config['ENVIRONMENT'])) {
            switch (strtoupper($config['ENVIRONMENT'])) {
                case 'DEVELOPMENT' :
                    error_reporting(E_ALL);
                break;

                case 'TRACE' :
                    error_reporting(E_ALL);
                    Trace::__bootstrap();
                break;

                case 'PRODUCTION' :
                default:
                    error_reporting(0);
                break;
            }
        }

        echo Module::__bootstrap();

        require_once "{$start['app']}/app.php";

        echo Module::__shutdown();

        if (isset($config['ENVIRONMENT']) && strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            Trace::__shutdown();
        }
    }

    private static function config($app)
    {
        if (!file_exists("{$app}/config.php")) {
            Install::initialize();
            exit;
        };

        return require_once "{$app}/config.php";
    }
}

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

/* End of file codeHive.php */
/* Location: ./system/core/codeHive.php */
