<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Model;

class Service {
    public static function get($obj) {
        $model = new Model($obj['table']);

        $object = $model->setFilter($obj)->get($obj);
        $ref = &$object;
        self::resolveRelations($obj,$ref);

        return array(
            "data" => $object["data"],
            "count" => $object["count"],
            "filter" => $obj,
            "sql" => $object["sql"],
            "success" => true
        );
    }

    private static function resolveRelations($obj,&$object) {
        if(isset($obj['relations'])) {
            foreach ($obj['relations'] as $filter) {
                $model = new Model($filter['table']);

                foreach ($object["data"] as $index =>$data) {
                    $object["data"][$index]["$".$filter['table']] = $model->setFilter($filter,$data)->get($filter);
                    $ref = &$object["data"][$index]["$".$filter['table']];
                    self::resolveRelations($filter,$ref);
                }
            }
        }
    }

    public static function insert($obj) {

    }

    public static function delete($obj) {
        $model = new Model($obj['table']);

        $response = $model->delete($obj);

        return array(
            "sql" => $response["sql"],
            "primary" => $response['primary'],
            "success" => true
        );
    }
}