<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Object Module
 *
 * control URL Parameters
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Object{

	/**
	 * Object get
	 *
	 * Fetching Data from Object table by Taxonomy
	 *
	 * @access	public
	 * @param	taxonomy string
	 * @param	array 	sql array
	 * @return	void
	 */
	public static function get( $taxonomy, $arr=array() ){
		Module::import("Query");

		if(!is_array($arr["where"]))$arr['where'] = array();
		$taxonomy = explode(",", $taxonomy);
		$arr['where']['taxonomy'] = ":in:'".implode("','",$taxonomy)."'";
		return Query::get("objects",$arr);
	}

	/**
	 * Object fetch
	 *
	 * Fetching Data from Object table by Taxonomy
	 *
	 * @access	public
	 * @param	taxonomy string
	 * @param	array 	data array
	 * @return	void
	 */
	public static function fetch( $taxonomy, $data=array(), $where=array(), $additional=array() ){

		return self::get($taxonomy, array_merge(["data"=>$data, "where"=>$where],$additional));
	}


}

/* End of file Object.php */
/* Location: ./system/module/Object.php */
