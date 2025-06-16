<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Model {
    public $schema;
    private $table;

    private $sql = "";
    private $sql_order_by = "";
    private $block = false;
    private $block_bool = 0;

    public $joins = array();
    public $group_bys = array();

    public  $primary;
    public $autoincrement;

    function __construct($object = array()) {
        // 매개변수가 문자열이면 테이블속성만 넣었다고 가정
        if (is_string($object)) {
            $object = array("table" =>$object);
        }

        if(Config::$connect == null) Lib::error("Config init 함수를 실행시켜주세요.");


        $this->schema = array(
            "columns" => array(),
            "tables" => array(),
            "join_columns" => array()
        );

        if(!$object["table"]) Lib::error("Model construct() : 테이블을 지정해주세요.");
        $this->table =$object["table"];

        // 테이블 확인
        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".Config::DATABASE."'";
        $result = @mysqli_query(Config::$connect, $sql);
        if(!$result) Lib::error(mysqli_error(Config::$connect));

        while($row = mysqli_fetch_assoc($result)){
            array_push($this->schema['tables'], $row['TABLE_NAME']);
        }

        if(!$this->isTable()) Lib::error("Model construct() : 테이블을 찾을수 없습니다.");

        // Primary Key 확인
        $primary = $this->getPrimary($this->table);
        $this->primary = $primary['COLUMN_NAME'];
        $primary_type = $primary['DATA_TYPE'];
        $this->autoincrement = $primary["EXTRA"] ? true : false;

        if(!$this->primary) Lib::error("해당 테이블에 Primary 값이 존재하지않습니다.");
        if($primary_type == "int" && !$this->autoincrement) Lib::error("Primary 타입이 int인데 autoincrement가 설정되어있지않습니다..");

        // 테이블 스키마 정보 조회
        $this->schema[$this->table]['columns'] = $this->getColumns($this->table);
        $this->schema[$this->table]['columns_info'] = $this->getColumnsInfo($this->table);

    }

    function init() {
        $this->sql = "";
        $this->sql_order_by = "";
        $this->block = false;
        $this->block_bool = 0;

        $this->joins = array();
    }

    function blockStart($logical = "AND") {
        if($this->block) Lib::error("block이 이미 시작되어있습니다.");
        $this->block = true;
        $this->sql .= " {$logical} ( ";

        return $this;
    }

    function blockEnd() {
        if(!$this->block) Lib::error("block이 시작된적이 없습니다.");
        
        $this->block = false;
        $this->block_bool = 0;
        $this->sql .= " ) ";
        
        return $this;
    }

    function setFilter($obj,$parent = null) {
        if(isset($obj['joins'])) {
            foreach($obj['joins'] as $item) {
                $this->join($item);
            }
        }

        if(isset($obj['where'])) {
            foreach($obj['where'] as $item) {
                if($item['column'] == 'primary') $item['column'] = $this->primary;

                if($item['encrypt']) $item['value'] = Lib::encrypt($item['value']);

                if (strpos($item['value'], '$parent.') === 0 && $parent) {
                    $parts = explode('.', $item['value']);
                    if($parent[$parts[1]] == "") Lib::error("setFilter() : 부모에게 $parts[1] 값이 없습니다");
                    $this->where($item['column'],$parent[$parts[1]],$item['logical'],$item['operator']);
                }else {
                    $this->where($item['column'],$item['value'],$item['logical'],$item['operator']);
                }
            }
        }

        if(isset($obj['between'])) {
            foreach($obj['between'] as $item) {
                $this->between($item['column'],$item['start'],$item['end'],$item['logical']);
            }
        }

        if(isset($obj['in'])) {
            foreach($obj['in'] as $item) {
                $this->in($item['column'],$item['value'],$item['logical']);
            }
        }

        if(isset($obj['blocks'])) {
            foreach($obj['blocks'] as $item) {
                $this->blockStart($item['logical']);
                $this->setFilter($item,$parent);
                $this->blockEnd();
            }
        }

        if(isset($obj['group_bys'])) {
            $this->groupBy($obj['group_bys']);
        }

        if(isset($obj['order_by'])) {
            foreach ($obj['order_by'] as $item) {
                $this->orderBy($item['column'], $item['value']);
            }
        }

        return $this;
    }

    function count(){
        $sql = $this->getSql(array("count" => true));
        try {
            $result = mysqli_query(Config::$connect, $sql);

            $total_count = mysqli_num_rows($result);
            return $total_count ? $total_count : 0;

        }catch (\Exception $e) {
            Lib::error($e->getMessage() . "\n$sql");
        }

    }

    function get($_param = array()) {
        $page = $_param['page'] ? $_param['page'] : 0;
        $limit = $_param['limit'] ? $_param['limit'] : 0;
        $skip  = ($page - 1) * $limit;

        $sql = $this->getSql($_param);
        if($limit) $sql .= " LIMIT $skip, $limit";

        $object["data"] = array();
        $object["count"] = $this->count();
        $object['total_page'] = $limit ? ceil($object["count"] / $limit) : 0;
        $object["sql"] = $sql;

        $index = 1;
        try {
            $result = mysqli_query(Config::$connect, $sql);
        }catch (\Exception $e) {
            Lib::error($e->getMessage() . "\n$sql");
        }

        while($row = mysqli_fetch_assoc($result)){
            $row["__no__"] = ($page -1) * $limit + $index;
            $row["__no_desc__"] = $object['count'] - $index + 1 - (($page -1) * $limit);

            if (isset($_param['add_object']) && is_array($_param['add_object'])) {
                foreach ($_param['add_object'] as $add_object) {
                    $row[$add_object['name']] = $add_object['value'];
                }
            }

            $row['primary'] = $row[$this->primary];
            foreach ($row as $key => $value) {
                if($this->primary == $key) continue;

                if(Lib::isDecode($value)) {
                    $decoded_value = json_decode($value, true);

                    if (!is_null($decoded_value)) {
                        $row[$key] = $decoded_value;
                    }
                }

            }
            array_push($object["data"], $row);
            $index++;
        }

        $this->init();

        return $object;
    }

    function getSql($_param = array()) {
        $select_field = "$this->table.*";
        $join_sql = "";

        foreach ($this->joins as $join) {
            $columns = $this->schema[$join['table']]['columns'];
            if($join['select_column'] == "*") {
                foreach ($columns as $column) {
                    $select_field .= ", {$join['table']}.{$column} as ".'`$'."{$join['table']}__{$column}`";
                }
            }else {
                foreach ($join['select_column'] as $column) {
                    if(in_array($column, $columns)) {
                        $select_field .= ", {$join['table']}.{$column} as ".'`$'."{$join['table']}__{$column}`";
                    }else {
                        Lib::error("Model getSql() : {$join['table']}에  {$column}컬럼이 존재하지않습니다.");
                    }
                }
            }


            $join_sql .= "{$join['type']} JOIN {$join['table']} AS {$join['table']} ON ";
            $join_sql .= "{$this->table}.{$join['base']} = {$join['table']}.{$join['foreign']} ";

            if (isset($join['on']) && is_array($join['on'])) {
                foreach ($join['on'] as $on) {
                    $join_sql .= "{$on['logical']} {$join['table']}.{$on['column']} {$on['operator']} '{$on['value']}' ";
                }
            }
        }

        if($_param['count']) $select_field = "{$this->table}.{$this->primary}";

        $having_sql = "";
        $group_sql = "";
        if(!empty($this->group_bys)) {
            foreach ($this->group_bys['by'] as $by) {
                if($group_sql == "") $group_sql .= "GROUP BY $by";
                else $group_sql .= ", $by";
            }

            foreach ($this->group_bys['selects'] as $select) {
                if (strpos($select['column'], '.') === false) {
                    $select['column'] = "{$this->table}.{$select['column']}";
                }
                $select_field .= ", {$select['type']}({$select['column']}) AS {$select['as']}";
            }

            if(isset($this->group_bys['having'])) {
                foreach ($this->group_bys['having'] as $having) {
                    $having_sql .= "{$having['logical']} {$having['column']} {$having['operator']} ";
                    if(is_numeric($having['value'])) $having_sql .= $having['value'];
                    else $having_sql .= "'{$having['value']}'";
                }
            }
        }

        $sql = "SELECT $select_field FROM {$this->table} AS {$this->table} ";
        $sql .= $join_sql;
        $sql .= "WHERE 1 {$this->sql} ";
        $sql .= $group_sql;
        if($having_sql) $sql .= " HAVING 1=1 {$having_sql} ";
        $sql .= isset($this->sql_order_by) && $this->sql_order_by ? " ORDER BY $this->sql_order_by" : " ORDER BY $this->primary DESC";

        return $sql;
    }

    function join($object) {
        if(!in_array($object['table'], $this->schema['tables'])) Lib::error("Model setJoins() : {$object['table']} 테이블을 찾을수 없습니다.");

        $this->schema[$object['table']]['columns'] = $this->getColumns($object['table']);
        array_push($this->joins,$object);
    }

    function groupBy($object) {
        $this->group_bys = $object;
    }

    function orderBy($column,$value) {
        if (strpos($column, '.') !== false) {
            list($table, $column) = explode('.', $column);
        } else {
            $table = $this->table;
        }
        $columns = $this->schema[$table]['columns'];

        if(!in_array($column, $columns)) Lib::error("Model orderBy() : {$table}에  {$column}컬럼이 존재하지않습니다.");

        if($this->sql_order_by) $this->sql_order_by .= ",";
        $this->sql_order_by .= " {$table}.{$column} {$value}";

        return $this;
    }

    function where($column,$value,$logical = "AND",$operator = "=") {
        if (strpos($column, '.') !== false) {
            list($table, $column) = explode('.', $column);
        } else {
            $table = $this->table;
        }
        $columns = $this->schema[$table]['columns'];

        if($column == 'primary') $column = $this->primary;

        if(in_array($column, $columns)){
            if($value == "") return $this;
            if($value == "__null__") $value = "";

            if($this->block) {
                if(!$this->block_bool) $this->block_bool = 1;
                else $this->sql .= " $logical ";
            }else {
                $this->sql .= " $logical ";
            }

            if($value == "CURDATE()") {
                $this->sql .= "$table.`{$column}` {$operator} {$value}";
            }else if($value == "null") {
                $operator_upper = strtoupper($operator);
                if (in_array($operator_upper, ["=", "IS"])) {
                    $this->sql .= "$table.`{$column}` IS NULL";
                } else if (in_array($operator_upper, ["!=", "IS NOT"])) {
                    $this->sql .= "$table.`{$column}` IS NOT NULL";
                } else {
                    Lib::error("Model where() : NULL 비교는 =, !=, IS, IS NOT 만 사용할 수 있습니다.");
                }
            }else {
                $this->sql .= "$table.`{$column}` {$operator} '{$value}'";
            }

        }else {
            Lib::error("Model where() : {$table}에  {$column}컬럼이 존재하지않습니다.");
        }

        return $this;
    }

    function between($column,$start,$end,$logical = "AND") {
        if (strpos($column, '.') !== false) {
            list($table, $column) = explode('.', $column);
        } else {
            $table = $this->table;
        }
        $columns = $this->schema[$table]['columns'];

        if(strtolower($column) == "curdate()" || strtolower($column) == "now()" || strtotime($column) !== false) {
            if(!in_array($start, $columns)) Lib::error("Model between() : start 컬럼이 존재하지않습니다.");
            if(!in_array($end, $columns)) Lib::error("Model between() : end 컬럼이 존재하지않습니다.");
            if($this->block) {
                if(!$this->block_bool) $this->block_bool = 1;
                else $this->sql .= " {$logical} ";
            }else {
                $this->sql .= " {$logical} ";
            }

            if(strtotime($column) !== false) $this->sql .= "'$column' ";
            else $this->sql .= "$column ";
            $this->sql .= "BETWEEN $table.{$start} AND $table.{$end} ";
        }else {
            if(in_array($column, $columns)){
                if(strpos($start,":") === false) $start .= " 00:00:00";
                if(strpos($end,":") === false) $end .= " 23:59:59";

                if($this->block) {
                    if(!$this->block_bool) $this->block_bool = 1;
                    else $this->sql .= " {$logical} ";
                }else {
                    $this->sql .= " {$logical} ";
                }

                $this->sql .= "$table.{$column} BETWEEN '{$start}' AND '{$end}' ";
            }else {
                Lib::error("Model between() : {$table}에  {$column}컬럼이 존재하지않습니다.");
            }
        }

        return $this;
    }

    function in($column,$value,$logical = "AND") {
        if (strpos($column, '.') !== false) {
            list($table, $column) = explode('.', $column);
        } else {
            $table = $this->table;
        }
        $columns = $this->schema[$table]['columns'];

        if(!is_array($value)) Lib::error("Model in() : 비교값이 배열이 아닙니다.");

        if(in_array($column, $columns) && count($value)){
            if($this->block) {
                if(!$this->block_bool) $this->block_bool = 1;
                else $this->sql .= " $logical ";
            }else {
                $this->sql .= " $logical ";
            }

            $this->sql .= "$table.`{$column}` IN (";

            $bool = false;
            foreach($value as $v) {
                if($bool) $this->sql .= ", ";
                else $bool = true;

                if(is_numeric($v)) $this->sql .= "$v";
                else $this->sql .= "'$v'";

            }

            $this->sql .= ")";
        }else {
            Lib::error("Model in() : {$table}에  {$column}컬럼이 존재하지않습니다.");
        }

        return $this;
    }

    function insert($_param){

        $param = $this->escape($_param);

        if($this->autoincrement) {
            $param[$this->primary] = empty($param[$this->primary]) ? '' : $param[$this->primary];

        }else {
            $param[$this->primary] = empty($param[$this->primary]) ? Lib::generateUniqueId() : $param[$this->primary];
        }

        foreach($this->schema[$this->table]['columns'] as $column) {
            $info = $this->schema[$this->table]['columns_info'][$column];

            //보낸 데이터에서 해당 값이없는데 null설정이 되어있다면 건너뛰게
            if (!array_key_exists($column, $param)) {
                if ($info['IS_NULLABLE'] == "YES") {
                    continue;
                }
            }

            $value = $param[$column];
            if($column == $this->primary && $value == '') continue; // 10.2부터 int에 빈값이 허용안되기때문에 빈값일경우 패스

            // 컬럼의 데이터타입이 datetime 인데 널값이 허용이면 넘기고 아니면 기본값을 넣어서 쿼리작성
            if($info['DATA_TYPE'] == "int" || $info['DATA_TYPE'] == "tinyint") {
                if($value == '') {
                    if($info['IS_NULLABLE'] == "NO") $value = '0';
                    else continue;
                }
            }
            if($info['DATA_TYPE'] == "datetime") {
                if($value == '') {
                    if($info['IS_NULLABLE'] == "NO") $value = '0000-00-00 00:00:00';
                    else continue;
                }
            }
            if($info['DATA_TYPE'] == "date") {
                if($value == '') {
                    if($info['IS_NULLABLE'] == "NO") $value = '0000-00-00';
                    else continue;
                }
            }

            if($column == 'insert_date') {
                if($value == "0000-00-00 00:00:00") $value = 'now()';
            }
            if($column == 'wr_datetime') $value = 'now()';

            if(!empty($columns)) $columns .= ", ";
            $columns .= "`{$column}`";

            if(!empty($values)) $values .= ", ";

            if($value == "now()") $values .= "{$value}";
            else $values .= "'{$value}'";
        }

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($values)";

        try {
            $result = mysqli_query(Config::$connect, $sql);
        }catch (\Exception $e) {
            Lib::error($e->getMessage() . "\n$sql");
        }

        if($param[$this->primary]) {
            $response = array("sql" => $sql,"primary" => $param[$this->primary]);
        }else {
            $response = array("sql" => $sql,"primary" => mysqli_insert_id(Config::$connect));
        }

        return $response;
    }

    function update($_param){
        $param = $this->escape($_param);

        if($param['primary']) $param[$this->primary] = $param['primary'];

        if(!isset($param[$this->primary])) Lib::error("Model update() : 고유 키 값이 존재하지 않습니다.");

        $search_sql = " AND $this->primary='{$param[$this->primary]}' ";

        foreach($param as $key => $value){
            if($key == "update_date") continue;
            if(in_array($key, $this->schema[$this->table]['columns'])){
                $column = $this->schema[$this->table]['columns_info'][$key];
                if(!empty($update_sql)) $update_sql .= ", ";

                if($value == "now()") $update_sql .= "`{$key}`={$value}";
                else if($column['DATA_TYPE'] == 'int' && $value == 'incr') $update_sql = "`{$key}`={$key}+1";
                else if($column['DATA_TYPE'] == 'int' && $value == 'decr') $update_sql = "`{$key}`={$key}-1";
                else $update_sql .= "`{$key}`='{$value}'";
            }
        }

        if(in_array("update_date", $this->schema[$this->table]['columns'])){
            $update_sql .= ", `update_date` = now() ";
        }

        $sql = "UPDATE {$this->table} SET $update_sql WHERE 1 $search_sql";

        try {
            $result = mysqli_query(Config::$connect, $sql);
        }catch (\Exception $e) {
            Lib::error($e->getMessage() . "\n$sql");
        }

        return array("sql" => $sql,"primary" => $param[$this->primary]);
    }

    function whereUpdate($_param){
        $param = $this->escape($_param);

        if($param['primary']) $param[$this->primary] = $param['primary'];

        foreach($param as $key => $value){
            if($key == "update_date") continue;
            if(in_array($key, $this->schema[$this->table]['columns'])){
                $column = $this->schema[$this->table]['columns_info'][$key];
                if(!empty($update_sql)) $update_sql .= ", ";

                if($value == "now()") $update_sql .= "`{$key}`={$value}";
                else if($column['DATA_TYPE'] == 'int' && $value == 'incr') $update_sql = "`{$key}`={$key}+1";
                else if($column['DATA_TYPE'] == 'int' && $value == 'decr') $update_sql = "`{$key}`={$key}-1";
                else $update_sql .= "`{$key}`='{$value}'";
            }
        }

        if(in_array("update_date", $this->schema[$this->table]['columns'])){
            $update_sql .= ", `update_date` = now() ";
        }

        $sql = "UPDATE {$this->table} SET $update_sql";
        $sql .= "WHERE 1 {$this->sql} ";

        try {
            $result = mysqli_query(Config::$connect, $sql);
        }catch (\Exception $e) {
            Lib::error($e->getMessage() . "\n$sql");
        }

        return array("sql" => $sql,"primary" => $param[$this->primary]);
    }

    function delete($_param){

        $param = $this->escape($_param);

        if($param['primary']) $param[$this->primary] = $param['primary'];

        if(!isset($param[$this->primary])) Lib::error("Model delete() : 고유 키 값이 존재하지 않습니다.");

        $search_sql = " AND $this->primary='{$param[$this->primary]}' ";

        $sql = "DELETE FROM {$this->table} WHERE 1 $search_sql ";

        try {
            $result = mysqli_query(Config::$connect, $sql);
        }catch (\Exception $e) {
            Lib::error($e->getMessage() . "\n$sql");
        }

        return array("sql" => $sql,"primary" => $param[$this->primary]);
    }

    function isTable() {
        return in_array($this->table,$this->schema['tables']);
    }

    function getPrimary($table) {
        $sql = "SELECT COLUMN_NAME, EXTRA,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".Config::DATABASE."' AND TABLE_NAME = '{$table}' AND COLUMN_KEY = 'PRI';";
        $result = @mysqli_query(Config::$connect, $sql);
        if(!$result) Lib::error(mysqli_error(Config::$connect));

        if(!$row = mysqli_fetch_assoc($result)) Lib::error("Model getPrimary($table) : Primary 값이 존재하지않습니다 Primary설정을 확인해주세요.");

        return $row;
    }

    function getColumnsInfo($table) {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='{$table}' AND TABLE_SCHEMA='".Config::DATABASE."' ";
        $array = array();

        $result = @mysqli_query(Config::$connect, $sql);
        if(!$result) Lib::error(mysqli_error(Config::$connect));

        while($row = mysqli_fetch_assoc($result)){
            $array[$row['COLUMN_NAME']] = $row;
        }


        return $array;
    }

    function getColumns($table) {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='{$table}' AND TABLE_SCHEMA='".Config::DATABASE."' ";
        $array = array();

        $result = @mysqli_query(Config::$connect, $sql);
        if(!$result) Lib::error(mysqli_error(Config::$connect));

        while($row = mysqli_fetch_assoc($result)){
            array_push($array, $row['COLUMN_NAME']);
        }

        return $array;
    }

    function escape($_param) {
        $param = array();
        foreach($_param as $key => $value){
            if (is_array($value)) $value = Lib::jsonEncode($value);
            if (is_object($value)) $value = Lib::jsonEncode($value);
            if (is_bool($value)) $value = $value ? "true" : "false";

            $param[$key] = mysqli_real_escape_string(Config::$connect, $value);
        }
        return $param;
    }
}
