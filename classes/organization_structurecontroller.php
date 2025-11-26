<?php

use \Slim\Views\PhpRenderer;


class organization_structurecontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
        $this->container['classify_structure'] = [
            "classify_structure_name" => "組織架構",
            "classify_structure_role_id" => 8, //內部人員-職稱分類
            "classify_structure_department_id" => 9, //內部人員-部門分類
            "classify_structure_customer_role_id" => 22, //外部人員-部門分類
            "classify_structure_customer_department_id" => 23, //外部人員-職稱分類
        ];
    }

    public function compressImage($source = false, $destination = false, $quality = 80, $filters = false)
    {
        $info = getimagesize($source);
        switch ($info['mime']) {
            case 'image/jpeg':
                /* Quality: integer 0 - 100 */
                if (!is_int($quality) or $quality < 0 or $quality > 100)
                    $quality = 80;
                return imagecreatefromjpeg($source);
            case 'image/gif':
                return imagecreatefromgif($source);
            case 'image/png':
                /* Quality: Compression integer 0(none) - 9(max) */
                if (!is_int($quality) or $quality < 0 or $quality > 9)
                    $quality = 6;
                return imagecreatefrompng($source);
            case 'image/webp':
                /* Quality: Compression 0(lowest) - 100(highest) */
                if (!is_int($quality) or $quality < 0 or $quality > 100)
                    $quality = 80;
                return imagecreatefromwebp($source);
            case 'image/bmp':
                /* Quality: Boolean for compression */
                if (!is_bool($quality))
                    $quality = true;
                return imagecreatefrombmp($source);
            default:
                return;
        }
    }

    public function response_return($response, $data)
    {
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($data);
        return $response;
    }

    function upload_custom_testcases($request, $response, $args) //Geting the excel data of student grade for uploading.
    {
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumn++;
            $data = [];
            $record_empty_column = [];
            for ($row = 1; $row <= $highestRow; ++$row) {
                $tmp = [];
                $record_counter = 0;
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    if ($row == 1 && strval($worksheet->getCell($col . $row)->getValue()) == "") {
                        $record_empty_column[] = $record_counter;
                    }
                    $tmp[] = strval($worksheet->getCell($col . $row)->getValue());
                    $record_counter++;
                }
                if (count($tmp) != 0)
                    $data[] = $tmp;
            }
            foreach ($record_empty_column as $column_value) {
                foreach ($data as $key => $value) {
                    unset($data[$key][$column_value]);
                }
            }
            $result = $data;
        } else {
            $result = array(
                "status" => "failed"
            );
        }
        return $this->response_return($response, $result);
    }


    public function get_classify_structure($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        // $blog_type_id = 1;
        // $params['blog_type_id'] = $blog_type_id;
        $result = $Component->get_classify_structure($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_role($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new Component($this->container->db);
        if (array_key_exists('classify_structure_id', $params) && $params['classify_structure_id']) {
            $params['classify_structure_id'] = $params['classify_structure_id'];
        } else {
            $params['classify_structure_id'] = $this->container['classify_structure']['classify_structure_role_id'];
        }
        if (isset($params['cur_page'])) {
            $cur_page = $params['cur_page'];
            unset($params['cur_page']);
        }
        if (isset($params['size'])) {
            $size = $params['size'];
            unset($params['size']);
        }
        $result_role = $organization_structure->get_role($params);
        if (!is_null($cur_page)) {
            $params['cur_page'] = $cur_page;
        }
        if (!is_null($size)) {
            $params['size'] = $size;
        }
        $relation_id_arr = [];
        if (array_key_exists('data', $result_role)) {
            foreach ($result_role['data'] as $row_id => $row_data) {
                array_push($relation_id_arr, $row_data['classify_structure_type_id']);
            }
            if (count($relation_id_arr) === 0) {
                $result = [
                    "data" => [],
                    "total" => 0
                ];
            } else {
                $result = $Component->get_classify_structure_type_folder($params, $relation_id_arr, $result_role['relation_select'], $result_role['relation_from'], $result_role['relation_order']);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_role($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        if (array_key_exists('classify_structure_id', $data) && $data['classify_structure_id']) {
            $data['classify_structure_id'] = $data['classify_structure_id'];
        } else {
            $data['classify_structure_id'] = $this->container['classify_structure']['classify_structure_role_id'];
        }
        $result_classify_structure_type_folder = $Component->post_classify_structure_type_folder($data, $last_edit_user_id);
        if ($result_classify_structure_type_folder['status'] == 'success') {
            $classify_structure_type_id = $result_classify_structure_type_folder['classify_structure_type_id'];
            $result = $organization_structure->post_role([$data], $last_edit_user_id, $classify_structure_type_id);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_role($request, $response, $args)
    {
        $data = $request->getParsedBody();

        function patch_role_classify_structure_type_folder_recursive($data, $db)
        {
            $last_edit_user_id = $_SESSION['id'];
            foreach ($data as $key => $column) {
                $column['classify_structure_type_parent_id'] = null;
                $Component = new component($db);
                $organization_structure = new organization_structure($db);
                $classify_structure_type_id_arr = $organization_structure->get_role($column)['data'];
                $column['classify_structure_type_parent_id'] = null;
                foreach ($classify_structure_type_id_arr as $classify_structure_type_id_arr_index => $classify_structure_type_id_arr_value) {
                    $column['classify_structure_type_id'] = $classify_structure_type_id_arr_value['classify_structure_type_id'];
                }
                $Component->patch_classify_structure_type_folder([$column], $last_edit_user_id);
                if (array_key_exists('children', $column)) {
                    $result = patch_role_classify_structure_type_folder_recursive($column['children'], $db);
                }
            }
            return $result;
        }
        function patch_role_recursive($data, $db)
        {
            $last_edit_user_id = $_SESSION['id'];
            foreach ($data as $key => $column) {
                $organization_structure = new organization_structure($db);
                $result = $organization_structure->patch_role([$column], $last_edit_user_id);
                if (array_key_exists('children', $column)) {
                    patch_role_recursive($column['children'], $db);
                }
            }
            return $result;
        }
        $db = $this->container->db;
        $result = patch_role_classify_structure_type_folder_recursive($data, $db);
        $result = patch_role_recursive($data, $db);

        // $organization_structure = new organization_structure($this->container->db);
        // $Component = new component($this->container->db);
        // $last_edit_user_id = $_SESSION['id'];
        // $Component->patch_classify_structure_type_folder($data, $last_edit_user_id);
        // $last_edit_user_id = $_SESSION['id'];
        // $result = $organization_structure->patch_role($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_role($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_role($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_customer_role($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new Component($this->container->db);
        if (array_key_exists('classify_structure_id', $params) && $params['classify_structure_id']) {
            $params['classify_structure_id'] = $params['classify_structure_id'];
        } else {
            $params['classify_structure_id'] = $this->container['classify_structure']['classify_structure_customer_role_id'];
        }
        $result_role = $organization_structure->get_role($params);
        $relation_id_arr = [];
        if (array_key_exists('data', $result_role)) {
            foreach ($result_role['data'] as $row_id => $row_data) {
                array_push($relation_id_arr, $row_data['classify_structure_type_id']);
            }
            if (count($relation_id_arr) === 0) {
                $result = [
                    "data" => [],
                    "total" => 0
                ];
            } else {
                $result = $Component->get_classify_structure_type_folder($params, $relation_id_arr, $result_role['relation_select'], $result_role['relation_from']);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_customer_role($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        if (array_key_exists('classify_structure_id', $data) && $data['classify_structure_id']) {
            $data['classify_structure_id'] = $data['classify_structure_id'];
        } else {
            $data['classify_structure_id'] = $this->container['classify_structure']['classify_structure_customer_role_id'];
        }
        $result_classify_structure_type_folder = $Component->post_classify_structure_type_folder($data, $last_edit_user_id);
        if ($result_classify_structure_type_folder['status'] == 'success') {
            $classify_structure_type_id = $result_classify_structure_type_folder['classify_structure_type_id'];
            $result = $organization_structure->post_role([$data], $last_edit_user_id, $classify_structure_type_id);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_customer_role($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $Component->patch_classify_structure_type_folder([$data], $last_edit_user_id);
        $result = $organization_structure->patch_role([$data], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_customer_role($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_role($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_department($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new Component($this->container->db);
        if (array_key_exists('classify_structure_id', $params) && $params['classify_structure_id']) {
            $params['classify_structure_id'] = $params['classify_structure_id'];
        } else {
            $params['classify_structure_id'] = $this->container['classify_structure']['classify_structure_department_id'];
            $result_department = $organization_structure->get_department($params);
            unset($result_department['relation_select']);
            unset($result_department['relation_from']);
            unset($result_department['relation_order']);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result_department);
            return $response;
        }
        if (isset($params['cur_page'])) {
            $cur_page = $params['cur_page'];
            unset($params['cur_page']);
        }
        if (isset($params['size'])) {
            $size = $params['size'];
            unset($params['size']);
        }
        $result_department = $organization_structure->get_department($params);
        if (!is_null($cur_page)) {
            $params['cur_page'] = $cur_page;
        }
        if (!is_null($size)) {
            $params['size'] = $size;
        }
        $relation_id_arr = [];
        if (array_key_exists('data', $result_department)) {
            foreach ($result_department['data'] as $row_id => $row_data) {
                if ($row_data['classify_structure_type_id'] != null) {
                    array_push($relation_id_arr, $row_data['classify_structure_type_id']);
                }
            }
            if (count($relation_id_arr) != 0) {
                $result = $Component->get_classify_structure_type_folder($params, $relation_id_arr, $result_department['relation_select'], $result_department['relation_from'], $result_department['relation_order']);
            } else {
                $result = ['data' => [], 'total' => 0];
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_department_by_user_id($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new Component($this->container->db);
        $params["department_id"] = 0;
        $params["classify_structure_id"] = 9;
        $user_role_select = $organization_structure->user_role_select(["user_id" => $_SESSION['id']])["data"];
        foreach ($user_role_select as $key => $value) $params["department_id"] = $value["department_id"];
        if (array_key_exists('classify_structure_id', $params) && $params['classify_structure_id']) {
            $params['classify_structure_id'] = $params['classify_structure_id'];
        } else {
            $params['classify_structure_id'] = $this->container['classify_structure']['classify_structure_department_id'];
            $result_department = $organization_structure->get_department($params);
            unset($result_department['relation_select']);
            unset($result_department['relation_from']);
            unset($result_department['relation_order']);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result_department);
            return $response;
        }
        if (isset($params['cur_page'])) {
            $cur_page = $params['cur_page'];
            unset($params['cur_page']);
        }
        if (isset($params['size'])) {
            $size = $params['size'];
            unset($params['size']);
        }
        $result_department = $organization_structure->get_department($params);
        if (!is_null($cur_page)) {
            $params['cur_page'] = $cur_page;
        }
        if (!is_null($size)) {
            $params['size'] = $size;
        }
        $relation_id_arr = [];
        if (array_key_exists('data', $result_department)) {
            foreach ($result_department['data'] as $row_id => $row_data) {
                if ($row_data['classify_structure_type_id'] != null) {
                    array_push($relation_id_arr, $row_data['classify_structure_type_id']);
                }
            }
            if (count($relation_id_arr) != 0) {
                $result = $Component->get_classify_structure_type_folder($params, $relation_id_arr, $result_department['relation_select'], $result_department['relation_from'], $result_department['relation_order']);
            } else {
                $result = ['data' => [], 'total' => 0];
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        if (array_key_exists('classify_structure_id', $data) && $data['classify_structure_id']) {
            $data['classify_structure_id'] = $data['classify_structure_id'];
        } else {
            $data['classify_structure_id'] = $this->container['classify_structure']['classify_structure_department_id'];
        }
        $result_classify_structure_type_folder = $Component->post_classify_structure_type_folder($data, $last_edit_user_id);
        if ($result_classify_structure_type_folder['status'] == 'success') {
            $classify_structure_type_id = $result_classify_structure_type_folder['classify_structure_type_id'];
            $result = $organization_structure->post_department([$data], $last_edit_user_id, $classify_structure_type_id);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        function patch_classify_structure_type_folder_recursive($data, $db)
        {
            $last_edit_user_id = $_SESSION['id'];
            foreach ($data as $key => $column) {
                $column['classify_structure_type_parent_id'] = null;
                $Component = new component($db);
                $organization_structure = new organization_structure($db);
                $classify_structure_type_id_arr = $organization_structure->get_department($column)['data'];
                $column['classify_structure_type_parent_id'] = null;
                foreach ($classify_structure_type_id_arr as $classify_structure_type_id_arr_index => $classify_structure_type_id_arr_value) {
                    $column['classify_structure_type_id'] = $classify_structure_type_id_arr_value['classify_structure_type_id'];
                }
                $Component->patch_classify_structure_type_folder([$column], $last_edit_user_id);
                if (array_key_exists('children', $column)) {
                    $result = patch_classify_structure_type_folder_recursive($column['children'], $db);
                }
            }
            return $result;
        }
        function patch_department_recursive($data, $db)
        {
            $last_edit_user_id = $_SESSION['id'];
            foreach ($data as $key => $column) {
                $organization_structure = new organization_structure($db);
                $result = $organization_structure->patch_department([$column], $last_edit_user_id);
                if (array_key_exists('children', $column)) {
                    patch_department_recursive($column['children'], $db);
                }
            }
            return $result;
        }
        $db = $this->container->db;
        $result = patch_classify_structure_type_folder_recursive($data, $db);
        $result = patch_department_recursive($data, $db);
        // $result = $organization_structure->patch_department($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_department($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_customer_department($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new Component($this->container->db);
        if (array_key_exists('classify_structure_id', $params) && $params['classify_structure_id']) {
            $params['classify_structure_id'] = $params['classify_structure_id'];
        } else {
            $params['classify_structure_id'] = $this->container['classify_structure']['classify_structure_customer_department_id'];
        }
        $result_department = $organization_structure->get_department($params);
        $relation_id_arr = [];
        if (array_key_exists('data', $result_department)) {
            foreach ($result_department['data'] as $row_id => $row_data) {
                if ($row_data['classify_structure_type_id'] != null) {
                    array_push($relation_id_arr, $row_data['classify_structure_type_id']);
                }
            }
            if (count($relation_id_arr) != 0) {
                $result = $Component->get_classify_structure_type_folder($params, $relation_id_arr, $result_department['relation_select'], $result_department['relation_from']);
            } else {
                $result = ['data' => [], 'total' => 0];
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_customer_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        if (array_key_exists('classify_structure_id', $data) && $data['classify_structure_id']) {
            $data['classify_structure_id'] = $data['classify_structure_id'];
        } else {
            $data['classify_structure_id'] = $this->container['classify_structure']['classify_structure_customer_department_id'];
        }
        $result_classify_structure_type_folder = $Component->post_classify_structure_type_folder($data, $last_edit_user_id);
        if ($result_classify_structure_type_folder['status'] == 'success') {
            $classify_structure_type_id = $result_classify_structure_type_folder['classify_structure_type_id'];
            $result = $organization_structure->post_department([$data], $last_edit_user_id, $classify_structure_type_id);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_customer_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $Component->patch_classify_structure_type_folder([$data], $last_edit_user_id);
        $result = $organization_structure->patch_department([$data], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_customer_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_department($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    //拿資料夾順序
    public function get_folder_index($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_folder_index($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    //拿資料夾
    public function get_classify_structure_type_folder($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_classify_structure_type_folder($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    //新增資料夾
    public function post_classify_structure_type_folder($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_classify_structure_type_folder($params, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_classify_structure_type_folder($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_classify_structure_type_folder($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_classify_structure_type_folder($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->delete_classify_structure_type_folder($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_user($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new organization_structure($this->container->db);
        $result = $Component->get_user($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function user_role_select($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->user_role_select($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function user_role_insert($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->user_role_insert($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function user_role_delete($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->user_role_delete($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_user_parent($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new organization_structure($this->container->db);
        $result = $Component->get_user_parent($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_gender($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_gender($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_gender($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->post_gender($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_gender($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->patch_gender($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_gender($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_gender($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_staff_self($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $params['user_id'] = $_SESSION['id'];
        $result = $organization_structure->get_staff($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_statistics_staff_gender($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_statistics_staff_gender($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_statistics_staff_department($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_statistics_staff_department($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_statistics_staff_role($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_statistics_staff_role($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_staff_diverge($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_staff_diverge($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_staff_diverge_self($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $user_id = $_SESSION['id'];
        if (!is_null($user_id)) {
            $params['user_id'] = $user_id;
        }
        $result = $organization_structure->get_staff_diverge($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    public function patch_staff_diverge($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->patch_staff_diverge($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_staff($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_staff($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_staff_by_user_id($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $params["department_id_arr"] = 0;
        $user_role_select = $organization_structure->user_role_select(["user_id" => $_SESSION['id']])["data"];
        foreach ($user_role_select as $key => $value) $params["department_id_arr"] = "{{$value["department_id"]}}";
        $result = $organization_structure->get_staff($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_staff($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $result = [
            'data' => []
        ];
        foreach ($data as $column) {
            $organization_structure_register = $home->organization_structure_register([$column]);
            if (array_key_exists('data', $organization_structure_register)) {
                $result['data'] = array_merge($organization_structure_register['data'], $result['data']);
            }
        }
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->post_staff($result['data'], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_staff($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $result = [];
        foreach ($data as $key => $value) {
            if ($value['type'] == 'self') {
                $data[$key]['user_id'] = $_SESSION['id'];
                $data[$key]['is_login'] = 1;
            }
            $value['user_name'] = strval($value['staff_name']);
            $value['edit_time'] = "NOW()";
            $value['oldpassword'] = $value['old_password'];
            $updateUserDetail = $home->updateUserDetail($value);
            if (array_key_exists('password', $value) && array_key_exists('password1', $value) && array_key_exists('oldpassword', $value)) {
                $updatePassword = $home->updatePassword($value);
            } else {
                $updatePassword['status'] = 'success';
            }
            if ($updateUserDetail['status'] === 'success' && $updatePassword['status'] === 'success') {
            } else {
                $result = [
                    'updateUserDetail' => $updateUserDetail,
                    'updatePassword' => $updatePassword
                ];
                goto exceptionError;
            }

            $readUserDetailEditorName = $home->readUserDetailEditorName($value['user_id']);
            if ($readUserDetailEditorName['editor']) {
                array_push($result, [
                    'status' => 'success',
                    'editor' => $readUserDetailEditorName['editor']
                ]);
            } else {
                $result = ['readUserDetailEditorName' => $readUserDetailEditorName];
                goto exceptionError;
            }
        }
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->patch_staff($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
        exceptionError:
        $response = $response->withStatus(500);
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_staff($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Home = new Home($this->container->db);
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_staff($data);
        if (array_key_exists('user_id_arr', $result)) {
            foreach ($result['user_id_arr'] as $row => $row_data) {
                $deleteUserDetail = $Home->deleteUserDetail($row_data);
                if ($deleteUserDetail['status'] === 'success') {
                    $result = ['status' => 'success'];
                } else {
                    $result = ['status' => 'flase'];
                }
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_staff_import_manual($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        if (isset($params['cur_page'])) {
            $cur_page = $params['cur_page'];
            unset($params['cur_page']);
        }
        if (isset($params['size'])) {
            $size = $params['size'];
            unset($params['size']);
        }
        $result_department = $organization_structure->get_department([
            "classify_structure_id" => 9,
            "classify_structure_type_id" => 0,
        ]);
        if (!is_null($cur_page)) {
            $params['cur_page'] = $cur_page;
        }
        if (!is_null($size)) {
            $params['size'] = $size;
        }
        $relation_id_arr = [];
        if (array_key_exists('data', $result_department)) {
            foreach ($result_department['data'] as $row_id => $row_data) {
                if ($row_data['classify_structure_type_id'] != null) {
                    array_push($relation_id_arr, $row_data['classify_structure_type_id']);
                }
            }
            if (count($relation_id_arr) != 0) {
                $data = $Component->get_classify_structure_type_folder($params, $relation_id_arr, $result_department['relation_select'], $result_department['relation_from'], $result_department['relation_order']);
            } else {
                $data = ['data' => [], 'total' => 0];
            }
        }
        $get_role_department = $organization_structure->get_role_department($params);
        $data = array_merge(
            $data,
            ["role_department" => $get_role_department["data"]]
        );
        $response = $organization_structure->get_staff_import_manual($data, $response, $params);
        return $response;
    }

    public function get_staff_import_data($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $last_edit_user_id = $_SESSION['id'];
            $data_arr_arr = $organization_structure->read_excel_all_sheet($uploadedFile, true);
            $parser_decode = [
                "staff_serial_name" => "編號(系統帳號)",
                "staff_name" => "姓名",
                "staff_english_name" => "英文名",
                "gender_id" => "性別（男1女2）",
                "e_mail" => "Email",
                "address" => "地址",
                "property_json" => "更多資訊",
            ];
            $get_role_department = $organization_structure->get_role_department([]);
            foreach ($data_arr_arr as $data_arr_key => $data_arr) {
                foreach ($get_role_department['data'] as $item) {
                    $sheetName = $item['role_language_data'][0]['language_culture_value'];
                    if ($data_arr_key == $sheetName) {
                        $data_import = $organization_structure->decodestaffData($data_arr, $parser_decode, $item);

                        $result = [
                            'data' => [],
                        ];
                        foreach ($data_import as $column) {
                            $organization_structure_register = $home->organization_structure_register([$column]);
                            if (array_key_exists('data', $organization_structure_register)) {
                                $result['data'] = array_merge($organization_structure_register['data'], $result['data']);
                            } else {
                                goto import_error;
                            }
                        }
                        $result = $organization_structure->post_staff($result['data'], $last_edit_user_id);
                    }
                }
            }
        } else {
            import_error:
            $result = array(
                "status" => "failure",
                "message" => "匯入失敗"
            );
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_staff_export_excel($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $params['excel'] = true;
        $result = [
            "data" => $organization_structure->get_staff($params),
            "response" => $response,
            "name" => '人員名單',
        ];
        $response = $organization_structure->getExcel($result);
        return $response;
    }

    public function reset_user_password($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $home = new Home($this->container->db);
        $result = [];
        foreach ($data as $key => $value) {
            $value['password'] = '0000';
            $result = $home->resetPassword($value);
            if ($result['status'] != 'success') {
                goto exceptionError;
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
        exceptionError:
        $response = $response->withStatus(500);
        $response = $response->withJson($result);
        return $response;
    }

    public function get_statistics_line($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_statistics_line($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_role_department($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_role_department($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_department_depth($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_department_depth($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_department_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $department_depth_id_arr = [];
        $department_depth_id_arr['department_depth_id_arr'] = [];
        foreach ($data as $index => $department_depth) {
            $department_depth_id_arr['department_id'] = $department_depth['department_id'];
            if (array_key_exists('department_depth_id', $department_depth)) {
                $patch_department_depth = $organization_structure->patch_department_depth([$department_depth], $last_edit_user_id);
                if ($patch_department_depth['status'] == 'success') {
                    array_push($department_depth_id_arr['department_depth_id_arr'], $department_depth['department_depth_id']);
                }
                $result = $patch_department_depth;
            } else {
                $post_department_depth = $organization_structure->post_department_depth([$department_depth], $last_edit_user_id);
                if ($post_department_depth['status'] === 'success') {
                    array_push($department_depth_id_arr['department_depth_id_arr'], $post_department_depth['department_depth_id']);
                }
                $result = $post_department_depth;
            }
        }
        if (!isset($department_depth['single'])) {
            $result = $organization_structure->delete_department_depth([$department_depth_id_arr], $last_edit_user_id);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_role_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $role_depth_id_arr = [];
        $role_depth_id_arr['role_depth_id_arr'] = [];
        foreach ($data as $index => $role_depth) {
            $role_depth_id_arr['role_id'] = $role_depth['role_id'];
            if (array_key_exists('role_depth_id', $role_depth)) {
                $patch_role_depth = $organization_structure->patch_role_depth([$role_depth], $last_edit_user_id);
                if ($patch_role_depth['status'] == 'success') {
                    array_push($role_depth_id_arr['role_depth_id_arr'], $role_depth['role_depth_id']);
                }
            } else {
                $post_role_depth = $organization_structure->post_role_depth([$role_depth], $last_edit_user_id);
                if ($post_role_depth['status'] === 'success') {
                    array_push($role_depth_id_arr['role_depth_id_arr'], $post_role_depth['role_depth_id']);
                }
            }
        }
        $result = $organization_structure->delete_role_depth([$role_depth_id_arr], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_role_property_json($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_role_property_json($params);
        if (count($result['data']) == 0) {
            $last_edit_user_id = $_SESSION['id'];
            $result = $organization_structure->post_role_property_json([["property_json" => []]], $last_edit_user_id);
            $result = $organization_structure->get_role_property_json($params);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_role_property_json($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->patch_role_property_json($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_department_permission($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->get_department_permission($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_department_permission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->post_department_permission($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_department_permission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $organization_structure->patch_department_permission($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_department_permission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $result = $organization_structure->delete_department_permission($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
