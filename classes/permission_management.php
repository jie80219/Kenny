<?php

use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Formatter;

use function PHPSTORM_META\map;

class permission_management extends Model
{
    protected $container;
    protected $db;
    protected $db_sqlsrv;
    protected $branch_schema;


    // constructor receives container instance
    public function __construct()
    {
        global $container;
        $this->container = $container;
        $this->db = $container->db;
        $this->branch_schema = 'permission_management';
    }

    public function get_permission_list($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "permission_id" => null,
        ];
        $custom_filter_bind_values = [
            "permission_id" => null,
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
            "permission_id" => " AND \"permission_id\" = :permission_id",
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
        $order = 'ORDER BY permission_id';

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

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT permission_management.permission.id permission_id,
                    permission_management.permission.\"name\" permission_name,
                    permission_management.permission.\"url\", permission_management.permission.\"index\",
                    permission_management.permission.is_default
                    {$customize_select}
                    FROM permission_management.permission
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

    public function get_permission_group($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "permission_group_id" => null,
        ];
        $custom_filter_bind_values = [
            "permission_group_id" => null,
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
            "permission_group_id" => " AND \"permission_group_id\" = :permission_group_id",
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
        $order = 'ORDER BY permission_group_id';

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

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.permission_group.id permission_group_id,
                    {$this->branch_schema}.permission_group.\"name\" permission_group_name,
                    COALESCE(permission_data.permission_data,'[]')permission_data
                    {$customize_select}
                    FROM {$this->branch_schema}.permission_group
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.permission.permission_group_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'permission_id',{$this->branch_schema}.permission.id,
                                    'permission_name',{$this->branch_schema}.permission.\"name\",
                                    'url',{$this->branch_schema}.permission.\"url\", 
                                    'index',{$this->branch_schema}.permission.\"index\",
                                    'is_default',{$this->branch_schema}.permission.is_default
                                )
                            ) permission_data
                        FROM {$this->branch_schema}.permission
                        GROUP BY {$this->branch_schema}.permission.permission_group_id
                    ) permission_data ON permission_data.permission_group_id = {$this->branch_schema}.permission_group.id
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

    public function get_permission_level($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "permission_level_id" => null,
        ];
        $custom_filter_bind_values = [
            "permission_level_id" => null,
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
            "permission_level_id" => " AND \"permission_level_id\" = :permission_level_id",
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
        $order = 'ORDER BY permission_level_id';

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

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.permission_level.id permission_level_id,
                    {$this->branch_schema}.permission_level.\"name\" permission_level_name
                    {$customize_select}
                    FROM {$this->branch_schema}.permission_level
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

    public function get_permission_manage($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];


        $values = [
            "user_id" => '',
            "type_name" => '',
            "permission_time_start" => null,
            "permission_time_end" => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key === "permission_time_start" || $key === "permission_time_end") {
                    if ($params[$key] !== null && $params[$key] !== "")
                        $values[$key] = $params[$key];
                    if ($values[$key] === null)
                        unset($values[$key]);
                } else
                    $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "user_id" => " AND \"user_id\" = :user_id",
            "type_name" => " AND \"position_type\" = :type_name",
            "permission_time_start" => " AND CAST(permission_time_start AS TIMESTAMP) >= CAST(:permission_time_start AS TIMESTAMP)",
            "permission_time_end" => " AND CAST(permission_time_end AS TIMESTAMP) <= CAST(:permission_time_end AS TIMESTAMP)"
        ];

        $select_condition = "";

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($params[$key] !== null && $params[$key] !== "")
                    $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0 && $params['custom_filter_value'] !== "") {
            $select_condition = " AND (";
            foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
                $select_condition .= " CAST({$select_filter_arr_data} AS TEXT) LIKE '%{$params['custom_filter_value']}%' OR";
            }
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }

        $values["start"] = $start;
        $values["length"] = $length;
        unset($values['cur_page']);
        unset($values['size']);
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

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

                $order .= " {$column_data['column']}::TEXT {$sort_type},";
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *,  ROW_NUMBER() OVER (ORDER BY \"name\") \"key\"
            FROM (
                SELECT permisstion_data.*, user_permission.permission_time_start, user_permission.permission_time_end,
                COALESCE(user_permission.role_permission, '[]')role_permission
                FROM (
                    SELECT \"system\".\"user\".\"name\",  \"system\".\"user\".uid,
                        \"system\".\"user\".id user_id
                    FROM \"system\".\"user\"
                )permisstion_data
                LEFT JOIN (
                    SELECT {$this->branch_schema}.user_permission.user_id, 
                        to_char({$this->branch_schema}.user_permission.permission_time_start, 'YYYY-MM-DD')permission_time_start,
                        to_char({$this->branch_schema}.user_permission.permission_time_end, 'YYYY-MM-DD')permission_time_end,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'user_permission_id', {$this->branch_schema}.user_permission.id,
                                'permission_id', {$this->branch_schema}.permission.id,
                                'permission_name', {$this->branch_schema}.permission.\"name\",
                                'permission_url', {$this->branch_schema}.permission.\"url\",
                                'permission_level_id', {$this->branch_schema}.user_permission.permission_level_id
                            )
                            ORDER BY {$this->branch_schema}.permission.id
                        )role_permission
                    FROM {$this->branch_schema}.user_permission
                    LEFT JOIN system.user ON {$this->branch_schema}.user_permission.user_id = system.user.id
                    LEFT JOIN {$this->branch_schema}.permission ON {$this->branch_schema}.user_permission.permission_id = {$this->branch_schema}.permission.id
                    WHERE {$this->branch_schema}.permission.is_default IS NOT true
                    GROUP BY {$this->branch_schema}.user_permission.user_id, {$this->branch_schema}.user_permission.permission_time_start, {$this->branch_schema}.user_permission.permission_time_end
                )user_permission ON permisstion_data.user_id = user_permission.user_id
            )permisstion_data
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

    public function post_permission_manage($data)
    {
        foreach ($data as $row => $column) {
            $this->delete_permission_manage($column);
            foreach ($column['role_permission'] as $permission_row => $permission_data) {
                $permission_data['user_id'] = $column['user_id'];
                $user_permission_values = [
                    "user_id" => "",
                    "permission_id" => 0,
                    "permission_level_id" => 0,
                    "permission_time_start" => null,
                    "permission_time_end" => null,
                ];
                $user_permission_insert_cond = "";
                $user_permission_values_cond = "";

                foreach ($user_permission_values as $key => $value) {
                    if (array_key_exists($key, $permission_data)) {
                        $user_permission_bind_values[$key] = $permission_data[$key];
                        $user_permission_insert_cond .= "{$key},";
                        $user_permission_values_cond .= ":{$key},";
                    }
                }

                $user_permission_insert_cond = rtrim($user_permission_insert_cond, ',');
                $user_permission_values_cond = rtrim($user_permission_values_cond, ',');

                $sql_insert = "INSERT INTO {$this->branch_schema}.user_permission({$user_permission_insert_cond})
                    VALUES ({$user_permission_values_cond})
                    RETURNING id
                ";

                $stmt_insert = $this->db->prepare($sql_insert);

                if ($stmt_insert->execute($user_permission_bind_values)) {
                    $result = ["status" => "success"];
                } else {
                    $result = ["status" => "failure"];
                    return $result;
                }
            }
        }
        return $result;
    }

    public function patch_permission_manage($data)
    {
        foreach ($data as $row => $column) {
            $user_permission_bind_values = [
                "user_permission_id" => null,
                "user_id" => null,
                "permission_id" => null,
                "permission_level_id" => null,
                "permission_time_start" => null,
                "permission_time_end" => null,
            ];

            $user_permission_upadte_cond = "";
            $user_permission_fliter_cond = "";

            foreach ($user_permission_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'user_permission_id') {
                        $user_permission_bind_values[$key] = $column[$key];
                    } else {
                        if ($key == 'user_permission_name') {
                            $user_permission_upadte_cond .= "\"name\" = :{$key},";
                        } else {
                            $user_permission_upadte_cond .= "\"{$key}\" = :{$key},";
                        }
                        $user_permission_bind_values[$key] = $column[$key];
                    }
                } else {
                    unset($user_permission_bind_values[$key]);
                }
            }

            $user_permission_fliter_cond .= "AND {$this->branch_schema}.user_permission.\"id\" = :user_permission_id";
            $user_permission_upadte_cond = rtrim($user_permission_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.user_permission
                    SET {$user_permission_upadte_cond}
                    WHERE TRUE {$user_permission_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($user_permission_bind_values)) {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_permission_manage($delete_data)
    {
        $delete_user_permission_bind_values = [
            "user_id" => "",
        ];

        foreach ($delete_user_permission_bind_values as $key => $value) {
            if (array_key_exists($key, $delete_data)) {
                $delete_user_permission_bind_values[$key] = $delete_data[$key];
            }
        }

        $sql_delete = "DELETE FROM {$this->branch_schema}.user_permission
                    WHERE {$this->branch_schema}.user_permission.user_id = :user_id
                ";
        $stmt_delete_user_permission_file = $this->db->prepare($sql_delete);
        if ($stmt_delete_user_permission_file->execute($delete_user_permission_bind_values)) {
            $result = ['status' => 'success'];
        } else {
            $result = ['status' => 'failure'];
        }
        return $result;
    }

    public function get_permission($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "permission_id" => null,
            "permission_name" => null,
            "url" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";
        $custom_filter_bind_values = [
            "permission_id" => null,
            "permission_name" => null,
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
            "permission_id" => " AND permission_id = :permission_id",
            "permission_name" => " AND permission_name = :permission_name",
            "url" => " AND url = :url",
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
                    SELECT {$this->branch_schema}.permission.id permission_id, {$this->branch_schema}.permission.name permission_name,
                    {$this->branch_schema}.permission.\"url\", {$this->branch_schema}.permission.index, {$this->branch_schema}.permission.is_default
                    {$customize_select}
                    FROM {$this->branch_schema}.permission
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
            return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
        }
    }

    public function post_permission($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $permission_bind_values = [
                "permission_name" => null,
                "url" => null,
                "index" => null,
            ];

            $permission_insert_cond = "";
            $permission_values_cond = "";

            foreach ($permission_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'permission_name') {
                        $permission_insert_cond .= "\"name\",";
                    } else {
                        $permission_insert_cond .= "{$key},";
                    }
                    $permission_bind_values[$key] = $column[$key];
                    $permission_values_cond .= ":{$key},";
                } else {
                    unset($permission_bind_values[$key]);
                }
            }

            $permission_insert_cond = rtrim($permission_insert_cond, ',');
            $permission_values_cond = rtrim($permission_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.permission({$permission_insert_cond})
                VALUES ({$permission_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($permission_bind_values)) {
                $permission_id = $stmt_insert->fetchColumn(0);
                $result = ["status" => "success", 'permission_id' => $permission_id];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt_insert->errorInfo()];
            }
        }
        return $result;
    }

    public function patch_permission($data)
    {
        foreach ($data as $row => $column) {
            $permission_bind_values = [
                "permission_id" => null,
                "permission_name" => null,
                "url" => null,
                "index" => null,
                "is_default" => null,
            ];

            $permission_upadte_cond = "";
            $permission_fliter_cond = "";

            foreach ($permission_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'permission_id') {
                        $permission_bind_values[$key] = $column[$key];
                    } else {
                        if ($key == 'permission_name') {
                            $permission_upadte_cond .= "\"name\" = :{$key},";
                        } else {
                            $permission_upadte_cond .= "\"{$key}\" = :{$key},";
                        }
                        $permission_bind_values[$key] = $column[$key];
                    }
                } else {
                    unset($permission_bind_values[$key]);
                }
            }

            $permission_fliter_cond .= "AND {$this->branch_schema}.permission.\"id\" = :permission_id";
            $permission_upadte_cond = rtrim($permission_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.permission
                    SET {$permission_upadte_cond}
                    WHERE TRUE {$permission_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($permission_bind_values)) {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_permission($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_permission_bind_values = [
                "permission_id" => "",
            ];

            foreach ($delete_permission_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_permission_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM {$this->branch_schema}.permission
                WHERE {$this->branch_schema}.permission.\"id\" = :permission_id
            ";
            $stmt_delete_permission = $this->db->prepare($sql_delete);
            if ($stmt_delete_permission->execute($delete_permission_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
}
