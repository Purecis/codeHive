<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis localization Class.
 *
 * control Sessions With Advance Tecnique
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Localization
{
    /**
     * @var array
     */
    protected static $phrases = array();
    protected static $phrases_space = array();
    public static $locale = 'en-us';

    // --------------------------------------------------------------------

    /**
     * Locale translate.
     *
     * @param	mixen 	str or array keys only
     *
     * @return mixen
     */
    public static function translate($msg, $args = array(), $space = false)
    {
        self::prepare($space);

        if (!$space) {
            $localeArr = isset(self::$phrases[$msg]) ? self::$phrases[$msg] : $msg;
        } else {
            $localeArr = isset(self::$phrases_space[$space][$msg]) ? self::$phrases_space[$space][$msg] : $msg;
        }

        if (is_array($localeArr)) {
            $parsing = self::parse($localeArr, $args);
            if ($parsing) {
                $msg = $parsing;
            }
        } elseif (is_string($localeArr)) {
            $msg = $localeArr;
        }

        // we use this bcz preg_replace Modifier /e deprecated
        $arr = array();
        foreach ($args as $k => $v) {
            $arr[":{$k}"] = $v;
        }
        $msg = strtr($msg, $arr);

        //$msg = preg_replace('/:(\w+)/e', 'isset($args["$1"])?$args["$1"]:0', $msg);

        return $msg;
    }

    // --------------------------------------------------------------------

    /**
     * Locale Parse.
     *
     * @param	mixen 	str or array keys only
     *
     * @return mixen
     */
    private static function parse($msg_arr, $args)
    {
        foreach ($msg_arr as $exp => $msg) {
            if (self::plural($exp, $args)) {
                if (is_array($msg)) {
                    return self::parse($msg, $args);
                } else {
                    return $msg;
                }
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Locale plural.
     *
     * @param	mixen 	str or array keys only
     *
     * @return mixen
     */
    private static function plural($exp, $args)
    {
        $exp = explode(':', $exp);

        if (!isset($args[$exp[0]])) {
            return false;
        }

        if ($exp[1] == 'eq') {
            return $args[$exp[0]] == $exp[2];
        } elseif ($exp[1] == 'gt') {
            return $args[$exp[0]] > $exp[2];
        } elseif ($exp[1] == 'gte') {
            return $args[$exp[0]] >= $exp[2];
        } elseif ($exp[1] == 'lt') {
            return $args[$exp[0]] < $exp[2];
        } elseif ($exp[1] == 'lte') {
            return $args[$exp[0]] <= $exp[2];
        } elseif ($exp[1] == 'zero') {
            return $args[$exp[0]] == 0;
        } elseif ($exp[1] == 'one') {
            return $args[$exp[0]] == 1;
        } elseif ($exp[1] == 'few') {
            return $args[$exp[0]] >= 2 && $args[$exp[0]] <= 4;
        } elseif ($exp[1] == 'other') {
            return true;
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Locale prepare.
     *
     * @param	mixen 	str or array keys only
     *
     * @return mixen
     */
    public static function prepare($space = false, $force = false)
    {
        global $config;

        $locale = Session::exist('locale') ? Session::get('locale') : (isset($config['locale']) ? $config['locale'] : self::$locale);

        if ($space) {
            if ($force) {
                Module::import($space);
            }
            $path = Module::path($space)."/language/{$locale}";
        } else {
            $path = APP_PATH."/language/{$locale}";
            if (!file_exists($path)) {
                $path = APP_PATH."/locale/{$locale}";
            }
            if (!file_exists($path)) {
                $path = "{$config['system']}/language/{$locale}";
            }
        }
        if (!is_dir($path)) {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Missing Locales', $path);
            }
            return;
        }

        foreach (glob("{$path}/*.php") as $filename) {
            $lng = self::localeFile($filename);
            if (!is_array($lng)) {
                return false;
            }
            if (!$space) {
                self::$phrases = array_merge(self::$phrases, $lng);
            } else {
                if (!is_array(self::$phrases_space[$space])) {
                    self::$phrases_space[$space] = array();
                }
                self::$phrases_space[$space] = array_merge(self::$phrases_space[$space], $lng);
            }
        }

        if (!$space) {
            return self::$phrases;
        } else {
            return self::$phrases_space[$space];
        }
    }

    private static function localeFile($filename)
    {
        return require_once $filename;
    }

    public static function requestLocale($locale = 'locale')
    {
        global $config;
        $locale = Request::get($locale);
        if ($locale) {
            Session::set(array('locale' => $locale));
        }else{
            $locale = $config['locale'];
            if(!$locale){
                $locale = "en-us";
            }
        }
        return $locale;
    }
    
    public static function request($locale = 'locale'){
        self::requestLocale($locale);
    }
}

/* End of file Locale.php */
/* Location: ./system/core/Locale.php */
