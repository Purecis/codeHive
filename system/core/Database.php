<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Database Class
 *
 * This class Manage Database Connections & Queries
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Database{

	/**
	 * Database Live Connection
	 *
	 * @var mixen
	 * @access protected
	 */
	protected static $db = null;

	// --------------------------------------------------------------------

	/**
	 * Database Connect
	 *
	 * @access	public
	 * @param	string 	Database name
	 * @param	string 	Database host
	 * @param	string 	Database user
	 * @param	string 	Database pass
	 * @param	string 	Database port
	 * @return	mixen
	 */
	public static function connect($db_name=null,$db_host=null,$db_user=null,$db_pass=null,$db_port=null){
		global $config;

		if(!$config['database'])$config['database'] = array();

		if (!self::$db or ($db_name && $db_name != $config['database']['name'])){
			if($db_name)$config['database']['name'] = $db_name;
			if($db_host)$config['database']['host'] = $db_host;
			if($db_user)$config['database']['user'] = $db_user;
			if($db_pass)$config['database']['pass'] = $db_pass;
			if($db_port)$config['database']['port'] = $db_port;

			$type = $config['database']['type'];
			$host = $config['database']['host'];
			$name = $config['database']['name'];
			$user = $config['database']['user'];
			$pass = $config['database']['pass'];
			$port = $config['database']['port'];

			try{
				// Checking Database Type…
				if($type == 'sqlite')	self::$db = new PDO("sqlite:{$name}");
				if($type == 'mysql')	self::$db = new PDO("mysql:host={$host};dbname={$name};charset=utf8;Allow User Variables=True", $user, $pass);
				if($type == 'pgsql')	self::$db = new PDO("pgsql:dbname={$name};host={$host}", $user, $pass );
				if($type == 'oracle')	self::$db = new PDO("OCI:dbname={$name};charset=UTF-8", $user, $pass);
				if($type == 'firebird')	self::$db = new PDO("firebird:dbname={$host}:{$name}", "SYSDBA", $pass);
				if($type == 'infomix')	self::$db = new PDO("informix:DSN={$name}", $user, $pass);
				if($type == 'dblib') 	self::$db = new PDO ("dblib:host={$host}:{$port};dbname={$name}",$user,$pass);
				if($type == 'odbc')		self::$db = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq={$name}");

				self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//set character set utf8

			}catch(PDOException $e){
				//if($config['ENVIRONMENT'] == 'debug')debug::error("Database Error",$e->getMessage());
				//else
				die("Database Error :".$e->getMessage());
			}
    	}
		return self::$db;
	}

	// --------------------------------------------------------------------

	/**
	 * Database Query
	 *
	 * @access	public
	 * @param	string 	sql
	 * @return	array
	 */
	public static function query($sql){
		global $config;

		self::connect();
		$return = new stdClass();
		try{
			$query = self::$db->query($sql);
			//$query = $link->prepare('SELECT * FROM users WHERE username = :name LIMIT 1;');
			//$query->execute([':name' => $username]); # No need to escape it!

			$temp_sql = strtolower($sql);
			$s = strpos($temp_sql,'select');
			$i = strpos($temp_sql,'insert');
			$u = strpos($temp_sql,'update');
			$d = strpos($temp_sql,'delete');
			$t = strpos($temp_sql,'truncate');
			if($s < 10 && $s !== false)$type = 'select';
			if($i < 10 && $i !== false)$type = 'insert';
			if($u < 10 && $u !== false)$type = 'update';
			if($d < 10 && $d !== false)$type = 'delete';
			if($d < 10 && $d !== false)$type = 'truncate';

			$fetch = $config['database']['fetch']=="array"?PDO::FETCH_ASSOC:PDO::FETCH_CLASS;
			if(in_array($type, array('select','update','delete','truncate')))$return->count = $query->rowCount();
			if($type == 'select')$return->data = @$query->fetchAll($fetch);
			if($type == 'insert')$return->last = self::$db->lastInsertId();

			$return->status = true;

			if($config['ENVIRONMENT'] == 'debug')debug::count('Database Queries');

		}catch(PDOException $e){
			if($config['ENVIRONMENT'] == 'debug')debug::error("Database Error",String::escape($e->getMessage()));
			$return->status = false;
			$return->error = $e->getMessage();
    	}

    	if(in_array($config['ENVIRONMENT'], array('debug','development')))$return->sql = $sql;
    	return $return;
	}
}

/* End of file Database.php */
/* Location: ./system/core/Database.php */
