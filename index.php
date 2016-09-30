<?php
/**
 * codeHive Boot File.
 *
 * @author		Purecis Dev Team
 * @copyright	Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license		http://codehive.purecis.com/license
 *
 * @link		http://codehive.purecis.com
 * @since		Version 2.0
 * @filesource
 */
session_start();

include 'system/CoreServices/codeHive.php';

App\System\codeHive::boot(['app' => 'sample']);

/* End of file index.php */
/* Location: ./index.php */