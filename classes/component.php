<?php

use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use function PHPSTORM_META\map;

class component extends Model
{
    protected $container;
    protected $db;
    protected $db_sqlsrv;

    // constructor receives container instance
    public function __construct()
    {
        global $container;
        $this->container = $container;
        $this->db = $container->db;
    }

    function formatToPgsqlArray($input)
    {
        // 去掉前后空格
        $input = trim($input);

        // 检查输入是否为单个整数
        if (is_numeric($input) && intval($input) == $input) {
            return '{' . intval($input) . '}';
        }

        // 检查输入是否为数组格式 [1, 36]
        if (preg_match('/^\[\s*(-?\d+\s*,\s*)*-?\d+\s*\]$/', $input)) {
            // 去除方括号和空格
            $input = trim($input, '[] ');
            // 将字符串分割为整数数组
            $elements = explode(',', $input);
            // 去掉元素中的空格
            $elements = array_map('trim', $elements);
            // 返回 PGSQL 数组格式
            return '{' . implode(',', $elements) . '}';
        }

        // 如果输入格式不匹配，返回 null 或报错信息
        return null;
    }

    public function get_protable_use_demo_data($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "permission_id" => 0,
            "permission_name" => "",
        ];
        $custom_filter_bind_values = [
            "permission_id" => 0,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "permission_id" => " AND permission_id = :permission_id",
            "permission_name" => " AND permission_name LIKE '%' || :permission_name || '%' ",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY permission_id) \"key\"
                FROM(
                    SELECT permission.permission_id, permission.permission_name, permission.permission_url, permission.permission_icon,
                        permission.permission_group_id, permission.permission_index,
                        permission_group.permission_group_name
                    FROM system.permission
                    LEFT JOIN system.permission_group ON permission_group.permission_group_id = permission.permission_group_id
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function get_protable_use_demo_permission_group_id_data($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "permission_group_id" => 0,
            "permission_group_name" => "",
        ];
        $custom_filter_bind_values = [
            "permission_group_id" => 0,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "permission_group_id" => " AND permission_group_id = :permission_group_id",
            "permission_group_name" => " AND permission_group_name LIKE '%' || :permission_group_name || '%' ",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY permission_group_id) \"key\"
                FROM(
                    SELECT permission_group.permission_group_id, permission_group.permission_group_name
                    FROM system.permission_group
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function get_classify_structure($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "classify_structure_id" => 0,
        ];
        $custom_filter_bind_values = [
            "key" => 0,
            "title" => "",
            "children" => "",
            "classify_structure_id" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values_default[$key] = $params[$key];
            }
        }

        $condition = "";
        $condition_values = [
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
        ];
        $select_condition = "";

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $values = $custom_filter_return['bind_values'];

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values_count = $values;
        $values["start"] = $start;
        $values["length"] = $length;

        //預設排序
        $order = '';

        if (array_key_exists('order', $params)) {
            $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'employment_time_start':
                        $order .= " to_char(employment_time_start, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    case 'employment_time_end':
                        $order .= " to_char(employment_time_end, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *
                FROM(
                    SELECT '[' || classify_structure.classify_structure_type.classify_structure_type_id || ']' AS key, 
                    classify_structure.classify_structure_type.name  AS title,
                    COALESCE(subtype.children, '[]') AS children,
                    classify_structure.classify_structure_type.classify_structure_id
                    FROM classify_structure.classify_structure_type
                    INNER JOIN (
                        SELECT classify_structure.classify_structure_type.classify_structure_type_parent_id, 
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'key', '[' || classify_structure.classify_structure_type.classify_structure_type_parent_id ||
                                    ',' || classify_structure.classify_structure_type.classify_structure_type_id ||']',
                                    'title', classify_structure.classify_structure_type.name
                                )
                            )  children
                        FROM classify_structure.classify_structure_type
                        WHERE classify_structure.classify_structure_type.classify_structure_type_parent_id != 0
                        GROUP BY classify_structure.classify_structure_type.classify_structure_type_parent_id
                    )subtype ON subtype.classify_structure_type_parent_id = classify_structure.classify_structure_type.classify_structure_type_id
                    WHERE classify_structure.classify_structure_type.classify_structure_type_parent_id = 0
                )dt
                WHERE TRUE {$condition} {$select_condition}
                {$order}
        ";

