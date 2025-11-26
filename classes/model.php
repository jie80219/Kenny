<?php

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Slim\App;

class Model
{
    protected $container;
    protected $samples;

    public function __construct($container)
    {
        $this->container = $container;
        $this->samples = [];
    }

    //001 簡化select sql 可配合003 initialize_search
    protected function judge_server_side($data, $arr)
    {
        $arr['cur_page'] = $data['cur_page'];
        $arr['size'] = $data['size'];
        $length = $arr['size'] * $arr['cur_page'];
        $start = $length - $arr['size'];
        $arr["length"] = $length;
        $arr["start"] = $start;
        unset($arr["cur_page"]);
        unset($arr["size"]);
        return $arr;
    }
    //002 判斷是否為json格式資料
    public function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    //003 預設select 數量
    public function initialize_search()
    {
        $default_value = [
            "cur_page" => 1,
            "size" => 100000,
        ];
        return $default_value;
    }
    //004 讀取excel並轉回二維陣列
    public function getExcel($data, $break = false)
    {
        //獲得最大欄位 Thead用
        function getMaxColumns($data)
        {
            $columns = [];
            foreach ($data as $row) {
                $columns = array_merge($columns, array_keys($row));
            }
            return array_unique($columns);
        }
        //補充欄位
        function normalizeData($data, $columns)
        {
            $normalizedData = [];
            foreach ($data as $row) {
                $normalizedRow = [];
                foreach ($columns as $column) {
                    $normalizedRow[$column] = isset($row[$column]) ? $row[$column] : "";
                }
                $normalizedData[] = $normalizedRow;
            }
            return $normalizedData;
        }
        $response = $data['response'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowArray = [];
        $row_count = 1;

        $columns = getMaxColumns($data['data']);
        $normalizedData = normalizeData($data['data'], $columns);
        foreach ($normalizedData as $index => $row) {
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

        // 自動調整列寬
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', "attachment; filename={$data['name']}報表.xlsx");
        return $response;
    }
    //004 讀取excel並轉回二維陣列
    public function get_word($data, $break = false)
    {
        // 假設 $data 是你的資料，包含 'response' 和 'data'
        $response = $data['response'];

        // 創建 PHPWord 實例
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // 將每一個陣列元素一行一行加入到 Word 文件中
        foreach ($data["data"] as $line) {
            $section->addText($line);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response = $response->withHeader('Content-Disposition', "attachment; filename={$data['name']}.docx");
        return $response;
    }
    //005 select在客製化查詢的資料處理
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
                if (is_null($value)) {
                    unset($custom_filter_arr[$key]);
                }
            }
            $default_arr = array_merge($default_arr, $custom_filter_arr);
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }
        return ["select_condition" => $select_condition, "bind_values" => $default_arr];
    }
    //006 select在客製化查詢的資料處理 - 進階版
    public function custom_filter_function_or_not($data, $select_condition, $default_arr, $custom_filter_arr)
    {
        if (array_key_exists('custom_filter_key', $data) && array_key_exists('custom_filter_value', $data) && count($data['custom_filter_key']) != 0) {
            $select_condition .= " AND (";
            foreach ($data['custom_filter_key'] as $custom_filter_key) {
                if (array_key_exists($custom_filter_key, $custom_filter_arr)) {
                    $select_condition .= " {$custom_filter_key}::text LIKE '%' || :{$custom_filter_key} || '%' OR";
                    $custom_filter_arr[$custom_filter_key] = $data['custom_filter_value'];
                }
            }
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
            if (array_key_exists('or_custom_filter_values', $data)) {
                $select_condition .= " AND (";
                foreach ($data['custom_filter_key'] as $custom_filter_key) {
                    if (array_key_exists($custom_filter_key, $custom_filter_arr)) {
                        foreach ($data['or_custom_filter_values'] as $key_or_custom_filter_values => $value_or_custom_filter_values) {
                            $select_condition .= " {$custom_filter_key}::text LIKE '%' || :{$custom_filter_key}_or_{$key_or_custom_filter_values} || '%' OR";
                            $custom_filter_arr["{$custom_filter_key}_or_{$key_or_custom_filter_values}"] = $value_or_custom_filter_values;
                        }
                    }
                }
                $select_condition = rtrim($select_condition, 'OR');
                $select_condition .= ")";
            }
            if (array_key_exists('and_custom_filter_values', $data)) {
                $select_condition .= " AND (";
                foreach ($data['custom_filter_key'] as $custom_filter_key) {
                    if (array_key_exists($custom_filter_key, $custom_filter_arr)) {
                        foreach ($data['and_custom_filter_values'] as $key_and_custom_filter_values => $value_and_custom_filter_values) {
                            $select_condition .= " {$custom_filter_key}::text LIKE '%' || :{$custom_filter_key}_and_{$key_and_custom_filter_values} || '%' AND";
                            $custom_filter_arr["{$custom_filter_key}_and_{$key_and_custom_filter_values}"] = $value_and_custom_filter_values;
                        }
                    }
                }
                $select_condition = rtrim($select_condition, 'AND');
                $select_condition .= ")";
            }
            if (array_key_exists('not_in_custom_filter_values', $data)) {
                $select_condition .= " AND (";
                foreach ($data['custom_filter_key'] as $custom_filter_key) {
                    if (array_key_exists($custom_filter_key, $custom_filter_arr)) {
                        foreach ($data['not_in_custom_filter_values'] as $key_not_in_custom_filter_values => $value_not_in_custom_filter_values) {
                            $select_condition .= " {$custom_filter_key}::text NOT LIKE '%' || :{$custom_filter_key}_not_in_{$key_not_in_custom_filter_values} || '%' OR";
                            $custom_filter_arr["{$custom_filter_key}_not_in_{$key_not_in_custom_filter_values}"] = $value_not_in_custom_filter_values;
                        }
                    }
                }
                $select_condition = rtrim($select_condition, 'OR');
                $select_condition .= ")";
            }

            foreach ($custom_filter_arr as $key => $value) {
                if ($value == null) {
                    unset($custom_filter_arr[$key]);
                }
            }
            $default_arr = array_merge($default_arr, $custom_filter_arr);
        }
        // var_dump($select_condition);exit(0);
        return ["select_condition" => $select_condition, "bind_values" => $default_arr];
    }
    //007 編碼轉換
    function auto_charset($fContents, $from = 'gbk', $to = 'utf-8')
    { // $fContents 字符串或是數組 $from 字符串的編碼 $to 要轉換的編碼
        // 將編碼轉換為大寫形式，統一處理
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;

        // 如果編碼相同或者$fContents為空或者非字符串標量則不轉換，直接返回
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            return $fContents;
        }

