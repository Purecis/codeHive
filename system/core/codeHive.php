<?php
/**
 * Purecis codeHive Class
 *
 * bootstrap class 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Purecis Dev Team
 * @copyright	Copyright (c) 2004 - 2015, Purecis, Inc.
 * @license		http://purecis.com/license
 * @link		http://purecis.com
 * @since		Version 2.0
 * @filesource
 */

/**
 * auto load class when it called (Library classes only)
 */
spl_autoload_register(function($class) {
	global $config;
	//if(strpos($class,"codeHive\Module\\") !== false){
	//	$class = substr($class,strlen("codeHive\Module\\"));
	//	Module::import($class);
	//}else{
		$class = str_replace("\\", "/", $class);
		if(is_file("{$config['system']}/core/{$class}.php")){
			require_once "{$class}.php";
			if(is_callable(array($class,'__bootstrap')))call_user_func(array($class,'__bootstrap'));
			if($config['ENVIRONMENT'] == 'debug')debug::message("Used System Cores",$class);
		}else{
			if($config['ENVIRONMENT'] == 'debug')debug::error("System Cores Faild",$class);
		}
	//}
});

/**
 * Locale Alias __
 *
 * @access	public
 * @param	mixen 	str or array keys only
 * @return	mixen
 */
function __($e,$a=array(),$space=false){
	return Internationalization::translate($e,$a,$space);
}

/**
 * Class Start
 */
class codeHive{//composer, artisan, Hive , Arti, Zorba, codeHive, iBuilder

	public static function start($start = array('app'=>'app')){
		global $config;

		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
		  throw new Exception('The codeHive Framework v2 requires PHP version 5.3 or higher, (5.4) is Recomonded.');
		}
		
		define(VersionMajor,"2");
		define(VersionMinor,"0");
		define(VersionPatch,"00");
		define(VersionCode,"Alpha");
		define(VersionBuild,"2096");
		
		define(VERSION,"v.".VersionMajor.".".VersionMinor.".".VersionPatch." ".VersionCode.", Build ".VersionBuild);
		
		$config = array();
		$config['app'] = $start['app']?:"app";
		$config['assets'] = $start['assets']?:"assets";
		$config['system'] = $start['system']?:"system";

		$config = array_merge($config,self::config($config['app']));

		if(!$config['settings'])$config['settings'] = array();

		// defines
		define(INDEX_FILE, $config['index']?:'index.php');
		define(SESSION_PERFIX, $config['session']?:'');
		if($config['license'])define(SECURITY_HASH, $config['license']['hash']);

		// env
		if ($config['ENVIRONMENT']){
			switch ($config['ENVIRONMENT']){
				case 'development':
					error_reporting(E_ALL);
				break;

				case 'debug':
					error_reporting(E_ALL);
					Debug::__bootstrap();
				break;

				case 'production':
					error_reporting(0);
				break;

				default:
					exit('The application environment is not set correctly.');
			}
		}

		echo Module::__bootstrap();

		require_once "{$start['app']}/app.php";

		echo Module::__shutdown();

		if($config['ENVIRONMENT'] == 'debug')Debug::__shutdown();
	}

	private static function config($app){
		if(!file_exists("{$app}/config.php")){
			Install::initialize();
			exit;
		};
		return require_once "{$app}/config.php";
	}
}

/* End of file codeHive.php */
/* Location: ./system/core/codeHive.php */