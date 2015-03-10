<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis API Module
 *
 * This class Control API Requests
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class API{

	/**
	 * Variables
	 *
	 * @var mixen
	 * @access protected
	 */
	private static $apis;

	// --------------------------------------------------------------------

	/**
	 * api bootstrap
	 *
	 * @return	void
	 */
	public static function __bootstrap(){
		Module::import('Router');
		// todo .. create function to change the api main url
		Router::on("api",array('fire','do','token'),function(&$scope,$router){
			global $config;
			if($config['ENVIRONMENT'] == "debug")$config['ENVIRONMENT'] = "development";

			$name = $router->fire;
			$path = "{$config['app']}/controller/api.{$name}.php";
			if(!is_file($path))$path = "{$config['app']}/controller/{$name}.php";
			if(!is_file($path))$path = "{$config['app']}/controller/api.php";
			if(is_file($path))require_once $path;

			// search in module controller
			if(!isset(self::$apis[$router->fire])){
				$mod = Module::path($router->fire);
				$name = $router->do; 
				$path = "{$mod}/controller/api.{$name}.php";
				if(!is_file($path))$path = "{$mod}/controller/{$name}.php";
				if(!is_file($path))$path = "{$mod}/controller/api.php";
				if(is_file($path))require_once $path;
			}

			if(isset(self::$apis[$name])){
				//check is callable
				return call_user_func_array(self::$apis[$name], array(&$scope,$router));
			}
		});
	}

	// --------------------------------------------------------------------

	/**
	 * api register
	 *
	 * @return	function
	 */
	public static function register($api,$callback){
		self::$apis[$api] = $callback;
		return $callback;
	}

	// --------------------------------------------------------------------

	/**
	 * api access : check token if send to make query
	 *
	 * @return	boolean
	 */
	public static function access($name="token"){
		global $config;
		$token = Request::get($name);
		return ($token == md5($config['license']['key'].$config['license']['secret']));
	}
}