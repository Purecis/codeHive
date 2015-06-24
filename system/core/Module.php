<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Module Class
 *
 * This class control all the request attrbutes and protect 
 * them from known attac and injection
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Module{

	/**
	 * List of paths to load views from
	 *
	 * @var array
	 * @access protected
	 */
	protected static $modules = array();

	/**
	 * BootStrap
	 *
	 * @access	public
	 * @return	void
	 */
	public static function __bootstrap(){
		global $config;
		self::import($config['modules']);
	}

	// --------------------------------------------------------------------

	/**
	 * Use and Import Module
	 *
	 * Defines Module and prepare to call bootstrap for each
	 *
	 * @access	public
	 * @param	mixen 	module or array of modules
	 * @return	void
	 */
	public static function import($mod){
		if(is_array($mod)){
			foreach($mod as $m){
				if(!isset(self::$modules[$m]))self::register($m);
			}
		}else{
			if(!isset(self::$modules[$mod]))self::register($mod);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Register Module and initialize it by calling bootstrap
	 *
	 * @access	public
	 * @param	string 	module name
	 * @return	void
	 */
	private static function register($k){
		global $config;

		$path = self::path($k,true);

		if(!$path){
			if($config['ENVIRONMENT'] == 'debug')debug::error("Missing Module","{$k}");
			else die("Module <b>{$k}</b> not Found in {$path}.");
		}else{
			self::$modules[$k] = array("dir"=>dirname($path),"version"=>"1");
			require_once $path;
			$k = str_replace(".", "\\", $k);
			if(is_callable(array($k,"__bootstrap")))call_user_func(array($k,"__bootstrap"));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check if Module Used
	 *
	 * @access	public
	 * @param	mixen 	module or array of modules
	 * @return	boolean
	 */
	public static function used($mod){
		if(is_array($mod)){
			foreach($mod as $m){
				if(!isset(self::$modules[$m]))return false;
			}
			return true;
		}else{
			if(isset(self::$modules[$mod]))return true;
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Module Path
	 *
	 * @access	public
	 * @param	mixen 	module or array of modules
	 * @return	boolean
	 */
	public static function path($mod,$full=false){
		if(isset(self::$modules[$mod])){
			return self::$modules[$mod]['dir'];
		}

		global $config;
		if(strpos($mod, ".")){
			$ex = explode(".", $mod);
			$file = end($ex);
		}else{
			$file = $mod;
		}

		$path = "{$config['app']}/module/{$mod}/{$file}.php";
		if(!is_file($path))$path = "{$config['app']}/hook/{$mod}/{$file}.php";
		if(!is_file($path))$path = "{$config['app']}/plugin/{$mod}/{$file}.php";
		if(!is_file($path))$path = "{$config['app']}/extension/{$mod}/{$file}.php";
		if(!is_file($path))$path = "{$config['assets']}/extensions/{$mod}/{$file}.php";
		if(!is_file($path))$path = "{$config['system']}/module/{$mod}.php";

		if(is_file($path)){
			if($full) return $path;
			else return dirname($path);
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Shutdown
	 *
	 * @access	public
	 * @return	void
	 */
	public static function __shutdown(){
		global $config;
		foreach(self::$modules as $k => $v){
			if(is_callable(array($k,"__shutdown")))call_user_func(array($k,"__shutdown"));
		}
		if($config['ENVIRONMENT'] == 'debug'){
			foreach(self::$modules as $k => $v){
				debug::message("Used Modules","{$k}");
			}
		}
	}
}

/* End of file Module.php */
/* Location: ./system/core/Module.php */