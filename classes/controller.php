<?php

class Controller
{
    protected $container;

    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    protected function responseJson($response, $result) {
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function permission_layer_deal($data, $count, $pre){
        $result = [];
        if (!$data == []){
            foreach ($data as $key => $value){
                if ($value['permission_layer'] == $count && $value['permission_former'] == null){
                    $result[$key]['permission_child_id'] = $value['permission_child_id'];
                    $result[$key]['permission_child_name'] = $value['permission_child_name'];
                    $result[$key]['permission_child_url'] = $value['permission_child_url'];
                    $pre = $value['permission_child_id'];
                    array_splice($data, $key, 1);
                    $count += 1;
                    $result[$key][$result[$key]['permission_child_name']] = $this->permission_layer_deal($data, $count, $pre);
                    if ($result[$key][$result[$key]['permission_child_name']] == []){
                        unset($result[$key][$result[$key]['permission_child_name']]);
                    }
                } else if ($value['permission_layer'] == $count && $value['permission_former'] == $pre) {
                    $result[$key]['permission_child_id'] = $value['permission_child_id'];
                    $result[$key]['permission_child_name'] = $value['permission_child_name'];
                    $result[$key]['permission_child_url'] = $value['permission_child_url'];
                    $pre = $value['permission_child_id'];
                    array_splice($data, $key, 1);
                    $count += 1;
                    $result[$key][$result[$key]['permission_child_name']] = $this->permission_layer_deal($data, $count, $pre);
                    if ($result[$key][$result[$key]['permission_child_name']] == []){
                        unset($result[$key][$result[$key]['permission_child_name']]);
                    }
                }
                $pre = 0;
                $count = 0;
            }
        }
        return $result;
    }
}
