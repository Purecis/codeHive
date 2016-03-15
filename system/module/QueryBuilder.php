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
 * @todo            shared lock, lock for update
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
    // private $ascol  = array();
    public $where  = "";
    private $join  = "";
    private $group = array();
    private $order = array();
    private $limit = "";
    private $query;
    private $records;
    private $union = "";
    public $join_on = "";

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

    public function on()
    {
        $args = func_get_args();
        if (!strlen($this->join_on)) $str = " ON ";
        else $str = " AND ";

        $str .= call_user_func_array(array($this, "_parseOperators"), $args);

        $this->join_on .= $str;

        return $this;
    }

    public function orOn()
    {
        $args = func_get_args();
        if (!strlen($this->join_on)) $str = " ON ";
        else $str = " OR ";

        $str .= call_user_func_array(array($this, "_parseOperators"), $args);

        $this->join_on .= $str;

        return $this;
    }

    /**
     * Builder : group
     * @return Object QueryBuilder
     */
    public function group()
    {
        $this->group = array_merge($this->group, func_get_args());
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
            array_push($this->order, array($args[0], strtoupper($args[1])));

        } else if (sizeof($args) == 1) {
            array_push($this->order, [$args[0], "DESC"]);

        } else {
            array_push($this->order, ["id", "DESC"]);

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

    public function union()
    {
        $args = func_get_args();
        $this->union .= $this->_unionParse($args[0]);

        return $this;
    }

    public function unionAll()
    {
        $args = func_get_args();
        $this->union .= $this->_unionParse($args[0], "ALL ");

        return $this;
    }

    /**
     * generate SELECT statement
     * @return Object SQL Query
     */
    public function _get()
    {
        $args = func_get_args();

        $this->query = "SELECT ";
        $this->query .= $this->_param();
        $this->query .= " FROM ";
        $this->query .= $this->_table($this->table);
        $this->query .= $this->join;
        $this->query .= $this->_where();
        $this->query .= $this->_group();
        $this->query .= $this->_order();
        $this->query .= $this->limit;
        $this->query .= $this->union;
        if(!$args[0])$this->query .= ";";
        return $this->query;
    }

    public function get()
    {
        $this->_get();
        return $this->records = Database::query($this->query);
    }

    /**
     * generate INSERT, UPDATE statement
     * @return Object SQL Query
     */
    private function _set()
    {
        $args = func_get_args();

        if (!strlen($this->where)) {
            $this->query = "INSERT INTO ";
            $this->query .= $this->_table($this->table);
            $this->query .= " (".$this->_param("insert",1).")";
            $this->query .= " VALUES";
            $this->query .= " (".$this->_param("insert",2).")";

        }else{
            $this->query = "UPDATE ";
            $this->query .= $this->_table($this->table);
            $this->query .= " SET ";
            $this->query .= $this->_param("update");
            $this->query .= $this->join;
            $this->query .= $this->_where();
        }

        $this->query .= ";";

        return $this->query;
    }

    public function set()
    {
        $this->_set();
        return $this->query;
        return $this->records = Database::query($this->query);
    }

    /**
     * generate DELETE statement
     * @return Object SQL Query
     */
    private function _delete()
    {
        $this->query = "DELETE FROM ";
        $this->query .= $this->_table($this->table);
        $this->query .= $this->_where();
        $this->query .= $this->_order();
        $this->query .= $this->limit;
        $this->query .= ";";

        return $this->query;
    }

    public function delete()
    {
        $this->_delete();
        return $this->query;
        return $this->records = Database::query($this->query);
    }

    /**
     * generate TRUNCATE statement
     * @return Object SQL Query
     */
    private function _truncate()
    {
        $this->query = "TRUNCATE TABLE ";
        $this->query .= $this->_table($this->table);
        $this->query .= ";";

        return $this->query;
    }

    public function truncate()
    {
        $this->_truncate();
        return $this->query;
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

        if(is_array($args[0])){
            $temp = array();
            foreach($args as $arg){
                foreach($arg as $param){
                    array_push($temp, $this->_col($param));
                }
            }
            return $temp;
        }

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
        //    array_push($this->ascol, $exp[1]);
            return $this->_col($exp[0]). " AS `{$exp[1]}`";

        // }else if(in_array($args[0],$this->ascol)){
        //     return $this->_col($args[0],"single");

        }else if(substr_count($args[0],'.') == 1 && !$args[1]){
            $exp = explode(".",$args[0]);
            return "`{$exp[0]}`.`{$exp[1]}`";

        }else if($args[1] == "single"){
            return "`{$args[0]}`";

        }else if($args[1] == "string"){
            if(is_array($args[0]))return "('".implode("', '", $args[0])."')";
            else return "'".String::escape($args[0])."'";

        }else{
            return "`{$this->table}`.`{$args[0]}`";
        }
    }

    private function _group()
    {
        $args = func_get_args();
        $arr = array();

        if (!sizeof($this->group)) {
            return "";
        }

        foreach ($this->group as $col) {
            array_push($arr, $this->_col($col));
        }

        return " GROUP BY " . implode(", ", $arr);
    }

    private function _order()
    {
        $args = func_get_args();
        $arr = array();

        if (!sizeof($this->order)) {
            return "";
        }

        foreach ($this->order as $col) {
            array_push($arr, $this->_col($col[0]) . " {$col[1]}");
        }

        return " ORDER BY " . implode(", ", $arr);
    }

    private function _param()
    {
        $args = func_get_args();
        $arr = array();

        if (!sizeof($this->param)) {
            return "*";
        }

        //$this->param = $this->_col($this->param);

        if($args[0] == 'insert'){
            for ($i = $args[1]-1; $i < sizeof($this->param); $i+=2) {
                array_push($arr, $this->_col($this->param[$i],$args[1]==1?"single":"string"));
            }

        }else if($args[0] == 'update'){
            for ($i = 0; $i < sizeof($this->param); $i+=2) {
                array_push($arr, $this->_col($this->param[$i],"single")." = ".$this->_col($this->param[$i+1],"string"));
            }

        }else{
            foreach ($this->param as $param) {
                array_push($arr, $this->_col($param));
            }
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

    private function _unionParse($args)
    {
        $args = func_get_args();

        if($args[0] instanceof QueryBuilder){
            return " UNION {$args[1]}" . $args[0]->_get(true);

        }else if (is_callable($args[0])) {
            $q = new QueryBuilder();
            call_user_func($args[0], $q);
            return " UNION $args[1]" . $q->_get(true);

        }

        return "";
    }

    private function _joinParse($args)
    {
        $this->join_on = "";
        $str = " JOIN ";
        $str .= $this->_table($args[0]);
        array_shift($args);

        if (is_callable($args[0])) {
            $q = new QueryBuilder();
            $q->table($this->table);
            call_user_func($args[0], $q);
            return $q->join_on;
        }else{
            call_user_func_array(array($this, "on"), $args);
            $str .= $this->join_on;
        }

        return $str;
    }

    private function _whereParse($args)
    {
        if (is_callable($args[0])) {
            $q = new QueryBuilder();
            $q->table($this->table);
            call_user_func($args[0], $q);
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
                $str .= " = ".$this->_col($args[2],"string");
                break;

            case '!=':
                $str .= " <> ".$this->_col($args[2],"string");
                break;

            case '<>':
                $str .= " <> ".$this->_col($args[2],"string");
                break;

            case 'in':
                $str .= " IN ".$this->_col($args[2],"string");
                break;

            default:
                $str .= " = ".$this->_col($args[1],"string");
                break;
        }

        return $str;
    }
}