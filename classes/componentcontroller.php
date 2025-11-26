<?php

use \Slim\Views\PhpRenderer;


class componentcontroller extends ModelController
{
    protected $container;
    protected $samples = [];
    public function __construct()
    {
        global $container;
        $this->container = $container;
        // 初始範例資料
        $this->loadSamples($this);
    }
    // 00001 壓縮及處理圖片
    public function compressImage($source = false, $destination = false, $quality = 80, $filters = false)
    {
        $info = getimagesize($source);
        switch ($info['mime']) {
            case 'image/jpeg':
                /* Quality: integer 0 - 100 */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromjpeg($source);
            case 'image/gif':
                return imagecreatefromgif($source);
            case 'image/png':
                /* Quality: Compression integer 0(none) - 9(max) */
                if (!is_int($quality) or $quality < 0 or $quality > 9) $quality = 6;
                return imagecreatefrompng($source);
            case 'image/webp':
                /* Quality: Compression 0(lowest) - 100(highest) */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromwebp($source);
            case 'image/bmp':
                /* Quality: Boolean for compression */
                if (!is_bool($quality)) $quality = true;
                return imagecreatefrombmp($source);
            default:
                return;
        }
    }
    // 00002 拿取檔案
    public function get_file($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);

        $result = $Component->get_file_name($params);
        foreach ($result as $result_inner) {
            $result = $result_inner;
        }
        $diectory = $this->container->upload_directory;
        $file = $diectory . '/' . $result['file_name'];
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="' . basename($result['file_client_name']) . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', filesize($file));
        readfile($file);

