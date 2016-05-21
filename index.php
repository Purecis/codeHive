<?php
/**
 * codeHive Excute File.
 *
 * @author		Purecis Dev Team
 * @copyright	Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license		http://purecis.com/license
 *
 * @link		http://purecis.com
 * @since		Version 2.0
 * @filesource
 */
session_start();

include 'system/core/codeHive.php';

codeHive::start(array(
    'app' => 'online-exam',

    // define folders
    'container' => "apps",
    'assets' => 'assets',
    'system' => 'system',
));


/* End of file index.php */
/* Location: ./index.php */
