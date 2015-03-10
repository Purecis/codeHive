<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Benchmark Class
 *
 * This class Manage Benchmarks 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Benchmark{

	/**
	 * List of benchmarks
	 *
	 * @var array
	 * @access protected
	 */
	protected static $Benchmarks = array();

	// --------------------------------------------------------------------

	/**
	 * Benchmark Start
	 *
	 * @access	public
	 * @param	string 	Benchmark name
	 * @return	array
	 */
	public static function start($name){
		self::$Benchmarks[$name] = array();
		self::$Benchmarks[$name]['time'] = microtime(true);
		self::$Benchmarks[$name]['memory'] = memory_get_usage();
		return self::$Benchmarks[$name];
	}

	// --------------------------------------------------------------------

	/**
	 * Benchmark Complete
	 *
	 * @access	public
	 * @param	string 	Benchmark name
	 * @return	array
	 */
	public static function complete($name){
		global $config;
		
		$arr = array(
			"time" 		=> microtime(true),
			"memory" 	=> memory_get_usage()
		);
		if($config['ENVIRONMENT'] == 'debug'){
			$time = round($arr['time']-self::$Benchmarks[$name]['time'],4);
			$memory = $arr['memory'] - self::$Benchmarks[$name]['memory'];
			$memory = $memory." - ".File::format($memory);

			debug::error("Benchmark {$name}","Time : {$time} | Memory : {$memory}");
		}
		return $arr;
	}

}

/* End of file Benchmark.php */
/* Location: ./system/core/Benchmark.php */