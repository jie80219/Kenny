<?php

use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;

class dgx
{
    protected $container;
    protected $db;


    // constructor receives container instance
    public function __construct()
    {
        global $container;
        $this->container = $container;
        $this->db = $container->db_dgx;
    }

    public function isJson($string)
    {
        json_decode($string);
        return json_decode($string) !== false && json_last_error() === JSON_ERROR_NONE;
    }

    public function get_result($params)
    {
        $bind_values = [
            "result_code" => null
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
            "result_code" => " AND result.result_code = :result_code"
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $sql = "SELECT result_code, result_data
                FROM public.result
                WHERE TRUE {$condition}
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_result($data)
    {
        $result_values = [
            "result_code" => "",
            "result_data" => null,
        ];

        $result_insert_cond = "";
        $result_values_cond = "";

        foreach ($result_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $result_bind_values[$key] = $data[$key];
                $result_insert_cond .= "{$key},";
                $result_values_cond .= ":{$key},";
            }else if(is_null($result_values[$key])){
                unset($result_bind_values[$key]);
            }
        }

        $result_insert_cond = rtrim($result_insert_cond, ',');
        $result_values_cond = rtrim($result_values_cond, ',');

        $sql_insert = "INSERT INTO public.result({$result_insert_cond})
                VALUES ({$result_values_cond})
                ON CONFLICT(result_code)
                DO UPDATE
                SET result_data = EXCLUDED.result_data
            ";

        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($result_bind_values)) {
            $result = ["status" => "success"];
        } else {
            var_dump($stmt_insert->errorInfo());
            $result = ["status" => "failure"];
        }

        return $result;
    }

    public function patch_result($data)
    {
        $column = $data;
        $result_bind_values = [
            "result_code" => "",
            "result_data" => "",
        ];

        $result_upadte_cond = "";
        $result_fliter_cond = "";

        foreach ($result_bind_values as $key => $value) {
            if (array_key_exists($key, $column)) {
                if ($key != 'result_data') {
                    $result_bind_values[$key] = $column[$key];
                } else {
                    $result_bind_values[$key] = $column[$key];
                    $result_upadte_cond .= "{$key} = :{$key},";
                }
            }
        }

        $result_fliter_cond .= "AND public.result.result_code = :result_code";
        $result_upadte_cond = rtrim($result_upadte_cond, ',');

        $sql = "UPDATE public.result
                    SET {$result_upadte_cond}
                    WHERE TRUE AND public.result.result_code IN (
                        SELECT public.result.result_code
                        FROM public.result
                        WHERE TRUE {$result_fliter_cond}
                    )
            ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($result_bind_values)) {
        } else {
            var_dump($stmt->errorInfo());
            $result = ['status' => 'failure'];
        }
        $result = ["status" => "success"];
        return $result;
    }
}
