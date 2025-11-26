<?php

use \Slim\Views\PhpRenderer;


class librarycontroller
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
        return $this->response_return($response, $result);
    }



    // 部門單位/讀者群組串接
    public function get_department($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new Component($this->container->db);
        $oController = new Koha();
        $library = new library();

        if (array_key_exists('classify_structure_id', $params) && $params['classify_structure_id']) {
            $params['classify_structure_id'] = $params['classify_structure_id'];
        } else {
            $params['classify_structure_id'] = $this->container['classify_structure']['classify_structure_department_id'];
            $result_department = $organization_structure->get_department($params);
            unset($result_department['relation_select']);
            unset($result_department['relation_from']);
            unset($result_department['relation_order']);
            return $this->response_return($response, $result_department);
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
                $base_department = [];
                if (array_key_exists('data', $result)) $base_department = $result["data"];

                // 串接 Koha 讀者分類
                $koha_borrower_category = [];
                $koha_params = $params;
                $koha_params["cur_page"] = 1;
                $koha_params["size"] = 10000;
                $aData = $oController->get_borrower_category($koha_params);
                if (array_key_exists('data', $aData)) $koha_borrower_category = $aData["data"];

                $concatenation_result = $library->get_department_from_base_department_to_koha_borrower_category($base_department, $koha_borrower_category);
                $result["base_department"] = $base_department;
                $result["aData"] = $aData;
                $result["data"] = $concatenation_result;
            } else {
                $result = ['data' => [], 'total' => 0];
            }
        }
        return $this->response_return($response, $result);
    }
    public function post_department($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $Component = new component($this->container->db);
        $oController = new Koha();
        $oControllerLibrary = new library();
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

        $random_code = substr(bin2hex(random_bytes(ceil(10 / 2))), 0, 10);
        $default_category_data = [
            [
                "categorycode" => isset($data["categorycode"]) ? $data["categorycode"] : $random_code,
                "description" => isset($data["name"]) ? $data["name"] : $random_code . "群組",
                "enrolmentperiod" => 120,
                "enrolmentperioddate" => null,
                "password_expiry_days" => 180,
                "upperagelimit" => 18,
                "dateofbirthrequired" => 127,
                "enrolmentfee" => 0,
                "overduenoticerequired" => 0,
                "hidelostitems" => 1,
                "reservefee" => 0,
                "category_type" => "P",
                "can_be_guarantee" => 0,
                "reset_password" => 0,
                "change_password" => 1,
                "min_password_length" => 8,
                "require_strong_password" => 1,
                "BlockExpiredPatronOpacActions" => 1,
                "default_privacy" => "default",
                "exclude_from_local_holds_priority" => 0,
                // "branchcode" => "GW",
                "branches" => [],
                "message" => [
                    // [
                    //     "message_attribute_id" => 1,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 2,
                    //     "days_in_advance" => 4,
                    //     "email" => 1,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 4,
                    //     "days_in_advance" => null,
                    //     "email" => 1,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 5,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 6,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 10,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                ],
            ],
        ];
        $aData = $oController->post_borrower_category($default_category_data);
        foreach ($aData as $key => $value) {
            if (!isset($value["result"])) {
                return $this->response_return($response, [
                    "status" => "failure",
                    "message" => "資料有誤，請檢查",
                ]);
            }
            if ($value["result"] === false) {
                return $this->response_return($response, [
                    "status" => "failure",
                    "message" => "資料有誤，請檢查",
                ]);
            }
        }

        $aDataLibrary = $oControllerLibrary->post_borrower_category_map2department([[
            "categorycode" => isset($data["categorycode"]) ? $data["categorycode"] : $random_code,
            "department_id" => $result["department_id"]
        ]]);
        foreach ($aDataLibrary as $key => $value) {
            if (!isset($value["result"])) {
                return $this->response_return($response, [
                    "status" => "failure",
                    "message" => "新增群組失敗",
                    "error" => $aDataLibrary
                ]);
            }
        }
        return $this->response_return($response, [
            "status" => "success",
            "message" => "新增群組成功",
            "data" => $aDataLibrary
        ]);
    }
    public function patch_department($request, $response, $args)
    {
        $data = $request->getParsedBody();

        // 串接 Koha 讀者分類
        $oController = new Koha();
        $library = new library();
        foreach ($data as $key => $value) {
            $data[$key]["description"] = $data[$key]["name"];
            $categorycode = $library->get_borrower_category_map2department($value);
            $data[$key]["categorycode"] = $categorycode["categorycode"];
            $default_data = [
                "enrolmentperiod" => 120,
                "enrolmentperioddate" => null,
                "password_expiry_days" => 180,
                "upperagelimit" => 18,
                "dateofbirthrequired" => 127,
                "enrolmentfee" => 0,
                "overduenoticerequired" => 0,
                "hidelostitems" => 1,
                "reservefee" => 0,
                "category_type" => "P",
                "can_be_guarantee" => 0,
                "reset_password" => 0,
                "change_password" => 1,
                "min_password_length" => 8,
                "require_strong_password" => 1,
                "BlockExpiredPatronOpacActions" => 1,
                "default_privacy" => "default",
                "exclude_from_local_holds_priority" => 0,
                // "branchcode" => "GW",
                "branches" => [],
                "message" => [
                    // [
                    //     "message_attribute_id" => 1,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 2,
                    //     "days_in_advance" => 4,
                    //     "email" => 1,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 4,
                    //     "days_in_advance" => null,
                    //     "email" => 1,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 5,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 6,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                    // [
                    //     "message_attribute_id" => 10,
                    //     "days_in_advance" => null,
                    //     "email" => 0,
                    //     "wants_digest" => 0,
                    // ],
                ],
            ];
            $data[$key] = array_merge($data[$key], $default_data);
        }
        // $aData = $oController->patch_borrower_category($data);
        $result = $oController->patch_borrower_category($data);

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
        $oController = new Koha();
        foreach ($data as $key => $value) {
            if (is_null($value["categorycode"])) continue;
            $borrower = $oController->get_borrower(["categorycode" => $value["categorycode"]]);
            if (!empty($borrower["data"])) return $this->response_return($response, [
                "status" => "failure",
                "message" => "讀者類型:{$value['description']} 尚有其他讀者，無法刪除"
            ]);
        }
        $aData = $oController->delete_borrower_category($data);
        $result = $organization_structure->delete_department($data);
        return $this->response_return($response, $result);
    }
    // 人員基本資料維護/讀者串接
    public function get_staff($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $oController = new Koha();
        $library = new library();
        $result = $organization_structure->get_staff($params);

        $base_staff = [];
        if (array_key_exists('data', $result)) $base_staff = $result["data"];

        // 拿讀者串接的對應資料
        $concatention = $library->get_borrower_map2user($base_staff);
        $user_id_list = [];
        $borrower_list = [];
        foreach ($concatention as $key => $value) {
            $user_id_list[] = $value["user_id"];
            $borrower_list[] = $value["borrowernumber"];
        }

        // 串接 Koha 讀者
        $koha_borrower = [];
        $aData = $oController->get_borrower([
            "user_id" => $user_id_list,
            "borrowernumber" => $borrower_list,
        ]);
        if (array_key_exists('data', $aData)) $koha_borrower = $aData["data"];

        $concatenation_result = $library->get_staff_from_base_staff_to_koha_borrower($base_staff, $koha_borrower);
        $result["data"] = $concatenation_result;
        // $result["aData"] = $concatenation_result;
        // $result["concatention"] = $concatention;
        return $this->response_return($response, $result);
    }
    public function post_staff($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $oController = new Koha();
        $oControllerLibrary = new library();
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

        $final_execute = null;

        $library_data = "";
        $category_data = "";
        $library_request = $oController->get_library([])["data"];
        $category_request = $oController->get_borrower_category([])["data"];
        foreach ($library_request as $lkey => $lvalue) $library_data = $lvalue["branchcode"];
        foreach ($category_request as $ckey => $cvalue) $category_data = $cvalue["categorycode"];
        foreach ($result['data'] as $staff_key => $staff_value) {
            $post_staff_result = $organization_structure->post_staff([$staff_value], $last_edit_user_id);
            $default_category_data = [
                [
                    "surname" => $staff_value["staff_name"],
                    // "branchcode" => "GW",
                    // "categorycode" => "09595efda7", // 開發機
                    // "branchcode" => "y",
                    // "categorycode" => "6b95597805", // 測試機
                    "branchcode" => isset($staff_value["branchcode"]) ? $staff_value["branchcode"] : $library_data,
                    "categorycode" => isset($staff_value["categorycode"]) ? $staff_value["categorycode"] : $category_data,
                    "cardnumber" => $staff_value["staff_serial_name"],
                    "userid" => $staff_value["staff_serial_name"],
                    "password" => "Test" . $staff_value["staff_serial_name"],
                    "email" => $staff_value["email"],
                    "address" => $staff_value["address"],
                    "manager_id" => "0",
                    "message" => [],
                    "additional_attributes" => [
                        // [
                        //     "code" => "ext",
                        //     "description" => "讀者額外資料",
                        //     "repeatable" => 0,
                        //     "id" => 0,
                        //     "attribute" => "2",
                        //     "wantDelete" => 0
                        // ]
                    ]
                ]
            ];
            $aData = $oController->post_borrower($default_category_data);
            $patron_id = null;
            foreach ($aData as $aDataKey => $aDataValue) {
                if (!isset($aDataValue["result"])) {
                    return $this->response_return($response, [
                        "status" => "failure",
                        "message" => "新增讀者失敗",
                        "error" => $aData,
                        "library" => $library_request,
                        "category" => $category_request,
                        "body" => $data,
                        "result" => $result['data'],
                    ]);
                } else $patron_id = $aDataValue["result"]->patron_id;
            }
            // 另外新增一組API帳號供流通用
            $api_account_data = [
                [
                    "surname" => $staff_value["staff_name"] . "-api",
                    "branchcode" => isset($staff_value["branchcode"]) ? $staff_value["branchcode"] : $library_data,
                    "categorycode" => isset($staff_value["categorycode"]) ? $staff_value["categorycode"] : $category_data,
                    "cardnumber" => $staff_value["staff_serial_name"] . "KEY001",
                    "userid" => $staff_value["staff_serial_name"] . "-api",
                    "password" => "Test" . $staff_value["staff_serial_name"],
                    "email" =>  "api-" . $staff_value["email"],
                    "address" => $staff_value["address"],
                    "manager_id" => "0",
                    "message" => [],
                    "additional_attributes" => []
                ]
            ];
            $aData_api = $oController->post_borrower($api_account_data);
            $api_patron_id = null;
            foreach ($aData_api as $aData_api_Key => $aData_api_Value) {
                if (!isset($aData_api_Value["result"])) {
                    return $this->response_return($response, [
                        "status" => "failure",
                        "message" => "新增讀者失敗",
                        "error" => $aData_api,
                        "library" => $library_request,
                        "category" => $category_request,
                        "body" => $data,
                        "result" => $result['data'],
                    ]);
                } 
                else {
                    $api_patron_id = $aData_api_Value["result"]->patron_id;
                    $permissions = [
                        [
                            "borrowernumber" => $api_patron_id,
                            "userflags" => [
                                [
                                    "bit" => 1,
                                    "allow_flag" => 1,
                                    "permissions" => [
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "circulate_remaining_permissions" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "force_checkout" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_bookings" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_checkout_notes" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_curbside_pickups" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_restrictions" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "overdues_report" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "override_renewals" ]
                                    ]
                                ],
                                [
                                    "bit" => 6,
                                    "allow_flag" => 1,
                                    "permissions" => [
                                        [ "module_bit" => 6, "allow_code" => 1, "code" => "modify_holds_priority" ],
                                        [ "module_bit" => 6, "allow_code" => 1, "code" => "place_holds" ]
                                    ],
                                ],
                                [
                                    "bit" => 9,
                                    "allow_flag" => 1,
                                    "permissions" => [
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "advanced_editor" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "create_shared_macros" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "delete_all_items" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "delete_shared_macros" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_any_item" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_catalogue" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_items" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_items_restricted" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "fast_cataloging" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "manage_item_editor_templates" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "manage_item_groups" ]
                                    ],
                                ],
                            ],
                        ],
                    ];
                    $permission_aData = $oController->patch_borrower_permissions($permissions);
                }
            }

            $aData = $oController->post_borrower_map2user([[
                "borrowernumber" => $patron_id,
                "user_id" => $post_staff_result["user_id"],
                "api_borrowernumber" => $api_patron_id, 
                "api_borrower_userid" => $staff_value["staff_serial_name"] . "-api", 
                "api_borrower_password" => "Test" . $staff_value["staff_serial_name"], 
            ]]);
            $final_execute = $aData;
            foreach ($aData as $key => $value) {
                if (!isset($value["result"])) {
                    return $this->response_return($response, [
                        "status" => "failure",
                        "message" => "新增讀者失敗",
                        "log" => $aData
                    ]);
                }
            }
        }
        return $this->response_return($response, [
            "status" => "success",
            "message" => "新增成功",
            "log" => $final_execute,
        ]);
    }
    public function patch_staff($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $oController = new Koha();
        $library = new library();
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
                if (array_key_exists('user_language_data', $value)) {
                    $delete_user_role = $organization_structure->delete_user_role($value);
                    if ($delete_user_role['status'] === 'success') {
                        foreach ($value['user_language_data'] as $user_language_data_key => $user_language_data_value) {
                            $create_user_role = $organization_structure->create_user_role($value['user_id'], $user_language_data_value);
                            if ($create_user_role['status'] !== 'success') {
                                $result = ['create_user_role' => $create_user_role];
                                goto exceptionError;
                            }
                        }
                    } else {
                        $result = ['delete_user_role' => $delete_user_role];
                        goto exceptionError;
                    }
                }
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

        // 串接 Koha 讀者
        foreach ($data as $key => $value) {
            $koha_data = [];
            $borrowernumber = $library->get_borrower_map2user([$value]);
            foreach ($borrowernumber as $bkey => $bvalue) $koha_data["borrowernumber"] = $bvalue["borrowernumber"];
            $koha_data["surname"] = $value["staff_name"];
            $koha_data["branchcode"] = $value["branchcode"];
            $koha_data["categorycode"] = $value["categorycode"];
            $koha_data["email"] = $value["email"];
            $koha_data["address"] = $value["address"];
            $koha_data["manager_id"] = "0";
            $koha_data["message"] = [];
            $koha_data["additional_attributes"] = [];
            $aData = $oController->patch_borrower([$koha_data]);

            // 順帶修改 API account 資訊
            foreach ($borrowernumber as $bkey => $bvalue) $koha_data["borrowernumber"] = $bvalue["api_borrowernumber"];
            $koha_data["surname"] = $value["staff_name"] . "-api";
            $koha_data["email"] = "api-" . $value["email"];
            $aData = $oController->patch_borrower([$koha_data]);

            
        }

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
        $oController = new Koha();
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
        $aData = $oController->delete_borrower($data);
        return $this->response_return($response, $result);
    }
    // 匯出讀者
    public function get_staff_export_excel($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $oController = new Koha();
        $library = new library();

        $result = $organization_structure->get_staff($params);
        $base_staff = [];
        if (array_key_exists('data', $result)) $base_staff = $result["data"];
        // 拿讀者串接的對應資料
        $concatention = $library->get_borrower_map2user($base_staff);
        $user_id_list = [];
        $borrower_list = [];
        foreach ($concatention as $key => $value) {
            $user_id_list[] = $value["user_id"];
            $borrower_list[] = $value["borrowernumber"];
        }
        // 串接 Koha 讀者
        $koha_borrower = [];
        $aData = $oController->get_borrower([
            "user_id" => $user_id_list,
            "borrowernumber" => $borrower_list,
        ]);
        if (array_key_exists('data', $aData)) $koha_borrower = $aData["data"];
        $concatenation_result = $library->get_staff_from_base_staff_to_koha_borrower($base_staff, $koha_borrower);
        $result["data"] = $concatenation_result;

        $params['excel'] = true;
        $staff_data = $organization_structure->get_staff($params);
        foreach ($staff_data as $key => $value) {
            foreach ($result["data"] as $skey => $svalue) {
                if ($value["編號(系統帳號)"] === $svalue["cardnumber"]) {
                    $staff_data[$key]["卡號"] = $svalue["cardnumber"];
                    $staff_data[$key]["分館代碼"] = $svalue["branchcode"];
                    $staff_data[$key]["所屬分館"] = $svalue["branchname"];
                    $staff_data[$key]["讀者分類"] = $svalue["categoryname"];
                    break;
                }
            }
            unset($staff_data[$key]["單位 - 職稱1（可自行新增，新增依序為：單位 - 職稱2、單位 - 職稱3）"]);
            unset($staff_data[$key]["單位 - 職稱2（可自行新增，新增依序為：單位 - 職稱2、單位 - 職稱3）"]);
        }

        $excel = [
            "data" => $staff_data,
            "response" => $response,
            "name" => '人員名單',
        ];
        $response = $organization_structure->getExcel($excel);
        return $response;
    }
    // 範例檔案下載
    public function get_staff_import_manual($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $oController = new Koha();
        // $component = new component($this->container->db);
        // $data['name'] = '組織架構模組 - 人員匯入範例'; //針對組織架構 - 人員下載
        // $result = $component->get_manual($data);

        // foreach ($result['data'] as $result_inner) {
        //     $result = $result_inner;
        // }
        // $diectory = $this->container->upload_directory;
        // $file = $diectory . '/' . $result['file_name'];
        // $response = $response->withHeader('Content-Description', 'File Transfer')
        //     ->withHeader('Content-Type', 'application/octet-stream')
        //     ->withHeader('Content-Disposition', "attachment;filename={$result["file_client_name"]}")
        //     ->withHeader('Expires', '0')
        //     ->withHeader('Cache-Control', 'must-revalidate')
        //     ->withHeader('Pragma', 'public');
        // readfile($file);

        $library_request = $oController->get_library([])["data"];
        $category_request = $oController->get_borrower_category(["cur_page" => 1, "size" => 1000])["data"];
        $library_string = "";
        foreach ($library_request as $key => $value) $library_string .= "{$value['branchname']}: {$value['branchcode']}, ";
        $library_string = "(" . rtrim($library_string, ", ") . ")";
        $category_string = "";
        foreach ($category_request as $key => $value) {
            $name = trim($value['description']);
            $category_string .= "{$name}: {$value['categorycode']}, ";
        }
        $category_string = "(" . rtrim($category_string, ", ") . ")";

        $excel = [
            "data" => [
                [
                    " 編號(系統帳號) " => "",
                    " 姓名 " => "",
                    " 英文名 " => "",
                    " 性別（男1女2） " => "",
                    " Email " => "",
                    " 地址 " => "",
                    " 更多資訊 " => "",
                    " 卡號 " => "",
                    " 所屬分館{$library_string} " => "",
                    " 讀者分類{$category_string} " => "",
                ]
            ],
            "response" => $response,
            "name" => '人員名單',
        ];
        $response = $organization_structure->getExcel($excel);
        return $response;
    }
    // 讀者匯入檔案上傳
    public function get_staff_import_data($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $oController = new Koha();
        $oControllerLibrary = new library();

        $library_data = "";
        $library_string = "";
        $category_data = "";
        $category_string = "";
        $library_request = $oController->get_library([])["data"];
        $category_request = $oController->get_borrower_category(["cur_page" => 1, "size" => 1000])["data"];
        foreach ($library_request as $lkey => $lvalue) {
            $library_data = $lvalue["branchcode"];
            $library_string .= "{$lvalue['branchname']}: {$lvalue['branchcode']}, ";
        }
        $library_string = "(" . rtrim($library_string, ", ") . ")";
        foreach ($category_request as $ckey => $cvalue) {
            $category_data = $cvalue["categorycode"];
            $name = trim($cvalue['description']);
            $category_string .= "{$name}: {$cvalue['categorycode']}, ";
        }
        $category_string = "(" . rtrim($category_string, ", ") . ")";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $last_edit_user_id = $_SESSION['id'];
            $data_arr = $organization_structure->read_excel($uploadedFile, $data);

            $parser_decode = [
                "staff_serial_name" => "編號(系統帳號)",
                // "user_department_role_data" => "單位 - 職稱data",
                "staff_name" => "姓名",
                "staff_english_name" => "英文名",
                "gender_id" => "性別（男1女2）",
                "e_mail" => "Email",
                "address" => "地址",
                "property_json" => "更多資訊",
                "cardnumber" => "卡號",
                "branchcode" => "所屬分館{$library_string}",
                "categorycode" => "讀者分類{$category_string}",
            ];
            $data_import = $organization_structure->decodestaffData($data_arr, $parser_decode, ["department_role_data" => []]);

            $result = [
                'data' => [],
                "status" => "success"
            ];
            foreach ($data_import as $column) {
                $organization_structure_register = $home->organization_structure_register([$column]);
                if (array_key_exists('data', $organization_structure_register)) {
                    $result['data'] = array_merge($organization_structure_register['data'], $result['data']);
                }
            }
            // $result = $organization_structure->post_staff($result['data'], $last_edit_user_id);
            foreach ($result['data'] as $staff_key => $staff_value) {
                $result = $organization_structure->post_staff([$staff_value], $last_edit_user_id);
                $default_category_data = [
                    [
                        "surname" => $staff_value["staff_name"],
                        "branchcode" => isset($staff_value["branchcode"]) ? $staff_value["branchcode"] : $library_data,
                        "categorycode" => isset($staff_value["categorycode"]) ? $staff_value["categorycode"] : $category_data,
                        "cardnumber" => $staff_value["staff_serial_name"],
                        "userid" => $staff_value["staff_serial_name"],
                        "password" => "Test" . $staff_value["staff_serial_name"],
                        "email" => $staff_value["email"],
                        "address" => $staff_value["address"],
                        "manager_id" => "0",
                        "message" => [],
                        "additional_attributes" => []
                    ]
                ];
                $aData = $oController->post_borrower($default_category_data);
                $patron_id = null;
                foreach ($aData as $aDataKey => $aDataValue) {
                    if (!isset($aDataValue["result"])) {
                        return $this->response_return($response, [
                            "status" => "failure",
                            "message" => "新增讀者項目失敗",
                            "error" => $aData,
                            "library" => $library_request,
                            "category" => $category_request,
                            "body" => $data,
                            "result" => $result['data'],
                        ]);
                    } else $patron_id = $aDataValue["result"]->patron_id;
                }
                $aData = $oController->post_borrower_map2user([[
                    "borrowernumber" => $patron_id,
                    "user_id" => $result["user_id"]
                ]]);
                foreach ($aData as $key => $value) {
                    if (!isset($value["result"])) {
                        return $this->response_return($response, [
                            "status" => "failure",
                            "message" => "新增讀者對應失敗",
                            "log" => $aData
                        ]);
                    }
                }
            }
        } else {
            $result = array(
                "status" => "failure",
                "message" => "匯入失敗"
            );
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }



    // 新增讀者對應用的開放 API（僅供內部開發使用）
    public function get_staff_for_koha_relation($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $oController = new Koha();
        $library = new library();
        $aData = $oController->get_borrower($params);
        $transform_data = [];
        foreach($aData["data"] as $key => $value) {
            $push_array = [
                "remember" => true,
                "name" => $value["surname"],
                "serial_name" => $value["userid"],
                "staff_serial_name" => $value["userid"],
                "staff_name" => $value["surname"],
                "staff_english_name" => "",
                "gender_id" => $value["sex"] === "M" ? "1" : "2",
                "cardnumber" => $value["cardnumber"] . "KEY001",
                "branchcode" => $value["branchcode"],
                "categorycode" => $value["categorycode"],
                "staff_phone" => $value["phone"],
                "email" => $value["email"],
                "custom_line_id" => "",
                "address" => $value["address"],
                "user_language_data" => [],

                "borrowernumber" => $value["borrowernumber"], 
                "surname" => $value["surname"] . "-api",
                "userid" => $value["userid"] . "-api",
                "password" => $value["password"],
                "api-email" =>  "api-" . $value["email"],
                "manager_id" => "0",
                "message" => $value["message"],
                "additional_attributes" => $value["additional_attributes"], 
            ];
            $transform_data[] = $push_array;
        }
        return $this->response_return($response, [
            "status" => "success",
            "result" => $transform_data,
            "original" => $aData, 
        ]);
    }
    public function post_staff_for_koha_relation($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $organization_structure = new organization_structure($this->container->db);
        $home = new Home($this->container->db);
        $oController = new Koha();
        $oControllerLibrary = new library();

        $aData = $oController->get_borrower($body);
        $transform_data = [];
        $final_execute = null;
        foreach($aData["data"] as $key => $value) {
            // 準備新增讀者資料
            $push_array = [
                "remember" => true,
                "name" => $value["surname"],
                "serial_name" => $value["userid"],
                "staff_serial_name" => $value["userid"],
                "staff_name" => $value["surname"],
                "staff_english_name" => "",
                "gender_id" => $value["sex"] === "M" ? "1" : "2",
                "cardnumber" => $value["cardnumber"] . "KEY001",
                "branchcode" => $value["branchcode"],
                "categorycode" => $value["categorycode"],
                "staff_phone" => $value["phone"],
                "email" => $value["email"],
                "custom_line_id" => "",
                "address" => $value["address"],
                "user_language_data" => [],

                "surname" => $value["surname"] . "-api",
                "userid" => $value["userid"] . "-api",
                "password" => $value["password"],
                "api-email" =>  "api-" . $value["email"],
                "manager_id" => "0",
                "message" => [],
                "additional_attributes" => [], 
            ];
            // LDAP 註冊讀者
            $organization_structure_register = $home->organization_structure_register([$push_array]);
            $push_array["user_id"] = null;
            $usr_id = null;
            foreach($organization_structure_register["data"] as $register_key => $register_value) {
                $push_array["user_id"] = $register_value["user_id"];
                $user_id = $register_value["user_id"];
            }
            // 平台新增讀者
            $post_staff_result = $organization_structure->post_staff([$push_array], 215);
            // 轉換 koha 新增讀者的 email value，因為與平台的 key 名稱相衝突
            $tmp_email = $push_array["email"];
            $push_array["email"] = $push_array["api-email"];
            $push_array["staff_email"] = $tmp_email;
            unset($push_array["api-email"]);
            // koha 新增讀者 API account
            // post borrower 如果有傳 user_id 會死
            unset($push_array["user_id"]);
            // 另外新增一組API帳號供流通用
            $api_account_data = [
                [
                    "surname" => $value["surname"] . "-api",
                    "branchcode" => $value["branchcode"],
                    "categorycode" => $value["categorycode"],
                    "cardnumber" => $value["cardnumber"] . "KEY001",
                    "userid" => $value["userid"] . "-api",
                    "password" => "KEY001-" . $value["password"] . "-api", 
                    "email" => "api-" . $value["email"],
                    "address" => $value["address"],
                    "manager_id" => "0",
                    "message" => [],
                    "additional_attributes" => []
                ]
            ];
            $aData_api = $oController->post_borrower($api_account_data);
            $push_array["user_id"] = $user_id;
            $api_patron_id = null;
            foreach ($aData_api as $aData_api_Key => $aData_api_Value) {
                if (!isset($aData_api_Value["result"])) {
                    return $this->response_return($response, [
                        "status" => "failure",
                        "message" => "新增讀者失敗",
                        "error" => $aData_api,
                    ]);
                } 
                else {
                    // koha 新增讀者 API account 成功後，修改該讀者 API account 的權限
                    $api_patron_id = $aData_api_Value["result"]->patron_id;
                    $permissions = [
                        [
                            "borrowernumber" => $api_patron_id,
                            "userflags" => [
                                [
                                    "bit" => 1,
                                    "allow_flag" => 1,
                                    "permissions" => [
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "circulate_remaining_permissions" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "force_checkout" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_bookings" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_checkout_notes" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_curbside_pickups" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "manage_restrictions" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "overdues_report" ],
                                        [ "module_bit" => 1, "allow_code" => 1, "code" => "override_renewals" ]
                                    ]
                                ],
                                [
                                    "bit" => 6,
                                    "allow_flag" => 1,
                                    "permissions" => [
                                        [ "module_bit" => 6, "allow_code" => 1, "code" => "modify_holds_priority" ],
                                        [ "module_bit" => 6, "allow_code" => 1, "code" => "place_holds" ]
                                    ],
                                ],
                                [
                                    "bit" => 9,
                                    "allow_flag" => 1,
                                    "permissions" => [
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "advanced_editor" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "create_shared_macros" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "delete_all_items" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "delete_shared_macros" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_any_item" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_catalogue" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_items" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "edit_items_restricted" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "fast_cataloging" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "manage_item_editor_templates" ],
                                        [ "module_bit" => 9, "allow_code" => 1, "code" => "manage_item_groups" ]
                                    ],
                                ],
                            ],
                        ],
                    ];
                    $permission_aData = $oController->patch_borrower_permissions($permissions);
                }
            }
            // 新增平台讀者與 koha 讀者的 relation 對應
            $aData = $oController->post_borrower_map2user([[
                "borrowernumber" => $value["borrowernumber"],
                "user_id" => $post_staff_result["user_id"],
                "api_borrowernumber" => $api_patron_id, 
                "api_borrower_userid" => $value["userid"] . "-api", 
                "api_borrower_password" => "KEY001-" . $value["password"] . "-api", 
            ]]);
            $final_execute = $aData;
            foreach ($aData as $key => $value) {
                if (!isset($value["result"])) {
                    return $this->response_return($response, [
                        "status" => "failure",
                        "message" => "新增讀者對應失敗",
                        "log" => $aData
                    ]);
                }
            }
            // 紀錄 post 資料
            $transform_data[] = $push_array;
        }
        return $this->response_return($response, [
            "status" => "success",
            "message" => "新增成功",
            "result" => $transform_data,
            "original" => $aData, 
            "log" => $final_execute,
        ]);
    }
}