        $sql = "SELECT *
        FROM(
            {$sql_default}
            LIMIT :length
        )dt
        WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
        FROM(
            {$sql_default}
        )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function get_classify_structure_only_classify_structure($params)
    {
        $only_classify_structure = true;
        if (isset($params['only_classify_structure'])) {
            $only_classify_structure = false;
        }
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "classify_structure_id" => null,
            "name" => null,
        ];
        $custom_filter_bind_values = [
            "classify_structure_id" => null,
            "name" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
            "name" => " AND \"name\" = :name",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = "";

        if (array_key_exists('order', $params)) {
            $order = "ORDER BY ";
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT classify_structure.classify_structure.classify_structure_id,
                        classify_structure.classify_structure.\"name\"
                        {$customize_select}
                    FROM classify_structure.classify_structure
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
                {$order}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result['data']) === 0) {
                $result = null;
                return $result;
            }
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            if ($only_classify_structure) {
                foreach ($result['data'] as $row_index => $row_data) {
                    $classify_structure_id = $row_data['classify_structure_id'];
                }
                $result = $classify_structure_id;
            }
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function get_language_manage($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "language_manage_id" => null,
        ];
        $custom_filter_bind_values = [
            "language_manage_id" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "language_manage_id" => " AND language_manage_id = :language_manage_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY language_manage_id) \"key\"
                FROM(
                    SELECT language.\"language_manage\".language_manage_id, language.\"language_manage\".language_culture_id,  
                    language.language_manage.schema_name, language.language_manage.table_name, 
                    language.language_manage.column_name, language.language_manage.table_primary_id, 
                    language.language_manage.language_value
                    FROM language.\"language_manage\"
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_language_manage($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $language_manage_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "language_culture_id" => "",
                "schema_name" => "",
                "table_name" => "",
                "column_name" => "",
                "table_primary_id" => "",
                "language_value" => "",
                "language_culture_code" => "",
            ];

            $language_manage_insert_cond = "";
            $language_manage_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($language_manage_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'language_culture_code') {
                        $language_manage_bind_values[$key] = $column[$key];
                        $language_manage_insert_cond .= "language_culture_id,";
                        $language_manage_values_cond .= "(
                            SELECT language.language_culture.language_culture_id
                            FROM language.language_culture
                            WHERE language.language_culture.language_culture_code = :{$key}
                        ),";
                    } else {
                        $language_manage_bind_values[$key] = $column[$key];
                        $language_manage_insert_cond .= "{$key},";
                        $language_manage_values_cond .= ":{$key},";
                    }
                } else {
                    unset($language_manage_bind_values[$key]);
                }
            }

            $language_manage_insert_cond = rtrim($language_manage_insert_cond, ',');
            $language_manage_values_cond = rtrim($language_manage_values_cond, ',');

            $sql_insert = "INSERT INTO language.language_manage({$language_manage_insert_cond})
                VALUES ({$language_manage_values_cond})
                ON CONFLICT(schema_name, table_name, column_name, table_primary_id, language_culture_id)
                DO UPDATE
                SET language_value = EXCLUDED.language_value
                RETURNING language_manage_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($language_manage_bind_values)) {
                $language_manage_id = $stmt_insert->fetchColumn(0);
                $result = ["status" => "success", "language_manage_id" => $language_manage_id];
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "language_manage_id" => $language_manage_id];
        }
        return $result;
    }

    public function patch_language_manage($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $language_manage_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => null,
                "language_manage_id" => null,
                "schema_name" => "",
                "table_name" => "",
                "column_name" => "",
                "table_primary_id" => "",
                "language_value" => "",
            ];

            $language_manage_upadte_cond = "";
            $language_manage_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($language_manage_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'language_manage_id') {
                        $language_manage_bind_values[$key] = $column[$key];
                    } else {
                        $language_manage_bind_values[$key] = $column[$key];
                        $language_manage_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($language_manage_bind_values[$key]);
                }
            }

            $language_manage_fliter_cond .= "AND language.language_manage.language_manage_id = :language_manage_id";
            $language_manage_upadte_cond = rtrim($language_manage_upadte_cond, ',');

            $sql = "UPDATE language.language_manage
                    SET {$language_manage_upadte_cond}
                    WHERE TRUE {$language_manage_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($language_manage_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_language_manage($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_language_manage_bind_values = [
                "language_manage_id" => "",
            ];

            foreach ($delete_language_manage_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_language_manage_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM language.language_manage
                WHERE language.language_manage.language_manage_id = :language_manage_id
            ";
            $stmt_delete_language_manage = $this->db->prepare($sql_delete);
            if ($stmt_delete_language_manage->execute($delete_language_manage_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    //拿資料夾順序
    public function get_folder_index($params)
    {
        $bind_values = [
            "classify_structure_type_id" => null,
            "classify_structure_id" => null,
        ];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }
        $condition = "";
        $condition_values = [
            "classify_structure_type_id" => " AND classify_structure_type_parent_id::text = :classify_structure_type_id::text",
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $sql = "SELECT folder_index.index+1 AS index
                               FROM (
                                   (SELECT 0 AS index)
                                       UNION ALL 
                                   (SELECT classify_structure.classify_structure_type.index
                                   FROM classify_structure.classify_structure_type
                                   WHERE TRUE {$condition}
                                   ORDER BY classify_structure.classify_structure_type.index DESC LIMIT 1)
                               ) folder_index
                                       ORDER BY folder_index.index DESC LIMIT 1
               ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($bind_values)) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result[$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function getExcel($data, $break = false)
    {
        $response = $data['response'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowArray = [];

        $row_count = 1;
        foreach ($data['data'] as $index => $row) {
            if ($index === 0) {
                $rowArray[] = array_keys($row);
            }
            array_push($rowArray, array_values($row));
            $row_count++;
        }

        $spreadsheet->getActiveSheet()
            ->fromArray(
                $rowArray,
                // The data to set
                NULL,
                // Array values with this value will not be set
                'A1' // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
            );
        $spreadsheet->getActiveSheet()->getStyle("A1:S{$row_count}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        if ($break) {
            $spreadsheet->getActiveSheet()->getStyle("E2:S{$row_count}")->getAlignment()->setWrapText(true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', "attachment; filename={$data['name']}報表.xlsx");
        return $response;
    }

    //拿資料夾所有檔案
    public function get_classify_structure_type_file($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "classify_structure_type_id" => null,
            "blog_id" => null
        ];

        $customize_select = "";
        $customize_table = "";
        $customize_group = "";


        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        //預設排序
        // 先排序置頂再排序blog 順序
        $order = "ORDER BY classify_structure_type_id";

        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= ", {$column_data['column']} {$sort_type}";
                }
            }
            // $order = rtrim($order, ',');
        }
        $condition = "";
        $condition_values = [
            "classify_structure_type_id" => " AND classify_structure_type_id = :classify_structure_type_id",
            "blog_id" => " AND blog_id = :blog_id",
            "upload_time_start" => " AND (EXTRACT(DAY FROM upload_time_start::timestamp - :upload_time_start::timestamp) >= 0 AND upload_time_start::timestamp IS NOT NULL)",
            "upload_time_end" => " AND (EXTRACT(DAY FROM upload_time_end::timestamp - :upload_time_end::timestamp) <= 0 AND upload_time_end::timestamp IS NOT NULL)",
            "file_client_name" => " AND file_client_name = :file_client_name"

        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
            $select_condition = " AND (";
            foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
                $select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
            }
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        // $order = "ORDER BY to_char(annoucement_time::timestamp, 'yyyy-MM-dd') DESC";
        // $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY blog_id) \"key\"
        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                    FROM(
                        SELECT classify_structure.classify_structure_type_file.classify_structure_type_id,
                            classify_structure.classify_structure_type_file.file_id,
                            classify_structure.file.file_client_name AS file_client_name,  
                            to_char(classify_structure.file.upload_time, 'YYYY-MM-DD') AS upload_time, 
                            system.\"user\".name AS upload_user_name,
                            to_char(classify_structure.file.upload_time, 'YYYY-MM-DD') AS last_edit_time,
                            last_edit_user.name AS last_edit_user_name
                            {$customize_select}
                        FROM classify_structure.classify_structure_type_file
                        LEFT JOIN classify_structure.file ON classify_structure.classify_structure_type_file.file_id = classify_structure.file.id
                        LEFT JOIN system.\"user\" ON classify_structure.file.user_id = system.\"user\".id
                        LEFT JOIN system.\"user\" AS last_edit_user ON classify_structure.file.last_edit_user_id = last_edit_user.id
                        {$customize_table}
                    )dt
                    WHERE TRUE {$condition} {$select_condition}  
                    {$order}
            ";
        $sql = "SELECT *
                FROM(
                    {$sql_default}
                    LIMIT :length
                )dt
                WHERE \"key\" > :start
            ";

        $sql_count = "SELECT COUNT(*)
                FROM(
                    {$sql_default}
                )sql_default
            ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }
    //拿資料夾
    public function get_classify_structure_type_folder($params, $for_relation_in = [], $for_relation_select = "", $for_relation_from = "", $for_relation_order = "")
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "id" => null,
            "classify_structure_id" => null,
            "classify_structure_type_id" => null,
        ];
        $custom_filter_bind_values = [
            "classify_structure_type_id" => null,
            "classify_structure_type_parent_id" => null,
            "name" => null,
            "index" => null,
            "background_color" => null,
            "font_color" => null,
        ];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $select_condition = "";

        //預設排序
        // 先排序置頂再排序blog 順序
        $order = $for_relation_order;

        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= ", {$column_data['column']} {$sort_type}";
                }
            }
            // $order = rtrim($order, ',');
        }
        $condition = "";
        $condition_values = [
            "id" => " AND classify_structure_type_id = :id",
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
            "classify_structure_type_id" => " AND classify_structure_type_parent_id = :classify_structure_type_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        if (count($for_relation_in) != 0) {
            $condition .= " AND classify_structure_type_id IN (";
            foreach ($for_relation_in as $key => $value) {
                if (!is_null($value)) {
                    $condition .= " {$value},";
                }
            }
            $condition = rtrim($condition, ',');
            $condition .= ")";
        }
        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;
        $sql_rutine = " SELECT classify_structure.classify_structure_type.classify_structure_type_id, 
                          classify_structure.classify_structure_type.classify_structure_type_id \"value\", 
                          classify_structure.classify_structure_type.classify_structure_type_parent_id, 
                          classify_structure.classify_structure_type.classify_structure_id, 
                          classify_structure.classify_structure_type.name,
                          classify_structure.classify_structure_type.\"name\" title,
                          classify_structure.classify_structure_type.index,
                          classify_structure.classify_structure_type.background_color,
                          classify_structure.classify_structure_type.font_color,
                          COALESCE(blog_data.blog_data,'[]')blog_data,
                          COALESCE(children.children,'[]')children,
                          COALESCE(classify_structure_type_file_data.classify_structure_type_file_data,'[]')classify_structure_type_file_data
                          {$for_relation_select}
                          FROM classify_structure.classify_structure_type
                            LEFT JOIN (
                                SELECT classify_structure.classify_structure_type_file.classify_structure_type_id,
                                    JSON_AGG(
                                        JSON_BUILD_OBJECT(
                                            'classify_structure_type_file_id',classify_structure.classify_structure_type_file.classify_structure_type_file_id,
                                            'file_id',classify_structure.classify_structure_type_file.file_id
                                        )
                                    )classify_structure_type_file_data
                                FROM classify_structure.classify_structure_type_file
                                GROUP BY classify_structure.classify_structure_type_file.classify_structure_type_id
                            ) classify_structure_type_file_data ON classify_structure.classify_structure_type.classify_structure_type_id = classify_structure_type_file_data.classify_structure_type_id
                          LEFT JOIN (
                              SELECT classify_structure.classify_structure_type.classify_structure_type_id, 
                              JSON_AGG(
                                  JSON_BUILD_OBJECT(
                                      'blog_id', classify_structure.blog.id, 
                                      'title', classify_structure.blog.title, 
                                      'content', classify_structure.blog.content, 
                                      'blog_type_id', classify_structure.blog.blog_type_id, 
                                      'annoucement_time', to_char(classify_structure.blog.annoucement_time, 'YYYY-MM-DD'), 
                                      'top', classify_structure.blog.\"top\", 
                                      'blog_index', classify_structure.blog.blog_index, 
                                      'more_content', classify_structure.blog.more_content, 
                                      'background_color', classify_structure.blog.background_color, 
                                      'font_color', classify_structure.blog.font_color, 
                                      'pic_background_color', classify_structure.blog.pic_background_color 
                                  )
                              )blog_data
                              FROM classify_structure.classify_structure_type
                              LEFT JOIN classify_structure.classify_structure_type_blog ON classify_structure.classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type_blog.classify_structure_type_id
                              LEFT JOIN classify_structure.blog ON classify_structure.classify_structure_type_blog.blog_id = classify_structure.blog.id
                              GROUP BY classify_structure.classify_structure_type.classify_structure_type_id
                          )blog_data ON classify_structure.classify_structure_type.classify_structure_type_id = blog_data.classify_structure_type_id
                          LEFT JOIN (
                              SELECT classify_structure.classify_structure_type.classify_structure_type_id parent_classify_structure_type_id,
                              JSON_AGG(
                                  JSON_BUILD_OBJECT(
                                      'classify_structure_type_id', classify_structure_type_children.classify_structure_type_id,
                                      'name', classify_structure_type_children.name
                                  )
                                  ORDER BY classify_structure_type_children.index ASC,classify_structure_type_children.classify_structure_type_id ASC
                              )children
                              FROM classify_structure.classify_structure_type classify_structure_type_children
                              INNER JOIN classify_structure.classify_structure_type ON classify_structure_type_children.classify_structure_type_parent_id = classify_structure.classify_structure_type.classify_structure_type_id
                              WHERE classify_structure.classify_structure_type.classify_structure_type_id IS NOT NULL
                              GROUP BY classify_structure.classify_structure_type.classify_structure_type_id
                          )children ON classify_structure.classify_structure_type.classify_structure_type_id = children.parent_classify_structure_type_id
                          {$for_relation_from}
          ";

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                   FROM(
                       {$sql_rutine}
                       -- WHERE classify_structure.classify_structure_type.classify_structure_type_parent_id=classify_structure.classify_structure_type_id
                   )dt
                   WHERE TRUE {$condition} {$select_condition}  
                   {$order}
           ";
        $sql = "SELECT *
               FROM(
                   {$sql_default}
                   LIMIT :length
               )dt
               WHERE \"key\" > :start
           ";

        $sql_count = "SELECT COUNT(*)
               FROM(
                   {$sql_default}
               )sql_default
           ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            $key_array = [
                "children" => "{$sql_rutine}"
            ];
            $valid_array = [
                "classify_structure_type_id" => "WHERE classify_structure.classify_structure_type.classify_structure_type_id= :classify_structure_type_id"
            ];
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['data'] = $this->rutine(["data" => json_encode($result['data']), "key" => $key_array, 'valid' => $valid_array])['data'];
            $result['total'] = $result_count;
            return $result;
        } else {        
            return ["status" => "failed"];
        }
    }
    //新增資料夾
    public function post_classify_structure_type_folder($data, $last_edit_user_id)
    {
        $folder_values = [
            "name" => "",
            "classify_structure_type_id" => "",
            "classify_structure_id" => "",
            "index" => "",
            "background_color" => '',
            "font_color" => '',
            "upload_user_id" => "",
            "upload_time" => "",
            "last_edit_user_id" => "",
            "last_edit_time" => ""
        ];

        $folder_insert_cond = "";
        $folder_values_cond = "";
        $data['upload_user_id'] = $last_edit_user_id;
        $data['upload_time'] = "NOW()";
        $data['last_edit_user_id'] = $last_edit_user_id;
        $data['last_edit_time'] = "NOW()";

        foreach ($folder_values as $key => $value) {
            if ($key == "index") {
                $folder_bind_values[$key] = $this->get_folder_index(["classify_structure_type_id" => $data['classify_structure_type_id']])[0]['index'];
                $data[$key] = $folder_bind_values[$key];
            }
            if (array_key_exists($key, $data)) {
                if ($key == 'classify_structure_type_id') {
                    $folder_bind_values['classify_structure_type_parent_id'] = $data[$key];
                    $folder_insert_cond .= "classify_structure_type_parent_id,";
                    $folder_values_cond .= ":classify_structure_type_parent_id,";
                } else {
                    $folder_bind_values[$key] = $data[$key];
                    $folder_insert_cond .= "{$key},";
                    $folder_values_cond .= ":{$key},";
                }
            } else {
                unset($folder_bind_values[$key]);
            }
        }

        $folder_insert_cond = rtrim($folder_insert_cond, ',');
        $folder_values_cond = rtrim($folder_values_cond, ',');

        $sql_insert = "INSERT INTO classify_structure.classify_structure_type({$folder_insert_cond})
                  VALUES ({$folder_values_cond})
                  RETURNING classify_structure.classify_structure_type.classify_structure_type_id
               ";
        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($folder_bind_values)) {
            $classify_structure_type_id = $stmt_insert->fetchColumn(0);
            $data['classify_structure_type_id'] = $classify_structure_type_id;

            $result = [
                "status" => "success",
                "classify_structure_type_id" => $classify_structure_type_id
            ];
        } else {
            $result = ["status" => "failure"];
        }

        return $result;
    }
    //修改資料夾名稱、分類
    public function patch_classify_structure_type_folder($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $folder_bind_values = [
                "classify_structure_type_id" => "",
                "classify_structure_type_parent_id" => "",
                "name" => "",
                "background_color" => '',
                "font_color" => '',
                "index" => "",
                "last_edit_user_id" => "",
                "last_edit_time" => ""
            ];

            $folder_upadte_cond = "";
            $folder_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";
            $column['index'] = null;

            foreach ($folder_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'classify_structure_type_id') {
                        $folder_bind_values[$key] = $column[$key];
                    } else if ($key == "index") {
                        $folder_bind_values[$key] = $this->get_folder_index(["classify_structure_type_id" => $column['classify_structure_type_parent_id']])[0]['index'];
                        $folder_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $folder_bind_values[$key] = $column[$key];
                        $folder_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($folder_bind_values[$key]);
                }
            }

            $folder_fliter_cond .= "AND classify_structure.classify_structure_type.classify_structure_type_id = :classify_structure_type_id";
            $folder_upadte_cond = rtrim($folder_upadte_cond, ',');

            $sql = "UPDATE classify_structure.classify_structure_type
                       SET {$folder_upadte_cond}
                       WHERE TRUE {$folder_fliter_cond}
               ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($folder_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    //刪除資料夾名稱、分類
    public function delete_classify_structure_type_folder($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_classify_structure_type_folder_bind_values = [
                "classify_structure_type_id" => "",
            ];

            foreach ($delete_classify_structure_type_folder_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_classify_structure_type_folder_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM classify_structure.classify_structure_type
                   WHERE classify_structure.classify_structure_type.classify_structure_type_id = :classify_structure_type_id
               ";
            $stmt_delete_classify_structure_type_folder = $this->db->prepare($sql_delete);
            if ($stmt_delete_classify_structure_type_folder->execute($delete_classify_structure_type_folder_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function get_customer($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "customer_id" => null,
            "name" => null,
            "birthday" => null,
            "city" => null,
            "cellphone" => null,
        ];
        $custom_filter_bind_values = [
            "customer_id" => null,
            "name" => null,
            "birthday" => null,
            "city" => null,
            "cellphone" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "customer_id" => " AND customer_id = :customer_id",
            "name" => " AND name = :name",
            "birthday" => " AND birthday = :birthday",
            "city" => " AND city = :city",
            "cellphone" => " AND cellphone = :cellphone",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        $sql_default = "WITH revenue(name,revenue_amount,revenue_count)  AS (
            VALUES
            ('0x8E0A7AF39B633D5EA25C3B7EF4DFC5464B36DB7AF375716EB065E29697CC071E',371.00,8)
            ,('0x21EDE41906B45079E75385B5AA33287CA09DE1AB86DE66EF88352FD1BE8DE368',280.00,10)
            ,('0x31C5E4B74E23231295FDB724AD578C02C4A723F4BA2B4AF99F129EC2F4B3AD41',0.00,0)
            ,('0xFF534C83C0EF23D1CE516BC80A65D0197003D27937D485BC549171D52CE13CEA',240.00,10)
            ,('0x9C1DEF02C9BE242842C1C1ABF2C5AA249A1EEB4763B47FF457133EE9199F1037',0.00,0)
            ,('0x6E70C1504EB27252542F58E4D3C8C83516E093334721A3CE1DD194FE3F98DA0F',230.00,4)
            ,('0x1DD6DA89DEECA1841ABD572562982EE905566F4469ACB5B44FD49001BCF570ED',0.00,0)
            ,('0x5A3A2D6A659769FCA243FC2A97644D27A75FB9AA4DF38D55145E5BEBDB4F06AA',535.00,10)
            ,('0xD9D899DA4FB0CF23FDF902C1B237A30AE854FFBC79FC67092F2E6358FF5E9308',0.00,0)
            ,('0x801B08C30D1A38E502BFC39A7914A2FF786C353FE409DE3524585487FAB951F9',174.00,6)
            ,('0xB31F0752F425226E63738C33820CAE6F96D8028DB42B6B10F50B9CE41BAE055C',0.00,0)
            ,('0x312290CADA8D18E936FF643027ECD204751A13FB4683AD638CF005B3BB22A11D',292.00,10)
            ,('0x58A3F88CAD27FB414123EF8C7A04C72331462E559ADB67992A01099591D63FDB',0.00,0)
            ,('0x3C34C1FBF79466EBBC6F9881C7BC13F6BC997498E92594C5131A0DB9CFFD38A4',327.70,8)
            ,('0x5EC9E6EC77BCC5A550B7F67C21AEB9DD3F6AD5FB19EDE0FEE08C8F2A01D94309',0.00,0)
            ,('0x4B923E5254103C807392248E01F962767674B575B2D41479A445DD0780FA8407',437.00,6)
            ,('0xFC5C271D266C0B100D0B451A22D71B57576F50EC68D0D9B07259179E620FD1E4',455.00,3)
            ,('0xAF918A41B6C8CE4B4E44EC163BAF9DBCFD7923D7911CA6FDF3EB74B3CE31BCE9',0.00,0)
            ,('0xB4C7C37787665CC1D66C6AD00A6F1C12EA34267D1B92A420FA6F3966329BC10B',174.00,8)
        )
        "
            . "SELECT *, ROW_NUMBER() OVER (ORDER BY customer_id) \"key\"
                FROM(
                    SELECT code, user_id, customer.name, customer_id, EXTRACT(year FROM (AGE(NOW(),birthday))) age, city, cellphone,revenue_amount,revenue_count
                    FROM crm.customer
                    LEFT JOIN revenue ON revenue.name = customer.name
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_customer($data, $last_edit_user_id)
    {
        // foreach ($data as $row => $column) {
        //     $language_manage_bind_values = [
        //         "last_edit_user_id" => 0,
        //         "last_edit_time" => "",
        //         "language_culture_id" => "",
        //         "schema_name" => "",
        //         "table_name" => "",
        //         "column_name" => "",
        //         "table_primary_id" => "",
        //         "language_value" => "",
        //         "language_culture_code" => "",
        //     ];

        //     $language_manage_insert_cond = "";
        //     $language_manage_values_cond = "";
        //     $column['last_edit_user_id'] = $last_edit_user_id;
        //     $column['last_edit_time'] = 'NOW()';

        //     foreach ($language_manage_bind_values as $key => $value) {
        //         if (array_key_exists($key, $column)) {
        //             if ($key == 'language_culture_code') {
        //                 $language_manage_bind_values[$key] = $column[$key];
        //                 $language_manage_insert_cond .= "language_culture_id,";
        //                 $language_manage_values_cond .= "(
        //                     SELECT language.language_culture.language_culture_id
        //                     FROM language.language_culture
        //                     WHERE language.language_culture.language_culture_code = :{$key}
        //                 ),";
        //             } else {
        //                 $language_manage_bind_values[$key] = $column[$key];
        //                 $language_manage_insert_cond .= "{$key},";
        //                 $language_manage_values_cond .= ":{$key},";
        //             }
        //         } else {
        //             unset($language_manage_bind_values[$key]);
        //         }
        //     }

        //     $language_manage_insert_cond = rtrim($language_manage_insert_cond, ',');
        //     $language_manage_values_cond = rtrim($language_manage_values_cond, ',');

        //     $sql_insert = "INSERT INTO language.language_manage({$language_manage_insert_cond})
        //         VALUES ({$language_manage_values_cond})
        //         RETURNING language_manage_id
        //     ";

        //     $stmt_insert = $this->db->prepare($sql_insert);

        //     if ($stmt_insert->execute($language_manage_bind_values)) {
        //         $language_manage_id = $stmt_insert->fetchColumn(0);
        //     } else {
        //         return ['status' => 'failure'];
        //     }

        //     $result = ["status" => "success", "language_manage_id" => $language_manage_id];
        // }
        // return $result;
    }

    public function patch_customer($data, $last_edit_user_id)
    {
        // foreach ($data as $row => $column) {
        //     $language_manage_bind_values = [
        //         "last_edit_user_id" => 0,
        //         "last_edit_time" => null,
        //         "language_manage_id" => null,
        //         "schema_name" => "",
        //         "table_name" => "",
        //         "column_name" => "",
        //         "table_primary_id" => "",
        //         "language_value" => "",
        //     ];

        //     $language_manage_upadte_cond = "";
        //     $language_manage_fliter_cond = "";
        //     $column['last_edit_user_id'] = $last_edit_user_id;
        //     $column['last_edit_time'] = "NOW()";

        //     foreach ($language_manage_bind_values as $key => $value) {
        //         if (array_key_exists($key, $column)) {
        //             if ($key == 'language_manage_id') {
        //                 $language_manage_bind_values[$key] = $column[$key];
        //             } else {
        //                 $language_manage_bind_values[$key] = $column[$key];
        //                 $language_manage_upadte_cond .= "{$key} = :{$key},";
        //             }
        //         } else {
        //             unset($language_manage_bind_values[$key]);
        //         }
        //     }

        //     $language_manage_fliter_cond .= "AND language.language_manage.language_manage_id = :language_manage_id";
        //     $language_manage_upadte_cond = rtrim($language_manage_upadte_cond, ',');

        //     $sql = "UPDATE language.language_manage
        //             SET {$language_manage_upadte_cond}
        //             WHERE TRUE {$language_manage_fliter_cond}
        //     ";

        //     $stmt = $this->db->prepare($sql);
        //     if ($stmt->execute($language_manage_bind_values)) {
        //         $result = ["status" => "success"];
        //     } else {
        //         $result = ['status' => 'failure'];
        //     }
        // }
        // return $result;
    }

    public function delete_customer($data)
    {
        // foreach ($data as $row => $delete_data) {
        //     $delete_language_manage_bind_values = [
        //         "language_manage_id" => "",
        //     ];

        //     foreach ($delete_language_manage_bind_values as $key => $value) {
        //         if (array_key_exists($key, $delete_data)) {
        //             $delete_language_manage_bind_values[$key] = $delete_data[$key];
        //         }
        //     }

        //     $sql_delete = "DELETE FROM language.language_manage
        //         WHERE language.language_manage.language_manage_id = :language_manage_id
        //     ";
        //     $stmt_delete_language_manage = $this->db->prepare($sql_delete);
        //     if ($stmt_delete_language_manage->execute($delete_language_manage_bind_values)) {
        //         $result = ["status" => "success"];
        //     } else {
        //         $result = ['status' => 'failure'];
        //     }
        // }
        // return $result;
    }

    public function custom_filter_function($data, $select_condition, $default_arr, $custom_filter_arr)
    {
        if (array_key_exists('custom_filter_key', $data) && array_key_exists('custom_filter_value', $data) && count($data['custom_filter_key']) != 0) {
            $select_condition .= " AND (";
            foreach ($data['custom_filter_key'] as $custom_filter_key) {
                if (array_key_exists($custom_filter_key, $custom_filter_arr)) {
                    $select_condition .= " {$custom_filter_key}::text LIKE '%' || :{$custom_filter_key} || '%' OR";
                    $custom_filter_arr[$custom_filter_key] = $data['custom_filter_value'];
                }
            }
            foreach ($custom_filter_arr as $key => $value) {
                if ($value == null) {
                    unset($custom_filter_arr[$key]);
                }
            }
            $default_arr = array_merge($default_arr, $custom_filter_arr);
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }
        return ["select_condition" => $select_condition, "bind_values" => $default_arr];
    }

    public function rutine($data, $depth = 1)
    {
        $decodedJson = json_decode($data['data'], true);
        $key_array = $data['key'];
        $valid_array = $data['valid'];
        foreach ($decodedJson as $array => $array_value) {
            if ($depth == 1) {
                $decodedJson[$array]['depth'] = $depth;
                $depth += 1;
            } else {
                $depth = 1;
            }
            foreach ($array_value as $key => $value) {
                if (array_key_exists($key, $key_array)) {
                    foreach ($value as $children_id => $children_value) {
                        foreach ($valid_array as $valid_key => $valid_value) {
                            if (array_key_exists($valid_key, $children_value)) {
                                $sql_data[$valid_key] = $children_value[$valid_key];
                                $sql_select = $key_array[$key];
                                $sql_select .= $valid_value;
                                $stmt_select = $this->db->prepare($sql_select);
                                $stmt_select->execute($sql_data);
                                $sql_return = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($sql_return as $row_id => $row_value) {
                                    foreach ($row_value as $row_value_key => $row_value_value) {
                                        if ($this->isJson($row_value_value)) {
                                            $sql_return[$row_id][$row_value_key] = json_decode($row_value_value, true);
                                        }
                                    }
                                }
                                $decodedJson[$array][$key][$children_id] = $sql_return[0];
                                $decodedJson[$array][$key][$children_id]['depth'] = $depth;
                                if (isset($sql_return[0][$key])) {
                                    if (count($sql_return[0][$key]) != 0) {
                                        $return_value = $this->rutine(["data" => json_encode($sql_return), "key" => $key_array, "valid" => $valid_array], $depth)['data'];
                                        $decodedJson[$array][$key][$children_id][$key] = $return_value[0][$key];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return ["data" => $decodedJson, "key" => $key_array, "valid" => $valid_array];
    }

    public function isJson($string)
    {
        json_decode($string);
        return json_decode($string) !== false && json_last_error() === JSON_ERROR_NONE;
    }

    public function initialize_search()
    {
        $default_value = [
            "cur_page" => 1,
            "size" => 100000,
        ];
        return $default_value;
    }

    public function print_qrcode_pdf($data)
    {
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator('');
        $pdf->SetAuthor('');
        $pdf->SetTitle('QRcode PDF');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 17);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // set style for barcode
        $style = array(
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $position_point = [
            ["qrcode" => ["x" => 10, "y" => 30], "text" => ["x" => 62, "y" => 30]],
            ["qrcode" => ["x" => 110, "y" => 30], "text" => ["x" => 162, "y" => 30]],
            ["qrcode" => ["x" => 10, "y" => 90], "text" => ["x" => 62, "y" => 90]],
            ["qrcode" => ["x" => 110, "y" => 90], "text" => ["x" => 162, "y" => 90]],
            ["qrcode" => ["x" => 10, "y" => 150], "text" => ["x" => 62, "y" => 150]],
            ["qrcode" => ["x" => 110, "y" => 150], "text" => ["x" => 162, "y" => 150]],
            ["qrcode" => ["x" => 10, "y" => 210], "text" => ["x" => 62, "y" => 210]],
            ["qrcode" => ["x" => 110, "y" => 210], "text" => ["x" => 162, "y" => 210]]
        ];

        foreach ($data as $key => $value) {
            if ($key % 8 == 0) {
                // add a page
                $pdf->AddPage();
            }

            $pdf->write2DBarcode($value["qrcode"], 'QRCODE,L', $position_point[$key % 8]['qrcode']['x'], $position_point[$key % 8]['qrcode']['y'], 50, 50, $style, 'N');
            for ($count = 1; $count <= 5; $count++) {
                if (array_key_exists("line{$count}", $value)) {
                    $pdf->Text($position_point[$key % 8]['text']['x'], $position_point[$key % 8]['text']['y'] + 10 * ($count - 1), $value["line{$count}"]);
                }
            }
        }
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('QRcode.pdf', 'D');
    }

    public function get_manual($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "name" => null,
        ];
        $custom_filter_bind_values = [];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "name" => " AND \"name\" = :name",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = '';

        if (array_key_exists('order', $params)) {
            $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_mission_task_group = 'ASC';
                if ($column_data['mission_task_group'] != 'ascend') {
                    $sort_mission_task_group = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'employment_time_start':
                        $order .= " to_char(employment_time_start, 'yyyy-MM-dd') {$sort_mission_task_group},";
                        break;
                    case 'employment_time_end':
                        $order .= " to_char(employment_time_end, 'yyyy-MM-dd') {$sort_mission_task_group},";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_mission_task_group},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT classify_structure.\"file\".\"id\" file_id,
                    classify_structure.\"file\".file_name, classify_structure.\"file\".file_client_name
                    {$customize_select}
                    FROM (
                        SELECT classify_structure.classify_structure.classify_structure_id
                        FROM classify_structure.classify_structure
                        WHERE TRUE {$condition} {$select_condition}
                    )classify_structure
                    LEFT JOIN classify_structure.classify_structure_type ON classify_structure.classify_structure_id = classify_structure.classify_structure_type.classify_structure_id
                    LEFT JOIN classify_structure.classify_structure_type_file ON  classify_structure.classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type_file.classify_structure_type_id
                    LEFT JOIN classify_structure.\"file\" ON classify_structure.classify_structure_type_file.file_id = classify_structure.\"file\".\"id\"
                    {$customize_table}
                    ORDER BY classify_structure.\"file\".\"id\" DESC
                    LIMIT 1
                )dt
                WHERE file_id IS NOT NULL
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function get_file_name($params)
    {
        $bind_values = [
            'file_id' => null
        ];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            }
        }

        $sql = "SELECT classify_structure.file.file_name, classify_structure.file.file_client_name
                FROM classify_structure.file
                WHERE classify_structure.file.id = :file_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function uploadFile($data)
    {
        $uploadedFiles = $data['files'];
        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($this->container->upload_directory, $uploadedFile);
            $result = array(
                'status' => 'success',
                'file_name' => $filename,
                'file_client_name' => $uploadedFile->getClientFilename()
            );
        } else {
            $result = array(
                'status' => 'failed'
            );
        }
        return $result;
    }

    private function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function insertFile($data)
    {
        $sql = "INSERT INTO classify_structure.file(
            user_id, file_name, file_client_name)
            VALUES (:user_id, :file_name, :file_client_name);
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':file_name', $data['file_name'], PDO::PARAM_STR);
        $stmt->bindParam(':file_client_name', $data['file_client_name'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    //上傳classify_structure_type檔案
    public function post_multi_classify_structure_type_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $classify_structure_type_file_insert_cond = "";
            $classify_structure_type_file_values_cond = "";

            $per_classify_structure_type_file_bind_values = [
                "classify_structure_type_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if (array_key_exists($key, $per_classify_structure_type_file_bind_values)) {
                    if ($key == 'file_id') {
                        $per_classify_structure_type_file_bind_values[$key] = $per_file_id;
                        $classify_structure_type_file_insert_cond .= "{$key},";
                        $classify_structure_type_file_values_cond .= ":{$key},";
                    } else {
                        $per_classify_structure_type_file_bind_values[$key] = $datas[$key];
                        $classify_structure_type_file_insert_cond .= "{$key},";
                        $classify_structure_type_file_values_cond .= ":{$key},";
                    }
                }
            }
            $classify_structure_type_file_insert_cond = rtrim($classify_structure_type_file_insert_cond, ',');
            $classify_structure_type_file_values_cond = rtrim($classify_structure_type_file_values_cond, ',');

            $sql = "INSERT INTO classify_structure.classify_structure_type_file({$classify_structure_type_file_insert_cond})
                VALUES ({$classify_structure_type_file_values_cond})
                RETURNING classify_structure_type_file_id
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($per_classify_structure_type_file_bind_values)) {
                $classify_structure_type_file_id = $stmt->fetchColumn(0);
                $result = [
                    "status" => "success",
                    "classify_structure_type_file_id" => $classify_structure_type_file_id
                ];
            } else {
                $result = ["status" => "failure", 'errorInfo' => 'post_multi_classify_structure_type_file_insert'];
            }
        }
        return $result;
    }

    //修改classify_structure_type檔案
    public function patch_multi_classify_structure_type_file_patch($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $folder_bind_values = [
                "classify_structure_type_file_id" => "",
                "classify_structure_type_id" => "",
                "file_id" => "",
            ];

            $folder_upadte_cond = "";
            $folder_fliter_cond = "";

            foreach ($folder_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $folder_bind_values[$key] = $column[$key];
                    $folder_upadte_cond .= "{$key} = :{$key},";
                } else {
                    unset($folder_bind_values[$key]);
                }
            }

            $folder_fliter_cond .= "AND classify_structure.classify_structure_type_file.classify_structure_type_file_id = :classify_structure_type_file_id";
            $folder_upadte_cond = rtrim($folder_upadte_cond, ',');

            $sql = "UPDATE classify_structure.classify_structure_type_file
                        SET {$folder_upadte_cond}
                        WHERE TRUE {$folder_fliter_cond}
                ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($folder_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    //刪除classify_structure_type檔案
    public function delete_multi_classify_structure_type_file_delete($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_classify_structure_type_folder_condition = "";
            $delete_classify_structure_type_folder_bind_values = [
                "classify_structure_type_id" => "",
                "classify_structure_type_file_id" => "",
                "classify_structure_type_file_id_arr" => "",
            ];

            foreach ($delete_classify_structure_type_folder_bind_values as $key => $value) {
                if ($key === 'classify_structure_type_file_id_arr' && array_key_exists('classify_structure_type_file_id_arr', $delete_data) && count($delete_data['classify_structure_type_file_id_arr']) !== 0) {
                    unset($delete_classify_structure_type_folder_bind_values[$key]);
                    $delete_classify_structure_type_folder_condition .= " classify_structure_type_file_id IN ( SELECT classify_structure_type_file_id FROM classify_structure.classify_structure_type_file WHERE classify_structure_type_id = :classify_structure_type_id AND classify_structure_type_file_id NOT IN (";
                    foreach ($delete_data[$key] as $project_sop_step_user_involve_arr) {
                        $delete_classify_structure_type_folder_condition .= " {$project_sop_step_user_involve_arr},";
                    }
                    $delete_classify_structure_type_folder_condition = rtrim($delete_classify_structure_type_folder_condition, ',');
                    $delete_classify_structure_type_folder_condition .= ")) AND ";
                } else if (array_key_exists($key, $delete_data)) {
                    $delete_classify_structure_type_folder_bind_values[$key] = $delete_data[$key];
                    $delete_classify_structure_type_folder_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_classify_structure_type_folder_bind_values[$key]);
                }
            }

            $delete_classify_structure_type_folder_condition = rtrim($delete_classify_structure_type_folder_condition, 'AND ');

            $sql_delete = "DELETE FROM classify_structure.classify_structure_type_file
                    WHERE $delete_classify_structure_type_folder_condition
                ";
            $stmt_delete_classify_structure_type_folder = $this->db->prepare($sql_delete);
            if ($stmt_delete_classify_structure_type_folder->execute($delete_classify_structure_type_folder_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    public function get_language_culture($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "language_culture_id" => null,
        ];
        $custom_filter_bind_values = [
            "language_culture_id" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "language_culture_id" => " AND language_culture_id = :language_culture_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = "ORDER BY language_culture_id ASC";

        if (array_key_exists('order', $params)) {
            $order = "ORDER BY ";
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT language.language_culture.language_culture_id, 
                        language.language_culture.language_culture_name, 
                        language.language_culture.language_culture_code
                        {$customize_select}
                    FROM language.language_culture
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
                {$order}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result['data']) === 0) {
                $result['data'] = [];
                $result['total'] = 0;
                return $result;
            }
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_language_culture($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $language_culture_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
            ];

            $language_culture_insert_cond = "";
            $language_culture_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($language_culture_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $language_culture_bind_values[$key] = $column[$key];
                    $language_culture_insert_cond .= "{$key},";
                    $language_culture_values_cond .= ":{$key},";
                } else {
                    unset($language_culture_bind_values[$key]);
                }
            }

            $language_culture_insert_cond = rtrim($language_culture_insert_cond, ',');
            $language_culture_values_cond = rtrim($language_culture_values_cond, ',');

            $sql_insert = "INSERT INTO language.language_culture({$language_culture_insert_cond})
                VALUES ({$language_culture_values_cond})
                RETURNING language_culture_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $language_culture_language_data = [];
            if ($stmt_insert->execute($language_culture_bind_values)) {
                $Component = new component($this->container->db);
                $language_culture_id = $stmt_insert->fetchColumn(0);
                $column['language_culture_id'] = $language_culture_id;
                if (array_key_exists('name_language_data', $column)) {
                    foreach ($column['name_language_data'] as $name_language_data_index => $name_language_data_value) {
                        $column['schema_name'] = 'language';
                        $column['table_name'] = 'language_culture';
                        $column['column_name'] = 'name';
                        $column['table_primary_id'] = $language_culture_id;
                        $column['language_culture_code'] = $name_language_data_value['language_culture_code'];
                        $column['language_value'] = $name_language_data_value['language_culture_value'];
                        $language_manage_id = $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                        $language_culture_language_data = [["language_manage_id" => $language_manage_id]];
                    }
                }
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "language_culture_id" => $language_culture_id, "language_culture_language_data" => $language_culture_language_data];
        }
        return $result;
    }

    public function patch_language_culture($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $language_culture_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "language_culture_id" => null,
            ];

            $language_culture_upadte_cond = "";
            $language_culture_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($language_culture_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'language_culture_id') {
                        $language_culture_bind_values[$key] = $column[$key];
                    } else {
                        $language_culture_bind_values[$key] = $column[$key];
                        $language_culture_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($language_culture_bind_values[$key]);
                }
            }

            $language_culture_fliter_cond .= "AND language.language_culture.language_culture_id = :language_culture_id";
            $language_culture_upadte_cond = rtrim($language_culture_upadte_cond, ',');

            $sql = "UPDATE language.language_culture
                    SET {$language_culture_upadte_cond}
                    WHERE TRUE {$language_culture_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($language_culture_bind_values)) {
                $Component = new component($this->container->db);
                if (array_key_exists('name_language_data', $column)) {
                    foreach ($column['name_language_data'] as $name_language_data_index => $name_language_data_value) {
                        if (!array_key_exists('language_manage_id', $name_language_data_value)) {
                            $column['schema_name'] = 'language';
                            $column['table_name'] = 'language_culture';
                            $column['column_name'] = 'name';
                            $column['table_primary_id'] = $column['language_culture_id'];
                            $column['language_culture_code'] = $name_language_data_value['language_culture_code'];
                            $column['language_value'] = $name_language_data_value['language_culture_value'];
                            $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                        } else {
                            $column['schema_name'] = 'language';
                            $column['table_name'] = 'language_culture';
                            $column['column_name'] = 'name';
                            $column['table_primary_id'] = $column['language_culture_id'];
                            $column['language_culture_code'] = $name_language_data_value['language_culture_code'];
                            $column['language_value'] = $name_language_data_value['language_culture_value'];
                            $column['language_manage_id'] = $name_language_data_value['language_manage_id'];
                            $Component->patch_language_manage([$column], $last_edit_user_id);
                        }
                    }
                }
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_language_culture($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_language_culture_bind_values = [
                "language_culture_id" => "",
            ];

            foreach ($delete_language_culture_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_language_culture_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM language.language_culture
                WHERE language.language_culture.language_culture_id = :language_culture_id
            ";
            $stmt_delete_language_culture = $this->db->prepare($sql_delete);
            if ($stmt_delete_language_culture->execute($delete_language_culture_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function get_serial_encode_resource($params)
    {
        $result = $this->get_code_base(
            $params, //原本的查詢參數
            [
                "serial_encode_resource_id" => null,
            ], //原本的bind_values
            [
                "serial_encode_resource_id" => null,
            ], //原本的custom_filter_bind_values
            [
                "serial_encode_resource_id" => " AND serial_encode_resource_id = :serial_encode_resource_id",
            ], //原本的condition_values
            "ORDER BY serial_encode_resource_id ASC", //預設排序
            "SELECT question_bank.serial_encode_resource.serial_encode_resource_id,
                    question_bank.serial_encode_resource.from_schema, question_bank.serial_encode_resource.from_table,
                    question_bank.serial_encode_resource.from_column, question_bank.serial_encode_resource.create_time,
                    question_bank.serial_encode_resource.create_user_id
                    FROM question_bank.serial_encode_resource
            ", //客製化SQL
            $this->db //客製化Db
        );
        return $result;
    }

    public function post_serial_encode_resource($data, $last_edit_user_id)
    {
        $result = $this->post_code_base(
            $data, //原本的送入POST的參數
            [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "from_schema" => "",
                "from_table" => "",
                "from_column" => "",
                "create_time" => "",
                "create_user_id" => "",
            ], //原本的bind_values
            $last_edit_user_id,
            "question_bank", //客製化schema
            "serial_encode_resource", //客製化table
            "serial_encode_resource_id", //客製化回傳id
            $this->db //客製化Db
        );

        return $result;
    }

    public function patch_serial_encode_resource($data, $last_edit_user_id)
    {
        $result = $this->patch_code_base(
            $data, //原本的送入POST的參數
            [
                "serial_encode_resource_id" => "",
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "from_schema" => "",
                "from_table" => "",
                "from_column" => "",
                "create_time" => "",
                "create_user_id" => "",
            ], //原本的bind_values
            $last_edit_user_id,
            "question_bank", //客製化schema
            "serial_encode_resource", //客製化table
            "serial_encode_resource_id", //客製化回傳id
            $this->db //客製化Db
        );
        return $result;
    }

    public function delete_serial_encode_resource($data)
    {
        $result = $this->delete_code_base(
            $data, //原本的送入POST的參數
            [
                "serial_encode_resource_id" => null,
            ], //原本的bind_values
            "question_bank", //客製化schema
            "serial_encode_resource", //客製化table
            "serial_encode_resource_id", //客製化回傳id
            $this->db //客製化Db
        );
        return $result;
    }

    public function get_serial_encode_sign($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "serial_encode_sign_id" => null,
        ];
        $custom_filter_bind_values = [
            "serial_encode_sign_id" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "serial_encode_sign_id" => " AND serial_encode_sign_id = :serial_encode_sign_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = "ORDER BY serial_encode_sign_id ASC";

        if (array_key_exists('order', $params)) {
            $order = "ORDER BY ";
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT question_bank.serial_encode_sign.serial_encode_sign_id,
                    question_bank.serial_encode_sign.serial_encode_sign_value, question_bank.serial_encode_sign.create_time,
                    question_bank.serial_encode_sign.create_user_id
                        {$customize_select}
                    FROM question_bank.serial_encode_sign
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
                {$order}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_serial_encode_sign($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $serial_encode_sign_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "serial_encode_sign_value" => "",
                "create_time" => "",
                "create_user_id" => "",
            ];

            $serial_encode_sign_insert_cond = "";
            $serial_encode_sign_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';
            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = 'NOW()';

            foreach ($serial_encode_sign_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $serial_encode_sign_bind_values[$key] = $column[$key];
                    $serial_encode_sign_insert_cond .= "{$key},";
                    $serial_encode_sign_values_cond .= ":{$key},";
                } else {
                    unset($serial_encode_sign_bind_values[$key]);
                }
            }

            $serial_encode_sign_insert_cond = rtrim($serial_encode_sign_insert_cond, ',');
            $serial_encode_sign_values_cond = rtrim($serial_encode_sign_values_cond, ',');

            $sql_insert = "INSERT INTO question_bank.serial_encode_sign({$serial_encode_sign_insert_cond})
                VALUES ({$serial_encode_sign_values_cond})
                RETURNING serial_encode_sign_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($serial_encode_sign_bind_values)) {
                $serial_encode_sign_id = $stmt_insert->fetchColumn(0);
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "serial_encode_sign_id" => $serial_encode_sign_id];
        }
        return $result;
    }

    public function patch_serial_encode_sign($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $serial_encode_sign_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "serial_encode_sign_id" => null,
                "serial_encode_sign_value" => "",
                "create_time" => "",
                "create_user_id" => "",
            ];

            $serial_encode_sign_upadte_cond = "";
            $serial_encode_sign_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($serial_encode_sign_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'serial_encode_sign_id') {
                        $serial_encode_sign_bind_values[$key] = $column[$key];
                    } else {
                        $serial_encode_sign_bind_values[$key] = $column[$key];
                        $serial_encode_sign_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($serial_encode_sign_bind_values[$key]);
                }
            }

            $serial_encode_sign_fliter_cond .= "AND question_bank.serial_encode_sign.serial_encode_sign_id = :serial_encode_sign_id";
            $serial_encode_sign_upadte_cond = rtrim($serial_encode_sign_upadte_cond, ',');

            $sql = "UPDATE question_bank.serial_encode_sign
                    SET {$serial_encode_sign_upadte_cond}
                    WHERE TRUE {$serial_encode_sign_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($serial_encode_sign_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_serial_encode_sign($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_serial_encode_sign_bind_values = [
                "serial_encode_sign_id" => "",
            ];

            foreach ($delete_serial_encode_sign_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_serial_encode_sign_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM question_bank.serial_encode_sign
                WHERE question_bank.serial_encode_sign.serial_encode_sign_id = :serial_encode_sign_id
            ";
            $stmt_delete_serial_encode_sign = $this->db->prepare($sql_delete);
            if ($stmt_delete_serial_encode_sign->execute($delete_serial_encode_sign_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    public function removeEmptyValues(array $array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->removeEmptyValues($value); // 遞迴清理巢狀陣列
                if (empty($value)) {
                    unset($array[$key]);
                }
            } elseif ($value === '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    public function get_classify_structure_type_depth($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "classify_structure_id" => 0,
            "classify_structure_type_depth_id" => null,
            "classify_structure_type_id" => null,
        ];
        $custom_filter_bind_values = [
            "classify_structure_type_depth_id" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key === "classify_structure_id") {
                    unset($bind_values[$key]);
                    $bind_values["classify_structure_id_arr"] = $this->formatToPgsqlArray($params[$key]);
                } else {
                    $bind_values[$key] = $params[$key];
                }
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "classify_structure_type_depth_id" => " AND classify_structure_type_depth_id = :classify_structure_type_depth_id",
            "classify_structure_type_id" => " AND classify_structure_type_id = :classify_structure_type_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }
        if (isset($bind_values["classify_structure_id_arr"])) {
            $condition .= " AND classify_structure_id = ANY(:classify_structure_id_arr)";
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = "ORDER BY classify_structure_type_depth_id ASC";

        if (array_key_exists('order', $params)) {
            $order = "ORDER BY ";
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT classify_structure.classify_structure_type_depth.classify_structure_type_depth_id, 
                        classify_structure.classify_structure_type_depth.classify_structure_type_id, 
                        classify_structure.classify_structure_type.classify_structure_id, 
                        classify_structure.classify_structure_type_depth.depth_id, 
                        classify_structure.classify_structure_type_depth.\"name\", 
                        classify_structure.classify_structure_type_depth.\"name\" depth_name, 
                        classify_structure.classify_structure_type_depth.property_json, 
                        classify_structure.classify_structure_type_depth.last_edit_user_id, 
                        classify_structure.classify_structure_type_depth.last_edit_time, 
                        classify_structure.classify_structure_type_depth.create_user_id, 
                        classify_structure.classify_structure_type_depth.create_time,
                        COALESCE(classify_structure_type_depth_data.classify_structure_type_depth_data,'[]') classify_structure_type_depth_data
                        {$customize_select}
                    FROM classify_structure.classify_structure_type_depth
                    LEFT JOIN classify_structure.classify_structure_type ON classify_structure.classify_structure_type.classify_structure_type_id
                        = classify_structure.classify_structure_type_depth.classify_structure_type_id
                    LEFT JOIN (
                        SELECT all_classify_structure_type_data.classify_structure_type_depth_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                'classify_structure_type_id',classify_structure.classify_structure_type.classify_structure_type_id,
                                'classify_structure_id',classify_structure.classify_structure_type.classify_structure_id,
                                'classify_structure_type_parent_id',classify_structure.classify_structure_type.classify_structure_type_parent_id,
                                'name_language_data',COALESCE(name_language_data.name_language_data, '[]')
                                )
                            )classify_structure_type_depth_data    
                        FROM classify_structure.classify_structure_type
                        LEFT JOIN (
                            SELECT all_classify_structure_type.classify_structure_type_id root_id,
                                all_classify_structure_type.parent_id classify_structure_type_id, 
                                all_classify_structure_type.depth,
                                classify_structure.classify_structure_type_depth.\"classify_structure_type_depth_id\",
                                classify_structure.classify_structure_type_depth.\"name\" depth_name
                            FROM classify_structure.all_classify_structure_type(0,:classify_structure_id_arr) all_classify_structure_type
                            LEFT JOIN classify_structure.classify_structure_type_depth ON classify_structure.classify_structure_type_depth.classify_structure_type_id = all_classify_structure_type.classify_structure_type_id AND all_classify_structure_type.depth = classify_structure.classify_structure_type_depth.depth_id
                        ) all_classify_structure_type_data ON classify_structure.classify_structure_type.classify_structure_type_id = all_classify_structure_type_data.classify_structure_type_id
                    
                        LEFT JOIN (
                            SELECT classify_structure.\"classify_structure_type\".classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', classify_structure_type_language_json.language_manage_id,
                                    'language_culture_code', classify_structure_type_language_json.language_culture_code,
                                    'language_culture_value', classify_structure_type_language_json.language_value
                                )
                            )name_language_data
                            FROM classify_structure.\"classify_structure_type\"
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = 'classify_structure' AND language.language_manage.table_name = 'classify_structure_type'
                            )classify_structure_type_language_json ON classify_structure_type_language_json.table_primary_id = classify_structure.classify_structure_type.classify_structure_type_id
                            GROUP BY classify_structure.classify_structure_type.classify_structure_type_id
                        )name_language_data ON classify_structure.classify_structure_type.classify_structure_type_id = name_language_data.classify_structure_type_id
                        GROUP BY all_classify_structure_type_data.classify_structure_type_depth_id
                    ) classify_structure_type_depth_data ON classify_structure_type_depth_data.classify_structure_type_depth_id = classify_structure.classify_structure_type_depth.classify_structure_type_depth_id
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
                {$order}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result['data']) === 0) {
                $result['data'] = [];
                $result['total'] = 0;
                return $result;
            }
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_classify_structure_type_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $classify_structure_type_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "create_user_id" => 0,
                "create_time" => "",
                "classify_structure_type_id" => "",
                "depth_id" => "",
                "name" => "",
                "property_json" => "",
            ];

            $classify_structure_type_depth_insert_cond = "";
            $classify_structure_type_depth_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';
            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = 'NOW()';

            foreach ($classify_structure_type_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $column[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                    }
                    $classify_structure_type_depth_bind_values[$key] = $column[$key];
                    $classify_structure_type_depth_insert_cond .= "{$key},";
                    $classify_structure_type_depth_values_cond .= ":{$key},";
                } else {
                    unset($classify_structure_type_depth_bind_values[$key]);
                }
            }

            $classify_structure_type_depth_insert_cond = rtrim($classify_structure_type_depth_insert_cond, ',');
            $classify_structure_type_depth_values_cond = rtrim($classify_structure_type_depth_values_cond, ',');

            $sql_insert = "INSERT INTO classify_structure.classify_structure_type_depth({$classify_structure_type_depth_insert_cond})
                VALUES ({$classify_structure_type_depth_values_cond})
                RETURNING classify_structure_type_depth_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $classify_structure_type_depth_language_data = [];
            if ($stmt_insert->execute($classify_structure_type_depth_bind_values)) {
                $Component = new component($this->container->db);
                $classify_structure_type_depth_id = $stmt_insert->fetchColumn(0);
                $column['classify_structure_type_depth_id'] = $classify_structure_type_depth_id;
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "classify_structure_type_depth_id" => $classify_structure_type_depth_id, "classify_structure_type_depth_language_data" => $classify_structure_type_depth_language_data];
        }
        return $result;
    }

    public function patch_classify_structure_type_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $classify_structure_type_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "classify_structure_type_depth_id" => null,
                "classify_structure_type_id" => null,
                "depth_id" => null,
                "name" => null,
                "property_json" => null,
            ];

            $classify_structure_type_depth_upadte_cond = "";
            $classify_structure_type_depth_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($classify_structure_type_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'classify_structure_type_depth_id') {
                        $classify_structure_type_depth_bind_values[$key] = $column[$key];
                    } else {
                        if ($key === "property_json") {
                            $column[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        }
                        $classify_structure_type_depth_bind_values[$key] = $column[$key];
                        $classify_structure_type_depth_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($classify_structure_type_depth_bind_values[$key]);
                }
            }

            $classify_structure_type_depth_fliter_cond .= "AND classify_structure.classify_structure_type_depth.classify_structure_type_depth_id = :classify_structure_type_depth_id";
            $classify_structure_type_depth_upadte_cond = rtrim($classify_structure_type_depth_upadte_cond, ',');

            $sql = "UPDATE classify_structure.classify_structure_type_depth
                    SET {$classify_structure_type_depth_upadte_cond}
                    WHERE TRUE {$classify_structure_type_depth_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($classify_structure_type_depth_bind_values)) {
                $Component = new component($this->container->db);
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_classify_structure_type_depth($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_classify_structure_type_depth_bind_values = [
                "classify_structure_type_depth_id" => "",
            ];

            foreach ($delete_classify_structure_type_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_classify_structure_type_depth_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM classify_structure.classify_structure_type_depth
                WHERE classify_structure.classify_structure_type_depth.classify_structure_type_depth_id = :classify_structure_type_depth_id
            ";
            $stmt_delete_classify_structure_type_depth = $this->db->prepare($sql_delete);
            if ($stmt_delete_classify_structure_type_depth->execute($delete_classify_structure_type_depth_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function get_classify_structure_depth($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "classify_structure_depth_id" => null,
        ];
        $custom_filter_bind_values = [
            "classify_structure_depth_id" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "classify_structure_depth_id" => " AND classify_structure_depth_id = :classify_structure_depth_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = "ORDER BY classify_structure_depth_id ASC";

        if (array_key_exists('order', $params)) {
            $order = "ORDER BY ";
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT classify_structure.classify_structure_depth.classify_structure_depth_id, 
                        classify_structure.classify_structure_depth.classify_structure_id, 
                        classify_structure.classify_structure_depth.depth_id, 
                        classify_structure.classify_structure_depth.\"name\", 
                        classify_structure.classify_structure_depth.property_json, 
                        classify_structure.classify_structure_depth.last_edit_user_id, 
                        classify_structure.classify_structure_depth.last_edit_time, 
                        classify_structure.classify_structure_depth.create_user_id, 
                        classify_structure.classify_structure_depth.create_time 
                        {$customize_select}
                    FROM classify_structure.classify_structure_depth
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
                {$order}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result['data']) === 0) {
                $result['data'] = [];
                $result['total'] = 0;
                return $result;
            }
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_classify_structure_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $classify_structure_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "create_user_id" => 0,
                "create_time" => "",
                "classify_structure_id" => "",
                "depth_id" => "",
                "name" => "",
                "property_json" => "",
            ];

            $classify_structure_depth_insert_cond = "";
            $classify_structure_depth_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';
            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = 'NOW()';

            foreach ($classify_structure_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $column[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                    }
                    $classify_structure_depth_bind_values[$key] = $column[$key];
                    $classify_structure_depth_insert_cond .= "{$key},";
                    $classify_structure_depth_values_cond .= ":{$key},";
                } else {
                    unset($classify_structure_depth_bind_values[$key]);
                }
            }

            $classify_structure_depth_insert_cond = rtrim($classify_structure_depth_insert_cond, ',');
            $classify_structure_depth_values_cond = rtrim($classify_structure_depth_values_cond, ',');

            $sql_insert = "INSERT INTO classify_structure.classify_structure_depth({$classify_structure_depth_insert_cond})
                VALUES ({$classify_structure_depth_values_cond})
                RETURNING classify_structure_depth_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $classify_structure_depth_language_data = [];
            if ($stmt_insert->execute($classify_structure_depth_bind_values)) {
                $Component = new component($this->container->db);
                $classify_structure_depth_id = $stmt_insert->fetchColumn(0);
                $column['classify_structure_depth_id'] = $classify_structure_depth_id;
                // if (array_key_exists('name_language_data', $column)) {
                //     foreach ($column['name_language_data'] as $name_language_data_index => $name_language_data_value) {
                //         $column['schema_name'] = 'language';
                //         $column['table_name'] = 'classify_structure_depth';
                //         $column['column_name'] = 'name';
                //         $column['table_primary_id'] = $classify_structure_depth_id;
                //         $column['classify_structure_depth_code'] = $name_language_data_value['classify_structure_depth_code'];
                //         $column['language_value'] = $name_language_data_value['classify_structure_depth_value'];
                //         $language_manage_id = $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                //         $classify_structure_depth_language_data = [["language_manage_id" => $language_manage_id]];
                //     }
                // }
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "classify_structure_depth_id" => $classify_structure_depth_id, "classify_structure_depth_language_data" => $classify_structure_depth_language_data];
        }
        return $result;
    }

    public function patch_classify_structure_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $classify_structure_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "classify_structure_depth_id" => null,
                "classify_structure_id" => null,
                "depth_id" => null,
                "name" => null,
                "property_json" => null,
            ];

            $classify_structure_depth_upadte_cond = "";
            $classify_structure_depth_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($classify_structure_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'classify_structure_depth_id') {
                        $classify_structure_depth_bind_values[$key] = $column[$key];
                    } else {
                        if ($key === "property_json") {
                            $column[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        }
                        $classify_structure_depth_bind_values[$key] = $column[$key];
                        $classify_structure_depth_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($classify_structure_depth_bind_values[$key]);
                }
            }

            $classify_structure_depth_fliter_cond .= "AND classify_structure.classify_structure_depth.classify_structure_depth_id = :classify_structure_depth_id";
            $classify_structure_depth_upadte_cond = rtrim($classify_structure_depth_upadte_cond, ',');

            $sql = "UPDATE classify_structure.classify_structure_depth
                    SET {$classify_structure_depth_upadte_cond}
                    WHERE TRUE {$classify_structure_depth_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($classify_structure_depth_bind_values)) {
                $Component = new component($this->container->db);
                // if (array_key_exists('name_language_data', $column)) {
                //     foreach ($column['name_language_data'] as $name_language_data_index => $name_language_data_value) {
                //         if (!array_key_exists('language_manage_id', $name_language_data_value)) {
                //             $column['schema_name'] = 'language';
                //             $column['table_name'] = 'classify_structure_depth';
                //             $column['column_name'] = 'name';
                //             $column['table_primary_id'] = $column['classify_structure_depth_id'];
                //             $column['classify_structure_depth_code'] = $name_language_data_value['classify_structure_depth_code'];
                //             $column['language_value'] = $name_language_data_value['classify_structure_depth_value'];
                //             $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                //         } else {
                //             $column['schema_name'] = 'language';
                //             $column['table_name'] = 'classify_structure_depth';
                //             $column['column_name'] = 'name';
                //             $column['table_primary_id'] = $column['classify_structure_depth_id'];
                //             $column['classify_structure_depth_code'] = $name_language_data_value['classify_structure_depth_code'];
                //             $column['language_value'] = $name_language_data_value['classify_structure_depth_value'];
                //             $column['language_manage_id'] = $name_language_data_value['language_manage_id'];
                //             $Component->patch_language_manage([$column], $last_edit_user_id);
                //         }
                //     }
                // }
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_classify_structure_depth($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_classify_structure_depth_bind_values = [
                "classify_structure_depth_id" => "",
            ];

            foreach ($delete_classify_structure_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_classify_structure_depth_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM classify_structure.classify_structure_depth
                WHERE classify_structure.classify_structure_depth.classify_structure_depth_id = :classify_structure_depth_id
            ";
            $stmt_delete_classify_structure_depth = $this->db->prepare($sql_delete);
            if ($stmt_delete_classify_structure_depth->execute($delete_classify_structure_depth_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }


    public function get_classify_structure_type($params, $additional_select = "", $additional_join = "", $additional_field = "")
    {
        function formatToPgsqlArray($input)
        {
            // 去掉前后空格
            $input = trim($input);

            // 检查输入是否为单个整数
            if (is_numeric($input) && intval($input) == $input) {
                return '{' . intval($input) . '}';
            }

            // 检查输入是否为数组格式 [1, 36]
            if (preg_match('/^\[\s*(-?\d+\s*,\s*)*-?\d+\s*\]$/', $input)) {
                // 去除方括号和空格
                $input = trim($input, '[] ');
                // 将字符串分割为整数数组
                $elements = explode(',', $input);
                // 去掉元素中的空格
                $elements = array_map('trim', $elements);
                // 返回 PGSQL 数组格式
                return '{' . implode(',', $elements) . '}';
            }

            // 如果输入格式不匹配，返回 null 或报错信息
            return null;
        }

        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "classify_structure_id" => null,
            "classify_structure_type_id" => null,
            "classify_structure_type_depth_id" => null,
        ];
        $custom_filter_bind_values = [
            "classify_structure_depth_id" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key === "classify_structure_id") {
                    unset($bind_values[$key]);
                    $bind_values["classify_structure_id_arr"] = formatToPgsqlArray($params[$key]);
                } else {
                    $bind_values[$key] = $params[$key];
                }
            } else {
                if ($key === "classify_structure_type_id") {
                } else {
                    unset($bind_values[$key]);
                }
            }
        }

        $condition = "";
        $condition_values = [
            "classify_structure_type_id" => " AND classify_structure_type_parent_id = :classify_structure_type_id",
            "classify_structure_type_depth_id" => " AND classify_structure_type_depth_id = :classify_structure_type_depth_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                if ($key === "classify_structure_type_id") {
                } else {
                    unset($bind_values[$key]);
                }
            }
        }

        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $bind_values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $bind_values = $custom_filter_return['bind_values'];

        $values_count = $bind_values;
        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        //預設排序
        $order = "ORDER BY classify_structure_type_id ASC";

        if (array_key_exists('order', $params)) {
            $order = "ORDER BY ";
            foreach ($params['order'] as $key => $column_data) {
                if ($this->isJson($column_data)) {
                    $column_data = json_decode(($column_data), true);
                } else {
                    $order = '';
                    return;
                }
                $sort_type = 'ASC';
                if ($column_data['type'] != 'ascend') {
                    $sort_type = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'annoucement_time':
                        $order .= ", to_char(annoucement_time::timestamp, 'yyyy-MM-dd') {$sort_type}";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT classify_structure_type.classify_structure_type_id,
                    classify_structure_type.classify_structure_type_parent_id,
                    classify_structure_type.classify_structure_type_depth_id,
                    classify_structure_type.depth,
                    COALESCE(classify_structure_type.property_json::jsonb,'{}')property_json,
                    classify_structure_type.all_classify_structure_type_data,
                    classify_structure_type.title,
                    classify_structure_type.name,
                    classify_structure_type.name_language_data,
                    classify_structure_type.classify_structure_type_depth_data,
                    classify_structure_type.children
                    {$additional_select}
                    -- ,
                    -- classify_structure_type.classify_structure_type_id2
                        {$customize_select}
                    FROM classify_structure.get_classify_structure_type_children_record(:classify_structure_type_id,0,:classify_structure_id_arr
                    {$additional_join}
                    -- ,
                    -- '{\"classify_structure.classify_structure_type classify_structure_type2\":\"classify_structure.classify_structure_type.classify_structure_type_id = classify_structure_type2.classify_structure_type_id\"}'::jsonb,
                    -- '{\"classify_structure_type_id2\":\"classify_structure_type2.classify_structure_type_id\"}'::jsonb
                    ) classify_structure_type (
                        classify_structure_type_id integer,
                        classify_structure_type_parent_id integer,
                        classify_structure_type_depth_id integer,
                        depth integer,
                        property_json jsonb,
                        all_classify_structure_type_data jsonb,
                        title text,
                        name text,
                        name_language_data jsonb,
                        classify_structure_type_depth_data jsonb,
                        children jsonb
                        {$additional_field}
                        -- ,
                        -- classify_structure_type_id2 integer
                    )
                )dt
                WHERE TRUE {$condition} {$select_condition}  
                {$order}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";

        $sql_count = "SELECT COUNT(*)
            FROM(
                {$sql_default}
            )sql_default
        ";
        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result['data']) === 0) {
                $result['data'] = [];
                $result['total'] = 0;
                return $result;
            }
            $result_count = $stmt_count->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_classify_structure_type($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $classify_structure_type_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "classify_structure_id" => null,
                "classify_structure_type_parent_id" => null,
                "index" => 0,
                "property_json" => null,
                "name" => null,
            ];

            $classify_structure_type_insert_cond = "";
            $classify_structure_type_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($classify_structure_type_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $column[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        $classify_structure_type_insert_cond .= "{$key},";
                        $classify_structure_type_values_cond .= ":{$key},";
                    } else {
                        $classify_structure_type_bind_values[$key] = $column[$key];
                        $classify_structure_type_insert_cond .= "{$key},";
                        $classify_structure_type_values_cond .= ":{$key},";
                    }
                } else {
                    if ($key === "index") {
                        $classify_structure_type_insert_cond .= "{$key},";
                        $classify_structure_type_values_cond .= ":{$key},";
                    } else {
                        unset($classify_structure_type_bind_values[$key]);
                    }
                }
            }

            $classify_structure_type_insert_cond = rtrim($classify_structure_type_insert_cond, ',');
            $classify_structure_type_values_cond = rtrim($classify_structure_type_values_cond, ',');

            $sql_insert = "INSERT INTO classify_structure.classify_structure_type({$classify_structure_type_insert_cond})
                VALUES ({$classify_structure_type_values_cond})
                RETURNING classify_structure_type_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $classify_structure_type_language_data = [];
            if ($stmt_insert->execute($classify_structure_type_bind_values)) {
                $Component = new component($this->container->db);
                $classify_structure_type_id = $stmt_insert->fetchColumn(0);
                $column['classify_structure_type_id'] = $classify_structure_type_id;


                $name_language_data = [];
                if (array_key_exists('name_language_data', $column)) {
                    $name_language_data = $column['name_language_data'];
                    foreach ($column['name_language_data'] as $name_language_data_index => $name_language_data_value) {
                        if (!array_key_exists('language_manage_id', $name_language_data_value)) {
                            $column['schema_name'] = 'classify_structure';
                            $column['table_name'] = 'classify_structure_type';
                            $column['column_name'] = 'name';
                            $column['table_primary_id'] = $column['classify_structure_type_id'];
                            $column['language_culture_code'] = $name_language_data_value['language_culture_code'];
                            $column['language_value'] = $name_language_data_value['language_culture_value'];
                            $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                        } else {
                            $column['schema_name'] = 'classify_structure';
                            $column['table_name'] = 'classify_structure_type';
                            $column['column_name'] = 'name';
                            $column['table_primary_id'] = $column['classify_structure_type_id'];
                            $column['language_culture_code'] = $name_language_data_value['language_culture_code'];
                            $column['language_value'] = $name_language_data_value['language_culture_value'];
                            $column['language_manage_id'] = $name_language_data_value['language_manage_id'];
                            $Component->patch_language_manage([$column], $last_edit_user_id);
                        }
                    }
                }
                if (array_key_exists('classify_structure_type_depth_data', $column)) {
                    $classify_structure_type_depth_id_arr = [];
                    $classify_structure_type_depth_id_arr['classify_structure_type_depth_id_arr'] = [];
                    foreach ($column['classify_structure_type_depth_data'] as $index => $classify_structure_type_depth) {
                        $classify_structure_type_depth['classify_structure_type_id'] = $column['classify_structure_type_id'];
                        $classify_structure_type_depth_id_arr['classify_structure_type_id'] = $classify_structure_type_depth['classify_structure_type_id'];
                        if (array_key_exists('classify_structure_type_depth_id', $classify_structure_type_depth)) {
                            $patch_classify_structure_type_depth = $this->patch_classify_structure_type_depth([$classify_structure_type_depth], $last_edit_user_id);
                            if ($patch_classify_structure_type_depth['status'] == 'success') {
                                array_push($classify_structure_type_depth_id_arr['classify_structure_type_depth_id_arr'], $classify_structure_type_depth['classify_structure_type_depth_id']);
                            }
                        } else {
                            $post_classify_structure_type_depth = $this->post_classify_structure_type_depth([$classify_structure_type_depth], $last_edit_user_id);
                            if ($post_classify_structure_type_depth['status'] === 'success') {
                                array_push($classify_structure_type_depth_id_arr['classify_structure_type_depth_id_arr'], $post_classify_structure_type_depth['classify_structure_type_depth_id']);
                            }
                        }
                    }
                    $result = $this->delete_classify_structure_type_depth([$classify_structure_type_depth_id_arr], $last_edit_user_id);
                }
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "classify_structure_type_id" => $classify_structure_type_id, "name_language_data" => $name_language_data];
        }
        return $result;
    }

    public function patch_classify_structure_type($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $classify_structure_type_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "classify_structure_type_id" => null,
                "classify_structure_id" => null,
                "classify_structure_type_parent_id" => null,
                "index" => 0,
                "property_json" => null,
                "name" => null,
            ];

            $classify_structure_type_upadte_cond = "";
            $classify_structure_type_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($classify_structure_type_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'classify_structure_type_id') {
                        $classify_structure_type_bind_values[$key] = $column[$key];
                    } else {
                        if ($key === "property_json") {
                            $column[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        }
                        $classify_structure_type_bind_values[$key] = $column[$key];
                        $classify_structure_type_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($classify_structure_type_bind_values[$key]);
                }
            }

            $classify_structure_type_fliter_cond .= "AND classify_structure.classify_structure_type.classify_structure_type_id = :classify_structure_type_id";
            $classify_structure_type_upadte_cond = rtrim($classify_structure_type_upadte_cond, ',');

            $sql = "UPDATE classify_structure.classify_structure_type
                    SET {$classify_structure_type_upadte_cond}
                    WHERE TRUE {$classify_structure_type_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($classify_structure_type_bind_values)) {
                $Component = new component($this->container->db);
                if (array_key_exists('name_language_data', $column)) {
                    foreach ($column['name_language_data'] as $name_language_data_index => $name_language_data_value) {
                        $column['schema_name'] = 'classify_structure';
                        $column['table_name'] = 'classify_structure_type';
                        $column['column_name'] = 'name';
                        $column['table_primary_id'] = $column['classify_structure_type_id'];
                        $column['language_culture_code'] = $name_language_data_value['language_culture_code'];
                        $column['language_value'] = $name_language_data_value['language_culture_value'];
                        $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                    }
                }
                if (array_key_exists('classify_structure_type_depth_data', $column)) {
                    $classify_structure_type_depth_id_arr = [];
                    $classify_structure_type_depth_id_arr['classify_structure_type_depth_id_arr'] = [];
                    foreach ($column['classify_structure_type_depth_data'] as $index => $classify_structure_type_depth) {
                        $classify_structure_type_depth['classify_structure_type_id'] = $column['classify_structure_type_id'];
                        $classify_structure_type_depth_id_arr['classify_structure_type_id'] = $classify_structure_type_depth['classify_structure_type_id'];
                        if (array_key_exists('classify_structure_type_depth_id', $classify_structure_type_depth)) {
                            $patch_classify_structure_type_depth = $this->patch_classify_structure_type_depth([$classify_structure_type_depth], $last_edit_user_id);
                            if ($patch_classify_structure_type_depth['status'] == 'success') {
                                array_push($classify_structure_type_depth_id_arr['classify_structure_type_depth_id_arr'], $classify_structure_type_depth['classify_structure_type_depth_id']);
                            }
                        } else {
                            $post_classify_structure_type_depth = $this->post_classify_structure_type_depth([$classify_structure_type_depth], $last_edit_user_id);
                            if ($post_classify_structure_type_depth['status'] === 'success') {
                                array_push($classify_structure_type_depth_id_arr['classify_structure_type_depth_id_arr'], $post_classify_structure_type_depth['classify_structure_type_depth_id']);
                            }
                        }
                    }
                    $result = $this->delete_classify_structure_type_depth([$classify_structure_type_depth_id_arr], $last_edit_user_id);
                }
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_classify_structure_type($data)
    {
        $result = $this->delete_code_base(
            $data, //原本的送入POST的參數
            [
                "classify_structure_type_id" => null,
            ], //原本的bind_values
            "classify_structure", //客製化schema
            "classify_structure_type", //客製化table
            "classify_structure_type_id", //客製化回傳id
            $this->db //客製化Db
        );
        return $result;
    }
}
