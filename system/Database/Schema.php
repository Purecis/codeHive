<?php
/**
 * Schema Builder.
 * allow us to create database tables with elegant syntax
 *
 * @category   codeHive Database
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.Schema
 * @since      Class available since Release 2.1.0
 */
namespace App\System;

class Schema
{
    /**
     * SchemaBuilder Structure.
     */
    private $structure = [
        /*
        "users" => [
            "schema" => [
                "id" => ["INT", "NOT NULL", "AUTO_INCREMENT"],
                "name" => ["VARCHAR", "NOT NULL", "DEFAULT ''", "COMMENT ''"],
                "time" => ["TIMESTAMP", "NOT NULL", "DEFAULT CURRENT_TIMESTAMP", "COMMENT ''"],
            ],
            "index" => [
                "PRIMARY KEY (`id`)",
                "KEY `author` (`author`)",
                "KEY `parent` (`parent`)",
                "UNIQUE KEY `serial` (`serial`,`code`)"
            ],
            "options" => [
                "DEFAULT CHARSET=utf8",
                "ENGINE=InnoDB"
            ],
        ]
        */
    ];

    /**
     * Selected Schema key
     */
    private $last = "";
    private $lastCol = "";

    /**
     * Schema Table.
     *
     * @param	string 	Schema name
     * @return QueryBuilder Class
     */
    public function table($table)
    {
        $this->structure[$table] = [
            "schema" => [],
            "index" => [], // empty for primary key if not exist
            "options" => ["DEFAULT CHARSET=utf8"]
        ];
        $this->last = $table;

        return $this;
    }

    /**
     * Schema Table.
     *
     * @param	string 	Schema name
     * @return QueryBuilder Class
     */
    private function column($column, $type, $nullable = "NOT NULL", $default = "", $comment = "")
    {
        $this->structure[$this->last]["schema"][$column] = [$type, $nullable, $default, $comment];
        $this->lastCol = $column;

        return $this;
    }

    /**
     * Schema Type Columns.
     *
     * @return QueryBuilder Class
     */

    /**
     * tinyInt
     */
    public function tinyInt($column, $size=4)
    {
        $this->column($column, "TINYINT({$size})");
        
        return $this;
    }

    /**
     * smallInt
     */
    public function smallInt($column, $size=6)
    {
        $this->column($column, "SMALLINT({$size})");
        
        return $this;
    }

    /**
     * mediumInt
     */
    public function mediumInt($column, $size=9)
    {
        $this->column($column, "MEDIUMINT({$size})");
        
        return $this;
    }

    /**
     * integer
     */
    public function integer($column, $size=11)
    {
        $this->column($column, "INTEGER({$size})");
        
        return $this;
    }

    /**
     * int
     */
    public function int($column, $size=11)
    {
        $this->column($column, "INT({$size})");
        
        return $this;
    }

    /**
     * bigInt
     */
    public function bigInt($column, $size=20)
    {
        $this->column($column, "BIGINT({$size})");
        
        return $this;
    }

    /**
     * blob
     */
    public function blob($column)
    {
        $this->column($column, "BLOB");
        
        return $this;
    }

    /**
     * binary
     */
    public function binary($column, $size=20)
    {
        return $this->blob($column);
    }

    /**
     * char
     */
    public function char($column, $size=1)
    {
        $this->column($column, "CHAR({$size})");
        
        return $this;
    }

    /**
     * date
     */
    public function date($column)
    {
        $this->column($column, "DATE");
        
        return $this;
    }

    /**
     * datetime
     */
    public function datetime($column)
    {
        $this->column($column, "DATETIME");
        
        return $this;
    }

    /**
     * decimal
     */
    public function decimal($column, $size = 11, $point = 2)
    {
        $this->column($column, "DECIMAL({$size}, {$point})");
        
        return $this;
    }

    /**
     * double
     */
    public function double($column, $size = 11, $point = 2)
    {
        $this->column($column, "DOUBLE({$size}, {$point})");
        
        return $this;
    }

    /**
     * float
     */
    public function float($column)
    {
        $this->column($column, "FLOAT");
        
        return $this;
    }
    
    /**
     * varchar
     */
    public function varchar($column, $size=255)
    {
        $this->column($column, "VARCHAR({$size})");
        
        return $this;
    }

    /**
     * string
     */
    public function string($column, $size=255)
    {
        $this->column($column, "VARCHAR({$size})");
        
        return $this;
    }

    /**
     * text
     */
    public function text($column)
    {
        $this->column($column, "TEXT");
        
        return $this;
    }
    
    /**
     * mediumText
     */
    public function mediumText($column)
    {
        $this->column($column, "MEDIUMTEXT");
        
        return $this;
    }

    /**
     * longText
     */
    public function longText($column)
    {
        $this->column($column, "LONGTEXT");
        
        return $this;
    }

