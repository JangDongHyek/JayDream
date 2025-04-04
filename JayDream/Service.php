<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Model;

class Service {
    public static function get($obj) {
        $model = new Model($obj['table']);

        $object = $model->setFilter($obj)->get($obj);

        if(isset($obj['relations'])) {
            foreach ($object["data"] as $index =>$data) {
                foreach ($obj['relations'] as $filter) {
                    $sub_model = new Model($filter['table']);
                    $object["data"][$index]["$".$filter['table']] = $sub_model->setFilter($filter,$data)->get($filter);
                }
            }
        }

        return array(
            "data" => $object["data"],
            "count" => $object["count"],
            "filter" => $obj,
            "sql" => $object["sql"],
            "success" => true
        );
    }
}