        // 如果$fContents是字符串
        if (is_string($fContents)) {
            // 使用mb_convert_encoding函數轉換編碼
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($fContents, $to, $from);
            } else {
                return $fContents; // 如果mb_convert_encoding函數不存在，則返回原字符串
            }
        }
        // 如果$fContents是數組
        elseif (is_array($fContents)) {
            // 遍歷數組，對每個元素進行編碼轉換
            foreach ($fContents as $key => $val) {
                $_key = $this->auto_charset($key, $from, $to); // 對鍵進行編碼轉換
                $fContents[$_key] = $this->auto_charset($val, $from, $to); // 對值進行編碼轉換
                if ($key != $_key) {
                    unset($fContents[$key]); // 如果鍵有變化，則刪除原鍵
                }
            }
            return $fContents;
        } else {
            return $fContents; // 其他情況，直接返回
        }
    }
    //008 讀取圖表的整個controller範例
    public function post_chart_generator_serach_barcharts_controller($request, $response, $args)
    {
        $data = $request->getParsedBody();
        if (array_key_exists('import_excel_id', $data)) {
            $data_temp = $this->get_import_excel_sql($data);
            $data_parser = $this->pg_json_data_parser($data_temp['sql_default'], $data_temp['bind_values'], 'import_excel', 'import_excel_id', true);
            $data['data_sql'] = $data_parser['params']['data_sql'];
            $data['import_excel_json_select'] = $data_parser['import_excel_all']['import_excel_json_select'];
            $result = $this->post_chart_generator_serach_barcharts($data, $data_temp['bind_values']);
        } else if (array_key_exists('relation_category_id', $data)) {
            $data_temp = $this->get_relation_category($data);
            if (array_key_exists('data', $data_temp)) {
                $data_relation = $data_temp['data'][0];
                $relation_class = new $data_relation['relation_category_class']($this->container->db);
                $relation_category_function_name = $data_relation['relation_category_function_name'];
                $data_use = $relation_class->$relation_category_function_name($data_relation['relation_category_input']);
                $data_parser = $this->pg_json_data_parser($data_use['sql_default'], $data_use['bind_values'], 'import_excel', 'import_excel_id', true);
                $data['data_sql'] = $data_parser['params']['data_sql'];
                $data = array_merge($data, $data_relation['relation_category_input']);
                $data['import_excel_json_select'] = $data_parser['import_excel_all']['import_excel_json_select'];
                $result = $this->post_chart_generator_serach_barcharts($data, $data_use['bind_values']);
            } else {
                $result = ['status' => "failed"];
            }
        } else {
            $data['data_sql'] = null;
            $result = $this->post_chart_generator_serach_barcharts($data, []);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    //009 讀取圖表資料產生圖表data
    public function post_chart_generator_serach_barcharts($data, $bind_values = [])
    {
        if (!isset($data['x'])) {
            return 'failed';
        }
        if (!isset($data['y'])) {
            return 'failed';
        }
        $selectStr = '';
        $selectGroupStr = '';
        foreach ($data['y'] as $row => $column) {
            $selectGroupStr .= " init.\"{$column}\",";
        }

        $data['x'] = array_reverse($data['x']);

        foreach ($data['x'] as $key => $value) {
            $selectStr .= " init.\"{$value}\",";
        }
        if ($selectGroupStr == '') {
            $selectStr = substr($selectStr, 0, -1);
            $groupStr = "GROUP BY {$selectStr}";
            $orderStr = "ORDER BY {$selectStr}";
        } else {
            $selectGroupStr = rtrim($selectGroupStr, ',');
            $groupStr = substr("GROUP BY {$selectStr}", 0, -1);
            $orderStr = substr("ORDER BY {$selectStr}", 0, -1);
        }

        if (isset($data['valueArr'])) {
            $whereStr = ' WHERE TRUE';
            $groupbyStr = ' ORDER BY';

            foreach ($data['valueArr'] as $key => $value) {
                $whereStr  .= " AND \"{$key}\" in ( ";
                foreach ($value as $datakey => $datavalue) {
                    $whereStr .= "'{$datavalue}',";
                }
                $whereStr = substr($whereStr, 0, -1);
                $whereStr  .= ") ";
                if ($key == "\"学校编号\"" || $key == "\"考生编号\"" || $key == "\"班级\"") {
                    $groupbyStr  .= "\"" . $key . "\"";
                }
            }
        } else {
            $whereStr = '';
        }

        if (is_null($data['data_sql'])) {
            $sql_default = " SELECT {$selectStr}, COUNT(*) \"房間數量\", SUM(\"銷售數量\")::int \"銷售數量\"
                FROM(
                    SELECT chart_generator.room_price.city \"縣市\", chart_generator.price_label.range_level \"價錢等級\", chart_generator.room_price.\"day\" \"平假日\",
                    chart_generator.room_price.room_type \"房間類型\", chart_generator.room_price.person_capacity \"房間容納人數\",
                    CASE WHEN chart_generator.room_price.business = 0 THEN '否' ELSE '是' END \"是否為商務房\", room_sales.\"銷售數量\"
                    FROM chart_generator.room_price
                    LEFT JOIN chart_generator.price_label ON chart_generator.room_price.price_label_id = chart_generator.price_label.id
                    LEFT JOIN (
                        SELECT chart_generator.room_sales.room_id, COUNT(*) \"銷售數量\"
                        FROM chart_generator.room_sales
                        GROUP BY chart_generator.room_sales.room_id
                    )room_sales ON chart_generator.room_price.id = room_sales.room_id
                ) AS init
                {$groupStr}
                ";
        } else {
            $group_select = "";
            foreach ($data['y'] as $row => $column) {
                $result_type_arr = [];
                $return_type = "";
                $sql_check_type = "SELECT \"{$column}\"
                        FROM (
                            SELECT {$data['import_excel_json_select']}
                            FROM ({$data['data_sql']}) import_excel_json
                        )init
                    ";

                $stmt = $this->db->prepare($sql_check_type);
                if ($stmt->execute($bind_values)) {
                    $result_type_arr = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } else {
                    var_dump(['status' => 'failed', 'errorModule' => 'sql_check_type']);
                }
                $return_type = $this->is_array_all_int($result_type_arr);
                if ($return_type) {
                    $group_select .= " SUM(\"{$column}\"::INTEGER)\"{$column}\",";
                } else {
                    $group_select .= " COUNT(\"{$column}\")\"{$column}\",";
                }
            }
            $group_select = rtrim($group_select, ',');

            $sql_default = "SELECT {$selectStr} {$selectGroupStr}
                                FROM (
                                    SELECT {$selectStr} {$group_select}
                                    FROM (
                                        SELECT {$data['import_excel_json_select']}
                                        FROM ({$data['data_sql']}) import_excel_json
                                    )init
                                    {$groupStr}
                                )init
                ";
        }

        $sql = "SELECT {$selectStr} {$selectGroupStr}
                FROM (
                   {$sql_default}
                )AS init
                {$whereStr}
                {$orderStr}
            ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($bind_values)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            var_dump($stmt->errorInfo());
        }
        return $result;
    }
    //010 讀取既有資料表獲取data - 一般是拿來獲得匯入excel的資料
    public function get_import_excel_sql($params)
    {
        $bind_values = [
            "import_excel_id" => null,
            "name" => null,
        ];
        $custom_filter_bind_values = [
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
            "import_excel_id" => " AND import_excel_id = :import_excel_id",
            "name" => " AND name = :\"name\"",
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

        $sql_inside = " SELECT chart_generator.import_excel.id import_excel_id, chart_generator.import_excel.\"name\",
                        chart_generator.import_excel.import_data
                        {$customize_select}
                        FROM chart_generator.import_excel
                        {$customize_table}";
        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY import_excel_id) \"key\"
                FROM(
                    {$sql_inside}
                )dt
                WHERE TRUE {$condition} {$select_condition}  
        ";

        return ["sql_default" => $sql_default, "bind_values" => $bind_values];
    }
    //011 處理資料留，將二維陣列轉為json
    public function pg_json_data_parser($data, $value, $table_name, $primary_key, $only_sql = false)
    {
        $sql = "SELECT STRING_AGG('\"' || {$table_name}.import_data || '\" TEXT', ', ')import_excel_json_data, 
                STRING_AGG('import_excel_json.\"' || {$table_name}.import_data || '\"', ', ')import_excel_json_select,
                import_excel_table_data
                FROM (
                    SELECT *
                    FROM (
                        SELECT {$table_name}.{$primary_key}, jsonb_object_keys({$table_name}.import_data)import_data, import_excel_table_data
                        FROM (
                            SELECT {$table_name}.{$primary_key}, jsonb_array_elements(import_data)import_data, import_data import_excel_table_data
                            FROM ($data){$table_name}
                        ){$table_name}
                    ){$table_name}
                    GROUP BY {$table_name}.{$primary_key}, {$table_name}.import_data, import_excel_table_data
                ){$table_name}
                GROUP BY {$table_name}.{$primary_key}, import_excel_table_data";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($value)) {
            $result_temp = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sql = "WITH excel_data AS ({$data}) 
                    SELECT {$result_temp[0]['import_excel_json_select']}
                    FROM excel_data, jsonb_to_recordset(excel_data.import_data) AS
                    import_excel_json({$result_temp[0]['import_excel_json_data']})";
            $params['data_sql'] = $sql;
            if ($only_sql) {
                return ["params" => $params, "import_excel_all" => $result_temp[0]];
            }
            $result = $this->get_chart_generator_init($params, $value);
        } else {
            var_dump(['status' => 'failed', 'errorModule' => 'pg_json_data_parser']);
            exit(0);
        }
        return $result;
    }
    //012 針對圖表串接時，會用的關聯分類
    public function get_relation_category($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "relation_category_id" => null,
        ];
        $custom_filter_bind_values = [
            "relation_category_id" => null,
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
            "relation_category_id" => " AND relation_category_id = :relation_category_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY relation_category_id) \"key\"
                FROM(
                    SELECT chart_generator.relation_category.relation_category_id, chart_generator.relation_category.relation_category_name,
                    chart_generator.relation_category.relation_category_class, chart_generator.relation_category.relation_category_function_name,
                    chart_generator.relation_category.relation_category_input
                    {$customize_select}
                    FROM chart_generator.relation_category
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
    //013 判斷陣列內是否所有資料為數字
    function is_array_all_int($arr)
    {
        foreach ($arr as $val) {
            if (!is_numeric($val) && intval($val) == 0 && $val != '0') {
                return false;
            }
        }
        return true;
    }
    //014 配合前端Switch強製機True、False轉換成1、0
    function convertBooleansToIntegers($object)
    {
        foreach ($object as $key => $value) {
            if (is_bool($value)) {
                $object[$key] = $value ? 1 : 0; // true變為1, false變為0
            } else if (is_array($value) || is_object($value)) {
                // 遞迴處理嵌套的陣列或物件
                $object[$key] = $this->convertBooleansToIntegers($value);
            }
        }
        return $object;
    }
    //015 強制將array資料內所有value Trim()，且遞迴處理
    public function recursiveTrim($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursiveTrim($value);
            }
        } elseif (is_string($data)) {
            $data = trim($data);
        }
        return $data;
    }
    //016 尋找遞迴中不重複的指定key
    public function recursivefindMaxUnique($data, $key)
    {
        $depths = [];

        // 遞迴處理每個節點
        function traverseNode($node, &$depths, $key)
        {
            if (isset($node[$key])) {
                $depths[] = $node[$key];
            }
            if (isset($node['children']) && is_array($node['children'])) {
                foreach ($node['children'] as $child) {
                    traverseNode($child, $depths, $key);
                }
            }
        }

        // 遍歷每個根節點
        foreach ($data as $node) {
            traverseNode($node, $depths, $key);
        }

        // 去重並排序
        $uniqueDepths = array_values(array_unique($depths));
        sort($uniqueDepths);

        return $uniqueDepths;
    }
    //017 定義遞迴函數來查找 depth 的項目
    public function findDepthItems($data, $depth, $only_depth = 0)
    {
        $result = [];

        foreach ($data as $item) {
            if (isset($item['depth']) && $item['depth'] == $depth) {
                if ($only_depth == 1) {
                    unset($item['children']);
                }
                $result[] = $item;
            }

            // 如果有 children，則遞迴查找子項目
            if (isset($item['children']) && is_array($item['children'])) {
                $response_data = $this->findDepthItems($item['children'], $depth, $only_depth);
                if ($only_depth === 1) {
                    unset($response_data['children']);
                }
                $result = array_merge($result, $response_data);
            }
        }

        return $result;
    }
    //018 讀取Excel
    public function read_excel($uploadedFile)
    {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'

            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 1; $col < $highestColumnIndex + 1; ++$col) {
                    $tmp[trim(strval($worksheet->getCellByColumnAndRow($col, 1)))] = trim(strval($worksheet->getCellByColumnAndRow($col, $row))) == '-' ? '' : trim(strval($worksheet->getCellByColumnAndRow($col, $row)));
                }
                if (count($tmp) != 0) {
                    $data[] = $tmp;
                }
            }
        }
        return $data;
    }
    public function read_excel_all_sheet($uploadedFile)
    {

        $result = [];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            // 加載 Excel 文件
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);

            // 獲取所有的工作表
            $sheets = $spreadsheet->getAllSheets();

            foreach ($sheets as $sheet) {
                $sheetName = $sheet->getTitle(); // 取得工作表名稱
                $worksheet = $spreadsheet->getSheetByName($sheetName);

                // Get the highest row number and column letter referenced in the worksheet
                $highestRow = $worksheet->getHighestRow(); // e.g. 10
                $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                $sheetData = [];
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $tmp = [];
                    // 將第一行作為標題
                    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                        $header = trim(strval($worksheet->getCellByColumnAndRow($col, 1)));
                        $value = trim(strval($worksheet->getCellByColumnAndRow($col, $row)));
                        // 處理 '-' 為空的值
                        if ($value === '-') {
                            $value = '';
                        }
                        // 如果標題不為空，才加入到陣列
                        if (!empty($header)) {
                            $tmp[$header] = $value;
                        }
                    }
                    // 過濾掉整列皆為空的資料
                    if (array_filter($tmp)) { // 只保留有有效值的列
                        $sheetData[] = $tmp;
                    }
                }

                // 儲存當前工作表的資料到結果中
                $result[$sheetName] = $sheetData;
            }
        }

        return $result;
    }

    //019 CRUD GET的code base
    public function get_code_base(
        $params, //原本的查詢參數
        $bind_values_setting = [
            "mission_task_group_id" => null,
            "mission_id" => null,
        ], //原本的bind_values
        $custom_filter_bind_values_setting = [
            "mission_task_group_id" => null,
            "mission_id" => null,
        ], //原本的custom_filter_bind_values
        $condition_values_setting = [
            "mission_task_group_id" => " AND mission_task_group_id = :mission_task_group_id",
            "mission_id" => " AND mission_id = :mission_id",
        ], //原本的condition_values
        $order_setting = "ORDER BY mission_task_group_id", //預設排序
        $sql_default_inside_setting = "", //客製化SQL
        $db_setting //客製化Db
    ) {
        //初始化與前端配合server_side的程式碼固定區
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];
        $bind_values = $bind_values_setting;
        $custom_filter_bind_values = $custom_filter_bind_values_setting;
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
        $condition_values = $condition_values_setting;

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

        $order = $order_setting;

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
                if ($column_data['column'] != 'ascend' && $column_data['column'] != 'ASC') {
                    $sort_mission_task_group = 'DESC';
                }

                $order .= " {$column_data['column']} {$sort_mission_task_group},";
            }
            $order = rtrim($order, ',');
        }

        $sql_default_inside = $sql_default_inside_setting;

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    {$sql_default_inside}
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

        $sql_people = "SELECT COUNT(*)
            FROM(
                {$sql_default_inside}
            )sql_default_inside
        ";
        $stmt = $db_setting->prepare($sql);
        $stmt_count = $db_setting->prepare($sql_count);
        $stmt_people = $db_setting->prepare($sql_people);
        if ($stmt->execute($bind_values) && $stmt_count->execute($values_count) && $stmt_people->execute()) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = $stmt_count->fetchColumn(0);
            $result_people = $stmt_people->fetchColumn(0);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            $result['total'] = $result_count;
            $result['people'] = $result_people;
            return $result;
        } else {
            return ["status" => "failure", "errorInfo" => $stmt->errorInfo()];
        }
    }
    //020 CRUD POST的code base
    public function post_code_base(
        $data, //原本的送入POST的參數
        $bind_values_setting = [
            "mission_task_group_id" => null,
            "mission_id" => null,
        ], //原本的bind_values
        $last_edit_user_id = "", //最後編輯人id
        $schema_setting = "", //客製化schema
        $table_setting = "", //客製化table
        $return_id_setting = "", //客製化回傳id
        $db_setting, //客製化Db
        $conflict = []
    ) {
        foreach ($data as $row => $column) {
            $code_base_bind_values = $bind_values_setting;

            $code_base_insert_cond = "";
            $code_base_values_cond = "";
            foreach ($code_base_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $code_base_bind_values[$key] = $column[$key];
                    $code_base_insert_cond .= "{$key},";
                    $code_base_values_cond .= ":{$key},";
                } else if ($key === 'last_edit_user_id' || $key === 'last_edit_time' || $key === 'create_user_id' || $key === 'create_time') {
                    if ($key === 'last_edit_user_id' || $key === 'create_user_id') {
                        $code_base_bind_values[$key] = $last_edit_user_id;
                    } else {
                        $code_base_bind_values[$key] = "NOW()";
                    }
                    $code_base_insert_cond .= "{$key},";
                    $code_base_values_cond .= ":{$key},";
                } else {
                    unset($code_base_bind_values[$key]);
                }
            }
            $conflict_string = "";
            if (count($conflict) !== 0) {
                $conflict_string = "ON CONFLICT(";
                foreach ($conflict as $key => $value) {
                    $conflict_string .= $value . ',';
                }
                $conflict_string = rtrim($conflict_string, ',');
                $conflict_string .= ')';
                $conflict_string .= 'DO UPDATE';
                $conflict_string .= ' SET ' . $return_id_setting . '=EXCLUDED.' . $return_id_setting;
            }
            $code_base_insert_cond = rtrim($code_base_insert_cond, ',');
            $code_base_values_cond = rtrim($code_base_values_cond, ',');
            $sql_insert = "INSERT INTO {$schema_setting}.{$table_setting}({$code_base_insert_cond})
                VALUES ({$code_base_values_cond})
                {$conflict_string}
                RETURNING {$return_id_setting}
            ";


            $stmt_insert = $db_setting->prepare($sql_insert);
            if ($stmt_insert->execute($code_base_bind_values)) {
                $return_id = $stmt_insert->fetchColumn(0);
                $data[$return_id_setting] = $return_id;
                $result = ["status" => "success", "$return_id_setting" => $return_id];
            } else {
                $result = ['status' => 'failure', 'errorInfo' => $stmt_insert->errorInfo()];
            }
        }
        return $result;
    }
    //021 CRUD PATCH的code base
    public function patch_code_base(
        $data, //原本的送入POST的參數
        $bind_values_setting = [
            "mission_task_group_id" => null,
            "mission_id" => null,
        ], //原本的bind_values
        $last_edit_user_id,
        $schema_setting = "", //客製化schema
        $table_setting = "", //客製化table
        $key_setting = "", //客製化回傳id
        $db_setting //客製化Db
    ) {
        foreach ($data as $row => $column) {
            $code_base_bind_values = $bind_values_setting;

            $code_base_upadte_cond = "";
            $code_base_fliter_cond = "";
            foreach ($code_base_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == "{$key_setting}") {
                        $code_base_bind_values[$key] = $column[$key];
                    } else if ($key === 'last_edit_user_id' || $key === 'last_edit_time') {
                        if ($key === 'last_edit_user_id') {
                            $code_base_bind_values[$key] = $last_edit_user_id;
                        } else {
                            $code_base_bind_values[$key] = "NOW()";
                        }
                        $code_base_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $code_base_bind_values[$key] = $column[$key];
                        $code_base_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($code_base_bind_values[$key]);
                }
            }
            $code_base_fliter_cond .= "AND {$schema_setting}.{$table_setting}.{$key_setting} = :{$key_setting}";
            $code_base_upadte_cond = rtrim($code_base_upadte_cond, ',');

            $sql = "UPDATE {$schema_setting}.{$table_setting}
                    SET {$code_base_upadte_cond}
                    WHERE TRUE {$code_base_fliter_cond}
            ";

            $stmt = $db_setting->prepare($sql);
            if ($stmt->execute($code_base_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    //021 CRUD DELETE的code base
    public function delete_code_base(
        $data, //原本的送入POST的參數
        $bind_values_setting = [
            "mission_task_group_id" => null,
            "mission_id" => null,
        ], //原本的bind_values
        $schema_setting = "", //客製化schema
        $table_setting = "", //客製化table
        $key_setting = "", //客製化回傳id
        $db_setting //客製化Db
    ) {
        foreach ($data as $row => $delete_data) {
            $delete_code_base_bind_values = $bind_values_setting;
            foreach ($delete_code_base_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_code_base_bind_values[$key] = $delete_data[$key];
                } else {
                    unset($delete_code_base_bind_values[$key]);
                }
            }
            $sql_delete = "DELETE FROM {$schema_setting}.{$table_setting}
                    WHERE {$schema_setting}.{$table_setting}.{$key_setting} = :{$key_setting}
                ";
            $stmt_delete_code_base = $db_setting->prepare($sql_delete);
            if ($stmt_delete_code_base->execute($delete_code_base_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    //022 掃描所有指定資料夾的route
    public function scanRoutes($routes, $container)
    {
        $scannedRoutes = []; // 建立一個新的陣列     
        foreach ($routes as $route) {
            $pattern = $route->getPattern(); // e.g., "/component/manual"
            $methods = $route->getMethods(); // e.g., ["GET", "POST"]
            $handler = $route->getCallable(); // e.g., "\componentcontroller::class . ':get_manual'"          
            // 獲得 Controller 和方法名稱

            // 初始化樣本
            $sampleInput = null;
            $sampleOutput = null;

            if (is_string($handler) && preg_match('/([a-zA-Z0-9_]+):([a-zA-Z0-9_]+)/', $handler, $matches)) {
                $controllerClass = $matches[1];
                $methodName = $matches[2];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass($container);
                    if (method_exists($controller, 'getSampleInput')) {
                        $sampleInput = $controller->getSampleInput($methodName);
                    }
                    if (method_exists($controller, 'getSampleOutput')) {
                        $sampleOutput = $controller->getSampleOutput($methodName);
                    }
                } else {
                    $sampleInput = null;
                    $sampleOutput = null;
                }
            }

            $scannedRoutes[] = [
                'pattern' => $pattern,
                'methods' => $methods,
                'handler' => is_string($handler) ? $handler : 'unknown',
                'sampleInput' => $sampleInput,
                'sampleOutput' => $sampleOutput
            ];
        }
        return $scannedRoutes;
    }
    //023 產生出Swagger所需要的json設定
    public function generateSwaggerJson($routes, $container)
    {
        $routes = $this->scanRoutes($routes, $container);
        $swagger = [
            "openapi" => "3.0.3",
            "info" => [
                "title" => "Auto-generated Swagger",
                "description" => "This is a dynamically generated Swagger file",
                "version" => "1.0.0"
            ],
            "servers" => [
                [
                    "url" => "http://localhost:8082",
                    "description" => "Local development server"
                ]
            ],
            "paths" => []
        ];

        foreach ($routes as $route) {
            $path = $route['pattern']; // Extract API path
            foreach ($route['methods'] as $method) {
                $methodLower = strtolower($method);
                if (!isset($swagger['paths'][$path])) {
                    $swagger['paths'][$path] = [];
                }
                if ($methodLower === 'get') {
                    $swagger['paths'][$path][$methodLower] = [
                        "tags" => $this->extractTagsFromPath($path),
                        "summary" => "Auto-generated summary for $path",
                        "description" => "Description for the route $path",
                        "operationId" => $this->generateOperationId($path, $methodLower),
                        "parameters" => $route['sampleInput'] ? array_map(function ($key, $value) {
                            return [
                                "name" => $key,
                                "in" => "query", // 若為查詢參數，則設定為 query
                                "required" => true, // 可根據需求調整為 false
                                "description" => "Description for $key", // 可自訂描述
                                "schema" => [
                                    "type" => gettype($value), // 根據值類型推斷
                                    "example" => $value // 提供範例數據
                                ]
                            ];
                        }, array_keys($route['sampleInput']), $route['sampleInput']) : [],
                        "responses" => [
                            "200" => [
                                "description" => "Successful response",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "example" => $route['sampleOutput']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else {
                    $swagger['paths'][$path][$methodLower] = [
                        "tags" => $this->extractTagsFromPath($path),
                        "summary" => "Auto-generated summary for $path",
                        "description" => "Description for the route $path",
                        "operationId" => $this->generateOperationId($path, $methodLower),
                        "parameters" => [],
                        "requestBody" => $route['sampleInput'] ? [
                            "required" => true,
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "example" => $route['sampleInput']
                                    ]
                                ]
                            ]
                        ] : null,
                        "responses" => [
                            "200" => [
                                "description" => "Successful response",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "example" => $route['sampleOutput']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                }
            }
        }

        return $swagger;
    }
    //024 製作Tag也就是Swagger分類的route切割
    public function extractTagsFromPath($path)
    {
        // Extract the first segment as the tag, e.g., "/component/manual" -> "component"
        $segments = explode('/', trim($path, '/'));
        return [array_shift($segments)];
    }
    //025 
    public function generateOperationId($path, $method)
    {
        // Generate a unique operationId based on path and method
        return $method . str_replace(['/', '_'], '', $path);
    }

    //026 動態新增或更新範例資料
    public function setSample($method, $type, $data)
    {
        // 檢查是否存在該方法
        if (!isset($this->samples[$method])) {
            $this->samples[$method] = [];
        }

        // 更新指定的類型（input 或 output）
        $this->samples[$method][$type] = $data;
    }

    //027 獲取範例資料
    public function getSample($method, $type)
    {
        return $this->samples[$method][$type] ?? null;
    }
    //028 處理字串『 - 』的左邊變成key右邊變成value，組成一整個array
    public function parseKeyValueString($input)
    {
        $result = [];
        // 將逗號分隔的部分拆分
        $pairs = explode('、', $input);

        foreach ($pairs as $pair) {
            // 將每組 key-value 用 " - " 分隔
            $keyValue = explode(' - ', $pair);

            if (count($keyValue) === 2) {
                $result = array_merge($result, [
                    trim($keyValue[0]) => trim($keyValue[1])
                ]);
            }
        }

        return $result;
    }
}
