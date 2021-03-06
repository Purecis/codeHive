<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis View Class.
 *
 * This class control all the request attrbutes and protect
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class View
{
    /**
     * List of paths to load views from.
     *
     * @var array
     */
    protected static $views = array();

    // --------------------------------------------------------------------

    /**
     * Controller Register.
     *
     * @param	string 	view name and you can add by module (view@module)
     * @param	mixen 	callback
     *
     * @return string
     */
    public static function load($tpl, $force = false)
    {
        global $config;

        if (strpos($tpl, '@')) {
            $ex = explode('@', $tpl);
            if ($force) {
                Module::import($ex[1]);
            }
            $path = (sizeof($ex) > 1) ? Module::path($ex[1]) : false;
            $path = "{$path}/view/{$ex[0]}.html";
        } else {
            $path = APP_PATH."/view/{$tpl}.html";
            if (!file_exists($path)) {
                $path = "{$config['system']}/view/{$tpl}.html";
            }
        }
        if (!file_exists($path)) {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Missing View', $tpl);
            } else {
                die("View <b>{$tpl}</b> not Found.");
            }

            return;
        }

        return Shortcode::trigger(file_get_contents($path));
    }

    // --------------------------------------------------------------------

    /**
     * Define Scope.
     *
     * @param	array 	key:value
     *
     * @return array scope
     */
    public static function scope($arr = array())
    {
        return Controller::scope($arr);
    }
}

/* End of file View.php */
/* Location: ./system/core/View.php */
