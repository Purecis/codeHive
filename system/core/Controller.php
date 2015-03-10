<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Controller Class
 *
 * This class Manage Controllers 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Controller{

	/**
	 * List of paths to load views from
	 *
	 * @var array
	 * @access protected
	 */
	protected static $controllers = array();
	public static $scope;

	// --------------------------------------------------------------------

	/**
	 * Controller bootstrap
	 *
	 * Update : For better performance change this start to per file load
	 *
	 * @access	public
	 * @return	string
	 */
	public static function __bootstrap(){
		global $config;
		foreach (glob("{$config['app']}/controller/*.bootstrap.php") as $filename){
			require_once $filename;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Controller Register
	 *
	 * @access	public
	 * @param	string 	controller name
	 * @param	mixen 	callback
	 * @return	string
	 */
	public static function register($name,$fn){
		$name = urlencode($name);

		self::$controllers[$name] = $fn;
		
		return self::$controllers;
	}

	// --------------------------------------------------------------------

	/**
	 * Controller Trigger
	 *
	 * @access	public
	 * @param	mixen 	controller
	 * @param	mixen 	args to pass
	 * @return	mixen
	 */
	public static function trigger($name,$arg=null){
		global $config;

		if(empty(self::$scope))self::$scope = new stdClass();

		if(is_string($name)){
			if(array_key_exists($name,self::$controllers)){
				$name = self::$controllers[$name];
			}else{
				$path = "{$config['app']}/controller/{$name}.php";
				if(is_file($path))require_once $path;
			}
			if(array_key_exists($name,self::$controllers)){
				$name = self::$controllers[$name];
			}
		}
		if(is_array($name)){
			$path = "{$config['app']}/controller/{$name[0]}.php";
			if(is_file($path))require_once $path;
			$name[0] = str_replace(".", "\\", $name[0]);
		}

		if(is_callable($name)){
			return call_user_func_array($name, array(&self::$scope,$arg));
		}else{
			if($config['ENVIRONMENT'] == 'debug')debug::error("Missing Controller",$name);
			else die("Controller <b>{$name}</b> not Found.");
		}
	}
}

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */