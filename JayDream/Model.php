<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Model {
    private $connect;
    private $schema;
    private $table;

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

        if(!$object["table"]) Lib::error("JlModel construct() : 테이블을 지정해주세요.");
        $this->table =$object["table"];

        // 테이블 확인
        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".Config::DATABASE."'";
        $result = @mysqli_query($this->connect, $sql);
        if(!$result) Lib::error(mysqli_error($this->connect));

        while($row = mysqli_fetch_assoc($result)){
            array_push($this->schema['tables'], $row['TABLE_NAME']);
        }

        if(!$this->isTable()) Lib::error("JlModel construct() : 테이블을 찾을수 없습니다.");

        // Primary Key 확인
        $primary = $this->getPrimary($this->table);
        $this->primary = $primary['COLUMN_NAME'];
        $primary_type = $primary['DATA_TYPE'];
        $this->autoincrement = $primary["EXTRA"] ? true : false;

        if(!$this->primary) Lib::error("해당 테이블에 Primary 값이 존재하지않습니다.");
        if($primary_type == "int" && !$this->autoincrement) Lib::error("Primary 타입이 int인데 autoincrement가 설정되어있지않습니다..");

        // 테이블 스키마 정보 조회
        $this->schema['columns'] = $this->getColumns($this->table);
        $this->schema['columns_info'] = $this->getColumnsInfo($this->table);

    }

    function insert($_param){

        $param = $this->escape($_param);

        if($this->autoincrement) {
            $param[$this->primary] = empty($param[$this->primary]) ? '' : $param[$this->primary];

        }else {
            $param[$this->primary] = empty($param[$this->primary]) ? $this->generatePrimaryKey() : $param[$this->primary];
        }

        foreach($this->schema['columns'] as $column) {
            $info = $this->schema['columns_info'][$column];
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

        if(!isset($param[$this->primary])) Lib::error("JlModel update() : 고유 키 값이 존재하지 않습니다.");

        $search_sql = " AND $this->primary='{$param[$this->primary]}' ";

        foreach($param as $key => $value){
            if($key == "update_date") continue;
            if(in_array($key, $this->schema['columns'])){
                $column = $this->schema['columns_info'][$key];
                if(!empty($update_sql)) $update_sql .= ", ";

                if($value == "now()") $update_sql .= "`{$key}`={$value}";
                else if($column['DATA_TYPE'] == 'int' && $value == 'incr') $update_sql = "`{$key}`={$key}+1";
                else if($column['DATA_TYPE'] == 'int' && $value == 'decr') $update_sql = "`{$key}`={$key}-1";
                else $update_sql .= "`{$key}`='{$value}'";
            }
        }

        if(in_array("update_date", $this->schema['columns'])){
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

        if(!isset($param[$this->primary])) Lib::error("JlModel delete() : 고유 키 값이 존재하지 않습니다.");

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

        if(!$row = mysqli_fetch_assoc($result)) Lib::error("JlModel getPrimary($table) : Primary 값이 존재하지않습니다 Primary설정을 확인해주세요.");

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

    function generatePrimaryKey() {
        return 'P-' . uniqid() . str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT);
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
