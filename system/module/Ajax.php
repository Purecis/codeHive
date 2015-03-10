<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Ajax Module
 *
 * This class Control Ajax Requests
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Ajax{

	/**
	 * Variables
	 *
	 * @var mixen
	 * @access protected
	 */
	private static $ajaxs;

	// --------------------------------------------------------------------

	/**
	 * ajax bootstrap
	 *
	 * @return	void
	 */
	public static function __bootstrap(){
		Module::import('Router');
		
		Router::on("ajax",array('fire','do','token'),function(&$scope,$router){
			if(isset(self::$ajaxs[$router->fire])){
				return call_user_func_array(self::$ajaxs[$router->fire], array(&$scope,$router));
			}
		});
	}

	// --------------------------------------------------------------------

	/**
	 * ajax register
	 *
	 * @return	function
	 */
	public static function register($ajax,$callback){
		self::$ajaxs[$ajax] = $callback;
		return $callback;
	}
}