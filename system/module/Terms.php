<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Terms Module
 *
 * This class Control Terms Requests
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Terms{

	/**
	 * Terms bootstrap
	 *
	 * @return	void
	 */
	public static function set($arr){
		Module::import('Query');
		/*
		$arr = array(
			"name" => "cat name",
			"objects" => ["1"],
			//"unlink" => ["123"],
			"taxonomy" => "categories",
			"parent" => 0,
			"meta" => array(
				"img" => "image source"
			)
		);
		*/
		if(isset($arr['id'])){
			if(is_array($arr['id'])){
				$a = array();
				foreach($arr['id'] as $id){
					$args = $arr;
					$args['id'] = $id;
					array_push($a,self::set($args));
				}
				return $a;
			}
			$term = Query::get('terms',array(
				'where' => array(
					'id' => $arr['id']
				)
			));
		}else{
			$term = Query::get('terms',array(
				'where' => array(
					'name' => $arr['name'],
					"taxonomy" => $arr['taxonomy'],
					'parent' => $arr['parent'],
				)
			));
		}

		// add it if not exist
		if($term->count < 1){
			$n = Query::set("terms",array(
				"data" => array(
					"name" => $arr['name'],
					"taxonomy" => $arr['taxonomy'],
					"parent" => $arr['parent']
				)
			));
			if($n->status){
				$current = $n->last;
			}else{
				die($n->error);
			}
		}else{
			$current = $term->data[0]['id'];
		}

		// linking terms
		$append_count = 0;
		if(isset($arr['objects'])){
			if(!is_array($arr['objects']))$arr['objects'] = array($arr['objects']);
			if(sizeof($arr['objects']) > 0){
				foreach($arr['objects'] as $linkId){
					$q = Query::set("relations",array(
						"data" => array(
							"oid" => $linkId,
							"rid" => $current,
							"table" => "terms"
						),
						"ignore" => true
					));
					if($q->status == true && $q->last != 0)$append_count++;
				}
			}
		}
		if(isset($arr['unlink'])){
			if(is_array($arr['unlink']))$arr['unlink'] = implode(",",$arr['unlink']);
			Query::remove("relations",array(
				"where" => array(
					"rid" => $current,
					"oid" => ":in:".$arr['unlink'],
					"table" => "terms"
				)
			));
		}
		//unobject

		//update it for extra info or meta :) just do update without updating same table just meta
		$meta = isset($arr['meta'])?$arr['meta']:array();
		if($append_count > 0)$meta['count'] = ":append:{$append_count}";
		// update if id send
		if(isset($arr['id']) && isset($arr['name']))$meta['name'] = $arr['name'];
		if(isset($arr['id']) && isset($arr['taxonomy']))$meta['taxonomy'] = $arr['taxonomy'];
		if(isset($arr['id']) && isset($arr['parent']))$meta['parent'] = $arr['parent'];

		$u = Query::set("terms",array(
			"data" => $meta,
			"where" => array("id"=>$current)
		));

		//update count or with meta update it, and insert relationship with objects sended (is array or one)

		//print_r($term);
		$cls = new stdClass();
		$cls->last = $current;
		$cls->status = $u->status;
		return $cls;
	}

	// --------------------------------------------------------------------

	/**
	 * Terms get
	 *
	 * 1. get terms with taxonomies linked to object
	 *    ['objects'=>keys_array(object), meta=>keys_array(terms), taxonomy=>string]
	 *
	 * 2. get objects related to those terms
	 *	  ['terms'=>keys_array(terms), meta=>keys_array(object), taxonomy=>string]
	 *
	 * 3. get list of terms by parent hierarchical structure
	 *    [parent=>termId, levels=>int, taxonomy=>string]
	 *
	 * 4. get term child parents
	 *
	 * 5. get term by id
	 *    [id=>termId, taxonomy=>string, meta=>keys_array(term)]
	 *
	 * @return	function
	 */
	public static function get($arr){
		// loop with array, and set parents then push childs
		/*
		$arr = array(
		//	"id" => 1,
		//	"terms" 		=> [55,56],// if send get all object related to this post with taxonomies
		//	'objects' 		=> [1,2,5], // if send get all terms that linked to those objects with the taxonomy
			"taxonomy" 	=> "categories",
			'parent' 	=> 61, // if send get the list of child parent std class
			"levels" => 4,
		//	"meta" => array("img")
		);
		*/

		//return terms
		if(isset($arr['id'])){
			if(!is_array($arr['id']))$arr['id'] = array($arr['id']);
			$sql = array(
				"data"=> isset($arr['meta'])?$arr['meta']:array(),
				"where" => array(
					"id" => ":in:".implode(",", $arr['id']),
				)
			);
			if(isset($arr['taxonomy']))$sql['where']['taxonomy'] = $arr['taxonomy'];
			array_push($sql['data'], "id");
			array_push($sql['data'], "name");
			array_push($sql['data'], "parent");
			array_push($sql['data'], "count");

			return Query::get("terms",$sql);
		}

		//return terms
		if(isset($arr['objects'])){
			if(!is_array($arr['objects']))$arr['objects'] = array($arr['objects']);
			$sql = array(
				"data"=> isset($arr['meta'])?$arr['meta']:array(),
				"join" => array(
					"relations" => "relations.oid in (".implode(", ",$arr['objects']).") AND relations.table = 'terms'"
				),
				"where" => array(
					"relations.rid" => "~terms.id",//"~:like:terms.id",
				)
			);
			if(isset($arr['taxonomy']))$sql['where']['taxonomy'] = $arr['taxonomy'];
			array_push($sql['data'], "id");
			array_push($sql['data'], "name");

			return Query::get("terms",$sql);
		}

		//return objects
		if(isset($arr['terms'])){//need update: all childs related
			$sql = array(
				"data"=> isset($arr['meta'])?$arr['meta']:array(),
				"join" => array(
					"relations" => "relations.rid in (".implode(", ",$arr['terms']).") AND relations.table = 'terms'"
				),
				"where" => array(
					"relations.oid" => "~objects.id",
				)
			);
			if(isset($arr['taxonomy']))$sql['where']['taxonomy'] = $arr['taxonomy'];
			array_push($sql['data'], "id");
			array_push($sql['data'], "author");

			return Query::get("objects",$sql);
		}

		//return terms by parent
		if(isset($arr['parent'])){
			if(!isset($arr['levels']))$arr['levels'] = 1;
			$st = Query::__structure('terms');
			$sarr = array();
			$warr = array();
			$sql = "SELECT ";
			for($i=1;$i<=$arr['levels'];$i++){
				$str = "t{$i}.id AS lev{$i}_id, t{$i}.name AS lev{$i}_name";
				if(isset($arr['meta'])){
					foreach($arr['meta'] as $m){
						if(array_key_exists($m, $st)){
							$str .= ", t{$i}.{$m} AS lev{$i}_{$m}";
						}else{
							$str .= ", lev{$i}_{$m}.value as lev{$i}_{$m}";
						}
					}
				}
				array_push($sarr,$str);
			}
			$sql .= implode(",\n", $sarr);
			$sql .= " FROM terms AS t1 ";
			for($i=2;$i<=$arr['levels'];$i++){
				$str = "LEFT JOIN terms AS t{$i} ON t{$i}.parent = t".($i-1).".id";
				if(isset($arr['taxonomy']))$str .= " and t".($i).".taxonomy = '{$arr['taxonomy']}'";
				array_push($warr,$str);
			}
			for($i=1;$i<=$arr['levels'];$i++){
				if(isset($arr['meta'])){
					foreach($arr['meta'] as $m){
						if(!array_key_exists($m, $st)){
							array_push($warr, "LEFT JOIN meta as lev{$i}_{$m} ON `lev{$i}_{$m}`.`oid` = `t{$i}`.`id` and `lev{$i}_{$m}`.`key`='{$m}' and `lev{$i}_{$m}`.`table` = 'terms' ");
						}
					}
				}
			}
			$sql .= implode(" \n", $warr);

			if(isset($arr['wheremeta'])){ // where
				foreach($arr['wheremeta'] as $k => $v){
					$sql .= " LEFT JOIN meta as `{$k}` ON `{$k}`.`oid` = `t1`.`id` and `{$k}`.`key`='{$k}' and `{$k}`.`table` = 'terms' ";
				}
			}
			$sql .= " WHERE t1.parent = {$arr['parent']}";
			if(isset($arr['taxonomy']))$sql .= " and t1.taxonomy = '{$arr['taxonomy']}' ";

			if(isset($arr['wheremeta'])){ // where
				$wh = array();
				foreach($arr['wheremeta'] as $k => $v){
					$sql .= " and `{$k}`.`value` = '{$v}' ";
				}
			}
			$sql .= ";";
			//ECHO $sql;
			
			$return = new stdClass();
			$count = 1;
			$q = Database::query($sql);
			foreach($q->data as $row){
				
				$me = $return;
				foreach($row as $col => $value){
					if(!empty($value)){
						$t = explode("_", $col);
						$lev = str_replace("lev", "", $t[0]);
						
						if($t[1] == 'id'){
							if(!isset($me->child))$me->child = new stdClass();
							if(!isset($me->child->$value))$me->child->$value = new stdClass();
							$me = $me->child->$value;
						}
						$me->$t[1] = $value;
					}
				}
				$count++;
			}

			if(isset($return->child)){
				return $return->child;
			}
			return false;
		}
	}


	//remove
	// set force to remove all relations or remove just the relation in the same taxonomy
	// if count zero then remove it from terms either
	// remove all meta .. or set it from Query class bcz trigger need super visor priviliges
	public static function remove($id){
		$r = Query::remove("terms",array('where'=>array('id'=>$id)));
		Query::remove("relations",array('where'=>array('rid'=>$id,'table'=>'terms')));
		return $r;
	}
}