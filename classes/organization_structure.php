<?php

use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use function PHPSTORM_META\map;

class organization_structure extends Model
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
        $this->branch_schema = 'organization_structure';
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
            "classify_structure_type_id" => " AND classify_structure_type_parent_id::text = :classify_structure_type_id",
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function get_department($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "department_id" => null,
            "classify_structure_id" => null,
            "classify_structure_type_id" => null,
            "department_depth_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_depth_data" => null,
            "department_language_data" => null,
            "count_role" => null,
            "count_user" => null,
            "create_user_name" => null,
            "create_time" => null,
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
            "department_id" => " AND department_id = :department_id",
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
            "classify_structure_type_id" => " AND classify_structure_type_parent_id = :classify_structure_type_id",
            "department_depth_id" => " AND department_depth_id = :department_depth_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY department_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.\"department\".department_id, {$this->branch_schema}.\"department\".department_index,
                    {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id, classify_structure.classify_structure_type.classify_structure_id, 
                    all_department_data.department_depth_id,
                    classify_structure.classify_structure_type.classify_structure_type_parent_id, 
                    {$this->branch_schema}.\"department\".create_user_id, {$this->branch_schema}.\"department\".create_time,
                    {$this->branch_schema}.staff.staff_name create_user_name,
                    COALESCE({$this->branch_schema}.department_depth.property_json::jsonb || {$this->branch_schema}.department.property_json::jsonb,'{}') property_json,
                    COALESCE(department_language_data.department_language_data, '[]')department_language_data,
                    COALESCE(department_depth_data.department_depth_data, '[]')department_depth_data,
                    \"user_role\".count_user, \"user_role\".count_role
                    {$customize_select}
                    FROM {$this->branch_schema}.\"department\"
                    LEFT JOIN {$this->branch_schema}.staff ON {$this->branch_schema}.\"department\".create_user_id = {$this->branch_schema}.staff.user_id
                    LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.\"department\".department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                    LEFT JOIN classify_structure.classify_structure_type ON {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type.classify_structure_type_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.all_department_zero.department_id root_id,
                            {$this->branch_schema}.all_department_zero.parent_id department_id, 
                            {$this->branch_schema}.all_department_zero.depth,
                            {$this->branch_schema}.department_depth.\"department_depth_id\",
                            {$this->branch_schema}.department_depth.\"name\" depth_name
                        FROM {$this->branch_schema}.all_department_zero
                        LEFT JOIN {$this->branch_schema}.department_depth ON {$this->branch_schema}.department_depth.department_id = {$this->branch_schema}.all_department_zero.department_id AND {$this->branch_schema}.all_department_zero.depth = {$this->branch_schema}.department_depth.depth_id
                    ) all_department_data ON {$this->branch_schema}.department_classify_structure_type.department_id = all_department_data.department_id
                    LEFT JOIN {$this->branch_schema}.department_depth ON {$this->branch_schema}.department_depth.department_id = all_department_data.root_id AND all_department_data.depth = {$this->branch_schema}.department_depth.depth_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.\"department\".department_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'language_manage_id', department_language_json.language_manage_id,
                                'language_culture_code', department_language_json.language_culture_code,
                                'language_culture_value', department_language_json.language_value
                            )
                        )department_language_data
                        FROM {$this->branch_schema}.\"department\"
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                        )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.\"department\".department_id
                        GROUP BY {$this->branch_schema}.\"department\".department_id
                    )department_language_data ON {$this->branch_schema}.department_classify_structure_type.department_id = department_language_data.department_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.department_depth.department_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'department_depth_id',{$this->branch_schema}.department_depth.department_depth_id,
                                    'department_id',{$this->branch_schema}.department_depth.department_id,
                                    'depth_id',{$this->branch_schema}.department_depth.depth_id,
                                    'name',{$this->branch_schema}.department_depth.\"name\",
                                    'property_json',{$this->branch_schema}.department_depth.property_json
                                )
                                ORDER BY {$this->branch_schema}.department_depth.depth_id
                            ) department_depth_data
                        FROM {$this->branch_schema}.department_depth
                        GROUP BY {$this->branch_schema}.department_depth.department_id
                    ) department_depth_data ON {$this->branch_schema}.\"department\".department_id = department_depth_data.department_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.\"user_role\".department_id,
                            COUNT(DISTINCT {$this->branch_schema}.\"user_role\".user_id) count_user,
                            COUNT(DISTINCT {$this->branch_schema}.\"user_role\".role_id) count_role
                        FROM {$this->branch_schema}.\"user_role\"
                        GROUP BY {$this->branch_schema}.\"user_role\".department_id
                    )\"user_role\" ON department_language_data.department_id = \"user_role\".department_id
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
            $result['relation_select'] = ",{$this->branch_schema}.department_classify_structure_type.department_id
                , COALESCE(department_language_data.department_language_data, '[]')department_language_data
                , COALESCE(department_role.department_role, '[]')department_role
                , COALESCE(department_depth_data.department_depth_data, '[]')department_depth_data
                , COALESCE(user_role.count_role, 0)count_role
                , COALESCE(user_role.count_user, 0)count_user
                , COALESCE({$this->branch_schema}.department_depth.property_json::jsonb || {$this->branch_schema}.department.property_json::jsonb,'{}') property_json
                , all_department_data.department_depth_id
                , all_department_data.depth_name
                , {$this->branch_schema}.staff.staff_name create_user_name
                , {$this->branch_schema}.department.create_time
            ";
            $result['relation_order'] = " ORDER BY \"index\" ASC";
            $result['relation_from'] = "
                INNER JOIN {$this->branch_schema}.department_classify_structure_type ON classify_structure.classify_structure_type.classify_structure_type_id = {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                INNER JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                LEFT JOIN {$this->branch_schema}.staff ON {$this->branch_schema}.department.create_user_id = {$this->branch_schema}.staff.user_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.all_department_zero.department_id root_id,
                        {$this->branch_schema}.all_department_zero.parent_id department_id, 
                        {$this->branch_schema}.all_department_zero.depth,
                        {$this->branch_schema}.department_depth.\"department_depth_id\",
                        {$this->branch_schema}.department_depth.\"name\" depth_name
                    FROM {$this->branch_schema}.all_department_zero
                    LEFT JOIN {$this->branch_schema}.department_depth ON {$this->branch_schema}.department_depth.department_id = {$this->branch_schema}.all_department_zero.department_id AND {$this->branch_schema}.all_department_zero.depth = {$this->branch_schema}.department_depth.depth_id
                ) all_department_data ON {$this->branch_schema}.department_classify_structure_type.department_id = all_department_data.department_id
                LEFT JOIN {$this->branch_schema}.department_depth ON {$this->branch_schema}.department_depth.department_id = all_department_data.root_id AND all_department_data.depth = {$this->branch_schema}.department_depth.depth_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.department_role.department_id,
                    COUNT(DISTINCT {$this->branch_schema}.\"department_role\".role_id) count_role,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'department_role_id', {$this->branch_schema}.department_role.id,
                            'role_id', {$this->branch_schema}.\"role\".id,
                            'classify_structure_type_id', role_language_data.classify_structure_type_id,
                            'role_language_data', COALESCE(role_language_data.role_language_data, '[]')
                        )
                    )department_role
                    FROM {$this->branch_schema}.department_role
                    LEFT JOIN {$this->branch_schema}.\"role\" ON {$this->branch_schema}.department_role.role_id = {$this->branch_schema}.\"role\".id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'language_manage_id', role_language_json.language_manage_id,
                                'language_culture_code', role_language_json.language_culture_code,
                                'language_culture_value', role_language_json.language_value
                            )
                        )role_language_data
                        FROM {$this->branch_schema}.\"role\"
                        LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                        ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                        GROUP BY {$this->branch_schema}.\"role\".id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                    )role_language_data ON {$this->branch_schema}.\"role\".id = role_language_data.role_id
                    GROUP BY {$this->branch_schema}.department_role.department_id
                )department_role ON {$this->branch_schema}.department_classify_structure_type.department_id = department_role.department_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.\"department\".department_id,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'language_manage_id', department_language_json.language_manage_id,
                            'language_culture_code', department_language_json.language_culture_code,
                            'language_culture_value', department_language_json.language_value
                        )
                    )department_language_data
                    FROM {$this->branch_schema}.\"department\"
                    LEFT JOIN (
                        SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                        language.language_manage.table_name, language.language_manage.column_name,
                        language.language_manage.table_primary_id, language.language_manage.language_value,
                        language.language_culture.language_culture_code
                        FROM language.language_manage
                        LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                        WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                    )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.\"department\".department_id
                    GROUP BY {$this->branch_schema}.\"department\".department_id
                )department_language_data ON {$this->branch_schema}.department_classify_structure_type.department_id = department_language_data.department_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.\"user_role\".department_id,
                        COUNT(DISTINCT {$this->branch_schema}.\"user_role\".user_id) count_user,
                        COUNT(DISTINCT {$this->branch_schema}.\"user_role\".role_id) count_role
                    FROM {$this->branch_schema}.\"user_role\"
                    GROUP BY {$this->branch_schema}.\"user_role\".department_id
                )\"user_role\" ON department_language_data.department_id = \"user_role\".department_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.department_depth.department_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'department_depth_id',{$this->branch_schema}.department_depth.department_depth_id,
                                'department_id',{$this->branch_schema}.department_depth.department_id,
                                'depth_id',{$this->branch_schema}.department_depth.depth_id,
                                'name',{$this->branch_schema}.department_depth.\"name\",
                                'property_json',{$this->branch_schema}.department_depth.property_json,
                                'department_depth_staff_data',department_depth_staff_data.department_depth_staff_data
                            )
                            ORDER BY {$this->branch_schema}.department_depth.depth_id
                        ) department_depth_data
                    FROM {$this->branch_schema}.department_depth
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.department_depth_staff.department_depth_id, 
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'department_depth_staff_id',{$this->branch_schema}.department_depth_staff.department_depth_staff_id, 
                                    'staff_id',{$this->branch_schema}.department_depth_staff.staff_id
                                )
                            ) department_depth_staff_data
                        FROM {$this->branch_schema}.department_depth_staff
                        GROUP BY {$this->branch_schema}.department_depth_staff.department_depth_id                
                    ) department_depth_staff_data ON department_depth_staff_data.department_depth_id = {$this->branch_schema}.department_depth.department_depth_id
                    GROUP BY {$this->branch_schema}.department_depth.department_id
                ) department_depth_data ON department_language_data.department_id = department_depth_data.department_id
            ";
            return $result;
        } else {
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function post_department($data, $last_edit_user_id, $classify_structure_type_id)
    {
        foreach ($data as $row => $column) {
            $department_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "create_user_id" => 0,
                "create_time" => "",
                "property_json" => null,
            ];

            $department_insert_cond = "";
            $department_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';
            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = 'NOW()';

            foreach ($department_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $department_bind_values[$key] = json_encode($column[$key]);
                    } else {
                        $department_bind_values[$key] = $column[$key];
                    }
                    $department_insert_cond .= "{$key},";
                    $department_values_cond .= ":{$key},";
                } else {
                    unset($department_bind_values[$key]);
                }
            }

            $department_insert_cond = rtrim($department_insert_cond, ',');
            $department_values_cond = rtrim($department_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.department({$department_insert_cond})
                VALUES ({$department_values_cond})
                RETURNING department_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            $department_language_data = [];
            if ($stmt_insert->execute($department_bind_values)) {
                $Component = new component($this->container->db);
                $department_id = $stmt_insert->fetchColumn(0);
                $column['department_id'] = $department_id;
                if (array_key_exists('department_language_data', $column)) {
                    foreach ($column['department_language_data'] as $department_language_data_index => $department_language_data_value) {
                        $column['schema_name'] = $this->branch_schema;
                        $column['table_name'] = 'department';
                        $column['column_name'] = 'name';
                        $column['table_primary_id'] = $department_id;
                        $column['language_culture_code'] = $department_language_data_value['language_culture_code'];
                        $column['language_value'] = $department_language_data_value['language_culture_value'];
                        $language_manage_id = $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                        $department_language_data = [["language_manage_id" => $language_manage_id]];
                    }
                }
                if (array_key_exists('department_depth_data', $column)) {
                    $department_depth_id_arr = [];
                    $department_depth_id_arr['department_depth_id_arr'] = [];
                    foreach ($column['department_depth_data'] as $index => $department_depth) {
                        $department_depth['department_id'] = $department_id;
                        $department_depth_id_arr['department_id'] = $department_depth['department_id'];
                        if (array_key_exists('department_depth_id', $department_depth)) {
                            $patch_department_depth = $this->patch_department_depth([$department_depth], $last_edit_user_id);
                            if ($patch_department_depth['status'] == 'success') {
                                array_push($department_depth_id_arr['department_depth_id_arr'], $department_depth['department_depth_id']);
                            }
                        } else {
                            $post_department_depth = $this->post_department_depth([$department_depth], $last_edit_user_id);
                            if ($post_department_depth['status'] === 'success') {
                                array_push($department_depth_id_arr['department_depth_id_arr'], $post_department_depth['department_depth_id']);
                            }
                        }
                    }
                    $result = $this->delete_department_depth([$department_depth_id_arr], $last_edit_user_id);
                }

                $column['classify_structure_type_id'] = $classify_structure_type_id;
                $this->department_classify_structure_type_insert($column);
                $result = ["status" => "success", "department_id" => $department_id, "classify_structure_type_id" => $classify_structure_type_id, "department_language_data" => $department_language_data];
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function patch_department($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $department_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "department_id" => null,
                "property_json" => null,
            ];

            $department_upadte_cond = "";
            $department_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($department_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'department_id') {
                        $department_bind_values[$key] = $column[$key];
                    } else if ($key === "property_json") {
                        $department_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        $department_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $department_bind_values[$key] = $column[$key];
                        $department_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($department_bind_values[$key]);
                }
            }

            $department_fliter_cond .= "AND {$this->branch_schema}.department.department_id = :department_id";
            $department_upadte_cond = rtrim($department_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.department
                    SET {$department_upadte_cond}
                    WHERE TRUE {$department_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($department_bind_values)) {
                $Component = new component($this->container->db);

                $column['classify_structure_id'] = 9; //樹狀結構的department
                $classify_structure_type_id_arr = $this->get_department([
                    "department_id" => $column["department_id"]
                ])['data'];
                $column['classify_structure_type_parent_id'] = is_null($column['classify_structure_type_id']) === TRUE ? 0 : $column['classify_structure_type_id'];
                foreach ($classify_structure_type_id_arr as $classify_structure_type_id_arr_index => $classify_structure_type_id_arr_value) {
                    $column['classify_structure_type_id'] = $classify_structure_type_id_arr_value['classify_structure_type_id'];
                }
                $result_classify_structure_type_folder = $Component->patch_classify_structure_type_folder([$column], $last_edit_user_id);

                if (array_key_exists('department_language_data', $column)) {
                    foreach ($column['department_language_data'] as $department_language_data_index => $department_language_data_value) {
                        $column['schema_name'] = $this->branch_schema;
                        $column['table_name'] = 'department';
                        $column['column_name'] = 'name';
                        $column['table_primary_id'] = $column['department_id'];
                        $column['language_culture_code'] = $department_language_data_value['language_culture_code'];
                        $column['language_value'] = $department_language_data_value['language_culture_value'];
                        $column['language_manage_id'] = $department_language_data_value['language_manage_id'];
                        $Component->patch_language_manage([$column], $last_edit_user_id);
                    }
                }
                if (array_key_exists('department_depth_data', $column)) {
                    $department_depth_id_arr = [];
                    $department_depth_id_arr['department_depth_id_arr'] = [];
                    foreach ($column['department_depth_data'] as $index => $department_depth) {
                        $department_depth['department_id'] = $column['department_id'];
                        $department_depth_id_arr['department_id'] = $department_depth['department_id'];
                        if (array_key_exists('department_depth_id', $department_depth)) {
                            $patch_department_depth = $this->patch_department_depth([$department_depth], $last_edit_user_id);
                            if ($patch_department_depth['status'] == 'success') {
                                array_push($department_depth_id_arr['department_depth_id_arr'], $department_depth['department_depth_id']);
                            }
                        } else {
                            $post_department_depth = $this->post_department_depth([$department_depth], $last_edit_user_id);
                            if ($post_department_depth['status'] === 'success') {
                                array_push($department_depth_id_arr['department_depth_id_arr'], $post_department_depth['department_depth_id']);
                            }
                        }
                    }
                    $result = $this->delete_department_depth([$department_depth_id_arr], $last_edit_user_id);
                }

                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_department($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_department_bind_values = [
                "department_id" => "",
            ];

            foreach ($delete_department_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_department_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM {$this->branch_schema}.department
                WHERE {$this->branch_schema}.department.department_id = :department_id
            ";
            $stmt_delete_department = $this->db->prepare($sql_delete);
            $this->department_classify_structure_type_delete($delete_department_bind_values);
            if ($stmt_delete_department->execute($delete_department_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function department_classify_structure_type_insert($datas)
    {
        $department_classify_structure_type_insert_cond = "";
        $department_classify_structure_type_values_cond = "";

        $per_department_classify_structure_type_bind_values = [
            "department_id" => "",
            "classify_structure_type_id" => null,
        ];
        foreach ($per_department_classify_structure_type_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_department_classify_structure_type_bind_values[$key] = $datas[$key];
                $department_classify_structure_type_insert_cond .= "{$key},";
                $department_classify_structure_type_values_cond .= ":{$key},";
            }
        }
        $department_classify_structure_type_insert_cond = rtrim($department_classify_structure_type_insert_cond, ',');
        $department_classify_structure_type_values_cond = rtrim($department_classify_structure_type_values_cond, ',');

        $sql = "INSERT INTO {$this->branch_schema}.department_classify_structure_type({$department_classify_structure_type_insert_cond})
                VALUES ({$department_classify_structure_type_values_cond})
                RETURNING id
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_department_classify_structure_type_bind_values)) {
            $department_classify_structure_type_id = $stmt->fetchColumn(0);
            $datas['department_classify_structure_type_id'] = $department_classify_structure_type_id;
        } else {
        }
    }

    public function department_classify_structure_type_delete($datas)
    {
        $per_department_classify_structure_type_bind_values = [
            "department_id" => "",
        ];
        foreach ($per_department_classify_structure_type_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_department_classify_structure_type_bind_values[$key] = $datas[$key];
            }
        }

        $sql = "DELETE FROM classify_structure.classify_structure_type
                WHERE classify_structure.classify_structure_type.classify_structure_type_id IN(
                    SELECT {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                    FROM {$this->branch_schema}.department_classify_structure_type
                    WHERE {$this->branch_schema}.department_classify_structure_type.department_id = :department_id
                )
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_department_classify_structure_type_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function get_role($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "role_id" => null,
            "department_id_arr" => null,
            "classify_structure_type_id_for_department" => null,
            "classify_structure_id" => null,
        ];
        $custom_filter_bind_values = [
            "role_id" => null,
            "department_id" => null,
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
            "role_id" => " AND role_id = :role_id",
            "department_id_arr" => " AND department_id = ANY(:department_id_arr)",
            "classify_structure_type_id_for_department" => " AND classify_structure_type_id_for_department = :classify_structure_type_id_for_department",
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY role_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.\"role\".role_index,
                        {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                        {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id classify_structure_type_id_for_department,
                        {$this->branch_schema}.\"department_role\".department_id,
                        {$this->branch_schema}.\"role\".create_user_id, {$this->branch_schema}.\"role\".create_time,
                        {$this->branch_schema}.staff.staff_name create_user_name,
                        classify_structure.classify_structure_type.classify_structure_id
                        {$customize_select}
                    FROM {$this->branch_schema}.\"role\"
                    LEFT JOIN {$this->branch_schema}.staff ON {$this->branch_schema}.\"role\".create_user_id = {$this->branch_schema}.staff.user_id
                    LEFT JOIN {$this->branch_schema}.\"department_role\" ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.\"department_role\".role_id
                    LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                    LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.\"department_role\".department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                    LEFT JOIN classify_structure.classify_structure_type ON {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type.classify_structure_type_id
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
            $result['relation_select'] = " ,{$this->branch_schema}.role_classify_structure_type.role_id 
                ,{$this->branch_schema}.role.is_default
                ,NULL department_id
                ,COALESCE(user_data.user_data, '[]')user_data
                ,COALESCE(role_language_data.role_language_data, '[]')role_language_data
                ,COALESCE(user_role.count_user, 0)count_user
                , COALESCE({$this->branch_schema}.role_property_json.property_json::jsonb || {$this->branch_schema}.role.property_json::jsonb,'{}') property_json
                , all_role_data.depth_name
                , {$this->branch_schema}.role.create_user_id, {$this->branch_schema}.role.create_time
                , {$this->branch_schema}.staff.staff_name create_user_name
                , COALESCE({$this->branch_schema}.department_role_data.department_role_data,'[]') department_role_data
            ";
            $result['relation_order'] = " ORDER BY \"index\" ASC";
            $result['relation_from'] = "
                LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON classify_structure.classify_structure_type.classify_structure_type_id = {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                INNER JOIN {$this->branch_schema}.role ON {$this->branch_schema}.role_classify_structure_type.role_id = {$this->branch_schema}.role.id
                LEFT JOIN {$this->branch_schema}.staff ON {$this->branch_schema}.role.create_user_id = {$this->branch_schema}.staff.user_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.all_role.role_id root_id,
                        {$this->branch_schema}.all_role.parent_id role_id, 
                        {$this->branch_schema}.all_role.depth,
                        {$this->branch_schema}.role_depth.\"name\" depth_name
                    FROM {$this->branch_schema}.all_role
                    LEFT JOIN {$this->branch_schema}.role_depth ON {$this->branch_schema}.role_depth.role_id = {$this->branch_schema}.all_role.role_id AND {$this->branch_schema}.all_role.depth = {$this->branch_schema}.role_depth.depth_id
                ) all_role_data ON {$this->branch_schema}.role_classify_structure_type.role_id = all_role_data.role_id
                CROSS JOIN {$this->branch_schema}.role_property_json
                LEFT JOIN (
                    SELECT {$this->branch_schema}.user_role.role_id,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'user_id', \"system\".user.\"id\",
                            'user_name', \"system\".user.\"name\"
                        )
                    )user_data
                    FROM {$this->branch_schema}.user_role
                    LEFT JOIN \"system\".user ON {$this->branch_schema}.user_role.user_id = \"system\".user.id
                    GROUP BY {$this->branch_schema}.user_role.role_id
                )user_data ON {$this->branch_schema}.role_classify_structure_type.role_id = user_data.role_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.\"role\".id role_id,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'language_manage_id', role_language_json.language_manage_id,
                            'language_culture_code', role_language_json.language_culture_code,
                            'language_culture_value', role_language_json.language_value
                        )
                    )role_language_data
                    FROM {$this->branch_schema}.\"role\"
                    LEFT JOIN (
                        SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                        language.language_manage.table_name, language.language_manage.column_name,
                        language.language_manage.table_primary_id, language.language_manage.language_value,
                        language.language_culture.language_culture_code
                        FROM language.language_manage
                        LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                        WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                    )role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                    GROUP BY {$this->branch_schema}.\"role\".id
                )role_language_data ON {$this->branch_schema}.role_classify_structure_type.role_id = role_language_data.role_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.\"user_role\".role_id,
                        COUNT(DISTINCT {$this->branch_schema}.\"user_role\".user_id) count_user
                    FROM {$this->branch_schema}.\"user_role\"
                    GROUP BY {$this->branch_schema}.\"user_role\".role_id
                )\"user_role\" ON role_language_data.role_id = \"user_role\".role_id
                LEFT JOIN {$this->branch_schema}.department_role_data ON role_language_data.role_id = {$this->branch_schema}.department_role_data.role_id
            ";
            return $result;
        } else {
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function post_role($data, $last_edit_user_id, $classify_structure_type_id)
    {
        foreach ($data as $row => $column) {
            $role_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "create_user_id" => 0,
                "create_time" => "",
                "property_json" => null,
            ];

            $role_insert_cond = "";
            $role_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';
            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = 'NOW()';

            foreach ($role_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $role_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                    } else {
                        $role_bind_values[$key] = $column[$key];
                    }
                    $role_insert_cond .= "{$key},";
                    $role_values_cond .= ":{$key},";
                } else {
                    unset($role_bind_values[$key]);
                }
            }

            $role_insert_cond = rtrim($role_insert_cond, ',');
            $role_values_cond = rtrim($role_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.role({$role_insert_cond})
                VALUES ({$role_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $role_language_data = [];
            if ($stmt_insert->execute($role_bind_values)) {
                $Component = new component($this->container->db);
                $role_id = $stmt_insert->fetchColumn(0);
                $column['role_id'] = $role_id;
                if (array_key_exists('role_language_data', $column)) {
                    foreach ($column['role_language_data'] as $role_language_data_index => $role_language_data_value) {
                        $column['schema_name'] = $this->branch_schema;
                        $column['table_name'] = 'role';
                        $column['column_name'] = 'name';
                        $column['table_primary_id'] = $role_id;
                        $column['language_culture_code'] = $role_language_data_value['language_culture_code'];
                        $column['language_value'] = $role_language_data_value['language_culture_value'];
                        $language_manage_id = $Component->post_language_manage([$column], $last_edit_user_id)['language_manage_id'];
                        $role_language_data = [["language_manage_id" => $language_manage_id]];
                    }
                }
                if (isset($column['department_role_data'])) {
                    $department_role_id_arr = [];
                    $department_role_id_arr['department_role_id_arr'] = [];
                    foreach ($column['department_role_data'] as $index => $department_role) {
                        $department_role['role_id'] = $column['role_id'];
                        $department_role_id_arr['role_id'] = $department_role['role_id'];
                        if (isset($department_role['department_role_id'])) {
                            $department_role['id'] = $department_role['department_role_id'];
                            $patch_code_base = $this->patch_code_base(
                                [$department_role], //原本的送入POST的參數
                                [
                                    "id" => "",
                                    "role_id" => "",
                                    "department_id" => "",
                                ], //原本的bind_values
                                $last_edit_user_id,
                                $this->branch_schema, //客製化schema
                                "department_role", //客製化table
                                "id", //客製化回傳id
                                $this->db //客製化Db
                            );

                            if ($patch_code_base['status'] == 'success') {
                                array_push($department_role_id_arr['department_role_id_arr'], $department_role['department_role_id']);
                            }
                        } else {
                            $post_code_base = $this->post_code_base(
                                [$department_role], //原本的送入POST的參數
                                [
                                    "role_id" => "",
                                    "department_id" => "",
                                ], //原本的bind_values
                                $last_edit_user_id,
                                $this->branch_schema, //客製化schema
                                "department_role", //客製化table
                                "id", //客製化回傳id
                                $this->db, //客製化Db,
                                [
                                    "role_id",
                                    "department_id"
                                ]
                            );

                            if ($post_code_base['status'] === 'success') {
                                array_push($department_role_id_arr['department_role_id_arr'], $post_code_base['id']);
                            }
                        }
                    }
                    $result = $this->delete_department_role([$department_role_id_arr], $last_edit_user_id);
                }

                if (array_key_exists('department_id', $column)) {
                    $this->department_role_insert($column);
                }

                $column['classify_structure_type_id'] = $classify_structure_type_id;
                $this->role_classify_structure_type_insert($column);
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "role_id" => $role_id, "classify_structure_type_id" => $classify_structure_type_id, "role_language_data" => $role_language_data];
        }
        return $result;
    }

    public function patch_role($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $role_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "role_id" => null,
                "property_json" => null,
            ];

            $role_upadte_cond = "";
            $role_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($role_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'role_id') {
                        $role_bind_values[$key] = $column[$key];
                    } else {
                        if ($key === "property_json") {
                            $role_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        } else {
                            $role_bind_values[$key] = $column[$key];
                        }
                        $role_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($role_bind_values[$key]);
                }
            }

            $role_fliter_cond .= "AND {$this->branch_schema}.role.id = :role_id";
            $role_upadte_cond = rtrim($role_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.role
                    SET {$role_upadte_cond}
                    WHERE TRUE {$role_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($role_bind_values)) {
                $Component = new component($this->container->db);
                $column['classify_structure_id'] = 8; //樹狀結構的department
                $classify_structure_type_id_arr = $this->get_role([
                    "role_id" => $column['role_id']
                ])['data'];
                $column['classify_structure_type_parent_id'] = is_null($column['classify_structure_type_id']) === TRUE ? 0 : $column['classify_structure_type_id'];
                foreach ($classify_structure_type_id_arr as $classify_structure_type_id_arr_index => $classify_structure_type_id_arr_value) {
                    $column['classify_structure_type_id'] = $classify_structure_type_id_arr_value['classify_structure_type_id'];
                }
                $result_classify_structure_type_folder = $Component->patch_classify_structure_type_folder([$column], $last_edit_user_id);

                if (array_key_exists('role_language_data', $column)) {
                    foreach ($column['role_language_data'] as $role_language_data_index => $role_language_data_value) {
                        $column['schema_name'] = $this->branch_schema;
                        $column['table_name'] = 'role';
                        $column['column_name'] = 'name';
                        $column['table_primary_id'] = $column['role_id'];
                        $column['language_culture_code'] = $role_language_data_value['language_culture_code'];
                        $column['language_value'] = $role_language_data_value['language_culture_value'];
                        $column['language_manage_id'] = $role_language_data_value['language_manage_id'];
                        $Component->patch_language_manage([$column], $last_edit_user_id);
                    }
                }
                if (isset($column['department_role_data'])) {
                    $department_role_id_arr = [];
                    $department_role_id_arr['department_role_id_arr'] = [];
                    foreach ($column['department_role_data'] as $index => $department_role) {
                        $department_role['role_id'] = $column['role_id'];
                        $department_role_id_arr['role_id'] = $department_role['role_id'];
                        if (isset($department_role['department_role_id'])) {
                            $department_role['id'] = $department_role['department_role_id'];
                            $patch_code_base = $this->patch_code_base(
                                [$department_role], //原本的送入POST的參數
                                [
                                    "id" => "",
                                    "role_id" => "",
                                    "department_id" => "",
                                ], //原本的bind_values
                                $last_edit_user_id,
                                $this->branch_schema, //客製化schema
                                "department_role", //客製化table
                                "id", //客製化回傳id
                                $this->db //客製化Db
                            );

                            if ($patch_code_base['status'] == 'success') {
                                array_push($department_role_id_arr['department_role_id_arr'], $department_role['department_role_id']);
                            }
                        } else {
                            $post_code_base = $this->post_code_base(
                                [$department_role], //原本的送入POST的參數
                                [
                                    "role_id" => "",
                                    "department_id" => "",
                                ], //原本的bind_values
                                $last_edit_user_id,
                                $this->branch_schema, //客製化schema
                                "department_role", //客製化table
                                "id", //客製化回傳id
                                $this->db, //客製化Db,
                                [
                                    "role_id",
                                    "department_id"
                                ]
                            );

                            if ($post_code_base['status'] === 'success') {
                                array_push($department_role_id_arr['department_role_id_arr'], $post_code_base['id']);
                            }
                        }
                    }
                    $result = $this->delete_department_role([$department_role_id_arr], $last_edit_user_id);
                }

                $result = ["status" => "success"];
            } else {

                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_role($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_role_bind_values = [
                "role_id" => "",
            ];

            foreach ($delete_role_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_role_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM {$this->branch_schema}.role
                WHERE {$this->branch_schema}.role.id = :role_id
            ";
            $stmt_delete_role = $this->db->prepare($sql_delete);
            $this->role_classify_structure_type_delete($delete_role_bind_values);
            if ($stmt_delete_role->execute($delete_role_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function role_classify_structure_type_insert($datas)
    {
        $role_classify_structure_type_insert_cond = "";
        $role_classify_structure_type_values_cond = "";

        $per_role_classify_structure_type_bind_values = [
            "role_id" => "",
            "classify_structure_type_id" => null,
        ];
        foreach ($per_role_classify_structure_type_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_role_classify_structure_type_bind_values[$key] = $datas[$key];
                $role_classify_structure_type_insert_cond .= "{$key},";
                $role_classify_structure_type_values_cond .= ":{$key},";
            }
        }
        $role_classify_structure_type_insert_cond = rtrim($role_classify_structure_type_insert_cond, ',');
        $role_classify_structure_type_values_cond = rtrim($role_classify_structure_type_values_cond, ',');

        $sql = "INSERT INTO {$this->branch_schema}.role_classify_structure_type({$role_classify_structure_type_insert_cond})
                VALUES ({$role_classify_structure_type_values_cond})
                RETURNING id
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_role_classify_structure_type_bind_values)) {
            $role_classify_structure_type_id = $stmt->fetchColumn(0);
            $datas['role_classify_structure_type_id'] = $role_classify_structure_type_id;
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function role_classify_structure_type_delete($datas)
    {
        $per_role_classify_structure_type_bind_values = [
            "role_id" => "",
        ];
        foreach ($per_role_classify_structure_type_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_role_classify_structure_type_bind_values[$key] = $datas[$key];
            }
        }

        $sql = "DELETE FROM classify_structure.classify_structure_type
                WHERE classify_structure.classify_structure_type.classify_structure_type_id IN(
                    SELECT {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                    FROM {$this->branch_schema}.role_classify_structure_type
                    WHERE {$this->branch_schema}.role_classify_structure_type.role_id = :role_id
                )
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_role_classify_structure_type_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function department_role_select($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "role_id" => null,
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
            "role_id" => " AND role_id = :role_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY role_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.department_role.department_id,
                    JSON_AGG({$this->branch_schema}.department_role.role_id)
                    FROM {$this->branch_schema}.department_role
                    GROUP BY {$this->branch_schema}.department_role.department_id
                )dt
                WHERE TRUE {$condition}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function department_role_insert($datas)
    {
        $department_role_insert_cond = "";
        $department_role_values_cond = "";

        $per_department_role_bind_values = [
            "department_id" => "",
            "role_id" => null,
            "department_role_index" => null,
        ];
        foreach ($per_department_role_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_department_role_bind_values[$key] = $datas[$key];
                $department_role_insert_cond .= "{$key},";
                $department_role_values_cond .= ":{$key},";
            }
        }
        $department_role_insert_cond = rtrim($department_role_insert_cond, ',');
        $department_role_values_cond = rtrim($department_role_values_cond, ',');

        $sql = "INSERT INTO {$this->branch_schema}.department_role({$department_role_insert_cond})
                VALUES ({$department_role_values_cond})
                RETURNING id
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_department_role_bind_values)) {
            $department_role_id = $stmt->fetchColumn(0);
            $datas['department_role_id'] = $department_role_id;
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function department_role_patch($datas)
    {
        $department_role_update_cond = "";

        $per_department_role_bind_values = [
            "department_role_id" => null,
            "department_id" => "",
            "role_id" => null,
        ];
        foreach ($per_department_role_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                if ($key == 'department_role_id') {
                    $per_department_role_bind_values[$key] = $datas[$key];
                } else {
                    $per_department_role_bind_values[$key] = $datas[$key];
                    $department_role_update_cond .= "{$key} = :{$key},";
                }
            }
        }
        $department_role_update_cond = rtrim($department_role_update_cond, ',');

        $sql = "UPDATE {$this->branch_schema}.department_role
                SET {$department_role_update_cond}
                WHERE TRUE AND {$this->branch_schema}.department_role.id = :department_role_id
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_department_role_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function department_role_delete($datas)
    {
        $per_department_role_bind_values = [
            "department_id" => "",
        ];
        foreach ($per_department_role_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_department_role_bind_values[$key] = $datas[$key];
            }
        }

        $sql = "DELETE FROM classify_structure.role
                WHERE classify_structure.role.role_id IN(
                    SELECT {$this->branch_schema}.department_role.role_id
                    FROM {$this->branch_schema}.department_role
                    WHERE {$this->branch_schema}.department_role.department_id = :department_id
                )
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_department_role_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    //拿資料夾
    public function get_classify_structure_type_folder($params, $for_relation_in = [], $for_relation_select = "", $for_relation_from = "")
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
                $condition .= " {$value},";
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
                        COALESCE(children.children,'[]')children
                        {$for_relation_select}
                        FROM classify_structure.classify_structure_type
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
                            )children
                            FROM classify_structure.classify_structure_type classify_structure_type_children
                            INNER JOIN classify_structure.classify_structure_type ON classify_structure_type_children.classify_structure_type_parent_id = classify_structure.classify_structure_type.classify_structure_type_id
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
            return $stmt->errorInfo();
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
            // var_dump($folder_bind_values);
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
                        $folder_bind_values[$key] = $this->get_folder_index(["classify_structure_type_id" => $column['classify_structure_type_id']])[0]['index'];
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
                var_dump($folder_bind_values);
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

    public function get_user($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "user_id" => null,
            "department_id" => null,
            "role_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_id" => null,
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
            "user_id" => " AND user_id = :user_id",
            "department_id" => " AND department_id = :department_id",
            "role_id" => " AND role_id = :role_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY user_id) \"key\"
                FROM(
                    SELECT staff_data.user_id, 
                        staff_data.staff_name,
                        staff_data.serial_name, 
                        COALESCE(user_role.deparment_role_data,'[]')deparment_role_data
                        {$customize_select}
                    FROM system.user
                    INNER JOIN (
                        SELECT {$this->branch_schema}.staff.user_id,
                            STRING_AGG({$this->branch_schema}.staff.staff_name,'、') staff_name,
                            STRING_AGG({$this->branch_schema}.staff.serial_name,'、') serial_name
                        FROM {$this->branch_schema}.staff
                        GROUP BY {$this->branch_schema}.staff.user_id 
                    ) staff_data ON system.user.id = staff_data.user_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.user_role.user_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'user_role_id', {$this->branch_schema}.user_role.id,
                                    'role_id', {$this->branch_schema}.user_role.role_id,
                                    'department_id', department_language_data.department_id,
                                    'department_language_data', COALESCE(department_language_data.department_language_data, '[]'),
                                    'role_language_data', COALESCE(role_language_data.role_language_data, '[]')
                                )
                            )deparment_role_data
                        FROM {$this->branch_schema}.user_role
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.\"role\".id role_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', role_language_json.language_manage_id,
                                    'language_culture_code', role_language_json.language_culture_code,
                                    'language_culture_value', role_language_json.language_value
                                )
                            )role_language_data
                            FROM {$this->branch_schema}.\"role\"
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                            )role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                            GROUP BY {$this->branch_schema}.\"role\".id
                        )role_language_data ON {$this->branch_schema}.user_role.role_id = role_language_data.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.department_role.department_id,
                            {$this->branch_schema}.department_role.role_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', department_language_json.language_manage_id,
                                    'language_culture_code', department_language_json.language_culture_code,
                                    'language_culture_value', department_language_json.language_value
                                )
                            )department_language_data
                            FROM {$this->branch_schema}.department_role
                            LEFT JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department_role.department_id = {$this->branch_schema}.department.department_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                            )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.department.department_id
                            GROUP BY {$this->branch_schema}.department_role.department_id, {$this->branch_schema}.department_role.role_id
                        )department_language_data ON {$this->branch_schema}.user_role.role_id = department_language_data.role_id
                        GROUP BY {$this->branch_schema}.user_role.user_id
                    )user_role ON staff_data.user_id = user_role.user_id
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function get_user_parent($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "user_id" => null,
            "department_id" => null,
            "role_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_id" => null,
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
            "user_id" => " AND user_id = :user_id",
            "department_id" => " AND department_id = :department_id",
            "role_id" => " AND role_id = :role_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY user_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.user_role.user_id, {$this->branch_schema}.user_role.role_id,
                    COALESCE(user_role_parent.user_id_parent, '[]')user_id_parent
                    FROM {$this->branch_schema}.user_role
                    LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.user_role.role_id = {$this->branch_schema}.role_classify_structure_type.role_id
                    LEFT JOIN classify_structure.classify_structure_type ON {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type.classify_structure_type_id
                    LEFT JOIN classify_structure.classify_structure_type classify_structure_type_parent ON classify_structure.classify_structure_type.classify_structure_type_parent_id = classify_structure_type_parent.classify_structure_type_id
                    LEFT JOIN {$this->branch_schema}.role_classify_structure_type role_classify_structure_type_parent ON classify_structure_type_parent.classify_structure_type_id = role_classify_structure_type_parent.classify_structure_type_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.user_role.role_id,
                        JSON_AGG(
                            {$this->branch_schema}.user_role.user_id
                        )user_id_parent
                        FROM {$this->branch_schema}.user_role
                        GROUP BY {$this->branch_schema}.user_role.role_id 
                    )user_role_parent ON role_classify_structure_type_parent.role_id = user_role_parent.role_id
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function user_role_select($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "user_id" => null,
            "department_id" => null,
            "no_department_id" => null,
            "role_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_id" => null,
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
            "user_id" => " AND user_id = :user_id",
            "department_id" => " AND department_id = :department_id",
            "no_department_id" => " AND department_id != :no_department_id",
            "role_id" => " AND role_id = :role_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY user_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.staff.staff_id, {$this->branch_schema}.staff.user_id,
                    {$this->branch_schema}.staff.staff_name, {$this->branch_schema}.staff.serial_name,
                    {$this->branch_schema}.user_role.id user_role_id,
                    {$this->branch_schema}.user_role.role_id, {$this->branch_schema}.user_role.department_id
                    {$customize_select}
                    FROM {$this->branch_schema}.staff
                    LEFT JOIN {$this->branch_schema}.user_role ON {$this->branch_schema}.staff.user_id = {$this->branch_schema}.user_role.user_id
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
            $result['status'] = 'success';
            return $result;
        } else {
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function user_role_insert($datas)
    {
        foreach ($datas as $row => $column) {
            $this->user_role_delete($column);
            foreach ($column['user_id'] as $per_user_id) {
                $user_role_insert_cond = "";
                $user_role_values_cond = "";

                $per_user_role_bind_values = [
                    "user_id" => null,
                    "role_id" => null,
                    "department_id" => null
                ];

                foreach ($per_user_role_bind_values as $key => $value) {
                    if (array_key_exists($key, $column)) {
                        if ($key == 'user_id') {
                            $per_user_role_bind_values[$key] = $per_user_id;
                            $user_role_insert_cond .= "{$key},";
                            $user_role_values_cond .= ":{$key},";
                        } else {
                            $per_user_role_bind_values[$key] = $column[$key];
                            $user_role_insert_cond .= "{$key},";
                            $user_role_values_cond .= ":{$key},";
                        }
                    }
                }
                $user_role_insert_cond = rtrim($user_role_insert_cond, ',');
                $user_role_values_cond = rtrim($user_role_values_cond, ',');

                $sql = "INSERT INTO {$this->branch_schema}.user_role({$user_role_insert_cond})
                        VALUES ({$user_role_values_cond})
                        RETURNING id
                    ";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute($per_user_role_bind_values)) {
                    $user_role_id = $stmt->fetchColumn(0);
                    $column['user_role_id'] = $user_role_id;
                    $result = ['status' => 'success'];
                } else {
                    var_dump($stmt->errorInfo());
                }
            }
        }
        return $result;
    }

    public function user_role_delete($datas)
    {
        $per_user_role_bind_values = [
            "role_id" => "",
            "department_id" => "",
        ];
        foreach ($per_user_role_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_user_role_bind_values[$key] = $datas[$key];
            }
        }

        $sql = "DELETE FROM {$this->branch_schema}.user_role
                WHERE {$this->branch_schema}.user_role.role_id = :role_id
                AND {$this->branch_schema}.user_role.department_id = :department_id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_user_role_bind_values)) {
            $result = ['status' => 'success'];
        } else {
            var_dump($stmt->errorInfo());
        }
        return $result;
    }
    public function get_staff_self($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "staff_id" => null,
            "user_id" => null,
        ];
        $custom_filter_bind_values = [
            "staff_id" => null,
            "user_id" => null,
            "staff_name" => null,
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
            "staff_id" => " AND staff_id = :staff_id",
            "user_id" => " AND user_id = :user_id",
            "staff_name" => " AND staff_name = :staff_name",
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


        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY staff_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.staff.staff_id, {$this->branch_schema}.staff.staff_name,
                    {$this->branch_schema}.staff.serial_name staff_serial_name, {$this->branch_schema}.staff.address,
                    {$this->branch_schema}.gender.gender_id, {$this->branch_schema}.gender.gender_name,
                    {$this->branch_schema}.staff.position_name, {$this->branch_schema}.staff.user_id,
                    {$this->branch_schema}.staff.is_login, {$this->branch_schema}.staff.staff_english_name,
                    to_char({$this->branch_schema}.staff.last_edit_time, 'yyyy-MM-dd HH-mi')last_edit_time,
                    {$this->branch_schema}.staff.last_edit_user_id,{$this->branch_schema}.staff.custom_line_id,
                    {$this->branch_schema}.staff.property_json,
                    last_edit_user.name last_edit_user_name,
                    \"system\".user.uid serial_name,
                    \"system\".user.email, CASE WHEN \"system\".user.line_user_id IS NOT NULL THEN 1 ELSE 0 END have_line_token,
                    {$this->branch_schema}.staff.create_user_id, {$this->branch_schema}.staff.create_time,
                    create_staff.staff_name create_user_name,
                    COALESCE(user_department_role_language_data.department_id_arr,'{}') department_id_arr,
                    COALESCE(user_department_role_language_data.role_id_arr,'{}') role_id_arr,
                    COALESCE(user_department_role_data.user_department_role_data, '[]')user_department_role_data,
                    COALESCE(user_department_role_language_data.user_department_role_data, '[]')user_department_role_language_data,
                    COALESCE(notify_preference_data.notify_preference_data, '[]')notify_preference_data,
                    {$this->branch_schema}.staff.employment_time_start , {$this->branch_schema}.staff.employment_time_end
                    {$customize_select}, COALESCE(user_external_token_data.user_external_token_data, '[]')user_external_token_data
                    FROM {$this->branch_schema}.staff
                    LEFT JOIN {$this->branch_schema}.staff create_staff ON {$this->branch_schema}.staff.create_user_id = create_staff.user_id
                    LEFT JOIN {$this->branch_schema}.gender ON {$this->branch_schema}.staff.gender_id = {$this->branch_schema}.gender.gender_id
                    LEFT JOIN \"system\".user ON {$this->branch_schema}.staff.user_id = \"system\".user.id
                    LEFT JOIN \"system\".user last_edit_user ON {$this->branch_schema}.staff.last_edit_user_id = last_edit_user.id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.staff_notify_preference.staff_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'notify_preference_id', {$this->branch_schema}.notify_preference.notify_preference_id,
                                'notify_preference_name', {$this->branch_schema}.notify_preference.notify_preference_name
                            )
                        )notify_preference_data
                        FROM {$this->branch_schema}.staff_notify_preference
                        LEFT JOIN {$this->branch_schema}.notify_preference ON {$this->branch_schema}.staff_notify_preference.notify_preference_id = {$this->branch_schema}.notify_preference.notify_preference_id
                        GROUP BY {$this->branch_schema}.staff_notify_preference.staff_id
                    )notify_preference_data ON {$this->branch_schema}.staff.staff_id = notify_preference_data.staff_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.user_role.user_id,
                            dt.department_ids department_id_arr,
                            ARRAY_AGG(role_dt.parent_id) role_id_arr,
                            JSON_AGG(
                                DISTINCT JSON_BUILD_OBJECT(
                                    'user_role_id', {$this->branch_schema}.user_role.id,
                                    'department_id', department_language_data.department_id,
                                    'department_classify_structure_type_id', department_language_data.classify_structure_type_id,
                                    'department_name_language_data', COALESCE(department_language_data.department_language_data, '[]'),
                                    'role_id', role_language_data.role_id,
                                    'role_classify_structure_type_id', role_language_data.classify_structure_type_id,
                                    'role_name_language_data', COALESCE(role_language_data.role_language_data, '[]')
                                )::JSONB
                            )user_department_role_data
                        FROM {$this->branch_schema}.user_role
                        LEFT JOIN {$this->branch_schema}.all_department dt ON {$this->branch_schema}.user_role.department_id = dt.parent_id
                        LEFT JOIN {$this->branch_schema}.all_role role_dt ON {$this->branch_schema}.user_role.role_id = role_dt.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', role_language_json.language_manage_id,
                                    'language_culture_code', role_language_json.language_culture_code,
                                    'language_culture_value', role_language_json.language_value
                                )
                            )role_language_data
                            FROM {$this->branch_schema}.\"role\"
                            LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                            ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                            GROUP BY {$this->branch_schema}.\"role\".id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                        )role_language_data ON {$this->branch_schema}.user_role.role_id = role_language_data.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.department.department_id,
                            -- SELECT {$this->branch_schema}.department_role.department_id,
                            -- {$this->branch_schema}.department_role.role_id,
                            {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', department_language_json.language_manage_id,
                                    'language_culture_code', department_language_json.language_culture_code,
                                    'language_culture_value', department_language_json.language_value
                                )
                            )department_language_data
                            -- FROM {$this->branch_schema}.department_role
                            -- LEFT JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department_role.department_id = {$this->branch_schema}.department.department_id
                            FROM {$this->branch_schema}.department
                            LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                            )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.department.department_id
                            -- GROUP BY {$this->branch_schema}.department_role.department_id, {$this->branch_schema}.department_role.role_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                            GROUP BY {$this->branch_schema}.department.department_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                        )department_language_data ON department_language_data.department_id = ANY(dt.department_ids)
                        GROUP BY {$this->branch_schema}.user_role.user_id,dt.department_ids
                    )user_department_role_language_data ON {$this->branch_schema}.staff.user_id = user_department_role_language_data.user_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.user_role.user_id,
                        JSON_AGG(
                            DISTINCT JSON_BUILD_OBJECT(
                                'user_role_id', {$this->branch_schema}.user_role.id,
                                'department_id', department_language_data.department_id,
                                'department_classify_structure_type_id', department_language_data.classify_structure_type_id,
                                'department_name_language_data', COALESCE(department_language_data.department_language_data, '[]'),
                                'all_department_data', {$this->branch_schema}.all_department_depth.all_department_data,
                                'role_id', role_language_data.role_id,
                                'role_classify_structure_type_id', role_language_data.classify_structure_type_id,
                                'role_name_language_data', COALESCE(role_language_data.role_language_data, '[]')
                            )::JSONB
                        )user_department_role_data
                        FROM {$this->branch_schema}.user_role
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', role_language_json.language_manage_id,
                                    'language_culture_code', role_language_json.language_culture_code,
                                    'language_culture_value', role_language_json.language_value
                                )
                            )role_language_data
                            FROM {$this->branch_schema}.\"role\"
                            LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                            ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                            GROUP BY {$this->branch_schema}.\"role\".id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                        )role_language_data ON {$this->branch_schema}.user_role.role_id = role_language_data.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.department.department_id,
                            -- SELECT {$this->branch_schema}.department_role.department_id,
                            -- {$this->branch_schema}.department_role.role_id,
                            {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', department_language_json.language_manage_id,
                                    'language_culture_code', department_language_json.language_culture_code,
                                    'language_culture_value', department_language_json.language_value
                                )
                            )department_language_data
                            -- FROM {$this->branch_schema}.department_role
                            -- LEFT JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department_role.department_id = {$this->branch_schema}.department.department_id
                            FROM {$this->branch_schema}.department
                            LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                            )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.department.department_id
                            -- GROUP BY {$this->branch_schema}.department_role.department_id, {$this->branch_schema}.department_role.role_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                            GROUP BY {$this->branch_schema}.department.department_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                        )department_language_data ON department_language_data.department_id = {$this->branch_schema}.user_role.department_id
                        LEFT JOIN {$this->branch_schema}.all_department_depth ON {$this->branch_schema}.all_department_depth.department_id = {$this->branch_schema}.user_role.department_id
                        GROUP BY {$this->branch_schema}.user_role.user_id
                    )user_department_role_data ON {$this->branch_schema}.staff.user_id = user_department_role_data.user_id
                    LEFT JOIN (
                        SELECT id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'is_google_bound', is_google_bound,
                                    'is_apple_bound', is_apple_bound,
                                    'is_line_bound', is_line_bound
                                )
                            )user_external_token_data
                        FROM
                        (
                            SELECT
                                \"user\".id,
                                CASE
                                    WHEN COUNT(CASE WHEN user_external_token.external_token_type_id = 3 AND user_external_token.user_external_token_token IS NOT NULL THEN 1 END) > 0 THEN 1
                                    ELSE 0
                                END AS is_google_bound,
                                CASE
                                    WHEN COUNT(CASE WHEN user_external_token.external_token_type_id = 5 AND user_external_token.user_external_token_token IS NOT NULL THEN 1 END) > 0 THEN 1
                                    ELSE 0
                                END AS is_apple_bound,
                                CASE
                                    WHEN \"user\".line_user_id IS NOT NULL AND \"user\".line_notify_token IS NOT NULL THEN 1
                                    ELSE 0
                                END AS is_line_bound
                            FROM system.\"user\"
                            LEFT JOIN system.user_external_token ON \"user\".id = user_external_token.user_id
                            GROUP BY \"user\".id
                        )AS external_token_data
                        GROUP BY id
                    )user_external_token_data ON user_external_token_data.id = {$this->branch_schema}.staff.user_id
                    
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function get_staff_diverge($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "staff_id" => null,
            "user_id" => null,
            "staff_name" => null,
        ];
        $custom_filter_bind_values = [
            "staff_id" => null,
            "staff_name" => null,
            "uid" => null,
            "employment_time_start" => null,
            "employment_time_end" => null,
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
            "staff_id" => " AND staff_id = :staff_id",
            "user_id" => " AND user_id = :user_id",
            "staff_name" => " AND staff_name = :staff_name",
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
        $order = 'ORDER BY staff_id';
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


        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
            FROM(
                SELECT {$this->branch_schema}.staff.staff_id, {$this->branch_schema}.staff.staff_name,
                {$this->branch_schema}.staff.serial_name, {$this->branch_schema}.staff.address,
                {$this->branch_schema}.gender.gender_id, {$this->branch_schema}.gender.gender_name,
                {$this->branch_schema}.staff.position_name, {$this->branch_schema}.staff.user_id,
                {$this->branch_schema}.staff.is_login, {$this->branch_schema}.staff.staff_english_name,
                \"system\".user.email, 
                CASE WHEN \"system\".user.line_user_id IS NOT NULL THEN 1 ELSE 0 END have_line_token, \"system\".user.uid,
                {$this->branch_schema}.staff.staff_name \"name\",
                user_role.\"position\", user_role.position_type,
                user_permission_data.permission_time_start employment_time_start, user_permission_data.permission_time_end employment_time_end
                , '[]' file_id,
                COALESCE(notify_preference_data.notify_preference_data, '[]')notify_preference_data,
                COALESCE(user_permission_data.user_permission_data, '[]')user_permission_data,
                COALESCE(permission_data.permission_data, '[]')permission_data
                {$customize_select}
                FROM {$this->branch_schema}.staff
                LEFT JOIN {$this->branch_schema}.gender ON {$this->branch_schema}.staff.gender_id = {$this->branch_schema}.gender.gender_id
                INNER JOIN \"system\".user ON {$this->branch_schema}.staff.user_id = \"system\".user.id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.staff_notify_preference.staff_id,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'notify_preference_id', {$this->branch_schema}.notify_preference.notify_preference_id,
                            'notify_preference_name', {$this->branch_schema}.notify_preference.notify_preference_name
                        )
                    )notify_preference_data
                    FROM {$this->branch_schema}.staff_notify_preference
                    LEFT JOIN {$this->branch_schema}.notify_preference ON {$this->branch_schema}.staff_notify_preference.notify_preference_id = {$this->branch_schema}.notify_preference.notify_preference_id
                    GROUP BY {$this->branch_schema}.staff_notify_preference.staff_id
                )notify_preference_data ON {$this->branch_schema}.staff.staff_id = notify_preference_data.staff_id
                LEFT JOIN (
                    SELECT {$this->branch_schema}.user_role.user_id,
                        STRING_AGG(role_language_data.\"position\",',') \"position\",
                        STRING_AGG(department_language_data.\"position_type\",',') \"position_type\"
                    FROM {$this->branch_schema}.user_role
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                        role_language_json.language_value \"position\"
                        FROM {$this->branch_schema}.\"role\"
                        LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                        ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                    )role_language_data ON {$this->branch_schema}.user_role.role_id = role_language_data.role_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.department_role.department_id,
                        {$this->branch_schema}.department_role.role_id,
                        {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id,
                        department_language_json.language_value position_type
                        FROM {$this->branch_schema}.department_role
                        LEFT JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department_role.department_id = {$this->branch_schema}.department.department_id
                        LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                        )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.department.department_id
                    )department_language_data ON {$this->branch_schema}.user_role.department_id = department_language_data.department_id AND {$this->branch_schema}.user_role.role_id = department_language_data.role_id
                    GROUP BY {$this->branch_schema}.user_role.user_id
                )user_role ON \"system\".user.id = user_role.user_id
                LEFT JOIN (
                    SELECT permission_management.user_permission.user_id,
                        MIN(to_char(permission_management.user_permission.permission_time_start, 'yyyy-MM-dd'))permission_time_start,
                        MAX(to_char(permission_management.user_permission.permission_time_end, 'yyyy-MM-dd'))permission_time_end,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'user_permission_id', permission_management.user_permission.id,
                                'permission_level_id', permission_management.user_permission.permission_level_id,
                                'permission_time_start', to_char(permission_management.user_permission.permission_time_start, 'yyyy-MM-dd'),
                                'permission_time_end', to_char(permission_management.user_permission.permission_time_end, 'yyyy-MM-dd'),
                                'permission_id', permission_management.permission.id,
                                'permission_name', permission_management.permission.\"name\",
                                'permission_url', permission_management.permission.\"url\",
                                'permission_index', permission_management.permission.\"index\",
                                'permission_is_default', permission_management.permission.is_default
                            )
                        )user_permission_data
                    FROM permission_management.user_permission
                    LEFT JOIN permission_management.permission ON permission_management.user_permission.permission_id = permission_management.permission.id
                    GROUP BY permission_management.user_permission.user_id
                )user_permission_data ON \"system\".user.id = user_permission_data.user_id
                LEFT JOIN (
                    SELECT user_permission_group_data.user_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'permission_group_id',permission_management.permission_group.id,
                                'permission_group_name',permission_management.permission_group.name,
                                'permission_data',COALESCE(user_permission_group_data.user_permission_group_data,'[]')
                            )
                            ORDER BY permission_management.permission_group.id
                        )permission_data
                    FROM (
                        SELECT permission_management.user_permission.user_id,
                            permission_management.permission.permission_group_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'user_permission_id', permission_management.user_permission.id,
                                    'permission_level_id', permission_management.user_permission.permission_level_id,
                                    'permission_time_start', to_char(permission_management.user_permission.permission_time_start, 'yyyy-MM-dd'),
                                    'permission_time_end', to_char(permission_management.user_permission.permission_time_end, 'yyyy-MM-dd'),
                                    'permission_id', permission_management.permission.id,
                                    'permission_name', permission_management.permission.\"name\",
                                    'url', permission_management.permission.\"url\",
                                    'permission_index', permission_management.permission.\"index\",
                                    'permission_is_default', permission_management.permission.is_default
                                )
                                ORDER BY permission_management.permission.index
                            )user_permission_group_data
                        FROM permission_management.user_permission
                        LEFT JOIN permission_management.permission ON permission_management.user_permission.permission_id = permission_management.permission.id
                        GROUP BY permission_management.user_permission.user_id,permission_management.permission.permission_group_id
                    )user_permission_group_data
                    LEFT JOIN permission_management.permission_group ON user_permission_group_data.permission_group_id = permission_management.permission_group.id
                    GROUP BY user_permission_group_data.user_id
                )permission_data ON \"system\".user.id = permission_data.user_id
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function patch_staff_diverge($data)
    {
        foreach ($data as $row => $column) {
            $permission_management = new permission_management($this->db);
            $column['role_permission'] = $column['user_permission_data'];
            $return = $permission_management->post_permission_manage([$column]);
            $return['status'] = 'success';
            return $return;
        }
    }
    //這裡下面staff的name沒有跟著規則走，之後記得改
    public function get_staff($params)
    {
        //先判斷是否是匯出使用
        if (array_key_exists('excel', $params)) {
            unset($params['excel']);
            $excel_check = true;
        }

        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "staff_id" => null,
            "user_id" => null,
            "department_id" => null,
            "department_id_arr" => null,
            "role_id" => null,
            "role_id_arr" => null,
            "staff_id_arr" => null,
        ];
        $custom_filter_bind_values = [
            "staff_serial_name" => null,
            "user_department_role_data" => null,
            "staff_name" => null,
            "staff_english_name" => null,
            "gender_name" => null,
            "email" => null,
            "address" => null,
            "create_user_name" => null,
            "create_time" => null,
            "last_edit_user_name" => null,
            "last_edit_time" => null,
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
            "staff_id" => " AND staff_id = :staff_id",
            "user_id" => " AND user_id = :user_id",
            "staff_name" => " AND staff_name = :staff_name",
            "department_id" => " AND :department_id = ANY(department_id_arr)",
            "department_id_arr" => " AND :department_id_arr && department_id_arr",
            "role_id" => " AND :role_id = ANY(role_id_arr)",
            "role_id_arr" => " AND :role_id_arr && role_id_arr",
            "staff_serial_name" => " AND CAST(\"staff_serial_name\" AS TEXT) LIKE '%' || :staff_serial_name || '%' ",
            "user_department_role_data" => " AND CAST(\"user_department_role_data\" AS TEXT) LIKE '%' || :user_department_role_data || '%' ",
            "staff_name" => " AND CAST(\"staff_name\" AS TEXT) LIKE '%' || :staff_name || '%' ",
            "staff_english_name" => " AND CAST(\"staff_english_name\" AS TEXT) LIKE '%' || :staff_english_name || '%' ",
            "gender_name" => " AND CAST(\"gender_name\" AS TEXT) LIKE '%' || :gender_name || '%' ",
            "email" => " AND CAST(\"email\" AS TEXT) LIKE '%' || :email || '%' ",
            "address" => " AND CAST(\"address\" AS TEXT) LIKE '%' || :address || '%' ",
            "create_user_name" => " AND CAST(\"create_user_name\" AS TEXT) LIKE '%' || :create_user_name || '%' ",
            "create_time" => " AND CAST(\"create_time\" AS TEXT) LIKE '%' || :create_time || '%' ",
            "last_edit_user_name" => " AND CAST(\"last_edit_user_name\" AS TEXT) LIKE '%' || :last_edit_user_name || '%' ",
            "last_edit_time" => " AND CAST(\"last_edit_time\" AS TEXT) LIKE '%' || :last_edit_time || '%' ",
            "staff_id_arr" => "",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                //匯出勾選的內容 - 資料處理
                if ($key == "staff_id_arr" && count($params[$key]) != 0) {
                    $condition .= " AND staff_id IN (";
                    foreach ($params[$key] as $staff_id_arr_index => $staff_id_arr_value) {
                        $condition .= " $staff_id_arr_value,";
                    }
                    $condition = rtrim($condition, ',');
                    $condition .= ")";
                    unset($bind_values[$key]);
                } else {
                    $condition .= $value;
                }
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


        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY staff_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.staff.staff_id, {$this->branch_schema}.staff.staff_name,
                    \"system\".user.uid staff_serial_name, {$this->branch_schema}.staff.address,
                    {$this->branch_schema}.gender.gender_id, {$this->branch_schema}.gender.gender_name,
                    {$this->branch_schema}.staff.position_name, {$this->branch_schema}.staff.user_id,
                    {$this->branch_schema}.staff.is_login, {$this->branch_schema}.staff.staff_english_name,
                    to_char({$this->branch_schema}.staff.last_edit_time, 'YYYY-MM-DD HH:mi')last_edit_time,
                    {$this->branch_schema}.staff.last_edit_user_id,{$this->branch_schema}.staff.custom_line_id,
                    {$this->branch_schema}.staff.property_json,{$this->branch_schema}.staff.staff_phone,
                    last_edit_user.name last_edit_user_name,
                    \"system\".user.uid serial_name,
                    \"system\".user.email, CASE WHEN \"system\".user.line_user_id IS NOT NULL THEN 1 ELSE 0 END have_line_token,
                    {$this->branch_schema}.staff.create_user_id,
                    to_char({$this->branch_schema}.staff.create_time, 'YYYY-MM-DD HH:mi')create_time, 
                    create_staff.staff_name create_user_name,
                    COALESCE(user_department_role_language_data.department_id_arr,'{}') department_id_arr,
                    COALESCE(user_department_role_language_data.role_id_arr,'{}') role_id_arr,
                    COALESCE(user_department_role_data.user_department_role_data, '[]')user_department_role_data,
                    COALESCE(user_department_role_language_data.user_department_role_data, '[]')user_department_role_language_data,
                    COALESCE(notify_preference_data.notify_preference_data, '[]')notify_preference_data,
                    {$this->branch_schema}.staff.employment_time_start , {$this->branch_schema}.staff.employment_time_end
                    {$customize_select}, COALESCE(user_external_token_data.user_external_token_data, '[]')user_external_token_data
                    FROM {$this->branch_schema}.staff
                    LEFT JOIN {$this->branch_schema}.staff create_staff ON {$this->branch_schema}.staff.create_user_id = create_staff.user_id
                    LEFT JOIN {$this->branch_schema}.gender ON {$this->branch_schema}.staff.gender_id = {$this->branch_schema}.gender.gender_id
                    LEFT JOIN \"system\".user ON {$this->branch_schema}.staff.user_id = \"system\".user.id
                    LEFT JOIN \"system\".user last_edit_user ON {$this->branch_schema}.staff.last_edit_user_id = last_edit_user.id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.staff_notify_preference.staff_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'notify_preference_id', {$this->branch_schema}.notify_preference.notify_preference_id,
                                'notify_preference_name', {$this->branch_schema}.notify_preference.notify_preference_name
                            )
                        )notify_preference_data
                        FROM {$this->branch_schema}.staff_notify_preference
                        LEFT JOIN {$this->branch_schema}.notify_preference ON {$this->branch_schema}.staff_notify_preference.notify_preference_id = {$this->branch_schema}.notify_preference.notify_preference_id
                        GROUP BY {$this->branch_schema}.staff_notify_preference.staff_id
                    )notify_preference_data ON {$this->branch_schema}.staff.staff_id = notify_preference_data.staff_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.user_role.user_id,
                            dt.department_ids department_id_arr,
                            ARRAY_AGG(role_dt.parent_id) role_id_arr,
                            JSON_AGG(
                                DISTINCT JSON_BUILD_OBJECT(
                                    'user_role_id', {$this->branch_schema}.user_role.id,
                                    'department_id', department_language_data.department_id,
                                    'department_classify_structure_type_id', department_language_data.classify_structure_type_id,
                                    'department_name_language_data', COALESCE(department_language_data.department_language_data, '[]'),
                                    'role_id', role_language_data.role_id,
                                    'role_classify_structure_type_id', role_language_data.classify_structure_type_id,
                                    'role_name_language_data', COALESCE(role_language_data.role_language_data, '[]')
                                )::JSONB
                            )user_department_role_data
                        FROM {$this->branch_schema}.user_role
                        LEFT JOIN {$this->branch_schema}.all_department dt ON {$this->branch_schema}.user_role.department_id = dt.parent_id
                        LEFT JOIN {$this->branch_schema}.all_role role_dt ON {$this->branch_schema}.user_role.role_id = role_dt.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', role_language_json.language_manage_id,
                                    'language_culture_code', role_language_json.language_culture_code,
                                    'language_culture_value', role_language_json.language_value
                                )
                            )role_language_data
                            FROM {$this->branch_schema}.\"role\"
                            LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                            ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                            GROUP BY {$this->branch_schema}.\"role\".id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                        )role_language_data ON {$this->branch_schema}.user_role.role_id = role_language_data.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.department.department_id,
                            -- SELECT {$this->branch_schema}.department_role.department_id,
                            -- {$this->branch_schema}.department_role.role_id,
                            {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', department_language_json.language_manage_id,
                                    'language_culture_code', department_language_json.language_culture_code,
                                    'language_culture_value', department_language_json.language_value
                                )
                            )department_language_data
                            -- FROM {$this->branch_schema}.department_role
                            -- LEFT JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department_role.department_id = {$this->branch_schema}.department.department_id
                            FROM {$this->branch_schema}.department
                            LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                            )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.department.department_id
                            -- GROUP BY {$this->branch_schema}.department_role.department_id, {$this->branch_schema}.department_role.role_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                            GROUP BY {$this->branch_schema}.department.department_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                        )department_language_data ON department_language_data.department_id = ANY(dt.department_ids)
                        GROUP BY {$this->branch_schema}.user_role.user_id,dt.department_ids
                    )user_department_role_language_data ON {$this->branch_schema}.staff.user_id = user_department_role_language_data.user_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.user_role.user_id,
                        JSON_AGG(
                            DISTINCT JSON_BUILD_OBJECT(
                                'user_role_id', {$this->branch_schema}.user_role.id,
                                'department_id', department_language_data.department_id,
                                'department_classify_structure_type_id', department_language_data.classify_structure_type_id,
                                'department_name_language_data', COALESCE(department_language_data.department_language_data, '[]'),
                                'all_department_data', {$this->branch_schema}.all_department_depth.all_department_data,
                                'role_id', role_language_data.role_id,
                                'role_classify_structure_type_id', role_language_data.classify_structure_type_id,
                                'role_name_language_data', COALESCE(role_language_data.role_language_data, '[]'),
                                'user_role_department_data',COALESCE({$this->branch_schema}.user_role_department_data.user_role_department_data,'[]')
                            )::JSONB
                        )user_department_role_data
                        FROM {$this->branch_schema}.user_role
                        LEFT JOIN {$this->branch_schema}.user_role_department_data ON {$this->branch_schema}.user_role_department_data.user_role_id = {$this->branch_schema}.user_role.id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.\"role\".id role_id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', role_language_json.language_manage_id,
                                    'language_culture_code', role_language_json.language_culture_code,
                                    'language_culture_value', role_language_json.language_value
                                )
                            )role_language_data
                            FROM {$this->branch_schema}.\"role\"
                            LEFT JOIN {$this->branch_schema}.role_classify_structure_type ON {$this->branch_schema}.\"role\".id = {$this->branch_schema}.role_classify_structure_type.role_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                            ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                            GROUP BY {$this->branch_schema}.\"role\".id, {$this->branch_schema}.role_classify_structure_type.classify_structure_type_id
                        )role_language_data ON {$this->branch_schema}.user_role.role_id = role_language_data.role_id
                        LEFT JOIN (
                            SELECT {$this->branch_schema}.department.department_id,
                            -- SELECT {$this->branch_schema}.department_role.department_id,
                            -- {$this->branch_schema}.department_role.role_id,
                            {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'language_manage_id', department_language_json.language_manage_id,
                                    'language_culture_code', department_language_json.language_culture_code,
                                    'language_culture_value', department_language_json.language_value
                                )
                            )department_language_data
                            -- FROM {$this->branch_schema}.department_role
                            -- LEFT JOIN {$this->branch_schema}.department ON {$this->branch_schema}.department_role.department_id = {$this->branch_schema}.department.department_id
                            FROM {$this->branch_schema}.department
                            LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                            LEFT JOIN (
                                SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                language.language_manage.table_name, language.language_manage.column_name,
                                language.language_manage.table_primary_id, language.language_manage.language_value,
                                language.language_culture.language_culture_code
                                FROM language.language_manage
                                LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                            )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.department.department_id
                            -- GROUP BY {$this->branch_schema}.department_role.department_id, {$this->branch_schema}.department_role.role_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                            GROUP BY {$this->branch_schema}.department.department_id, {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id
                        )department_language_data ON department_language_data.department_id = {$this->branch_schema}.user_role.department_id
                        LEFT JOIN {$this->branch_schema}.all_department_depth ON {$this->branch_schema}.all_department_depth.department_id = {$this->branch_schema}.user_role.department_id
                        GROUP BY {$this->branch_schema}.user_role.user_id
                    )user_department_role_data ON {$this->branch_schema}.staff.user_id = user_department_role_data.user_id
                    LEFT JOIN (
                            SELECT id,
                                JSON_AGG(
                                    JSON_BUILD_OBJECT(
                                        'is_google_bound', is_google_bound,
                                        'is_apple_bound', is_apple_bound,
                                        'is_line_bound', is_line_bound
                                    )
                                )user_external_token_data
                            FROM
                            (
                                SELECT
                                    \"user\".id,
                                    CASE
                                        WHEN COUNT(CASE WHEN user_external_token.external_token_type_id = 3 AND user_external_token.user_external_token_token IS NOT NULL THEN 1 END) > 0 THEN 1
                                        ELSE 0
                                    END AS is_google_bound,
                                    CASE
                                        WHEN COUNT(CASE WHEN user_external_token.external_token_type_id = 5 AND user_external_token.user_external_token_token IS NOT NULL THEN 1 END) > 0 THEN 1
                                        ELSE 0
                                    END AS is_apple_bound,
                                    CASE
                                        WHEN \"user\".line_user_id IS NOT NULL AND \"user\".line_notify_token IS NOT NULL THEN 1
                                        ELSE 0
                                    END AS is_line_bound
                                FROM system.\"user\"
                                LEFT JOIN system.user_external_token ON \"user\".id = user_external_token.user_id
                                GROUP BY \"user\".id
                            )AS external_token_data
                            GROUP BY id
                        )user_external_token_data ON user_external_token_data.id = {$this->branch_schema}.staff.user_id
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
        // 匯出獨立判斷不走原本公版
        if ($excel_check) {
            //對應你SELECT出來column，我稱為解碼器
            $values = [
                "serial_name" => "編號(系統帳號)",
                "user_department_role_data" => "單位 - 職稱data",
                "staff_name" => "姓名",
                "staff_english_name" => "英文名",
                "gender_name" => "性別（男1女2）",
                "email" => "Email",
                "address" => "地址",
                "property_json" => "更多資訊data",
            ];
            $excel_column = "";
            foreach ($values as $key => $value) {
                $label = $values[$key];
                $excel_column .= "COALESCE(\"{$key}\"::text, '-') \"{$label}\","; //強制轉型所有欄位為TEXT，以方便後續使用
            }
            $excel_column = rtrim($excel_column, ',');
            $sql_excel = "SELECT {$excel_column}
                        FROM(
                            {$sql_default}
                        )db
                    ";
            $stmt = $this->db->prepare($sql_excel);
            if ($stmt->execute($values_count)) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row_id => $row_value) {
                    foreach ($row_value as $key => $value) {
                        if ($this->isJson($value)) {
                            $result[$row_id][$key] = json_decode($value, true);
                        }
                    }

                    $result[$row_id]['更多資訊'] = '';
                    foreach ($result[$row_id]['更多資訊data'] as $key => $value) {
                        $result[$row_id]['更多資訊'] .= "{$key} - {$value}、";
                    }
                    $result[$row_id]['更多資訊'] = rtrim($result[$row_id]['更多資訊'], '、');
                    foreach ($result[$row_id]['單位 - 職稱data'] as $index => $column) {
                        $columnIndex = $index + 1;
                        $department_string = reset($column['department_name_language_data'])['language_culture_value'];
                        $role_string = reset($column['role_name_language_data'])['language_culture_value'];
                        $result[$row_id]['單位 - 職稱' . $columnIndex . '（可自行新增，新增依序為：單位 - 職稱2、單位 - 職稱3）'] = "{$department_string} - {$role_string}";
                    }
                    //最後把這種一對多的欄位清掉
                    unset($result[$row_id]['單位 - 職稱data']);
                    unset($result[$row_id]['更多資訊data']);
                }
                return $result;
            } else {
                return [
                    "status" => "failed",
                    "message" => $stmt->errorInfo()
                ];
            }
        }

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

            // if (isset($params['serial_name'])) {
            //     $ldap = $this->container->ldap;
            //     $sr = ldap_search($ldap['conn'], $ldap['dn'], '(uid=' . $params['serial_name'] . ')');
            //     $info = ldap_get_entries($ldap['conn'], $sr);
            //     $result['ldap'] = false;
            //     if ($info['count'] !== 0) {
            //         $result['ldap'] = true;
            //     }
            // }
            $result['total'] = $result_count;
            return $result;
        } else {
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }


    public function get_statistics_staff_gender($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";
        $custom_filter_bind_values = [];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [];

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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY type) \"key\"
                        FROM(
                            -- SELECT
                            --     EXTRACT(YEAR FROM employment_time_start) AS year,
                            --     COUNT(CASE WHEN gender_id = 1 AND (employment_time_end IS NULL OR employment_time_end > NOW()) THEN 1 END) AS male_count,
                            --     COUNT(CASE WHEN gender_id = 2 AND (employment_time_end IS NULL OR employment_time_end > NOW()) THEN 1 END) AS female_count
                            -- FROM
                            --     organization_structure.staff
                            -- GROUP BY year
                            -- ORDER BY year
                            SELECT {$this->branch_schema}.gender.gender_name,
                                {$this->branch_schema}.gender.gender_name type,
                                COALESCE(staff_data.count_user,0) count_user,
                                COALESCE(staff_data.count_user,0) value
                            FROM {$this->branch_schema}.gender
                            LEFT JOIN(
                                SELECT {$this->branch_schema}.staff.gender_id,
                                    COUNT(DISTINCT {$this->branch_schema}.staff.user_id) count_user
                                FROM {$this->branch_schema}.staff
                                GROUP BY {$this->branch_schema}.staff.gender_id
                            )staff_data ON {$this->branch_schema}.gender.gender_id = staff_data.gender_id
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

    public function get_statistics_staff_role($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";
        $custom_filter_bind_values = [];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [];

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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY type) \"key\"
                        FROM(
                            -- SELECT
                            --     EXTRACT(YEAR FROM employment_time_start) AS year,
                            --     COUNT(CASE WHEN gender_id = 1 AND (employment_time_end IS NULL OR employment_time_end > NOW()) THEN 1 END) AS male_count,
                            --     COUNT(CASE WHEN gender_id = 2 AND (employment_time_end IS NULL OR employment_time_end > NOW()) THEN 1 END) AS female_count
                            -- FROM
                            --     organization_structure.staff
                            -- GROUP BY year
                            -- ORDER BY year
                            SELECT role_language_data.role_name,
                                role_language_data.role_name type,
                                COALESCE(staff_data.count_user,0) count_user,
                                COALESCE(staff_data.count_user,0) value, 
                                COALESCE(role_language_data.role_language_data, '[]')role_language_data
                            FROM {$this->branch_schema}.role
                            LEFT JOIN (
                                SELECT {$this->branch_schema}.\"role\".id role_id,
                                STRING_AGG(CASE WHEN role_language_json.language_culture_code='zh-tw' THEN role_language_json.language_value ELSE '' END,'、') role_name,
                                JSON_AGG(
                                    JSON_BUILD_OBJECT(
                                        'language_manage_id', role_language_json.language_manage_id,
                                        'language_culture_code', role_language_json.language_culture_code,
                                        'language_culture_value', role_language_json.language_value
                                    )
                                )role_language_data
                                FROM {$this->branch_schema}.\"role\"
                                LEFT JOIN (
                                    SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                    language.language_manage.table_name, language.language_manage.column_name,
                                    language.language_manage.table_primary_id, language.language_manage.language_value,
                                    language.language_culture.language_culture_code
                                    FROM language.language_manage
                                    LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                    WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                                )role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".id
                                GROUP BY {$this->branch_schema}.\"role\".id
                            )role_language_data ON {$this->branch_schema}.role.id = role_language_data.role_id
                            LEFT JOIN(
                                SELECT {$this->branch_schema}.user_role.role_id,
                                    COUNT(DISTINCT {$this->branch_schema}.user_role.user_id) count_user
                                FROM {$this->branch_schema}.user_role
                                GROUP BY {$this->branch_schema}.user_role.role_id
                            )staff_data ON {$this->branch_schema}.role.id = staff_data.role_id
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

    public function get_statistics_staff_department($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "depth" => 2
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";
        $custom_filter_bind_values = [];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                if ($key === "depth") {
                } else {
                    unset($bind_values[$key]);
                }
            }
        }

        $condition = "";
        $condition_values = [
            "depth" => " AND \"depth\" = :depth",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                if ($key === "depth") {
                    $condition .= $value;
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY type) \"key\"
                        FROM(
                            -- SELECT
                            --     EXTRACT(YEAR FROM employment_time_start) AS year,
                            --     COUNT(CASE WHEN gender_id = 1 AND (employment_time_end IS NULL OR employment_time_end > NOW()) THEN 1 END) AS male_count,
                            --     COUNT(CASE WHEN gender_id = 2 AND (employment_time_end IS NULL OR employment_time_end > NOW()) THEN 1 END) AS female_count
                            -- FROM
                            --     organization_structure.staff
                            -- GROUP BY year
                            -- ORDER BY year
                            SELECT {$this->branch_schema}.department.department_id,
                                department_language_data.department_name,
                                department_language_data.department_name type,
                                COALESCE(SUM(staff_data.count_user),0) count_user,
                                COALESCE(SUM(staff_data.count_user),0) value, 
                                COALESCE(department_language_data.department_language_data::JSONB, '[]')department_language_data,
                                {$this->branch_schema}.all_department.depth
                            FROM {$this->branch_schema}.department
                            LEFT JOIN {$this->branch_schema}.all_department ON {$this->branch_schema}.department.department_id = {$this->branch_schema}.all_department.parent_id
                            LEFT JOIN (
                                SELECT {$this->branch_schema}.\"department\".department_id,
                                STRING_AGG(CASE WHEN department_language_json.language_culture_code='zh-tw' THEN department_language_json.language_value ELSE '' END,'、') department_name,
                                JSON_AGG(
                                    JSON_BUILD_OBJECT(
                                        'language_manage_id', department_language_json.language_manage_id,
                                        'language_culture_code', department_language_json.language_culture_code,
                                        'language_culture_value', department_language_json.language_value
                                    )
                                )department_language_data
                                FROM {$this->branch_schema}.\"department\"
                                LEFT JOIN (
                                    SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                                    language.language_manage.table_name, language.language_manage.column_name,
                                    language.language_manage.table_primary_id, language.language_manage.language_value,
                                    language.language_culture.language_culture_code
                                    FROM language.language_manage
                                    LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                                    WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                                )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.\"department\".department_id
                                GROUP BY {$this->branch_schema}.\"department\".department_id
                            )department_language_data ON {$this->branch_schema}.department.department_id = department_language_data.department_id
                            LEFT JOIN(
                                SELECT {$this->branch_schema}.user_role.department_id,
                                    COUNT(DISTINCT {$this->branch_schema}.user_role.user_id) count_user,
                                    {$this->branch_schema}.all_department.department_ids
                                FROM {$this->branch_schema}.user_role
                                LEFT JOIN {$this->branch_schema}.all_department ON {$this->branch_schema}.user_role.department_id = {$this->branch_schema}.all_department.parent_id
                                GROUP BY {$this->branch_schema}.user_role.department_id,{$this->branch_schema}.all_department.department_ids
                            )staff_data ON {$this->branch_schema}.all_department.parent_id = ANY(staff_data.department_ids)
                            GROUP BY {$this->branch_schema}.department.department_id, department_language_data.department_name,
                                department_language_data.department_language_data::JSONB,{$this->branch_schema}.all_department.depth
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

    public function post_staff($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $staff_bind_values = [
                "staff_id" => 0,
                "user_id" => 0,
                "staff_serial_name" => null,
                "gender_id" => null,
                "position_name" => null,
                "staff_name" => null,
                "staff_english_name" => null,
                "staff_phone" => null,
                "address" => null,
                "last_edit_user_id" => 0,
                "create_user_id" => 0,
                "custom_line_id" => null,
                "property_json" => null,
                "last_edit_time" => "",
                "create_time" => "",
                "employment_time_start" => "",
                "employment_time_end" => null,
            ];

            $staff_insert_cond = "";
            $staff_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";
            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = "NOW()";
            $column['employment_time_start'] = "NOW()";

            foreach ($staff_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "staff_serial_name") {
                        $staff_insert_cond .= "serial_name,";
                    } else {
                        $staff_insert_cond .= "{$key},";
                    }
                    if ($key === "property_json") {
                        $staff_bind_values[$key] = json_encode($column[$key]);
                    } else {
                        $staff_bind_values[$key] = $column[$key];
                    }
                    $staff_values_cond .= ":{$key},";
                } else {
                    unset($staff_bind_values[$key]);
                }
            }

            $staff_insert_cond = rtrim($staff_insert_cond, ',');
            $staff_values_cond = rtrim($staff_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.staff({$staff_insert_cond})
                VALUES ({$staff_values_cond})
                RETURNING staff_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($staff_bind_values)) {
                $staff_id = $stmt_insert->fetchColumn(0);
                $column['staff_id'] = $staff_id;

                if (array_key_exists('staff_notify_preference_data', $column)) {
                    foreach ($column['staff_notify_preference_data'] as $staff_notify_preference_index => $staff_notify_preference_item) {
                        $staff_notify_preference_item['staff_id'] = $staff_id;
                        $this->staff_notify_preference_insert($staff_notify_preference_item);
                    }
                }

                if (array_key_exists('user_language_data', $column)) {
                    foreach ($column['user_language_data'] as $user_department_role_index => $user_department_role_item) {
                        $user_department_role_item['user_id'] = $column['user_id'];
                        $create_user_role = $this->create_user_role($column['user_id'], $user_department_role_item);
                        if ($create_user_role['status'] !== 'success') {
                            $result = ['create_user_role' => $create_user_role];
                        } else {
                            $user_language_data_value = $user_department_role_item;
                            $user_language_data_value['user_role_id'] = $create_user_role['user_role_id'];
                            if (isset($user_language_data_value['user_role_department_data'])) {
                                $last_edit_user_id = $_SESSION['id'];
                                $user_role_department_id_arr = [];
                                $user_role_department_id_arr['user_role_department_id_arr'] = [];
                                foreach ($user_language_data_value['user_role_department_data'] as $index => $user_role_department) {
                                    $user_role_department['user_role_id'] = $user_language_data_value['user_role_id'];
                                    $user_role_department_id_arr['user_role_id'] = $user_role_department['user_role_id'];
                                    $post_code_base = $this->post_code_base(
                                        [$user_role_department], //原本的送入POST的參數
                                        [
                                            "user_role_id" => "",
                                            "department_id" => "",
                                        ], //原本的bind_values
                                        $last_edit_user_id,
                                        $this->branch_schema, //客製化schema
                                        "user_role_department", //客製化table
                                        "user_role_department_id", //客製化回傳id
                                        $this->db, //客製化Db,
                                        [
                                            "user_role_id",
                                            "department_id"
                                        ]
                                    );

                                    if ($post_code_base['status'] === 'success') {
                                        array_push($user_role_department_id_arr['user_role_department_id_arr'], $post_code_base['user_role_department_id']);
                                    }
                                }
                            }
                        }
                    }
                }
                $result = ["status" => "success", "staff_id" => $staff_id, "user_id" => $column['user_id']];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt_insert->errorInfo()];
            }
        }
        return $result;
    }

    public function patch_staff($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $staff_bind_values = [
                "staff_id" => null,
                "user_id" => 0,
                "staff_name" => null,
                "staff_english_name" => null,
                "staff_phone" => null,
                "staff_serial_name" => null,
                "gender_id" => null,
                "position_name" => null,
                "address" => null,
                "custom_line_id" => null,
                "property_json" => null,
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "employment_time_start" => "",
                "employment_time_end" => null,
            ];

            $staff_upadte_cond = "";
            $staff_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($staff_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'staff_id') {
                        $staff_bind_values[$key] = $column[$key];
                    } else if ($key === "staff_serial_name") {
                        $staff_bind_values[$key] = $column[$key];
                        $staff_upadte_cond .= "serial_name = :{$key},";
                    } else {
                        if ($key === "property_json") {
                            $staff_bind_values[$key] = json_encode($column[$key]);
                        } else {
                            $staff_bind_values[$key] = $column[$key];
                        }
                        $staff_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($staff_bind_values[$key]);
                }
            }

            $staff_fliter_cond .= "AND {$this->branch_schema}.staff.staff_id = :staff_id";
            $staff_upadte_cond = rtrim($staff_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.staff
                    SET {$staff_upadte_cond}
                    WHERE TRUE {$staff_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($staff_bind_values)) {
                if (array_key_exists('staff_notify_preference_data', $column)) {
                    $this->staff_notify_preference_delete($column);
                    foreach ($column['staff_notify_preference_data'] as $staff_notify_preference_index => $staff_notify_preference_item) {
                        $staff_notify_preference_item['staff_id'] = $column['staff_id'];
                        $this->staff_notify_preference_insert($staff_notify_preference_item);
                    }
                } else {
                    $this->staff_notify_preference_delete($column);
                }

                if (array_key_exists('user_language_data', $column)) {
                    $delete_user_role = $this->delete_user_role($column);
                    if ($delete_user_role['status'] === 'success') {
                        foreach ($column['user_language_data'] as $user_language_data_key => $user_language_data_value) {
                            $create_user_role = $this->create_user_role($column['user_id'], $user_language_data_value);
                            if ($create_user_role['status'] !== 'success') {
                                $result = ['create_user_role' => $create_user_role];
                            } else {
                                $user_language_data_value['user_role_id'] = $create_user_role['user_role_id'];
                                if (isset($user_language_data_value['user_role_department_data'])) {
                                    $last_edit_user_id = $_SESSION['id'];
                                    $user_role_department_id_arr = [];
                                    $user_role_department_id_arr['user_role_department_id_arr'] = [];
                                    foreach ($user_language_data_value['user_role_department_data'] as $index => $user_role_department) {
                                        $user_role_department['user_role_id'] = $user_language_data_value['user_role_id'];
                                        $user_role_department_id_arr['user_role_id'] = $user_role_department['user_role_id'];
                                        $post_code_base = $this->post_code_base(
                                            [$user_role_department], //原本的送入POST的參數
                                            [
                                                "user_role_id" => "",
                                                "department_id" => "",
                                            ], //原本的bind_values
                                            $last_edit_user_id,
                                            $this->branch_schema, //客製化schema
                                            "user_role_department", //客製化table
                                            "user_role_department_id", //客製化回傳id
                                            $this->db, //客製化Db,
                                            [
                                                "user_role_id",
                                                "department_id"
                                            ]
                                        );
                                        if ($post_code_base['status'] === 'success') {
                                            array_push($user_role_department_id_arr['user_role_department_id_arr'], $post_code_base['user_role_department_id']);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $result = ['delete_user_role' => $delete_user_role];
                    }
                }
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_staff($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_staff_bind_values = [
                "staff_id" => "",
            ];

            foreach ($delete_staff_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_staff_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM {$this->branch_schema}.staff
                WHERE {$this->branch_schema}.staff.staff_id = :staff_id
            ";
            $stmt_delete_staff = $this->db->prepare($sql_delete);
            if ($stmt_delete_staff->execute($delete_staff_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function staff_notify_preference_select($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "notify_preference_id" => null,
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
            "notify_preference_id" => " AND notify_preference_id = :notify_preference_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $bind_values["start"] = $start;
        $bind_values["length"] = $length;

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY notify_preference_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.staff_notify_preference.staff_id,
                    JSON_AGG({$this->branch_schema}.staff_notify_preference.notify_preference_id)
                    FROM {$this->branch_schema}.staff_notify_preference
                    GROUP BY {$this->branch_schema}.staff_notify_preference.staff_id
                )dt
                WHERE TRUE {$condition}
        ";
        $sql = "SELECT *
            FROM(
                {$sql_default}
                LIMIT :length
            )dt
            WHERE \"key\" > :start
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function staff_notify_preference_insert($datas)
    {
        $staff_notify_preference_insert_cond = "";
        $staff_notify_preference_values_cond = "";

        $per_staff_notify_preference_bind_values = [
            "staff_id" => "",
            "notify_preference_id" => null,
        ];
        foreach ($per_staff_notify_preference_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_staff_notify_preference_bind_values[$key] = $datas[$key];
                $staff_notify_preference_insert_cond .= "{$key},";
                $staff_notify_preference_values_cond .= ":{$key},";
            }
        }
        $staff_notify_preference_insert_cond = rtrim($staff_notify_preference_insert_cond, ',');
        $staff_notify_preference_values_cond = rtrim($staff_notify_preference_values_cond, ',');

        $sql = "INSERT INTO {$this->branch_schema}.staff_notify_preference({$staff_notify_preference_insert_cond})
                VALUES ({$staff_notify_preference_values_cond})
                RETURNING id
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_staff_notify_preference_bind_values)) {
            $staff_notify_preference_id = $stmt->fetchColumn(0);
            $datas['staff_notify_preference_id'] = $staff_notify_preference_id;
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function staff_notify_preference_patch($datas)
    {
        $staff_notify_preference_update_cond = "";

        $per_staff_notify_preference_bind_values = [
            "staff_notify_preference_id" => null,
            "staff_id" => "",
            "notify_preference_id" => null,
        ];
        foreach ($per_staff_notify_preference_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                if ($key == 'staff_notify_preference_id') {
                    $per_staff_notify_preference_bind_values[$key] = $datas[$key];
                } else {
                    $per_staff_notify_preference_bind_values[$key] = $datas[$key];
                    $staff_notify_preference_update_cond .= "{$key} = :{$key},";
                }
            }
        }
        $staff_notify_preference_update_cond = rtrim($staff_notify_preference_update_cond, ',');

        $sql = "UPDATE {$this->branch_schema}.staff_notify_preference
                SET {$staff_notify_preference_update_cond}
                WHERE TRUE AND {$this->branch_schema}.staff_notify_preference.id = :staff_notify_preference_id
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_staff_notify_preference_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function staff_notify_preference_delete($datas)
    {
        $per_staff_notify_preference_bind_values = [
            "staff_id" => "",
        ];
        foreach ($per_staff_notify_preference_bind_values as $key => $value) {
            if (array_key_exists($key, $datas)) {
                $per_staff_notify_preference_bind_values[$key] = $datas[$key];
            }
        }

        $sql = "DELETE FROM {$this->branch_schema}.staff_notify_preference
                WHERE {$this->branch_schema}.staff_notify_preference.notify_preference_id IN(
                    SELECT {$this->branch_schema}.staff_notify_preference.notify_preference_id
                    FROM {$this->branch_schema}.staff_notify_preference
                    WHERE {$this->branch_schema}.staff_notify_preference.staff_id = :staff_id
                )
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($per_staff_notify_preference_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
        }
    }

    public function get_gender($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "gender_id" => null,
            "gender_name" => null,
        ];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";
        $custom_filter_bind_values = [
            "gender_id" => null,
            "gender_name" => null,
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
            "gender_id" => " AND gender_id = :gender_id",
            "gender_name" => " AND gender_name = :gender_name",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY gender_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema}.gender.gender_id, {$this->branch_schema}.gender.gender_name {$customize_select}
                    FROM {$this->branch_schema}.gender
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

    public function post_gender($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $gender_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "gender_name" => null,
            ];

            $gender_insert_cond = "";
            $gender_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($gender_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $gender_bind_values[$key] = $column[$key];
                    $gender_insert_cond .= "{$key},";
                    $gender_values_cond .= ":{$key},";
                } else {
                    unset($gender_bind_values[$key]);
                }
            }

            $gender_insert_cond = rtrim($gender_insert_cond, ',');
            $gender_values_cond = rtrim($gender_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.gender({$gender_insert_cond})
                VALUES ({$gender_values_cond})
                RETURNING gender_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($gender_bind_values)) {
                $gender_id = $stmt_insert->fetchColumn(0);
                $result = ["status" => "success", 'gender_id' => $gender_id];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt_insert->errorInfo()];
            }
        }
        return $result;
    }

    public function patch_gender($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $gender_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "gender_id" => null,
                "gender_name" => null,
            ];

            $gender_upadte_cond = "";
            $gender_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($gender_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'gender_id') {
                        $gender_bind_values[$key] = $column[$key];
                    } else {
                        $gender_bind_values[$key] = $column[$key];
                        $gender_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($gender_bind_values[$key]);
                }
            }

            $gender_fliter_cond .= "AND {$this->branch_schema}.gender.gender_id = :gender_id";
            $gender_upadte_cond = rtrim($gender_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.gender
                    SET {$gender_upadte_cond}
                    WHERE TRUE {$gender_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($gender_bind_values)) {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_gender($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_gender_bind_values = [
                "gender_id" => "",
            ];

            foreach ($delete_gender_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_gender_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM {$this->branch_schema}.gender
                WHERE {$this->branch_schema}.gender.gender_id = :gender_id
            ";
            $stmt_delete_gender = $this->db->prepare($sql_delete);
            if ($stmt_delete_gender->execute($delete_gender_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function create_user_role($user_id, $department_role)
    {
        if (array_key_exists('department_name', $department_role) && array_key_exists('role_name', $department_role)) {
            $bind_values = [
                'user_id' => $user_id === NULL ? 0 : $user_id,
                'department_name' => $department_role['department_name'],
                'role_name' =>  $department_role['role_name']
            ];
            $sql = "INSERT INTO {$this->branch_schema}.user_role(user_id, role_id, department_id)
                    VALUES (:user_id,(
                        SELECT {$this->branch_schema}.\"role\".role_id,
                        FROM {$this->branch_schema}.\"role\"
                        INNER JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'role'
                            AND language.language_manage.language_value = :role_name
                        )role_language_json ON role_language_json.table_primary_id = {$this->branch_schema}.\"role\".role_id
                    ),(
                        SELECT {$this->branch_schema}.\"department\".department_id,
                        FROM {$this->branch_schema}.\"department\"
                        INNER JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                            AND language.language_manage.language_value = :department_name
                        )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.\"department\".department_id
                    )
                    ON CONFLICT (user_id, role_id, department_id) 
                    DO UPDATE
                    SET user_id = EXCLUDED.user_id
                    RETURNING id
            ";
        } else {
            $bind_values = [
                'user_id' => $user_id === NULL ? 0 : $user_id,
                'department_id' => $department_role['department_id'] === NULL ? null : $department_role['department_id'],
                'role_id' => $department_role['role_id'] === NULL ? 0 : $department_role['role_id']
            ];
            $sql = "INSERT INTO {$this->branch_schema}.user_role(user_id, role_id, department_id)
                    VALUES (:user_id, :role_id, :department_id)
                    ON CONFLICT (user_id, role_id, department_id)
                    DO UPDATE
                    SET user_id = EXCLUDED.user_id
                    RETURNING id
            ";
        }
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($bind_values)) {
            $user_role_id = $stmt->fetchColumn(0);
            return ['status' => 'success', "user_role_id" => $user_role_id];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }

    public function delete_user_role($params)
    {
        $bind_values = ['user_id' => 0];
        foreach ($bind_values as $key => $value) {
            array_key_exists($key, $params) && ($bind_values[$key] = $params[$key]);
        }
        $sql = "DELETE FROM {$this->branch_schema}.user_role
                WHERE user_id = :user_id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($bind_values)) {
            return ['status' => 'success'];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }

    public function get_encode($role_id)
    {
        $role_encode_default = "";
        $role_table = "";
        $role_table_from = "";
        $serial_pad = 0;
        // $now_year = intval(date("Y")) - 1911;
        $now_year = 114;
        foreach ($role_id as $key => $value) {
            if ($value == 1) {
                $role_encode_default = "ADMINISTRARION";
                $role_table = "{$this->branch_schema}.staff_staff_id_seq";
                $serial_pad = 3;
            } else if ($value == 2) {
                $role_encode_default = "K";
                $role_table = "{$this->branch_schema}.staff_staff_id_seq";
                $serial_pad = 4;
            } else {
                $role_encode_default = "M{$now_year}";
                $role_table = "{$this->branch_schema}.staff_staff_id_seq";
                $serial_pad = 4;
            }
            $sql = "WITH max_id AS (
                        SELECT nextval('{$role_table}'::regclass) AS max_id_value
                    )
                    SELECT concat('{$role_encode_default}', lpad(max_id.max_id_value::text, {$serial_pad}, '0')) serial_name, max_id.max_id_value now_id
                    FROM max_id
                    ";
            $stmt = $this->db->prepare($sql);

            if ($stmt->execute()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
            return $result;
        }
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

    public function rutine($data)
    {
        $decodedJson = json_decode($data['data'], true);
        $key_array = $data['key'];
        $valid_array = $data['valid'];
        foreach ($decodedJson as $array => $array_value) {
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
                                if (count($sql_return[0][$key]) != 0) {
                                    $return_value = $this->rutine(["data" => json_encode($sql_return), "key" => $key_array, "valid" => $valid_array])['data'];
                                    $decodedJson[$array][$key][$children_id][$key] = $return_value[0][$key];
                                }
                            }
                        }
                    }
                }
            }
        }

        return ["data" => $decodedJson, "key" => $key_array, "valid" => $valid_array];
    }

    public function get_statistics_line($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [];

        $customize_select = "";
        $customize_table = "";
        $select_condition = "";
        $custom_filter_bind_values = [];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            } else {
                unset($bind_values[$key]);
            }
        }

        $condition = "";
        $condition_values = [];

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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY line_user_precent) \"key\"
                        FROM(
                            SELECT 
                                ROUND(all_user.non_line_user_num :: numeric / all_user.all_user_num :: numeric * 100, 2) non_line_user_precent,
                                ROUND(all_user.line_user_num :: numeric / all_user.all_user_num :: numeric * 100, 2) line_user_precent
                            FROM(
                                SELECT
                                    COUNT(id) AS all_user_num,
                                    COUNT(CASE WHEN line_notify_token IS NULL OR line_user_id IS NULL THEN id END) AS non_line_user_num,
                                    COUNT(CASE WHEN line_notify_token IS NOT NULL AND line_user_id IS NOT NULL THEN id END) AS line_user_num
                                FROM
                                    system.\"user\"
                            ) AS all_user
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



    public function get_department_depth($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "department_depth_id" => null,
            "department_id" => null,
            "depth_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_id" => null,
            "depth_id" => null,
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
            "department_depth_id" => " AND department_depth_id = :department_depth_id",
            "department_id" => " AND department_id = :department_id",
            "depth_id" => " AND depth_id = :depth_id",
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
        $order = 'ORDER BY depth_id';
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
            FROM(
                SELECT {$this->branch_schema}.department_depth.department_depth_id, 
                    {$this->branch_schema}.department_depth.department_id,
                    {$this->branch_schema}.department_depth.depth_id,
                    {$this->branch_schema}.department_depth.\"name\" depth_name, 
                    {$this->branch_schema}.department_depth.property_json,
                    COALESCE(department_depth_data.department_depth_data, '[]')department_depth_data
                    {$customize_select}
                FROM {$this->branch_schema}.department_depth
                LEFT JOIN (
                    SELECT all_department_data.department_depth_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                               'department_id',{$this->branch_schema}.\"department\".department_id, 
                               'department_index',{$this->branch_schema}.\"department\".department_index,
                               'classify_structure_type_id',{$this->branch_schema}.department_classify_structure_type.classify_structure_type_id,
                               'classify_structure_id',classify_structure.classify_structure_type.classify_structure_id,
                               'classify_structure_type_parent_id',classify_structure.classify_structure_type.classify_structure_type_parent_id,
                               'department_language_data',COALESCE(department_language_data.department_language_data, '[]')
                            )
                        )department_depth_data    
                    FROM {$this->branch_schema}.\"department\"
                    LEFT JOIN {$this->branch_schema}.department_classify_structure_type ON {$this->branch_schema}.\"department\".department_id = {$this->branch_schema}.department_classify_structure_type.department_id
                    LEFT JOIN classify_structure.classify_structure_type ON {$this->branch_schema}.department_classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type.classify_structure_type_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.all_department_zero.department_id root_id,
                            {$this->branch_schema}.all_department_zero.parent_id department_id, 
                            {$this->branch_schema}.all_department_zero.depth,
                            {$this->branch_schema}.department_depth.\"department_depth_id\",
                            {$this->branch_schema}.department_depth.\"name\" depth_name
                        FROM {$this->branch_schema}.all_department_zero
                        LEFT JOIN {$this->branch_schema}.department_depth ON {$this->branch_schema}.department_depth.department_id = {$this->branch_schema}.all_department_zero.department_id AND {$this->branch_schema}.all_department_zero.depth = {$this->branch_schema}.department_depth.depth_id
                    ) all_department_data ON {$this->branch_schema}.department_classify_structure_type.department_id = all_department_data.department_id
                
                    LEFT JOIN (
                        SELECT {$this->branch_schema}.\"department\".department_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'language_manage_id', department_language_json.language_manage_id,
                                'language_culture_code', department_language_json.language_culture_code,
                                'language_culture_value', department_language_json.language_value
                            )
                        )department_language_data
                        FROM {$this->branch_schema}.\"department\"
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema}' AND language.language_manage.table_name = 'department'
                        )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema}.\"department\".department_id
                        GROUP BY {$this->branch_schema}.\"department\".department_id
                    )department_language_data ON {$this->branch_schema}.department_classify_structure_type.department_id = department_language_data.department_id
                    GROUP BY all_department_data.department_depth_id
                ) department_depth_data ON department_depth_data.department_depth_id = {$this->branch_schema}.department_depth.department_depth_id
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function post_department_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $department_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "department_id" => null,
                "depth_id" => null,
                "name" => null,
                "property_json" => null,
            ];

            $department_depth_insert_cond = "";
            $department_depth_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($department_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $department_depth_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                    } else {
                        $department_depth_bind_values[$key] = $column[$key];
                    }
                    $department_depth_insert_cond .= "{$key},";
                    $department_depth_values_cond .= ":{$key},";
                } else {
                    unset($department_depth_bind_values[$key]);
                }
            }

            $department_depth_insert_cond = rtrim($department_depth_insert_cond, ',');
            $department_depth_values_cond = rtrim($department_depth_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.department_depth({$department_depth_insert_cond})
                VALUES ({$department_depth_values_cond})
                RETURNING department_depth_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($department_depth_bind_values)) {
                $department_depth_id = $stmt_insert->fetchColumn(0);
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "department_depth_id" => $department_depth_id];
        }
        return $result;
    }

    public function patch_department_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $department_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "department_depth_id" => null,
                "department_id" => null,
                "depth_id" => null,
                "name" => null,
                "property_json" => null,
            ];

            $department_depth_upadte_cond = "";
            $department_depth_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($department_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'department_depth_id') {
                        $department_depth_bind_values[$key] = $column[$key];
                    } else if ($key === "property_json") {
                        $department_depth_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        $department_depth_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $department_depth_bind_values[$key] = $column[$key];
                        $department_depth_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($department_depth_bind_values[$key]);
                }
            }

            $department_depth_fliter_cond .= "AND {$this->branch_schema}.department_depth.department_depth_id = :department_depth_id";
            $department_depth_upadte_cond = rtrim($department_depth_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.department_depth
                    SET {$department_depth_upadte_cond}
                    WHERE TRUE {$department_depth_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($department_depth_bind_values)) {
                if (isset($column['department_depth_staff_data'])) {
                    $department_depth_staff_id_arr = [];
                    $department_depth_staff_id_arr['department_depth_staff_id_arr'] = [];
                    foreach ($column['department_depth_staff_data'] as $index => $department_depth_staff) {
                        $department_depth_staff['department_depth_id'] = $column['department_depth_id'];
                        $department_depth_staff_id_arr['department_depth_id'] = $department_depth_staff['department_depth_id'];
                        if (array_key_exists('department_depth_staff_id', $department_depth_staff)) {
                            $patch_department_depth_staff = $this->patch_department_depth_staff([$department_depth_staff], $last_edit_user_id);
                            if ($patch_department_depth_staff['status'] == 'success') {
                                array_push($department_depth_staff_id_arr['department_depth_staff_id_arr'], $department_depth_staff['department_depth_staff_id']);
                            }
                        } else {
                            $post_department_depth_staff = $this->post_department_depth_staff([$department_depth_staff], $last_edit_user_id);
                            if ($post_department_depth_staff['status'] === 'success') {
                                array_push($department_depth_staff_id_arr['department_depth_staff_id_arr'], $post_department_depth_staff['department_depth_staff_id']);
                            }
                        }
                    }
                    $result = $this->delete_department_depth_staff([$department_depth_staff_id_arr], $last_edit_user_id);
                }
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_department_depth($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_department_depth_condition = "";
            $delete_department_depth_bind_values = [
                "department_id" => "",
                "department_depth_id" => "",
                "department_depth_id_arr" => "",
            ];

            foreach ($delete_department_depth_bind_values as $key => $value) {
                if ($key === 'department_depth_id_arr' && array_key_exists('department_depth_id_arr', $delete_data) && count($delete_data['department_depth_id_arr']) !== 0) {
                    unset($delete_department_depth_bind_values[$key]);
                    $delete_department_depth_condition .= " department_depth_id IN ( SELECT department_depth_id FROM {$this->branch_schema}.department_depth WHERE department_id = :department_id AND department_depth_id NOT IN (";
                    foreach ($delete_data[$key] as $project_sop_step_user_involve_arr) {
                        $delete_department_depth_condition .= " {$project_sop_step_user_involve_arr},";
                    }
                    $delete_department_depth_condition = rtrim($delete_department_depth_condition, ',');
                    $delete_department_depth_condition .= ")) AND ";
                } else if (array_key_exists($key, $delete_data)) {
                    $delete_department_depth_bind_values[$key] = $delete_data[$key];
                    $delete_department_depth_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_department_depth_bind_values[$key]);
                }
            }

            $delete_department_depth_condition = rtrim($delete_department_depth_condition, 'AND ');

            $sql_delete = "DELETE FROM {$this->branch_schema}.department_depth
                    WHERE $delete_department_depth_condition
                ";
            $stmt_delete_department_depth = $this->db->prepare($sql_delete);
            if ($stmt_delete_department_depth->execute($delete_department_depth_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function post_role_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $role_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "role_id" => null,
                "depth_id" => null,
                "name" => null,
                "property_json" => null,
            ];

            $role_depth_insert_cond = "";
            $role_depth_values_cond = "";

            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($role_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $role_depth_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                    } else {
                        $role_depth_bind_values[$key] = $column[$key];
                    }
                    $role_depth_insert_cond .= "{$key},";
                    $role_depth_values_cond .= ":{$key},";
                } else {
                    unset($role_depth_bind_values[$key]);
                }
            }

            $role_depth_insert_cond = rtrim($role_depth_insert_cond, ',');
            $role_depth_values_cond = rtrim($role_depth_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.role_depth({$role_depth_insert_cond})
                VALUES ({$role_depth_values_cond})
                RETURNING role_depth_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($role_depth_bind_values)) {
                $role_depth_id = $stmt_insert->fetchColumn(0);
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "role_depth_id" => $role_depth_id];
        }
        return $result;
    }

    public function patch_role_depth($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $role_depth_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()",
                "role_depth_id" => null,
                "role_id" => null,
                "depth_id" => null,
                "name" => null,
                "property_json" => null,
            ];

            $role_depth_upadte_cond = "";
            $role_depth_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($role_depth_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'role_depth_id') {
                        $role_depth_bind_values[$key] = $column[$key];
                    } else if ($key === "property_json") {
                        $role_depth_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        $role_depth_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $role_depth_bind_values[$key] = $column[$key];
                        $role_depth_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($role_depth_bind_values[$key]);
                }
            }

            $role_depth_fliter_cond .= "AND {$this->branch_schema}.role_depth.role_depth_id = :role_depth_id";
            $role_depth_upadte_cond = rtrim($role_depth_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.role_depth
                    SET {$role_depth_upadte_cond}
                    WHERE TRUE {$role_depth_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($role_depth_bind_values)) {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_role_depth($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_role_depth_condition = "";
            $delete_role_depth_bind_values = [
                "role_id" => "",
                "role_depth_id" => "",
                "role_depth_id_arr" => "",
            ];

            foreach ($delete_role_depth_bind_values as $key => $value) {
                if ($key === 'role_depth_id_arr' && array_key_exists('role_depth_id_arr', $delete_data) && count($delete_data['role_depth_id_arr']) !== 0) {
                    unset($delete_role_depth_bind_values[$key]);
                    $delete_role_depth_condition .= " role_depth_id IN ( SELECT role_depth_id FROM {$this->branch_schema}.role_depth WHERE role_id = :role_id AND role_depth_id NOT IN (";
                    foreach ($delete_data[$key] as $project_sop_step_user_involve_arr) {
                        $delete_role_depth_condition .= " {$project_sop_step_user_involve_arr},";
                    }
                    $delete_role_depth_condition = rtrim($delete_role_depth_condition, ',');
                    $delete_role_depth_condition .= ")) AND ";
                } else if (array_key_exists($key, $delete_data)) {
                    $delete_role_depth_bind_values[$key] = $delete_data[$key];
                    $delete_role_depth_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_role_depth_bind_values[$key]);
                }
            }

            $delete_role_depth_condition = rtrim($delete_role_depth_condition, 'AND ');

            $sql_delete = "DELETE FROM {$this->branch_schema}.role_depth
                    WHERE $delete_role_depth_condition
                ";
            $stmt_delete_role_depth = $this->db->prepare($sql_delete);
            if ($stmt_delete_role_depth->execute($delete_role_depth_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function get_role_property_json($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "role_property_json_id" => null,
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
            "role_property_json_id" => " AND role_property_json_id = :role_property_json_id",
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
        $order = 'ORDER BY role_property_json_id';
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
            FROM(
                SELECT {$this->branch_schema}.role_property_json.role_property_json_id, 
                    {$this->branch_schema}.role_property_json.property_json
                    {$customize_select}
                FROM {$this->branch_schema}.role_property_json
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function post_role_property_json($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $role_property_json_bind_values = [
                "create_user_id" => 0,
                "create_time" => "",
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "property_json" => null,
            ];

            $role_property_json_insert_cond = "";
            $role_property_json_values_cond = "";

            $column['create_user_id'] = $last_edit_user_id;
            $column['create_time'] = 'NOW()';
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($role_property_json_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key === "property_json") {
                        $role_property_json_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                    } else {
                        $role_property_json_bind_values[$key] = $column[$key];
                    }
                    $role_property_json_insert_cond .= "{$key},";
                    $role_property_json_values_cond .= ":{$key},";
                } else {
                    unset($role_property_json_bind_values[$key]);
                }
            }

            $role_property_json_insert_cond = rtrim($role_property_json_insert_cond, ',');
            $role_property_json_values_cond = rtrim($role_property_json_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.role_property_json({$role_property_json_insert_cond})
                VALUES ({$role_property_json_values_cond})
                RETURNING role_property_json_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($role_property_json_bind_values)) {
                $role_property_json_id = $stmt_insert->fetchColumn(0);
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "role_property_json_id" => $role_property_json_id];
        }
        return $result;
    }

    public function patch_role_property_json($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $role_property_json_bind_values = [
                "last_edit_user_id" => 0,
                "last_edit_time" => "",
                "role_property_json_id" => null,
                "property_json" => null,
            ];

            $role_property_json_upadte_cond = "";
            $role_property_json_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($role_property_json_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'role_property_json_id') {
                        $role_property_json_bind_values[$key] = $column[$key];
                    } else if ($key === "property_json") {
                        $role_property_json_bind_values[$key] = json_encode($column[$key], JSON_FORCE_OBJECT);
                        $role_property_json_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $role_property_json_bind_values[$key] = $column[$key];
                        $role_property_json_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($role_property_json_bind_values[$key]);
                }
            }

            $role_property_json_fliter_cond .= "AND {$this->branch_schema}.role_property_json.role_property_json_id = :role_property_json_id";
            $role_property_json_upadte_cond = rtrim($role_property_json_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.role_property_json
                    SET {$role_property_json_upadte_cond}
                    WHERE TRUE {$role_property_json_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($role_property_json_bind_values)) {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_role_property_json($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_role_property_json_condition = "";
            $delete_role_property_json_bind_values = [
                "role_property_json_id" => "",
            ];

            foreach ($delete_role_property_json_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_role_property_json_bind_values[$key] = $delete_data[$key];
                    $delete_role_property_json_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_role_property_json_bind_values[$key]);
                }
            }

            $delete_role_property_json_condition = rtrim($delete_role_property_json_condition, 'AND ');

            $sql_delete = "DELETE FROM {$this->branch_schema}.role_property_json
                    WHERE $delete_role_property_json_condition
                ";
            $stmt_delete_role_property_json = $this->db->prepare($sql_delete);
            if ($stmt_delete_role_property_json->execute($delete_role_property_json_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function get_department_permission($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "department_permission_id" => null,
            "department_id" => null,
            "depth_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_id" => null,
            "depth_id" => null,
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
            "department_permission_id" => " AND department_permission_id = :department_permission_id",
            "department_id" => " AND department_id = :department_id",
            "depth_id" => " AND depth_id = :depth_id",
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
        $order = 'ORDER BY department_id';
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
            FROM(
                SELECT 
                    department_permission_data.department_id, 
                    department_permission_data.permission_time_start, 
                    department_permission_data.permission_time_end,
                    COALESCE(department_permission_data.department_permission_data, '[]')department_permission_data
                    {$customize_select}
                FROM (
                    SELECT {$this->branch_schema}.department_permission.department_id, 
                        to_char({$this->branch_schema}.department_permission.permission_time_start, 'YYYY-MM-DD')permission_time_start,
                        to_char({$this->branch_schema}.department_permission.permission_time_end, 'YYYY-MM-DD')permission_time_end,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'department_permission_id', {$this->branch_schema}.department_permission.department_permission_id,
                                'permission_id', permission_management.permission.id,
                                'permission_name', permission_management.permission.\"name\",
                                'permission_url', permission_management.permission.\"url\",
                                'permission_level_id', {$this->branch_schema}.department_permission.permission_level_id
                            )
                            ORDER BY permission_management.permission.id
                        ) department_permission_data
                    FROM {$this->branch_schema}.department_permission
                    LEFT JOIN permission_management.permission ON {$this->branch_schema}.department_permission.permission_id = permission_management.permission.id
                    GROUP BY {$this->branch_schema}.department_permission.department_id, {$this->branch_schema}.department_permission.permission_time_start, {$this->branch_schema}.department_permission.permission_time_end
                )department_permission_data
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }

    public function post_department_permission($data)
    {
        foreach ($data as $row => $column) {
            $this->delete_department_permission($column);
            foreach ($column['department_permission_data'] as $permission_row => $permission_data) {
                $permission_data['department_id'] = $column['department_id'];
                $department_permission_values = [
                    "department_id" => "",
                    "permission_id" => 0,
                    "permission_level_id" => 0,
                    "permission_time_start" => null,
                    "permission_time_end" => null,
                ];
                $department_permission_insert_cond = "";
                $department_permission_values_cond = "";

                foreach ($department_permission_values as $key => $value) {
                    if (array_key_exists($key, $permission_data)) {
                        $department_permission_bind_values[$key] = $permission_data[$key];
                        $department_permission_insert_cond .= "{$key},";
                        $department_permission_values_cond .= ":{$key},";
                    }
                }

                $department_permission_insert_cond = rtrim($department_permission_insert_cond, ',');
                $department_permission_values_cond = rtrim($department_permission_values_cond, ',');

                $sql_insert = "INSERT INTO {$this->branch_schema}.department_permission({$department_permission_insert_cond})
                    VALUES ({$department_permission_values_cond})
                    RETURNING department_permission_id
                ";

                $stmt_insert = $this->db->prepare($sql_insert);

                if ($stmt_insert->execute($department_permission_bind_values)) {
                    $result = ["status" => "success"];
                } else {
                    var_dump($stmt_insert->errorInfo());
                    $result = ["status" => "failure"];
                    return $result;
                }
            }
        }
        return $result;
    }

    public function patch_department_permission($data)
    {
        foreach ($data as $row => $column) {
            $this->delete_department_permission($column);
            foreach ($column['department_permission_data'] as $permission_row => $permission_data) {
                $permission_data['department_id'] = $column['department_id'];
                $department_permission_values = [
                    "department_id" => "",
                    "permission_id" => 0,
                    "permission_level_id" => 0,
                    "permission_time_start" => null,
                    "permission_time_end" => null,
                ];
                $department_permission_insert_cond = "";
                $department_permission_values_cond = "";

                foreach ($department_permission_values as $key => $value) {
                    if (array_key_exists($key, $permission_data)) {
                        $department_permission_bind_values[$key] = $permission_data[$key];
                        $department_permission_insert_cond .= "{$key},";
                        $department_permission_values_cond .= ":{$key},";
                    }
                }

                $department_permission_insert_cond = rtrim($department_permission_insert_cond, ',');
                $department_permission_values_cond = rtrim($department_permission_values_cond, ',');

                $sql_insert = "INSERT INTO {$this->branch_schema}.department_permission({$department_permission_insert_cond})
                    VALUES ({$department_permission_values_cond})
                    RETURNING department_permission_id
                ";

                $stmt_insert = $this->db->prepare($sql_insert);

                if ($stmt_insert->execute($department_permission_bind_values)) {
                    $result = ["status" => "success"];
                } else {
                    var_dump($stmt_insert->errorInfo());
                    $result = ["status" => "failure"];
                    return $result;
                }
            }
        }
        return $result;
    }

    public function delete_department_permission($delete_data)
    {
        $delete_department_permission_bind_values = [
            "department_id" => "",
        ];

        foreach ($delete_department_permission_bind_values as $key => $value) {
            if (array_key_exists($key, $delete_data)) {
                $delete_department_permission_bind_values[$key] = $delete_data[$key];
            }
        }

        $sql_delete = "DELETE FROM {$this->branch_schema}.department_permission
                    WHERE {$this->branch_schema}.department_permission.department_id = :department_id
                ";
        $stmt_delete_department_permission_file = $this->db->prepare($sql_delete);
        if ($stmt_delete_department_permission_file->execute($delete_department_permission_bind_values)) {
            $result = ['status' => 'success'];
        } else {
            $result = ['status' => 'failure'];
        }
        return $result;
    }

    //解碼器
    function decodeStaffData($inputData, $encoder, $item)
    {
        $decodedData = [];
        foreach ($inputData as $row => $column) {
            // 先處理基本欄位的解碼
            foreach ($encoder as $decodedKey => $encodedKey) {
                if (isset($inputData[$row][$encodedKey])) {
                    if ($decodedKey === 'staff_name') {
                        $decodedData[$row]['name'] = $inputData[$row][$encodedKey];
                    } else if ($decodedKey === 'staff_serial_name') {
                        $decodedData[$row]['serial_name'] = $inputData[$row][$encodedKey];
                    }
                    $decodedData[$row][$decodedKey] = $inputData[$row][$encodedKey];
                }
            }

            //處理porperty_json
            if (array_key_exists('更多資訊', $inputData[$row])) {
                $decodedData[$row]['property_json'] = $this->parseKeyValueString($inputData[$row]['更多資訊']);
            }

            // 處理選項資料，假設最多有3個個單位 - 職位
            $user_languages = [];
            $user_role_department_data = [];
            foreach ($item['department_role_data'] as $departmentRole) {
                $headerValue = $departmentRole['department_language_data'][0]['language_culture_value'];
                if (isset($inputData[$row][$headerValue . '代號'])) {
                    $user_languages_column = $inputData[$row][$headerValue . '代號'];
                    $user_role_department_data[] = [
                        'department_id' => $user_languages_column,
                    ];
                }
            }
            $user_languages[] = [
                "user_role_department_data" => $user_role_department_data,
                'role_id' => $item['role_id']
            ];
            if (!empty($user_languages)) {
                $decodedData[$row]['user_language_data'] = $user_languages;
            }
        }
        return $decodedData;
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

    public function post_department_depth_staff($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $department_depth_staff_bind_values = [
                "department_depth_id" => null,
                "staff_id" => null,
            ];

            $department_depth_staff_insert_cond = "";
            $department_depth_staff_values_cond = "";

            foreach ($department_depth_staff_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $department_depth_staff_bind_values[$key] = $column[$key];
                    $department_depth_staff_insert_cond .= "{$key},";
                    $department_depth_staff_values_cond .= ":{$key},";
                } else {
                    unset($department_depth_staff_bind_values[$key]);
                }
            }

            $department_depth_staff_insert_cond = rtrim($department_depth_staff_insert_cond, ',');
            $department_depth_staff_values_cond = rtrim($department_depth_staff_values_cond, ',');

            $sql_insert = "INSERT INTO {$this->branch_schema}.department_depth_staff({$department_depth_staff_insert_cond})
                VALUES ({$department_depth_staff_values_cond})
                ON CONFLICT(department_depth_id,staff_id)
                DO UPDATE
                SET staff_id = EXCLUDED.staff_id
                RETURNING department_depth_staff_id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($department_depth_staff_bind_values)) {
                $department_depth_staff_id = $stmt_insert->fetchColumn(0);
            } else {
                return ['status' => 'failure'];
            }
            $result = ["status" => "success", "department_depth_staff_id" => $department_depth_staff_id];
        }
        return $result;
    }

    public function patch_department_depth_staff($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $department_depth_staff_bind_values = [
                "department_depth_staff_id" => null,
                "department_depth_id" => null,
                "staff_id" => null,
            ];

            $department_depth_staff_upadte_cond = "";
            $department_depth_staff_fliter_cond = "";

            foreach ($department_depth_staff_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'department_depth_staff_id') {
                        $department_depth_staff_bind_values[$key] = $column[$key];
                    } else {
                        $department_depth_staff_bind_values[$key] = $column[$key];
                        $department_depth_staff_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($department_depth_staff_bind_values[$key]);
                }
            }

            $department_depth_staff_fliter_cond .= "AND {$this->branch_schema}.department_depth_staff.department_depth_staff_id = :department_depth_staff_id";
            $department_depth_staff_upadte_cond = rtrim($department_depth_staff_upadte_cond, ',');

            $sql = "UPDATE {$this->branch_schema}.department_depth_staff
                    SET {$department_depth_staff_upadte_cond}
                    WHERE TRUE {$department_depth_staff_fliter_cond}
                    ON CONFLICT(department_depth_id,staff_id)
                    DO UPDATE
                    SET staff_id = EXCLUDED.staff_id
                    RETURNING department_depth_staff_id
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($department_depth_staff_bind_values)) {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', 'errorInfo' => $stmt->errorInfo()];
            }
        }
        return $result;
    }

    public function delete_department_depth_staff($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_department_depth_staff_condition = "";
            $delete_department_depth_staff_bind_values = [
                "department_depth_id" => "",
                "department_depth_staff_id" => "",
                "department_depth_staff_id_arr" => "",
            ];

            foreach ($delete_department_depth_staff_bind_values as $key => $value) {
                if ($key === 'department_depth_staff_id_arr' && array_key_exists('department_depth_staff_id_arr', $delete_data) && count($delete_data['department_depth_staff_id_arr']) !== 0) {
                    unset($delete_department_depth_staff_bind_values[$key]);
                    $delete_department_depth_staff_condition .= " department_depth_staff_id IN ( SELECT department_depth_staff_id FROM {$this->branch_schema}.department_depth_staff WHERE department_depth_id = :department_depth_id AND department_depth_staff_id NOT IN (";
                    foreach ($delete_data[$key] as $project_sop_step_user_involve_arr) {
                        $delete_department_depth_staff_condition .= " {$project_sop_step_user_involve_arr},";
                    }
                    $delete_department_depth_staff_condition = rtrim($delete_department_depth_staff_condition, ',');
                    $delete_department_depth_staff_condition .= ")) AND ";
                } else if (array_key_exists($key, $delete_data)) {
                    $delete_department_depth_staff_bind_values[$key] = $delete_data[$key];
                    $delete_department_depth_staff_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_department_depth_staff_bind_values[$key]);
                }
            }

            $delete_department_depth_staff_condition = rtrim($delete_department_depth_staff_condition, 'AND ');

            $sql_delete = "DELETE FROM {$this->branch_schema}.department_depth_staff
                    WHERE $delete_department_depth_staff_condition
                ";
            $stmt_delete_department_depth_staff = $this->db->prepare($sql_delete);
            if ($stmt_delete_department_depth_staff->execute($delete_department_depth_staff_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_department_role($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_department_role_condition = "";
            $delete_department_role_bind_values = [
                "department_role_id" => "",
                "role_id" => "",
                "department_role_id_arr" => "",
            ];

            foreach ($delete_department_role_bind_values as $key => $value) {
                if ($key === 'department_role_id_arr' && array_key_exists('department_role_id_arr', $delete_data)) {
                    unset($delete_department_role_bind_values[$key]);
                    if (count($delete_data['department_role_id_arr']) !== 0) {
                        $delete_department_role_condition .= " department_role_id IN ( SELECT department_role_id FROM {$this->branch_schema}.department_role WHERE role_id = :role_id AND department_role_id NOT IN (";
                        foreach ($delete_data[$key] as $project_sop_step_user_involve_arr) {
                            $delete_department_role_condition .= " {$project_sop_step_user_involve_arr},";
                        }
                        $delete_department_role_condition = rtrim($delete_department_role_condition, ',');
                        $delete_department_role_condition .= ")) AND ";
                    }
                } else if (array_key_exists($key, $delete_data)) {
                    $delete_department_role_bind_values[$key] = $delete_data[$key];
                    $delete_department_role_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_department_role_bind_values[$key]);
                }
            }

            $delete_department_role_condition = rtrim($delete_department_role_condition, 'AND ');

            $sql_delete = "DELETE FROM {$this->branch_schema}.\"department_role\"
                    WHERE $delete_department_role_condition
                ";
            $stmt_delete_department_role = $this->db->prepare($sql_delete);
            if ($stmt_delete_department_role->execute($delete_department_role_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function delete_user_role_department($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_user_role_department_condition = "";
            $delete_user_role_department_bind_values = [
                "user_role_department_id" => "",
                "user_role_id" => "",
                "user_role_department_id_arr" => "",
            ];

            foreach ($delete_user_role_department_bind_values as $key => $value) {
                if ($key === 'user_role_department_id_arr' && array_key_exists('user_role_department_id_arr', $delete_data)) {
                    unset($delete_user_role_department_bind_values[$key]);
                    if (count($delete_data['user_role_department_id_arr']) !== 0) {
                        $delete_user_role_department_condition .= " user_role_department_id IN ( SELECT user_role_department_id FROM {$this->branch_schema}.user_role_department WHERE user_role_id = :user_role_id AND user_role_department_id NOT IN (";
                        foreach ($delete_data[$key] as $project_sop_step_user_involve_arr) {
                            $delete_user_role_department_condition .= " {$project_sop_step_user_involve_arr},";
                        }
                        $delete_user_role_department_condition = rtrim($delete_user_role_department_condition, ',');
                        $delete_user_role_department_condition .= ")) AND ";
                    }
                } else if (array_key_exists($key, $delete_data)) {
                    $delete_user_role_department_bind_values[$key] = $delete_data[$key];
                    $delete_user_role_department_condition .= "{$key} = :{$key} AND ";
                } else {
                    unset($delete_user_role_department_bind_values[$key]);
                }
            }

            $delete_user_role_department_condition = rtrim($delete_user_role_department_condition, 'AND ');

            $sql_delete = "DELETE FROM {$this->branch_schema}.\"user_role_department\"
                    WHERE $delete_user_role_department_condition
                ";
            $stmt_delete_user_role_department = $this->db->prepare($sql_delete);
            if ($stmt_delete_user_role_department->execute($delete_user_role_department_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    public function get_staff_import_manual($data, $response, $params = [])
    {
        $role_department = $data["role_department"];
        $data = $data["data"];
        // 創建新的電子表格
        $spreadsheet = new Spreadsheet();

        // 添加第一個工作表：結構說明
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('結構說明');

        // 設置表頭名稱
        $headers = [];
        foreach ($data as $item) {
            $languageValues = array_column($item['department_language_data'], 'language_culture_value');
            $headerBase = implode('', $languageValues); // 如: 班級結構
            $headers[] = "{$headerBase}名稱";
            $headers[] = "{$headerBase}代號";
        }

        // 寫入表頭
        $sheet1->fromArray($headers, NULL, 'A1');

        // 遞迴處理資料並填充表格
        function processData(array $items, &$rows, $column)
        {
            foreach ($items as $item) {
                $languageValues = array_column($item['department_language_data'], 'language_culture_value');
                $languageValue = implode('', $languageValues); // 名稱
                $departmentId = $item['department_id'];

                // 添加當前資料到行陣列
                if (!isset($rows[$column])) {
                    $rows[$column] = [];
                }
                $rows[$column][] = [$languageValue, $departmentId];

                // 如果有 children，遞迴處理
                if (!empty($item['children'])) {
                    processData($item['children'], $rows, $column);
                }
            }
        }

        // 初始化資料存儲
        $rows = [];
        $column = 0;

        // 遍歷每個第一層項目
        foreach ($data as $item) {
            processData([$item], $rows, $column);
            $column++; // 下一組資料的列
        }

        // 將資料寫入第一個工作表
        $maxRow = 1;
        foreach ($rows as $column => $dataGroup) {
            $row = 2;
            foreach ($dataGroup as $data) {
                $sheet1->setCellValueByColumnAndRow($column * 2 + 1, $row, $data[0]); // 名稱
                $sheet1->setCellValueByColumnAndRow($column * 2 + 2, $row, $data[1]); // 代號
                $row++;
                $maxRow = max($maxRow, $row);
            }
        }

        // 遍歷每個角色，動態創建工作表
        foreach ($role_department as $item) {
            // 跳過空的角色資料
            if (empty($item['role_language_data']) || empty($item['department_role_data'])) {
                continue;
            }

            // 工作表名稱來自 role_language_data 的 language_culture_value
            $sheetName = $item['role_language_data'][0]['language_culture_value'];
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetName);

            // 表頭來自 department_role_data 的 department_language_data 的 language_culture_value
            $headers = [];
            $parser_decode = [
                "staff_serial_name" => "編號(系統帳號)",
                "staff_name" => "姓名",
                "staff_english_name" => "英文名",
                "gender_id" => "性別（男1女2）",
                "e_mail" => "Email",
                "address" => "地址",
                "property_json" => "更多資訊",
            ];
            foreach ($parser_decode as $parser_decode_key => $parser_decode_value) {
                $headers[] = "{$parser_decode_value}";
            }
            foreach ($item['department_role_data'] as $departmentRole) {
                $headerValue = $departmentRole['department_language_data'][0]['language_culture_value'];
                $headers[] = "{$headerValue}代號";
            }


            // 寫入表頭
            $sheet->fromArray($headers, NULL, 'A1');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', "attachment; filename={$data['name']}報表.xlsx");
        return $response;
    }

    public function get_role_department($params)
    {

        $result = $this->get_code_base(
            $params, //原本的查詢參數
            [], //原本的bind_values
            [], //原本的custom_filter_bind_values
            [], //原本的condition_values
            "", //預設排序
            "SELECT organization_structure.department_role_data.role_id,
                    organization_structure.department_role_data.department_role_data,
                    role_language_data.role_language_data
                FROM organization_structure.department_role_data
                LEFT JOIN ( SELECT role.id AS role_id,
                    json_agg(json_build_object('language_manage_id', name_language_json.language_manage_id, 'language_culture_code', name_language_json.language_culture_code, 'language_culture_value', name_language_json.language_value)) AS role_language_data
                FROM organization_structure.role
                    LEFT JOIN ( SELECT language_manage.language_manage_id,
                            language_manage.schema_name,
                            language_manage.table_name,
                            language_manage.column_name,
                            language_manage.table_primary_id,
                            language_manage.language_value,
                            language_culture.language_culture_code
                        FROM language.language_manage
                            LEFT JOIN language.language_culture ON language_manage.language_culture_id = language_culture.language_culture_id
                        WHERE language_manage.schema_name = 'organization_structure'::text AND language_manage.table_name = 'role'::text) name_language_json ON name_language_json.table_primary_id = role.id
                GROUP BY role.id) role_language_data ON role_language_data.role_id = organization_structure.department_role_data.role_id
            ", //客製化SQL
            $this->db //客製化Db
        );
        return $result;
    }
}
