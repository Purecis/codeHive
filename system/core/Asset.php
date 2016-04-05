<?php

defined('VERSION') or exit('Direct access to this location is not permitted');

/**
 * codeHive Asset.
 *
 * Asset class prepare resources, meta and elements to initiate in app
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/Asset
 * @since      File available since Release 2.0.0
 *
 * @version    V: 2.1.0
 */
class Asset
{
    /**
     * initialize js code.
     */
    protected static $code = array('var codeHive = codeHive || {};');

    /**
     * code variables.
     */
    protected static $codev = array();

    /**
     * codeHive Asset class constructor.
     *
     * define javascript default path values from codeHive
     */
    public static function __bootstrap()
    {
        // define globals (create function to globals .. call on listener & v get)
        global $config;
        self::define('path', '{}');
        self::define('path.base', Request::base());
        self::define('path.app', Request::base($config['app']));
        self::define('path.view', Request::base("{$config['app']}/view"));
        self::define('path.vendor', Request::base("{$config['app']}/vendor"));
        self::define('path.library', Request::base("{$config['assets']}/library"));
        self::define('path.domain', Request::domain());

        Hook::on('script', function () {
            $code = implode("\n\t\t", self::$code);

            return "\n\t<script type='text/javascript'>\n\t\t{$code}\n\t</script>";
        });
        Hook::on('defaults', "\n\t<meta charset='utf-8'>");
        Hook::on('defaults', "\n\t<meta name='viewport' content='width=device-width, initial-scale=1.0'>");
        Hook::on('defaults', "\n\t<meta http-equiv='X-UA-Compatible' content='IE=edge' />");
    }

    /**
     * define javascript code.
     *
     * @param string $a path
     * @param Mixen  $b equality
     * @param string $c method
     */
    public static function define($a, $b, $c = false)
    {
        self::$codev[$a] = $b;

        if ($b != '{}' && $b != '[]') {
            $b = "'{$b}'";
        } else {
            $b = "codeHive.{$a} || {$b}";
        }

        if (!$c) {
            $b = " = {$b}";
        } else {
            $b = ".{$c}({$b})";
        }

        array_push(self::$code, "codeHive.{$a}{$b};");
    }

    /**
     * get defined all variables.
     *
     * @return array List of all defintions
     */
    public static function variables()
    {
        return self::$codev;
    }

    /**
     * Parse Source string Load assets.
     *
     * @param string $src    Path of js file
     * @param string $folder default load folder
     * @param string $ext    extension to load inside folder
     *
     * @return string
     */
    private static function src_parser($src, $folder, $ext = false)
    {
        global $config;
        if (strpos($src, '://') === false) { // check is external
            $ex = explode('@', $src);
            $path = (sizeof($ex) > 1) ? Module::path($ex[1]) : false;
            if (!$path) {
                $path = "{$config['app']}";
            } else {
                $src = $ex[0];
            }
            $path = Request::base($path);
            if (!strpos($src, '.') && $ext) {
                $src = "{$src}/{$src}.{$ext}";
            }
            $src = "{$path}/{$folder}/{$src}";
        }

        return $src;
    }

    /**
     * Register Hook to Load script.
     *
     * @param string $src      Path of js file
     * @param string $folder   default load folder
     * @param string $listener default listener name
     */
    public static function script($src, $folder = 'vendor', $listener = 'script')
    {
        $src = self::src_parser($src, $folder);
        Hook::on($listener, "\n\t<script type='text/javascript' src='{$src}'></script>");
    }

    /**
     * Register Hook to Load style.
     *
     * @param string $src      Path of js file
     * @param string $folder   default load folder
     * @param string $listener default listener name
     */
    public static function style($src, $folder = 'vendor', $listener = 'style')
    {
        $src = self::src_parser($src, $folder);
        $extra = File::extension($src) == 'less' ? '/less' : '';
        // TODO:20 : make less and scss as plugins or hooks to fetch

        Hook::on($listener, "\n\t<link rel='stylesheet{$extra}' type='text/css' href='{$src}' />");
    }

    /**
     * Register Hook to Load element.
     *
     * @param string $src      Path of js file
     * @param string $folder   default load folder
     * @param string $listener default listener name
     */
    public static function element($src, $folder = 'vendor', $listener = 'element')
    {
        $src = self::src_parser($src, $folder, 'html');
        $extra = File::extension($src) == 'less' ? '/less' : '';

        Hook::addListener($listener, "\n\t<link rel='import' href='{$src}' />");
    }

    /**
     * get file source in vendor.
     *
     * @param string $src Path of js file
     *
     * @return string path
     */
    public static function vendor($src = '')
    {
        global $config;

        return Request::base("{$config['app']}/vendor{$src}");
    }
}

/* End of file Asset.php */
/* Location: ./system/core/Asset.php */