    /**
     * time
     */
    public function time($column)
    {
        $this->column($column, "TIME");
        
        return $this;
    }
    
    /**
     * timestamp
     */
    public function timestamp($column)
    {
        $this->column($column, "TIMESTAMP");
        
        return $this;
    }

    /**
     * enum	
     */
    public function enum($column, $values)
    {
        $this->column($column, "ENUM('" . implode("', '", $values) . "')");
        
        return $this;
    }

    /**
     * Schema Index and Options.
     *
     * @return QueryBuilder Class
     */

    /**
     * increment
     */
    public function increment($column=false)
    {
        if(!$column)$column = $this->lastCol;

        $this->structure[$this->last]["schema"][$column][2] = "AUTO_INCREMENT";
        $this->primary($column);

        return $this;
    }

    /**
     * nullable
     */
    public function nullable($nullable=true)
    {
        $this->structure[$this->last]["schema"][$this->lastCol][1] = $nullable ? "NULL" : "NOT NULL";
        
        return $this;
    }

    /**
     * primary
     */
    public function primary($column=false)
    {
        $args = func_get_args();
        if(sizeof($args) < 1){
            $column = isset($args[0]) ? $args[0] : $this->lastCol;
            $this->structure[$this->last]["schema"][$column][4] = "PRIMARY KEY";
        }else{
            array_push($this->structure[$this->last]["index"], "CONSTRAINT `" . implode("_", $args) . "` PRIMARY KEY (`" . implode("`, `", $args) . "`)");
        }

        return $this;
    }

    /**
     * unique
     */
    public function unique()
    {
        $args = func_get_args();
        if(sizeof($args) < 1){
            $column = isset($args[0]) ? $args[0] : $this->lastCol;
            $this->structure[$this->last]["schema"][$column][4] = "UNIQUE";
        }else{
            array_push($this->structure[$this->last]["index"], "CONSTRAINT `" . implode("_", $args) . "` UNIQUE (`" . implode("`, `", $args) . "`)");
        }

        return $this;
    }

    /**
     * index
     */
    public function index($column=false)
    {
        if(!$column)$column = $this->lastCol;
        array_push($this->structure[$this->last]["index"], "KEY `{$column}` (`{$column}`)");

        return $this;
    }

    /**
     * charset
     */
    public function charset($charset="utf8")
    {
        $this->structure[$this->last]["options"][0] = "DEFAULT CHARSET={$charset}";
        
        return $this;
    }

    /**
     * engine
     */
    public function engine($engine="InnoDB")
    {
        $this->structure[$this->last]["options"][1] = "ENGINE={$engine}";
        
        return $this;
    }

    /**
     * default
     */
    public function value($default="")
    {
        if($default != "CURRENT_TIMESTAMP"){
            $default = "'{$default}'";
        }
        $this->structure[$this->last]["schema"][$column][2] = "DEFAULT $default";
        
        return $this;
    }
    
    /**
     * default
     */
    public function onUpdate($column = false)
    {
        if(!$column)$column = $this->lastCol;

        if($default != "CURRENT_TIMESTAMP"){
            $default = "'{$default}'";
        }
        $this->structure[$this->last]["schema"][$column][4] = "UNIQUE";
        
        return $this;
    }
    
    // UNSIGNED
    // TIMESTAMP DEFAULT now() ON UPDATE now()
    // ON DELETE
    // ON UPDATE
    // SOFT DELETE
    // ADD as ALTER :D
    // renameColumn
    // dropColumn(single or array or multipe args it would be better)
    // hasTable
    // hasColumn
    // $table->primary(array('first', 'last'));
    // ALTER TABLE table_name ADD CONSTRAINT constraint_name PRIMARY KEY (P_Id,LastName)
    // when alter try to use another index in array to use the alter with small pices for update
    // $table->foreign('user_id')->references('id')->on('users');
    // dropForeign
    // dropPrimary
    // dropUnique
    // dropIndex
    // dropTable
    // exist

    public function _generate()
    {
        $_SQL = "CREATE TABLE IF NOT EXISTS ";
        foreach($this->structure as $table => $tableData){
            $_SQL .= "`{$table}` (";
            $_COLUMNS = [];
            foreach($tableData['schema'] as $column => $struct){
                array_push($_COLUMNS, "\n\t`{$column}` " . implode(" ", $struct));
            }
            $_SQL .= implode(", ", $_COLUMNS);
            if(sizeof($tableData['index']) > 0){
                $_SQL .= ", \n\t" . implode(", \n\t", $tableData['index']);
            }
            $_SQL .= "\n)";
            if(sizeof($tableData['options']) > 0){
                $_SQL .= " " . implode(" ", $tableData['options']);
            }
            $_SQL .= ";";
        }
        
        return $_SQL;
    }

    public function generate()
    {
        $query = _generate();
        return DataObject::execute($query);
    }
}

/* End of file Schema.php */
/* Location: ./system/Database/Schema.php */
