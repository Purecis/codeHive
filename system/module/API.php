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
	 * api router
	 *
	 * @return	array
	 */
	public static function router($arr){
		array_unshift($arr, "api");
		return Router::parse($arr);
	}

	/**
	 * api then
	 *
	 * @return	array
	 * @todo  creating scope, router, multi call in same scope
	 */
	private static $current = 0;
	public static function then($text,$args = false,$callback = false, $method = true){		
		if(!$callback){
			if(!$args){
				$callback = $text;
			}else{
				$callback = $args;
			}
			if(is_array($text))$args = $text;
			else $args = array();

			if(sizeof($args) > 0)self::$current--;
		}

		$arr = array();
		for ($i=0; $i <= self::$current; $i++) { 
			array_push($arr, "api-{$i}");
		}
		foreach ($args as $key)array_push($arr, $key);

		$router = Router::parse($arr);
		
		$curr = "api-".self::$current;	

		$route = isset($router->$curr)?$router->$curr:'';

		if((
				($route == $text) || 
				(is_object($text) && empty($route)) || 
				(is_array($text) && !empty($route))) 
			&& $method
		){
			self::$current++;
			if(sizeof($args) > 0)self::$current++;
			
			$cb = call_user_func_array($callback, array(&Controller::$scope,$router));
			//$cb = call_user_func($callback);
			if(is_array($cb) || is_object($cb))$cb = json_encode($cb);
			echo $cb;
			exit;
		};
	}

	public static function group($text,$args = false,$callback = false){
		return self::then($text,$args,$callback);
	}

	public static function get($text,$args = false,$callback = false){
		return self::then($text,$args,$callback,Request::method() == "GET");
	}

	public static function post($text,$args = false,$callback = false){
		return self::then($text,$args,$callback,Request::method() == "POST");
	}

	public static function delete($text,$args = false,$callback = false){
		return self::then($text,$args,$callback,Request::method() == "DELETE");
	}

	public static function put($text,$args = false,$callback = false){
		return self::then($text,$args,$callback,Request::method() == "PUT");
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