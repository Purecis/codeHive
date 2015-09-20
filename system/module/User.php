<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis User Module
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Extension
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class User{

	/**
	 * List of paths to load views from
	 *
	 * @var array
	 * @access protected
	 */
	protected static $current;
	public static $sessId = "userId";
	private static $lastRule = "";

	// --------------------------------------------------------------------

	/**
	 * User bootstrap
	 *
	 * @access	public
	 * @return	string
	 */
	public static function __bootstrap(){
		Module::import("Query");
		//Module::import("Meta");
	}

	// --------------------------------------------------------------------

	/**
	 * User register
	 *
	 * @access	public
	 * @param 	array of user data (email,pass[,oauth,name,rules,status,group,rel])
	 * @return	string
	 */
	public static function register($arr){
		$cls = new stdClass();
		$arr = (array)$arr;

		if(empty($arr['email']) || empty($arr['pass'])){
			$cls->error = "email or password could not be empty";
			$cls->code = 1;
			$cls->status = false;
			return $cls;
		}
		$arr['pass'] = self::encpass($arr['pass']);
		
		$arr['registered'] = isset($arr['registered'])?$arr['registered']:time();
		$arr['ip'] = isset($arr['ip'])?$arr['ip']:Request::ip();
		
		$nex = self::notexist($arr['email'],'email');
		if(!$nex->status)return $nex;

		if(!isset($arr['rules'])){
			$arr['rules'] = self::defaultRules();
		}

		$q = Query::set("users",['data'=>$arr]);
		if($q->status == true){
			Session::set(array(
				static::$sessId => $q->last
			));
		}
		return $q;
	}

	// --------------------------------------------------------------------

	/**
	 * User update
	 *
	 * @access	public
	 * @param 	array of user data (email,pass[,oauth,name,rules,status,group,rel])
	 * @return	mixen
	 */
	public static function update($arr,$where=false){
		$cls = new stdClass();
		$arr = (array)$arr;

		if(!$where){
			if(self::logged()){
				$where = array('id'=>Session::get(array(static::$sessId)));
			}else{
				$cls->status = false;
				$cls->error = "not logged";
				return $cls;
			}
		}

		if(!empty($arr['pass']))$arr['pass'] = self::encpass($arr['pass']);
		if(empty($arr['pass']))unset($arr['pass']);
		
		$arr['lastupdate'] = isset($arr['lastupdate'])?$arr['lastupdate']:time();
		$arr['lastip'] = isset($arr['lastip'])?$arr['lastip']:Request::ip();
		
		/* update if this email not related to another user
		$nex = self::notexist($arr['email'],'email');
		if(!$nex->status)return $nex;
		*/
		$q = Query::set("users",['data'=>$arr,'where'=>$where]);

		return $q;
	}

	// --------------------------------------------------------------------

	/**
	 * User get
	 *
	 * @access	public
	 * @param 	array of user data (email,pass,name,rules,status,group,rel)
	 * @param 	array where
	 * @return	mixen
	 */
	public static function get($data=false,$where=false){
		$cls = new stdClass();

		if(!$where){
			if(self::logged()){
				$where = array('id'=>Session::get(array(static::$sessId)));
			}else{
				$cls->status = false;
				$cls->error = "not logged";
				return $cls;
			}
		}

		if(!$data){
			$data = array("id","email");
		}

		$q = Query::get("users",array(
			"data" => $data,
			"where" => $where
		));

		return $q;
	}

	// --------------------------------------------------------------------

	/**
	 * check if user data in database
	 *
	 * @access	public
	 * @param 	string field value , ex: name@mail.com 
	 * @param 	string field key, ex: email 
	 * @return	object
	 */
	public static function notexist($field,$key='email'){
		$cls = new stdClass();

		$sql = Query::get("users",['data'=>'id','where'=>[$key=>$field]]);
		if($sql->count > 0){
			$cls->error = "{$key} already exist";
			$cls->code = 2;
			$cls->status = false;
			$cls->id = $sql->data[0]['id'];
		}else{
			$cls->status = true;
			$cls->id = 0;
		}

		return $cls;
	}

	// --------------------------------------------------------------------

	/**
	 * default rules
	 *
	 * @access	public
	 * @return	string
	 */
	public static function defaultRules(){
		return Query::get("users",['data'=>'rules','where'=>['id'=>0]])->data[0]['rules'];	
	}

	// --------------------------------------------------------------------

	/**
	 * Rules add or remove
	 *
	 * @access	public
	 * @param 	array of action [ add => array || string ]
	 * @return	mixen
	 */
	public static function rules($arr,$where=false){
		$cls = new stdClass();
		$arr = (array)$arr;

		if(!$where){
			if(isset($arr['where'])){
				$where = $arr['where'];
			}else{
				if(self::logged()){
					$where = array('id'=>Session::get(array(static::$sessId)));
				}else{
					$cls->status = false;
					$cls->error = "not logged";
					return $cls;
				}
			}
		}
		$rules = Query::get("users",['data'=>'rules','where'=>$where])->data[0]['rules'];
		if(!empty($rules))$rules = explode(",", $rules);
		else $rules = array();

		if(isset($arr['add'])){
			if(!is_array($arr['add']))$arr['add'] = array($arr['add']);
			foreach($arr['add'] as $ad){
				$k = array_search($ad, $rules);
				var_dump($k);
				if($k === false)array_push($rules, $ad);
			}
		}
		if(isset($arr['remove'])){
			if(!is_array($arr['remove']))$arr['remove'] = array($arr['remove']);
			foreach($arr['remove'] as $rm){
				$k = array_search($rm, $rules);
				unset($array[$k]);
			}
		}
		//print_r($rules);
		$rules = implode(",", $rules);

		$q = Query::set("users",['data'=>['rules'=>$rules],'where'=>$where]);
		return $q;
	}

	// --------------------------------------------------------------------

	/**
	 * User login
	 *
	 * @access	public
	 * @param 	array of user data (email,pass[,token])
	 * @return	string
	 */
	public static function login($arr){
		$arr = (Array)$arr;
		if(!isset($arr['token']))$arr['pass'] = self::encpass($arr['pass']);
		
		$q = Query::get("users",array(
			"data" => array("id","rules"),
			"where" => $arr
		));
		if($q->count > 0){
			Session::set(array(
				static::$sessId => $q->data[0]['id']
			));
		}else{
			$q = new stdClass();
			$q->error = "check your username and password";
			$q->code = 3;
			$q->status = false;
			self::logout(true);
		}
		return $q;
	}

	// --------------------------------------------------------------------

	/**
	 * User oauth
	 *
	 * @access	public
	 * @param 	array of user data (oauth,oauth_type,email)
	 * @return	string
	 */
	public static function oauth($arr){
		$ret = new stdClass();
		$ret->status = false;

		$arr = (Array)$arr;
		$oauth = $arr['oauth'];
		$email = $arr['email'];
		$name = $arr['name'];
		$provider = $arr['provider'];
		//$pass = self::encpass($arr['pass']);

		$q = Query::get("users",array(
			"data" => array("id"),
			"where" => array(
				"oauth_{$provider}" => $oauth,
				//"email" => $email
			)
		));
		// login
		if($q->count > 0){
			Session::set(array(
				static::$sessId => $q->data[0]['id']
			));
			$ret = $q;
			//$ret->status = true;
			$ret->type = "login";
		}

		// is email exist to link with
		if(!$ret->status){
			$q = Query::get("users",array(
				"data" => array("id"),
				"where" => array(
					"email" => $email
				)
			));
			if($q->count > 0){
				Query::set("users",array(
					"data" => array(
						"oauth_{$provider}" => $oauth
					),
					"where" => array(
						"id" => $q->data[0]['id']
					)
				));
				Session::set(array(
					static::$sessId => $q->data[0]['id']
				));
				$ret = $q;
				$ret->status = true;
				$ret->type = "link";
			}
		}

		// register user
		if(!$ret->status){
			$user = array(
				"name" => $name,
				"email" => $email,
				"pass" => self::encpass(String::randomId(10)),
				"oauth_{$provider}" => $oauth
			);
			$q = self::register($user);
			$ret = $q;
			$q->type = "register";
		}

		if(!$ret->status){
			$q = new stdClass();
			$q->error = "check your username and password";
			$q->code = 3;
			$q->status = false;
			self::logout(true);
		}
		return $ret;
	}

	// --------------------------------------------------------------------

	/**
	 * User setlog
	 *
	 * @access	public
	 * @return	void
	 */
	public static function setlog($id){
		Session::set(array(static::$sessId => $id));
	}

	// --------------------------------------------------------------------

	/**
	 * User logout
	 *
	 * @access	public
	 * @return	void
	 */
	public static function logout($force=false){
		Session::remove(array(static::$sessId),$force);
	}

	// --------------------------------------------------------------------

	/**
	 * User logged
	 *
	 * @access	public
	 * @return	string
	 */
	public static function logged(){
		return Session::exist(array(static::$sessId));
	}

	/**
	 * User Id
	 *
	 * @access	public
	 * @return	string
	 */
	public static function Id(){
		if(Session::exist(array(static::$sessId))){
			return Session::get(array(static::$sessId));
		}else{
			return 0;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * User info
	 *
	 * @access	public
	 * @return	string
	 */
	public static function info($more = array(),$more2 = array()){
		$cls = new stdClass();
		if(!self::logged() && is_array($more)){
			$cls->status = false;
			$cls->error = "not logged";
			$cls->code = 1;
		}else{
			if(!self::$current){
				if(!is_array($more)){
					$id = $more;
					$more = $more2;
				}
				if(empty($id))$id = Session::get(array(static::$sessId));

				$data = array("id","name","email","rules","status");
				$data = array_merge($data,$more);
				$cls = Query::get("users",array(
					"data" => $data,
					"where" => array(
						"id" => $id
					)
				));
			}else{
				// check the more and append to
			}
		}
		return $cls;
	}

	// --------------------------------------------------------------------

	/**
	 * User hasRule
	 *
	 * @access	public
	 * @return	boolean
	 */
	public static function hasRule($rules = array(),$defRules=false){
		self::$lastRule = $rules;

		if(!is_array($rules))$arr = explode(",", $rules);
		else $arr = $rules;

		if($defRules != false){
			if(!is_array($defRules))$rules = explode(",",$defRules);
			else $rules = $defRules;
		}else{
			$user = self::info();
			if(!$user->status)return false;
			$rules = explode(",",$user->data[0]['rules']);
		}
		$ret = true;
		
		foreach($arr as $rule){
			if(!in_array($rule, $rules))$ret = false;
		}

		return $ret;
	}


	// --------------------------------------------------------------------

	/**
	 * Password Encrypt
	 *
	 * use license hash which defined on config
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */
	public static function encpass($pass){
		return md5(String::encrypt($pass)).sha1(String::encrypt($pass)); 
	}


	// --------------------------------------------------------------------

	/**
	 * Access Denied
	 *
	 * use license hash which defined on config
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */
	public static function accessDenied($rule=self::$lastRule){
		$cls = new stdClass;
		$cls->status = false;
		$cls->error = "you need rules {$rule}";
		return $cls;
	}

}
