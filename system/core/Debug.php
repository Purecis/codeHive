<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Debug Class
 *
 * This class Control Files on the server.
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Debug{

	private static $message = array();
	private static $error = array();
	private static $count = array();
	private static $microtime;
	private static $memory;

	private static $disabled = false;

	public static function __bootstrap(){
		if(self::$disabled)return;
		self::$microtime = microtime(true);
		self::$memory = memory_get_usage();
	}

	public static function __shutdown(){
		if(self::$disabled)return;

		$time = round(microtime(true)-self::$microtime,4);
		$memory = memory_get_usage();
		$rmemory = $memory-self::$memory;
		$memory .= " - %c".File::format($memory);
		$rmemory .= " - %c".File::format($rmemory);
		$memoryp = memory_get_peak_usage(true);
		$memoryp .= " - %c".File::format($memoryp);
		$version = VERSION;
		$ext = File::extension(request::alias());

		$comment = (in_array($ext, array('js','css')))?true:false;

		if($comment)echo "/** Debug **";

		echo "\n\n\n\n\n\n<script>";
		echo "console.log('%cTime to Excute \t: %c{$time}', 'color:#999', 'color:#099');";
		echo "console.log('%cMemory Usage \t\t: %c{$rmemory}', 'color:#999', 'color:#ddd', 'color:#099');";
		echo "console.log('%cTotal Memory Usage : %c{$memory}', 'color:#999', 'color:#ddd', 'color:#099');";
		echo "console.log('%cTotal Memory Peaks : %c{$memoryp}', 'color:#999', 'color:#ddd', 'color:#099');";

		foreach(self::$count as $type => $arr){
			$msg = sizeof($arr);
			$space = 5-floor(strlen($type)/4);
			$space = str_repeat("\t", $space);
			echo "console.log('%c{$type}{$space}: %c{$msg}', 'color:#999', 'color:#099');";
		}

		foreach(self::$message as $type => $arr){
			$msg = implode($arr, ", ");
			$space = 5-floor(strlen($type)/4);
			$space = str_repeat("\t", $space);
			echo "console.log('%c{$type}{$space}: %c{$msg}', 'color:#999', 'color:#099');";
		}


		foreach(self::$error as $type => $arr){
			foreach($arr as $k => $v)if(is_array($arr[$k]))$arr[$k] = "(".implode($v, "|").")";
			$msg = implode($arr, ", ");
			$space = 5-floor(strlen($type)/4);
			$space = str_repeat("\t", $space);
			echo "console.log('%c{$type}{$space}: %c{$msg}', 'color:#900', 'color:#111');";
		}

		echo "console.log('%cCore Version \t\t: %c{$version}', 'color: #999', 'color:#ddd');";
		echo "console.log('%cCopyright \t\t\t: %cPowered By Pure Core International Solutions (Purecis)', 'color: #999', 'color:#ddd');";

		echo "</script>";

		if($comment)echo "*/";
		
	}

	public static function message($parent,$msg){
		if(!isset(self::$message[$parent]))self::$message[$parent] = array();
		if(array_search($msg,self::$message[$parent]) === false)array_push(self::$message[$parent],$msg);
	}

	public static function error($parent,$msg){
		if(!isset(self::$error[$parent]))self::$error[$parent] = array();
		if(array_search($msg,self::$error[$parent]) === false)array_push(self::$error[$parent],$msg);
	}

	public static function count($parent){
		if(!isset(self::$count[$parent]))self::$count[$parent] = array();
		array_push(self::$count[$parent],"");
	}

	public static function disable(){
		self::$disabled = true;
	}
}
?>