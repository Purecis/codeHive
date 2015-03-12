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

session_start();

include "system/core/codeHive.php";

$start = array(
	"app" 		=> "apps/sample",
	"assets" 	=> "assets",
	"system" 	=> "system"
); 

codeHive::start($start);

/* End of file index.php */
/* Location: ./index.php */