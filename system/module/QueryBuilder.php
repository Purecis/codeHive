<?php if (!defined('VERSION')) {
    exit('Direct access to this location is not permitted.');
}

/**
 * Purecis QueryBuilder Module
 *
 * This class Control QueryBuilder Requests
 *
 * @package         codeHive
 * @subpackage      Module
 * @category        Libraries
 * @author          Tamer Zorba
 * @link            http://purecis.com/
 */

class QueryBuilder
{
    /**
     * Variables
     *
     * @var mixen
     * @access protected
     */
    private $table;
    private $param  = array();
    public $where  = "";
    private $join  = "";
    private $group = "";
    private $order = "";
    private $limit = "";
    private $query;
    private $records;

    // --------------------------------------------------------------------
    public function __get($property) {
        echo "Getting '$property'\n";
        return 123;
    }
    /**
     * ajax table
     *
     * @return    void
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * set params to query
     * @param  String $args Mix of data to get
     * @return Object QueryBuilder
     */
    public function param()
    {
        $this->param = array_merge($this->param, func_get_args());
        return $this;
    }

    public function where()
    {
        if (strlen($this->where)) {
            $this->where .= " AND ";
        }

        $args = func_get_args();
        $this->where .= $this->_whereParse($args);
        return $this;
    }

    public function orWhere()
    {
        if (strlen($this->where)) {
            $this->where .= " OR ";
        }

        $args = func_get_args();
        $this->where .= $this->_whereParse($args);
        return $this;
    }

    /**
     * Builder : join
     * @return Object QueryBuilder
     */
    public function join()
    {
        $args = func_get_args();
        $this->join .= $this->_joinParse($args);
        return $this;
    }

    /**
     * Builder : group
     * @return Object QueryBuilder
     */
    public function group()
    {
        $args        = func_get_args();
        $this->group = " GROUP BY " . $this->_col($args[0]);
        return $this;
    }

    /**
     * Builder : order
     * @return Object QueryBuilder
     */
    public function order()
    {
        $args = func_get_args();
        if (sizeof($args) == 2) {
            $this->order = " ORDER BY " . $this->_col($args[0]) . " {$args[1]}";

        } else if (sizeof($args) == 1) {
            $this->order = " ORDER BY " . $this->_col($args[0]) . " DESC";

        } else {
            $this->order = " ORDER BY " . $this->_col("id") . " DESC";

        }
        return $this;
    }

    public function limit()
    {
        $args = func_get_args();
        if (sizeof($args) == 2) {
            $this->limit = " LIMIT {$args[0]}, {$args[1]}";
        } else {
            $this->limit = " LIMIT {$args[0]}";
        }
        return $this;
    }

    /**
     * generate select statement
     * @return Object SQL Query
     */
    private function _get()
    {
        $args = func_get_args();

        $this->query = "SELECT ";
        $this->query .= $this->_param();
        $this->query .= " FROM ";
        $this->query .= $this->_table($this->table);
        $this->query .= $this->join;
        $this->query .= $this->_where();
        $this->query .= $this->group;
        $this->query .= $this->order;
        $this->query .= $this->limit;
        if(!$args[0])$this->query .= ";";
        return $this->query;
    }

    public function get()
    {
        $this->_get();
        return $this->records = Database::query($this->query);
    }

    public function record()
    {
        $this->get();
        Module::import("QueryRecord");
        return new QueryRecord($this->records);
    }

    private function _table()
    {
        $args = func_get_args();
        return "`{$args[0]}`";
    }

    private function _col()
    {
        $args = func_get_args();

        if (is_callable($args[0])) {
            $q = new QueryBuilder();
            call_user_func($args[0], $q);
            return "( {$q->_get(true)} )";
        }

        $args[0] = trim($args[0]);

        if(strpos($args[0],'~') === 0){
            return $this->_col(substr($args[0],1));

        }else if(substr_count($args[0],' as ') == 1 && !$args[1]){
            $exp = explode(" as ",$args[0]);
            print_r($exp);
            return $this->_col($exp[0]). " AS `{$exp[1]}`";

        }else if(substr_count($args[0],'.') == 1 && !$args[1]){
            $exp = explode(".",$args[0]);
            return "`{$exp[0]}`.`{$exp[1]}`";

        }else if($args[1]){
            if(is_array($args[0]))return "('".implode("', '", $args[0])."')";
            else return "'{$args[0]}'";

        }else{
            return "`{$this->table}`.`{$args[0]}`";
        }
    }

    private function _param()
    {
        if (!sizeof($this->param)) {
            return "*";
        }

        // else
        $arr = array();
        foreach ($this->param as $param) {
            array_push($arr, $this->_col($param));
        }

        return implode(", ", $arr);
    }

    public function _where()
    {
        if (!strlen($this->where)) {
            return "";
        }

        //else
        return " WHERE {$this->where}";
    }

    private function _joinParse($args)
    {
        if (is_callable($args[0])) {
            $q = new QueryBuilder();
            $q->table($this->table);
            call_user_func($args[0], $q);
            return "( {$q->where} )";
        }

        // TODO : check is function and send the table to create inline where
        $str = "";

        $str .= " JOIN ";
        $str .= $this->_table($args[0]);
        $str .= " ON ";
        array_shift($args);
        $str .= call_user_func_array(array($this, "_parseOperators"), $args);
        // $str .= $this->_col($args[1]);
        // if (!$args[3]) {
        //     $str .= " = ";
        //     $str .= $this->_col($args[2]);
        // } else {
        //     $str .= " {$args[2]} ";
        //     $str .= $this->_col($args[3]);
        // }

        return $str;
    }

    private function _whereParse($args)
    {
        if (is_callable($args[0])) {
            $q = new QueryBuilder();
            $q->table($this->table);
            call_user_func($args[0], $q);
            // TODO : check if in to return full query in or it seems next args to check;
            return "( {$q->where} )";
        }

        return call_user_func_array(array($this, "_parseOperators"), $args);
    }

    private function _parseOperators()
    {
        $args = func_get_args();

        $str = "";
        $str .= $this->_col($args[0]);

        switch ($args[1]) {
            case '=':
                $str .= " = ".$this->_col($args[2],true);
                break;

            case '!=':
                $str .= " <> ".$this->_col($args[2],true);
                break;

            case '<>':
                $str .= " <> ".$this->_col($args[2],true);
                break;

            case 'in':
                $str .= " IN ".$this->_col($args[2],true);
                break;

            default:
                $str .= " = ".$this->_col($args[1],true);
                break;
        }

        return $str;
    }
}