        return $response;
    }
    // 00003 新增檔案
    public function post_file($request, $response, $args)
    {
        $data = $request->getParams();
        $data['files'] = $request->getUploadedFiles();
        $Component = new component($this->container->db);
        $file = $Component->uploadFile($data);
        unset($data['files']);
        $file['user_id'] = 0;

        $data['file_id'] = $Component->insertFile($file);
        if ($data['file_id'] == '' || $data['file_id'] == null) {
            $result['status'] = 'failed';
        } else {
            $result['file_id'] = $data['file_id'];
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00004 拿取語言
    public function get_language_culture($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_language_culture($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00005 新增語言
    public function post_language_culture($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_language_culture($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00006 修改語言
    public function patch_language_culture($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_language_culture($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00007 刪除語言
    public function delete_language_culture($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_language_culture($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00008 拿取多國語言管理

    // 定義每個功能的範例資料
    protected function getLanguageManageSample()
    {
        return [
            'get_language_manage' => [
                'input' => [
                    'language_manage_id' => 1,
                    'cur_page' => 1,
                    'size' => 10
                ],
                'output' => [
                    'data' => [
                        [
                            "language_manage_id" => 1,
                            "language_culture_id" => 1,
                            "schema_name" => "organization_structure",
                            "table_name" => "role",
                            "column_name" => "role_name",
                            "table_primary_id" => 2,
                            "language_value" => "職員",
                            "key" => 1
                        ]
                    ],
                    'total' => 1
                ]
            ]
        ];
    }
    public function get_language_manage($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_language_manage($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00009 新增多國語言管理
    protected function postLanguageManageSample()
    {
        return [
            'post_language_manage' => [
                'input' => [
                    [
                        "language_culture_code" => "zh-tw",
                        "schema_name" => "organization_structure",
                        "table_name" => "role",
                        "column_name" => "role_name",
                        "table_primary_id" => 1,
                        "language_value" => "主管"
                    ]
                ],
                'output' => [
                    "status" => "success",
                    "language_manage_id" => 4046
                ]
            ]
        ];
    }
    public function post_language_manage($request, $response, $args)
    {
        $sample = [
            'input' => [
                [
                    "language_culture_code" => "zh-tw",
                    "schema_name" => "organization_structure",
                    "table_name" => "role",
                    "column_name" => "role_name",
                    "table_primary_id" => 1,
                    "language_value" => "主管"
                ]

            ],
            'output' => [
                "status" => "success",
                "language_manage_id" => 4046
            ]
        ];
        // 檢查是否回傳sample
        if ($request->getQueryParam('sample', false)) {
            return $response->withJson($sample['output']);
        }
        $params = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_language_manage($params, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00010 修改多國語言管理
    protected function patchLanguageManageSample()
    {
        return [
            'patch_language_manage' => [
                'input' => [
                    [
                        "language_manage_id" => 4046,
                        "language_culture_id" => 1,
                        "schema_name" => "organization_structure",
                        "table_name" => "role",
                        "column_name" => "role_name",
                        "table_primary_id" => 1,
                        "language_value" => "主管1"
                    ]
                ],
                'output' => [
                    'status' => 'success',
                ]
            ]
        ];
    }
    public function patch_language_manage($request, $response, $args)
    {
        $sample = [
            'input' => [
                [
                    "language_manage_id" => 4046,
                    "language_culture_id" => 1,
                    "schema_name" => "organization_structure",
                    "table_name" => "role",
                    "column_name" => "role_name",
                    "table_primary_id" => 1,
                    "language_value" => "主管1"
                ]
            ],
            'output' => [
                'status' => 'success',
            ]
        ];
        // 檢查是否回傳sample
        if ($request->getQueryParam('sample', false)) {
            return $response->withJson($sample['output']);
        }
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_language_manage($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00011 刪除多國語言管理
    protected function deleteLanguageManageSample()
    {
        return [
            'delete_language_manage' => [
                'input' => [
                    [
                        "language_manage_id" => 4044
                    ]
                ],
                'output' => [
                    'status' => 'success',
                ]
            ]
        ];
    }
    public function delete_language_manage($request, $response, $args)
    {
        $sample = [
            'input' => [
                [
                    "language_manage_id" => 4044
                ]
            ],
            'output' => [
                'status' => 'success',
            ]
        ];
        // 檢查是否回傳sample
        if ($request->getQueryParam('sample', false)) {
            return $response->withJson($sample['output']);
        }
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->delete_language_manage($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00012 拿取客戶
    public function get_customer($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_customer($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00013 新增客戶
    public function post_customer($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_customer($params, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00014 修改客戶
    public function patch_customer($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_customer($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00015 刪除客戶
    public function delete_customer($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->delete_customer($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00016 印QRcode
    public function print_qrcode_pdf_sample($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);

        $data = [
            [
                "qrcode" => "A1",
                "line1" => "A20230628001",
                "line2" => "A1-2",
                "line3" => "A1-3",
            ],
            [
                "qrcode" => "A2",
                "line1" => "A20230628001",
            ],
            [
                "qrcode" => "A3",
                "line1" => "A2-1",
                "line2" => "A2-2",
            ],
            [
                "qrcode" => "A4",
            ],
            [
                "qrcode" => "A5",
                "line1" => "A5-1",
            ],
            [
                "qrcode" => "A6",
                "line1" => "A6-1",
                "line2" => "A6-2",
                "line3" => "A6-3",
            ],
            [
                "qrcode" => "A7",
                "line1" => "A7-1",
                "line2" => "A7-2",
                "line3" => "A7-3",
                "line4" => "A7-4",
                "line5" => "A7-5",
            ],
            [
                "qrcode" => "A8",
                "line1" => "A8-1",
            ]
        ];

        $result = $Component->print_qrcode_pdf($data);
        return $result;
    }
    // 00017 拿取ProTable範例用的Data
    public function get_protable_use_demo_data($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_protable_use_demo_data($params);
        return $this->return_response($response, $result);
    }
    // 00018 拿取ProTable範例用的Permission Group ID Data
    public function get_protable_use_demo_permission_group_id_data($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_protable_use_demo_permission_group_id_data($params);
        return $this->return_response($response, $result);
    }
    // 00019 Response 回傳的function
    function return_response($response, $result)
    {
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00020 拿取編碼來源
    public function get_serial_encode_resource($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_serial_encode_resource($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00021 新增編碼來源
    public function post_serial_encode_resource($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_serial_encode_resource($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00022 修改編碼來源
    public function patch_serial_encode_resource($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_serial_encode_resource($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00023 刪除編碼來源
    public function delete_serial_encode_resource($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_serial_encode_resource($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00024 拿取編碼符號
    public function get_serial_encode_sign($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_serial_encode_sign($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00025 新增編碼符號
    public function post_serial_encode_sign($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_serial_encode_sign($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00026 修改編碼符號
    public function patch_serial_encode_sign($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_serial_encode_sign($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00027 刪除編碼符號
    public function delete_serial_encode_sign($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_serial_encode_sign($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00028 拿取編碼符號
    public function get_manual($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_manual($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00029 新增編碼符號
    public function post_manual($request, $response, $args)
    {
        $data = $request->getParams();
        $result = [];
        $Component = new component($this->container->db);
        $data['files'] = $request->getUploadedFiles();
        $file = $Component->uploadFile($data);
        unset($data['files']);
        $file['user_id'] = 0;

        $data['file_id'] = $Component->insertFile($file);
        if ($data['file_id'] == '' || $data['file_id'] == null) {
            $result[] = ['status' => 'failed', 'info' => '上傳檔案失敗'];
        } else {
            $result[] = ['status' => 'success', 'info' => 'insertFile'];
        }
        $last_edit_user_id = $_SESSION['id'];

        $get_manual_response = $Component->get_manual($data);
        $classify_structure_responese = [];
        if (array_key_exists('data', $get_manual_response)) {
            $classify_structure_responese = $Component->get_classify_structure_only_classify_structure($data);
            if ($classify_structure_responese === null) {
                $result[] = ['status' => 'failed', 'info' => '系統中樹狀結構無此分類'];
            } else {
                $result[] = ['status' => 'success', 'info' => 'get_classify_structure_only_classify_structure'];
                $data['classify_structure_id'] = $classify_structure_responese;
                $post_classify_structure_type_folder_responese = $Component->post_classify_structure_type_folder($data, $last_edit_user_id);
                if (array_key_exists('status', $post_classify_structure_type_folder_responese) && $post_classify_structure_type_folder_responese['status'] !== 'success') {
                    $result[] = $post_classify_structure_type_folder_responese;
                } else {
                    $result[] = $post_classify_structure_type_folder_responese;
                    $data['classify_structure_type_id'] = $post_classify_structure_type_folder_responese['classify_structure_type_id'];
                    $data['file_id'] = [$data['file_id']];
                    $post_multi_classify_structure_type_file_insert_response = $Component->post_multi_classify_structure_type_file_insert($data);
                    if (array_key_exists('status', $post_multi_classify_structure_type_file_insert_response) && $post_multi_classify_structure_type_file_insert_response['status'] !== 'success') {
                        $result[] = ['status' => 'failed', 'info' => '系統中樹狀結構綁定檔案失敗'];
                    } else {
                        $result[] = ['status' => 'success', 'info' => 'post_multi_classify_structure_type_file_insert'];
                    }
                }
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_classify_structure($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $params['only_classify_structure'] = true;
        $result = $Component->get_classify_structure_only_classify_structure($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_classify_structure($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_classify_structure($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_classify_structure($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_classify_structure($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function delete_classify_structure($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_classify_structure($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_classify_structure_type($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $params['only_classify_structure_type'] = true;
        $result = $Component->get_classify_structure_type($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_classify_structure_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_classify_structure_type($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_classify_structure_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_classify_structure_type($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function delete_classify_structure_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_classify_structure_type($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_classify_structure_type_depth($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_classify_structure_type_depth($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_classify_structure_type_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_classify_structure_type_depth($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_classify_structure_type_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_classify_structure_type_depth($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function delete_classify_structure_type_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_classify_structure_type_depth($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_classify_structure_depth($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_classify_structure_depth($params);
        $response = $response->withHeader('operation_video-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_classify_structure_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_classify_structure_depth($data,  $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_classify_structure_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_classify_structure_depth($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function delete_classify_structure_depth($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $result = $Component->delete_classify_structure_depth($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
