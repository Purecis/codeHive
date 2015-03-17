<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis View Class
 *
 * This class control all the request attrbutes and protect 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class View{

	/**
	 * List of paths to load views from
	 *
	 * @var array
	 * @access protected
	 */
	protected static $views = array();

	// --------------------------------------------------------------------

	/**
	 * Controller Register
	 *
	 * @access	public
	 * @param	string 	view name and you can add by module (view@module)
	 * @param	mixen 	callback
	 * @return	string
	 */
	public static function load($tpl,$force=false){
		global $config;

		if(strpos($tpl, '@')){
			$ex = explode("@", $tpl);
			if($force)Module::import($ex[1]);
			$path = "{$config['app']}/module/{$ex[1]}/view/{$ex[0]}.html";
			if(!file_exists($path))$path = "{$config['assets']}/extensions/{$ex[1]}/view/{$ex[0]}.html";
		}else{
			$path = "{$config['app']}/view/{$tpl}.html";
			if(!file_exists($path))$path = "{$config['system']}/view/{$tpl}.html";
		}
		if(!file_exists($path)){
			if($config['ENVIRONMENT'] == 'debug')debug::error("Missing View",$tpl);
			else die("View <b>{$name}</b> not Found.");

			return null;
		}
		return Shortcode::trigger(file_get_contents($path));
	}

	// --------------------------------------------------------------------

	/**
	 * Define Scope
	 *
	 * @access	public
	 * @param	array 	key:value
	 * @return	array 	scope
	 */
	public static function scope($arr=array()){
		return Controller::scope($arr);
	}}

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */