<?php

use \Psr\Container\ContainerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class UtilityFormGenerator
{
    protected $container;
    protected $db;
    protected $db_sqlsrv;


    // constructor receives container instance
    public function __construct($db)
    {
        global $container;
        $this->container = $container;
        $this->db = $container->db;
        $this->db_sqlsrv = $container->db_sqlsrv;
    }
    public function is_json(?string $json_string = ""): bool
    {
        return is_string($json_string) &&
            is_array(json_decode($json_string, true)) &&
            (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
    public function isJson($string)
    {
        json_decode($string);
        return json_decode($string) !== false && json_last_error() === JSON_ERROR_NONE;
    }
    public function get_utility_form($data)
    {
        $condition = "";
        $args = [];
        if (array_key_exists("id", $data)) {
            $condition = "WHERE id = :id";
            $args[":id"] = $data["id"];
        }
        $sql = "SELECT id utility_form_id, code utility_form_code, name utility_form_name, board_length,
                    board_width, square_length, square_width
                FROM utility_form_generator.utility_form
                {$condition}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_utility_form_content($data)
    {
        $args = [":id" => $data["id"]];
        $sql = "SELECT id element_id, code element_code, name element_name,  position, connect_db, property, type, column, row
                FROM utility_form_generator.element
                LEFT JOIN utility_form_generator.utility_form_element ON 
                    utility_form_generator.utility_form_element.element_id = utility_form_generator.element.id
                WHERE utility_form_generator.utility_form_element.utility_form_id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $keys => $values) {
            foreach ($values as $key => $value) {
                $result[$keys][$key] = $this->rutine_json_decode($value);
            }
        }
        return $result;
    }
    public function post_utility_form($data)
    {
        $args = [":code" => $data["code"], ":name" => $data["name"]];
        $sql = "INSERT INTO utility_form_generator.utility_form (code, name) VALUES (:code, :name) RETURNING id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($args)) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = $result[0]["id"];
        } else {
            var_dump($stmt->errorInfo());
            $result = [
                "status" => "fail"
            ];
        }
        return $result;
    }
    public function post_utility_form_content($form, $data)
    {
        $insert_values = "";
        $VALUES = "";
        $args = [];
        foreach ($data as $keys => $values) {
            $insert_values .= "(";
            $VALUES .= "(";
            foreach ($values as $key => $value) {
                $insert_values .= ":{$key}_{$keys},";
                if ($key === "property") {
                    $args[":{$key}_{$keys}"] = json_encode($value);
                } else if (is_array($value)) {
                    $args[":{$key}_{$keys}"] = json_encode($value);
                } else {
                    $args[":{$key}_{$keys}"] = $value;
                }
                $VALUES .= "{$key},";
            }
            $insert_values = rtrim($insert_values, ",");
            $insert_values .= "),";
            $VALUES = rtrim($VALUES, ",");
            $VALUES .= ")";
        }
        $insert_values = rtrim($insert_values, ",");
        $sql = "INSERT INTO utility_form_generator.element $VALUES VALUES $insert_values
                RETURNING id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($args)) {
            $result = [
                "content" => $stmt->fetchAll(PDO::FETCH_ASSOC),
                "status" => "success"
            ];
        } else {
            var_dump($stmt->errorInfo());
            $result = [
                "status" => "fail"
            ];
        }
        return $result;
    }
    public function post_utility_form_element($form, $data)
    {
        $args = [":utility_form_id" => $form];
        $insert_values = "";
        foreach ($data as $keys => $values) {
            $insert_values .= "(";
            foreach ($values as $value) {
                $args[":id_{$keys}"] = $value;
                $insert_values .= ":id_{$keys}, :utility_form_id";
            }
            $insert_values = rtrim($insert_values, ",");
            $insert_values .= "),";
        }
        $insert_values = rtrim($insert_values, ",");
        $sql = "INSERT INTO utility_form_generator.utility_form_element (element_id, utility_form_id) VALUES $insert_values
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($args)) {
            $result = [
                "status" => "success"
            ];
        } else {
            var_dump($stmt->errorInfo());
            $result = [
                "status" => "fail"
            ];
        }
        return $result;
    }
    public function get_all_of_db()
    {
        $sql = "SELECT table_schema, table_name, json_agg(column_name) AS columns
                FROM information_schema.columns
                GROUP BY table_schema, table_name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $keys => $values) {
            foreach ($values as $key => $value) {
                if ($this->is_json($value)) {
                    $result[$keys][$key] = json_decode($value);
                }
            }
        }
        return $result;
    }

    public function get_apply_work_unit($data)
    { // 承億酒店-拿取表單適用單位
        $condition = "WHERE ";
        $args = [];
        if (empty($data)) $condition = "";
        else if (array_key_exists("formName", $data)) {
            $condition .= "utility_form_generator.utility_form.id = :id AND ";
            $args[":id"] = $data["formName"];
            unset($data["formName"]);
        }
        if (!empty($data)) {
            $condition .= "utility_form_apply.apply_value IS NOT NULL AND utility_form_apply.apply_value IN (";
            foreach ($data as $indexes => $rows) { // default only 1 condition needed to be considered
                foreach ($rows as $index => $row) {
                    $condition .= ":{$indexes}_{$index},";
                    $args[":{$indexes}_{$index}"] = $row;
                }
            }
            $condition = rtrim($condition, ",");
            $condition .= ")";
        }
        $condition = rtrim($condition, "AND ");

        $sql = "SELECT utility_form_id, utility_form_name, 
                    JSON_AGG(jsonb_build_object('custom_table', custom_table, 'custom_column', custom_column, 'custom_value', custom_value)) AS grouped_data
                FROM (
                    SELECT utility_form_generator.utility_form.id utility_form_id, utility_form_generator.utility_form.name utility_form_name, 
                            utility_form_apply.apply_table custom_table, 
                            CONCAT(utility_form_apply.apply_table, '.', utility_form_apply.apply_column) custom_column, utility_form_apply.apply_value custom_value
                    FROM utility_form_generator.utility_form
                    LEFT JOIN utility_form_generator.utility_form_apply 
                        ON utility_form_generator.utility_form_apply.utility_form_id = utility_form_generator.utility_form.id
                    {$condition}
                )your_table
                GROUP BY utility_form_id, utility_form_name;
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($args)) {
            $result = $this->composition_apply_work_unit($stmt->fetchAll(PDO::FETCH_ASSOC), $data);
        } else {
            var_dump($stmt->errorInfo());
            $result = [
                "status" => "fail"
            ];
        }

        return $result;
    }

    public function composition_apply_work_unit($data, &$filter)
    { // 承億酒店-拿取表單適用單位
        foreach ($data as $keys => $values) {
            foreach ($values as $key => $value) {
                $data[$keys][$key] = $this->rutine_json_decode($value);
            }
        }
        $result = [];
        foreach ($data as $keys => &$values) {
            $result[$keys] = $this->get_dynamic_table($values);
        }

        return $result;
    }

    public function get_dynamic_table($data)
    { // 承億酒店-拿取表單適用單位
        $result = [
            "utility_form_id" => $data["utility_form_id"],
            "utility_form_name" => $data["utility_form_name"],
            "grouped_data" => []
        ];

        foreach ($data["grouped_data"] as $keys => $values) {

            if ($values["custom_table"] !== null) {


                $args = [];

                foreach ($values as $key => $value) {
                    $args[":custom_value"] = $values["custom_value"];
                    $sql = "SELECT {$values['custom_column']}, {$values['custom_table']}.name
                            FROM {$values['custom_table']}
                            WHERE {$values['custom_column']} = :custom_value
                            ";
                    $stmt = $this->db->prepare($sql);
                    if ($stmt->execute($args)) {
                        $result["grouped_data"][$keys] = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]; // default unique id means that it won't return more than 1 row
                    } else {
                        var_dump($stmt->errorInfo());
                        $result = [
                            "status" => "fail"
                        ];
                        break;
                    }
                }
            }
        }

        return $result;
    }
    public function get_work_unit()
    {
        $sql = "SELECT id, name
                FROM utility_form_generator.work_unit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function rutine_json_decode($data)
    {
        if (gettype($data) == 'string' && $this->isJson($data)) {
            $data = json_decode($data, true);
            if (is_array($data)) {
                foreach ($data as $index => $row) {
                    if (is_array($row)) {
                        foreach ($row as $key => $value) {
                            if (gettype($value) == 'string' && $this->isJson($value)) {
                                $data[$index][$key] = $this->rutine_json_decode($value, true);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }
    public function test($data)
    {
        $condition = "";
        $args = [];
        if (array_key_exists("id", $data)) {
            $condition = "WHERE utility_form_generator.utility_form.id = :id";
            $args[":id"] = $data["id"];
        }
        $sql = "SELECT utility_form_apply.apply_table custom_table, CONCAT(utility_form_apply.apply_table, '.', utility_form_apply.apply_column) custom_column,
        		   utility_form_apply.apply_value custom_value
                FROM utility_form_generator.utility_form_apply
                JOIN utility_form_generator.utility_form 
                    ON utility_form_generator.utility_form.id = utility_form_generator.utility_form_apply.utility_form_id
                {$condition}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $keys => $values) {

            $sql = "SELECT {$values['custom_column']}, {$values['custom_table']}.name
                    FROM {$values['custom_table']}
                    WHERE {$values['custom_column']} = {$values['custom_value']}
                    ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }
}
