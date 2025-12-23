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
        // 공통 where 조건
        $baseWhere = [
            [
                'column'  => 'table_name',
                'value'   => $obj['table'],
                'logical' => 'AND',
                'operator'=> '='
            ],
            [
                'column'  => 'table_primary',
                'value'   => '$parent.primary',
                'logical' => 'AND',
                'operator'=> '='
            ]
        ];

        $relations = [];

        // file_keywords 배열이 있으면 반복
        if (!empty($obj['file_keywords']) && is_array($obj['file_keywords'])) {
            foreach ($obj['file_keywords'] as $keyword) {
                $relation = [
                    'table' => 'jd_file',
                    'as'    => 'jd_file_' . $keyword,
                    'where' => array_merge($baseWhere, [
                        [
                            'column'  => 'keyword',
                            'value'   => $keyword,
                            'logical' => 'AND',
                            'operator'=> '='
                        ]
                    ])
                ];
                $relations[] = $relation;
            }
        } else {
            // 단일 relation (기존 로직)
            $relations[] = [
                'table' => 'jd_file',
                'where' => $baseWhere
            ];
        }

        // obj에 relations 키 병합
        if (!isset($obj['relations']) || !is_array($obj['relations'])) {
            $obj['relations'] = $relations;
        } else {
            $obj['relations'] = array_merge($obj['relations'], $relations);
        }
    }

    private static function resolveRelations($obj,&$object) {
        if(isset($obj['relations'])) {
            foreach ($obj['relations'] as $filter) {
                if($filter['file_db'] == "true") self::injectFileRelation($filter);

                $model = new Model($filter['table']);
                $as = "$".$filter['table'];
                if(isset($filter['as']) && $filter['as']) $as = "$".$filter['as'];

                foreach ($object["data"] as $index =>$data) {
                    $object["data"][$index][$as] = $model->setFilter($filter,$data)->get($filter);
                    $ref = &$object["data"][$index][$as];
                    self::resolveRelations($filter,$ref);
                }
            }
        }
    }

    public static function insert($obj,$options) {
        $model = new Model($options['table']);
        $file_model = new Model("jd_file");
        $response = $model->insert($obj);

        $file_primaries = [];
        foreach (File::normalize($_FILES) as  $file) {
            $file_response = File::save($file,$options['table'],$obj,$response['primary']);
            $file_response['keyword'] = $file['keyword'];
            $file_response = $file_model->insert($file_response);
            $file_primaries[] = $file_response['primary'];

            foreach ($obj as $key => $value) {
                if ($value === "jd_file_primary_{$file['keyword']}") {
                    $newObj = array("primary" => $response['primary'],$key => $file_response['primary']);
                    $model->update($newObj);
                }
            }
        }

        foreach ($obj as $key => $value) {
            if ($value === 'jd_file_primary') {
                $newObj = array("primary" => $response['primary'],$key => $file_primaries);
                $model->update($newObj);
            }
        }



        $response['success'] = true;
        $response['trace'] = true;

        return $response;
    }

    public static function update($obj,$options) {
        $model = new Model($options['table']);
        $file_model = new Model("jd_file");
        $response = $model->update($obj);

        $file_primaries = [];
        foreach (File::normalize($_FILES) as  $file) {
            $file_response = File::save($file,$options['table'],$obj,$response['primary']);
            $file_response['keyword'] = $file['keyword'];
            $file_response = $file_model->insert($file_response);
            $file_primaries[] = $file_response['primary'];

            foreach ($obj as $key => $value) {
                if ($value === "jd_file_primary_{$file['keyword']}") {
                    $newObj = array("primary" => $response['primary'],$key => $file_response['primary']);
                    $model->update($newObj);
                }
            }
        }

        foreach ($obj as $key => $value) {
            if ($value === 'jd_file_primary') {
                $newObj = array("primary" => $response['primary'],$key => $file_primaries);
                $model->update($newObj);
            }
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

    public static function whereDelete($filter) {
        $model = new Model($filter['table']);
        $model->setFilter($filter);
        $response = $model->whereDelete();

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

            if($count) {
                if($filter['message']) Lib::error($filter['message']);
                else return true;
            }

            return false;
        }
    }

    public static function fileSave($obj,$options) {
        $file_model = new Model("jd_file");
        foreach (File::normalize($_FILES) as  $file) {
            $file_response = File::save($file,$options['table'],$obj);
            $file_response['keyword'] = $file['keyword'];
            $file_model->insert($file_response);
        }
        $response['file'] = $file_response;
        $response['success'] = true;

        return $response;
    }
}