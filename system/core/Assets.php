<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Assets Module
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Assets{

	protected static $code = array("var codeHive = codeHive || {};");
	protected static $codev = array();

	public static function __bootstrap(){
		// define globals (create function to globals .. call on listener & v get)
		global $config;
		self::define("path","{}");
		self::define("path.base",Request::base());
		self::define("path.app",Request::base($config['app']));
		self::define("path.view",Request::base("{$config['app']}/view"));
		self::define("path.vendor",Request::base("{$config['app']}/vendor"));
		self::define("path.library",Request::base("{$config['assets']}/library"));
		self::define("path.domain",Request::domain());
		
		Event::addListener('defaults',function(){

			// parse code
			$code = implode("\n\t\t", self::$code);
			return "\n\t<script type='text/javascript'>\n\t\t{$code}\n\t</script>";
		});
	}

	/**
	 * define javascript code
	 *
	 * @return	void
	 */
	public static function define($a,$b,$c=false){
		self::$codev[$a] = $b;
		
		if($b != "{}" && $b != "[]"){
			$b = "'{$b}'";
		}else{
			$b = "{$b} || codeHive.{$a}";
		}

		if(!$c){
			$b = " = {$b}";
		}else{
			$b = ".{$c}({$b})";
		}

		array_push(self::$code,"codeHive.{$a}{$b};");
	}

	/**
	 * get defined all variables
	 *
	 * @return	void
	 */
	public static function variables(){
		return self::$codev;
	}

	/**
	 * load script
	 *
	 * @return	void
	 */
	public static function script($src,$listener='script'){
		global $config;


		$external = explode("://", $src);// chk external
		if(sizeof($external) <= 1){

			$ex = explode("@",$src);
			$path = (sizeof($ex) > 1)?Module::path($ex[1]):false;
			if(!$path)$path = "{$config['app']}";
			else $src = $ex[0];

			$path = Request::base($path);
			$src = "{$path}/vendor/{$src}";
		}

		Event::addListener($listener,function() use ($src){
			return "\n\t<script type='text/javascript' src='{$src}'></script>";
		});
	}

	/**
	 * style load
	 *
	 * @return	void
	 */
	public static function style($src,$listener='style'){
		global $config;

		$external = explode("://", $src);// chk external
		if(sizeof($external) <= 1){

			$ex = explode("@",$src);
			$path = (sizeof($ex) > 1)?Module::path($ex[1]):false;
			if(!$path)$path = "{$config['app']}";
			else $src = $ex[0];

			$path = Request::base($path);

			$src = "{$path}/vendor/{$src}";
		}

		$extra = File::extension($src)=='less'?'/less':"";

		Event::addListener($listener,function() use ($src, $extra){
			return "\n\t<link rel='stylesheet{$extra}' type='text/css' href='{$src}' />";
		});
	}

	/**
	 * style load
	 *
	 * @return	void
	 */
	public static function vendor($src=''){
		global $config;

		return Request::base("{$config['app']}/vendor");
	}
}