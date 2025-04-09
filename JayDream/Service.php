<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Model;

class Service {
    public static function get($obj) {
        $model = new Model($obj['table']);

        if($obj['file_db'] == "true") self::injectFileRelation($obj);

        //연관된 파일 가져오는


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

    private static function injectFileRelation(&$obj) {
        $jdFileRelation = [
            'table' => 'jd_file',
            'where' => [
                [
                    'column'  => 'table_name',
                    'value'   => $obj['table'], // 현재 대상 테이블명
                    'logical' => 'AND',
                    'operator'=> '='
                ],
                [
                    'column'  => 'table_primary',
                    'value'   => '$parent.idx',
                    'logical' => 'AND',
                    'operator'=> '='
                ]
            ]
        ];

        // relations 키가 없거나 비어 있으면 새 배열 생성
        if (!isset($obj['relations']) || !is_array($obj['relations'])) {
            $obj['relations'] = [$jdFileRelation];
        } else {
            // 무조건 jd_file 객체를 추가
            $obj['relations'][] = $jdFileRelation;
        }
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
        $model = new Model($obj['table']);
        $file_model = new Model("jd_file");
        $response = $model->insert($obj);

        foreach ($_FILES as $key => $file) {
            $file_response = File::save($file,$obj['table'],$response['primary']);
            $file_response['keyword'] = $key;
            $file_model->insert($file_response);
        }

        $response['success'] = true;
        $response['trace'] = true;

        return $response;
    }

    public static function delete($obj) {
        $model = new Model($obj['table']);

        $response = $model->delete($obj);
        $response['success'] = true;

        return $response;
    }
}