<?php

defined('VERSION') or exit('Direct access to this location is not permitted');

/*
 * codeHive Schema.
 *
 * Schema class allow us to create tables with elegant style
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/Schema
 * @since      File available since Release 2.1.0
 *
 * @version    V: 2.1.0
 */
class Schema
{

    /**
     * Schema Table.
     *
     * @param	string 	Table Name
     *
     * @return mixen
     */
    public static function table($table)
    {
        $bldr = new SchemaBuilder();

        return $bldr->table($table);
    }

    // --------------------------------------------------------------------

    public static function version($sql)
    {
        // schema version update
    }
}

/* End of file Schema.php */
/* Location: ./system/core/Schema.php */
