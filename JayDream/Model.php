<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Model {
    private $connect;
    public $schema;
    private $table;

    private $sql = "";
    private $sql_order_by = "";
    private $where_group = false;
    private $where_group_index = 0;

    private $joins = array();

    public  $primary;
    public $autoincrement;

    function __construct($object = array()) {
        // 매개변수가 문자열이면 테이블속성만 넣었다고 가정
        if (is_string($object)) {
            $object = array("table" =>$object);
        }

        //connect전 필수 정보확인
        if(!Config::HOSTNAME) Lib::error("Model construct() : hostname를 입력해주세요.");
        if(!Config::USERNAME || Config::USERNAME == "exam") Lib::error("Model construct() : username를 입력해주세요.");
        if(!Config::PASSWORD || Config::PASSWORD == "pass") Lib::error("Model construct() : password를 입력해주세요.");
        if(!Config::DATABASE || Config::DATABASE == "exam") Lib::error("Model construct(): database를 입력해주세요.");

        $connect = new \mysqli(Config::HOSTNAME, Config::USERNAME, Config::PASSWORD, Config::DATABASE);
        if ($connect->connect_errno) Lib::error(mysqli_error($this->connect));

        $this->connect = $connect;

        $this->schema = array(
            "columns" => array(),
            "tables" => array(),
            "join_columns" => array()
        );

        if(!$object["table"]) Lib::error("Model construct() : 테이블을 지정해주세요.");
        $this->table =$object["table"];

        // 테이블 확인
        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".Config::DATABASE."'";
        $result = @mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect));

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

    function setFilter($obj,$parent = null) {
        if(isset($obj['where'])) {
            foreach($obj['where'] as $item) {
                if($item['column'] == 'primary') $item['column'] = $this->primary;

                if (strpos($item['value'], '$parent.') === 0 && $parent) {
                    $parts = explode('.', $item['value']);
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

        if(isset($obj['joins'])) {
            foreach($obj['joins'] as $item) {
                $this->join($item);
            }
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
        $result = mysqli_query($this->connect, $sql);
        if(!$result) $this->jl->error(mysqli_error($this->connect)."\n $sql");

        $total_count = mysqli_num_rows($result);

        return $total_count ? $total_count : 0;
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
        $result = mysqli_query($this->connect, $sql);
        if(!$result) $this->jl->error(mysqli_error($this->connect)."\n $sql");

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
                // JSON인지 확인하고 디코딩 시도
                $decoded_value = json_decode($value, true);

                // JSON 디코딩이 성공했다면 값을 디코딩된 데이터로 변경
                if (!is_null($decoded_value)) {
                    $row[$key] = $decoded_value;
                }
            }
            array_push($object["data"], $row);
            $index++;
        }

        return $object;
    }

    function getSql($_param = array()) {
        $select_field = "$this->table.*";
        $join_sql = "";

        foreach ($this->joins as $join) {
            $columns = $this->schema[$join['table']]['columns'];
            foreach ($join['select_column'] as $column) {
                if(in_array($column, $columns)) {
                    $select_field .= ", {$join['table']}.{$column} as {$join['table']}__{$column}";
                }else {
                    Lib::error("Model getSql() : {$join['table']}에  {$column}컬럼이 존재하지않습니다.");
                }
            }

            $join_sql .= "{$join['type']} JOIN {$join['table']} AS {$join['table']} ON ";
            $join_sql .= "{$this->table}.{$join['base']} = {$join['table']}.{$join['foreign']} ";

            foreach ($join['on'] as $on) {
                $join_sql .= "{$on['logical']} {$join['table']}.{$on['column']} {$on['operator']} '{$on['value']}' ";
            }
        }

        if($_param['count']) $select_field = "{$this->table}.{$this->primary}";

        $sql = "SELECT $select_field FROM {$this->table} AS {$this->table} ";
        $sql .= $join_sql;
        $sql .= "WHERE 1 {$this->sql} ";
        $sql .= isset($this->sql_order_by) && $this->sql_order_by ? " ORDER BY $this->sql_order_by" : " ORDER BY $this->primary DESC";

        return $sql;
    }

    function join($object) {
        if(!in_array($object['table'], $this->schema['tables'])) Lib::error("JlModel setJoins() : {$object['table']} 테이블을 찾을수 없습니다.");

        $this->schema[$object['table']]['columns'] = $this->getColumns($object['table']);
        array_push($this->joins,$object);
    }

    function orderBy($column,$value) {
        if (strpos($column, '.') !== false) {
            list($table, $column) = explode('.', $column);
        } else {
            $table = $this->table;
        }
        $columns = $this->schema[$table]['columns'];

        if(!in_array($column, $columns)) Lib::error("Model orderBy() : {$table}에  {$column}컬럼이 존재하지않습니다.");
        if(!in_array($value,array("DESC","ASC"))) Lib::error("Model orderBy() : DESC , ASC 둘중 하나만 선택가능합니다.");

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

        if(in_array($column, $columns)){
            if($value == "") return $this;
            if($value == "__null__") $value = "";

            if($this->where_group) {
                if(!$this->where_group_index) $this->where_group_index = 1;
                else $this->sql .= " $logical ";
            }else {
                $this->sql .= " $logical ";
            }

            if($value == "CURDATE()") $this->sql .= "$table.`{$column}` {$operator} {$value}";
            else $this->sql .= "$table.`{$column}` {$operator} '{$value}'";
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
            if($this->where_group) {
                if(!$this->where_group_index) $this->where_group_index = 1;
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

                if($this->where_group) {
                    if(!$this->where_group_index) $this->where_group_index = 1;
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

        if(!is_array($value)) Lib::error("JlModel in() : 비교값이 배열이 아닙니다.");

        if(in_array($column, $columns) && count($value)){
            if($this->where_group) {
                if(!$this->where_group_index) $this->where_group_index = 1;
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
            $value = $param[$column];
            if($column == $this->primary && $value == '') continue; // 10.2부터 int에 빈값이 허용안되기때문에 빈값일경우 패스

            // 컬럼의 데이터타입이 datetime 인데 널값이 허용이면 넘기고 아니면 기본값을 넣어서 쿼리작성
            if($info['DATA_TYPE'] == "int") {
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

            if($column == 'insert_date') $value = 'now()';
            if($column == 'wr_datetime') $value = 'now()';

            if(!empty($columns)) $columns .= ", ";
            $columns .= "`{$column}`";

            if(!empty($values)) $values .= ", ";

            if($value == "now()") $values .= "{$value}";
            else $values .= "'{$value}'";
        }

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($values)";

        $result = mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect)."\n $sql");

        if($param[$this->primary]) {
            $response = array("sql" => $sql,"primary" => $param[$this->primary]);
        }else {
            $response = array("sql" => $sql,"primary" => mysqli_insert_id($this->connect));
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

        $result = mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect)."\n $sql");

        return array("sql" => $sql,"primary" => $param[$this->primary]);
    }

    function delete($_param){

        $param = $this->escape($_param);

        if($param['primary']) $param[$this->primary] = $param['primary'];

        if(!isset($param[$this->primary])) Lib::error("Model delete() : 고유 키 값이 존재하지 않습니다.");

        $search_sql = " AND $this->primary='{$param[$this->primary]}' ";

        $sql = "DELETE FROM {$this->table} WHERE 1 $search_sql ";

        $result = mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect)."\n $sql");

        return array("sql" => $sql,"primary" => $param[$this->primary]);
    }

    function isTable() {
        return in_array($this->table,$this->schema['tables']);
    }

    function getPrimary($table) {
        $sql = "SELECT COLUMN_NAME, EXTRA,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".Config::DATABASE."' AND TABLE_NAME = '{$table}' AND COLUMN_KEY = 'PRI';";
        $result = @mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect));

        if(!$row = mysqli_fetch_assoc($result)) Lib::error("Model getPrimary($table) : Primary 값이 존재하지않습니다 Primary설정을 확인해주세요.");

        return $row;
    }

    function getColumnsInfo($table) {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='{$table}' AND TABLE_SCHEMA='".Config::DATABASE."' ";
        $array = array();

        $result = @mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect));

        while($row = mysqli_fetch_assoc($result)){
            $array[$row['COLUMN_NAME']] = $row;
        }


        return $array;
    }

    function getColumns($table) {
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='{$table}' AND TABLE_SCHEMA='".Config::DATABASE."' ";
        $array = array();

        $result = @mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect));

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

            $param[$key] = mysqli_real_escape_string($this->connect, $value);
        }
        return $param;
    }
}
