<?php if (!defined('VERSION')) {
    exit('Direct access to this location is not permitted.');
}

/**
 * Purecis QueryRecord Module
 *
 * This class Control QueryRecord Requests
 *
 * @package         codeHive
 * @subpackage      Module
 * @category        Libraries
 * @author          Tamer Zorba
 * @link            http://purecis.com/
 */
class QueryRecord
{

    private $current = 0;
    private $records;

    public function __construct($records)
    {
        $this->records = $records;
        return $this;
    }

    public function all()
    {
        return $this->records->data;
    }

    public function index($i)
    {
        return $this->records->data[$i];
    }

    public function first()
    {
        $this->current = 0;
        return $this->records->data[$this->current];
    }

    public function next()
    {
        $this->current += 1;
        return $this->records->data[$this->current];
    }

}
