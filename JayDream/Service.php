<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Model;

class Service {
    public static function get($filter) {
        $model = new Model($filter['table']);

        //연관된 파일 가져오는
        if($filter['file_db'] == "true") self::injectFileRelation($filter);

        $object = $model->setFilter($filter)->get($filter);
        $ref = &$object;
        self::resolveRelations($filter,$ref);

        return array(
            "data" => $object["data"],
            "count" => $object["count"],
            "filter" => $filter,
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

    public static function insert($obj,$options) {
        $model = new Model($options['table']);
        $file_model = new Model("jd_file");
        $response = $model->insert($obj);

        foreach (File::normalize($_FILES) as  $file) {
            $file_response = File::save($file,$options['table'],$response['primary']);
            $file_response['keyword'] = $file['keyword'];
            $file_model->insert($file_response);
        }

        $response['success'] = true;
        $response['trace'] = true;

        return $response;
    }

    public static function update($obj,$options) {
        $model = new Model($options['table']);
        $file_model = new Model("jd_file");
        $response = $model->update($obj);

        foreach (File::normalize($_FILES) as  $file) {
            $file_response = File::save($file,$options['table'],$response['primary']);
            $file_response['keyword'] = $file['keyword'];
            $file_model->insert($file_response);
        }

        $response['success'] = true;
        $response['trace'] = true;

        return $response;
    }

    public static function whereUpdate($obj,$options) {
        $model = new Model($options['table']);
        $model->setFilter($options);
        $response = $model->whereUpdate($obj);

        $response['success'] = true;
        $response['trace'] = true;

        return $response;
    }

    public static function delete($obj,$options) {
        $model = new Model($options['table']);
        $file_model = new Model("jd_file");

        $response = $model->delete($obj);

        $file_data = $file_model->where("table_name",$options['table'])->where("table_primary",$response['primary'])->get()['data'];
        foreach ($file_data as $d) {
            File::delete($d);
            $file_model->delete($d);
        }

        $response['success'] = true;

        return $response;
    }

    public static function hashes($hashes,&$obj) {
        foreach ($hashes as $hash) {
            $obj[$hash['column']] = Lib::encrypt($obj[$hash['alias']]);
        }
    }

    public static function exists($filters) {
        foreach ($filters as $filter) {
            $model = new Model($filter['table']);

            $count = $model->setFilter($filter)->get($filter)['count'];

            if($count) Lib::error($filter['message']);
        }
    }
}