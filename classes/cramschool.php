<?php

use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use function PHPSTORM_META\map;

class CramSchool
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

    public function get_blog($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "blog_type_id" => null,
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

        if ($bind_values['blog_type_id'] == 2) {
            $customize_select = ", cramschool.surrounding.name, cramschool.surrounding.name_serial, cramschool.surrounding.capacity, cramschool.surrounding.manage_user_id, cramschool.surrounding.note";
            $customize_table = "
                LEFT JOIN cramschool.surrounding_blog ON cramschool.blog.id = cramschool.surrounding_blog.blog_id
                LEFT JOIN cramschool.surrounding ON cramschool.surrounding_blog.surrounding_id = cramschool.surrounding.id
            ";
            $customize_group = ", cramschool.surrounding.name, cramschool.surrounding.name_serial, cramschool.surrounding.capacity, cramschool.surrounding.manage_user_id, cramschool.surrounding.note";
        } else if ($bind_values['blog_type_id'] == 4) {
            $customize_select = ", cramschool.teacher.user_id, cramschool.teacher.id teacher_id,
            cramschool.teacher.name teacher_name, cramschool.teacher.expersite,
            to_char(cramschool.teacher.employment_time_start, 'YYYY-MM-DD')employment_time_start,
            to_char(cramschool.teacher.employment_time_end, 'YYYY-MM-DD')employment_time_end,
            cramschool.teacher.phone, cramschool.teacher.address, cramschool.teacher.note";
            $customize_table = "
                LEFT JOIN cramschool.teacher_blog ON cramschool.blog.id = cramschool.teacher_blog.blog_id
                LEFT JOIN cramschool.teacher ON cramschool.teacher_blog.teacher_id = cramschool.teacher.id
            ";
            $customize_group = ", teachers, enroll_status";
            // } else if ($bind_values['blog_type_id'] == 5) {
        } else {
            $customize_select = ", COALESCE(grade_class_teacher.teachers, '[]')teachers, class_data.enroll_status, COALESCE(class_data.class_data, '[]')class_data, lesson_data.enroll_status, COALESCE(lesson_data.lesson_data, '[]')lesson_data, class_data.order_stage_id";
            $customize_table = "
                LEFT JOIN (
                    SELECT cramschool.lesson_category.id lesson_category_id, lesson_category_blog.blog_id, 
                    COALESCE(lesson_class.class_data, '[]')class_data,
                    CASE WHEN lesson_class.enroll_status_count > 0 THEN TRUE ELSE FALSE END enroll_status,
                    cramschool.blog.order_stage_id
                    FROM cramschool.lesson_category
                    LEFT JOIN cramschool.lesson_category_blog ON cramschool.lesson_category.id = cramschool.lesson_category_blog.lesson_category_id
                    LEFT JOIN cramschool.blog ON cramschool.lesson_category_blog.blog_id = cramschool.blog.id
                    LEFT JOIN (
                        SELECT cramschool.lesson_category_lesson.lesson_category_id,
                        cramschool.lesson_category_blog.blog_id, COUNT(CASE WHEN cramschool.class.enroll_status IS TRUE THEN TRUE END)enroll_status_count,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'class_id', cramschool.class.id,
                                'lesson_id',cramschool.lesson.id,
                                'blog_id',cramschool.blog.id,
                                'name', cramschool.class.\"name\",
								'content', COALESCE(cramschool.blog.content,''),
                                'file_id', COALESCE(class_file_data.class_file_id, '[]'),
                                'student_count', COALESCE(student_count.student_count, 0),
                                'class_teachers', COALESCE(class_teachers.class_teachers,'[]'),
                                'surrounding_name', cramschool.surrounding.\"name\",
                                'lesson_category_id', lesson_category_lesson.lesson_category_id
                            )
                            ORDER BY cramschool.class.id
                        ) class_data
                        FROM cramschool.lesson_category_lesson 
                        INNER JOIN cramschool.lesson ON cramschool.lesson_category_lesson.lesson_id = cramschool.lesson.id
                        INNER JOIN cramschool.lesson_category_blog ON cramschool.lesson_category_lesson.lesson_category_id = cramschool.lesson_category_blog.lesson_category_id
                        INNER JOIN cramschool.lesson_class ON cramschool.lesson.id = cramschool.lesson_class.lesson_id
                        INNER JOIN cramschool.class ON cramschool.lesson_class.class_id = cramschool.class.id
                        LEFT JOIN cramschool.class_blog ON cramschool.class_blog.class_id = cramschool.class.id
                        LEFT JOIN cramschool.blog ON cramschool.class_blog.blog_id = cramschool.blog.id
                        LEFT JOIN cramschool.surrounding ON cramschool.class.surrounding_id = cramschool.surrounding.id
                        LEFT JOIN (
                            SELECT cramschool.class_file.class_id, 
                            JSON_AGG(
                                cramschool.class_file.file_id
                            )class_file_id
                            FROM cramschool.class_file
                            GROUP BY cramschool.class_file.class_id
                        )class_file_data ON cramschool.class.id = class_file_data.class_id
                        LEFT JOIN (
                            SELECT cramschool.grade_class.class_id, COUNT(CASE WHEN cramschool.student.id IS NOT NULL THEN 1 END)student_count
                            FROM cramschool.grade_class
                            LEFT JOIN cramschool.grade_class_student ON cramschool.grade_class.id = cramschool.grade_class_student.grade_class_id
                            LEFT JOIN cramschool.student ON cramschool.grade_class_student.student_id = cramschool.student.id
                            GROUP BY cramschool.grade_class.class_id
                        )student_count ON cramschool.class.id = student_count.class_id
                        LEFT JOIN (
                            SELECT cramschool.class.id class_id,
                            JSON_AGG(
                                cramschool.teacher.name
                            )class_teachers
                            FROM cramschool.grade_class_teacher
                            LEFT JOIN cramschool.teacher ON cramschool.grade_class_teacher.teacher_id = cramschool.teacher.id
                            LEFT JOIN cramschool.grade_class ON cramschool.grade_class_teacher.grade_class_id = cramschool.grade_class.id
                            LEFT JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                            GROUP BY cramschool.class.id
                        )class_teachers ON cramschool.class.id = class_teachers.class_id
                        -- WHERE
                        GROUP BY cramschool.lesson_category_lesson.lesson_category_id, cramschool.lesson_category_blog.blog_id
                    )lesson_class ON cramschool.lesson_category.id = lesson_class.lesson_category_id AND cramschool.lesson_category_blog.blog_id = lesson_class.blog_id                    
                )class_data ON cramschool.blog.id = class_data.blog_id
                LEFT JOIN (
                    SELECT cramschool.lesson_category.id lesson_category_id, cramschool.lesson_category_blog.blog_id,
                    COALESCE(lesson_class.lesson_data, '[]')lesson_data, lesson_class.enroll_status_count enroll_status, cramschool.blog.order_stage_id
                    FROM cramschool.lesson_category
                    LEFT JOIN cramschool.lesson_category_blog ON cramschool.lesson_category.id = cramschool.lesson_category_blog.lesson_category_id
                    LEFT JOIN cramschool.blog ON cramschool.lesson_category_blog.blog_id = cramschool.blog.id   
                    LEFT JOIN (
                        SELECT cramschool.lesson_category_lesson.lesson_category_id, cramschool.lesson_category_blog.blog_id,
                        SUM (lesson_class.enroll_status_count) enroll_status_count,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'lesson_id', cramschool.lesson.id,
                                'name',  cramschool.lesson.\"name\",
                                'outline', cramschool.lesson.outline,
                                'file_id', COALESCE(lesson_file_data.lesson_file_id, '[]'),
                                'student_count', COALESCE(student_count.student_count, 0),
                                'lesson_teachers', COALESCE(lesson_teachers.lesson_teachers,'[]'),
                                'lesson_category_id', lesson_category_lesson.lesson_category_id
                            )
                            ORDER BY cramschool.lesson.id
                        ) lesson_data
                        FROM cramschool.lesson_category_lesson 
                        INNER JOIN cramschool.lesson ON cramschool.lesson_category_lesson.lesson_id = cramschool.lesson.id
                        INNER JOIN cramschool.lesson_category_blog ON cramschool.lesson_category_lesson.lesson_category_id = cramschool.lesson_category_blog.lesson_category_id
                        INNER JOIN (
                            SELECT cramschool.lesson_class.lesson_id, COUNT(CASE WHEN cramschool.class.enroll_status IS TRUE THEN TRUE END)enroll_status_count
                            FROM cramschool.lesson_class
                            INNER JOIN cramschool.class ON cramschool.lesson_class.class_id = cramschool.class.id 
                            GROUP BY cramschool.lesson_class.lesson_id
                        )lesson_class ON cramschool.lesson.id = lesson_class.lesson_id
                        LEFT JOIN (
                            SELECT cramschool.lesson_file.lesson_id, 
                            JSON_AGG(
                                cramschool.lesson_file.file_id
                            )lesson_file_id
                            FROM cramschool.lesson_file
                            GROUP BY cramschool.lesson_file.lesson_id
                        )lesson_file_data ON cramschool.lesson.id = lesson_file_data.lesson_id
                        LEFT JOIN (
                            SELECT cramschool.lesson_class.lesson_id, COUNT(CASE WHEN cramschool.student.id IS NOT NULL THEN 1 END)student_count
                            FROM cramschool.grade_class
                            LEFT JOIN cramschool.grade_class_student ON cramschool.grade_class.id = cramschool.grade_class_student.grade_class_id
                            LEFT JOIN cramschool.student ON cramschool.grade_class_student.student_id = cramschool.student.id
                            LEFT JOIN cramschool.lesson_class ON cramschool.grade_class.class_id = cramschool.lesson_class.class_id
                            GROUP BY cramschool.lesson_class.lesson_id
                        )student_count ON cramschool.lesson.id = student_count.lesson_id
                        LEFT JOIN (
                            SELECT cramschool.lesson_class.lesson_id,
                            JSON_AGG(
                                cramschool.teacher.name
                            )lesson_teachers
                            FROM cramschool.grade_class_teacher
                            LEFT JOIN cramschool.teacher ON cramschool.grade_class_teacher.teacher_id = cramschool.teacher.id
                            LEFT JOIN cramschool.grade_class ON cramschool.grade_class_teacher.grade_class_id = cramschool.grade_class.id
                            LEFT JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                            LEFT JOIN cramschool.lesson_class ON cramschool.class.id = cramschool.lesson_class.class_id                            
                            GROUP BY cramschool.lesson_class.lesson_id
                        )lesson_teachers ON cramschool.lesson.id = lesson_teachers.lesson_id
                        -- WHERE
                        GROUP BY cramschool.lesson_category_lesson.lesson_category_id, cramschool.lesson_category_blog.blog_id
                    )lesson_class ON cramschool.lesson_category.id = lesson_class.lesson_category_id AND cramschool.lesson_category_blog.blog_id = lesson_class.blog_id
                )lesson_data ON cramschool.blog.id = lesson_data.blog_id
                LEFT JOIN (
                    SELECT cramschool.lesson_category_lesson.lesson_category_id,
                    cramschool.lesson_category_blog.blog_id,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'teacher_id', cramschool.teacher.id,
                            'teacher_name', cramschool.teacher.\"name\"
                        )
                    )  teachers
                    FROM cramschool.grade_class_teacher
                    INNER JOIN cramschool.teacher ON cramschool.grade_class_teacher.teacher_id = cramschool.teacher.id
                    INNER JOIN cramschool.grade_class ON cramschool.grade_class_teacher.grade_class_id = cramschool.grade_class.id
                    INNER JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                    INNER JOIN cramschool.lesson_class ON cramschool.class.id = cramschool.lesson_class.class_id
                    INNER JOIN cramschool.lesson ON cramschool.lesson_class.lesson_id = cramschool.lesson.id
                    INNER JOIN cramschool.lesson_category_lesson ON cramschool.lesson.id = cramschool.lesson_category_lesson.lesson_category_id
                    INNER JOIN cramschool.lesson_category_blog ON cramschool.lesson_category_lesson.lesson_category_id = cramschool.lesson_category_blog.lesson_category_id
                    GROUP BY cramschool.lesson_category_lesson.lesson_category_id, cramschool.lesson_category_blog.blog_id
                )grade_class_teacher ON cramschool.blog.id = grade_class_teacher.blog_id
            ";
            $customize_group = ", teachers, enroll_status";
        }

        //預設排序
        // 先排序置頂再排序blog 順序
        $order = "ORDER BY CASE WHEN dt.top IS true THEN 0 ELSE 1 END
                , CASE WHEN dt.top_edit_time IS NULL THEN '1900-01-01' ELSE to_char(dt.top_edit_time::timestamp, 'yyyy-MM-dd') END DESC
        ";
        $order .= ", dt.blog_index ASC ";

        // var_dump($params['order']);
        // exit(0);
        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                // var_dump($this->isJson($column_data));
                // exit(0);
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
        // var_dump($order);
        // exit(0);

        $condition = "";
        $condition_values = [
            "blog_type_id" => " AND blog_type_id = :blog_type_id",
            "blog_id" => " AND blog_id = :blog_id"
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
                    SELECT cramschool.blog.id blog_id, cramschool.blog.title, cramschool.blog.content, cramschool.blog.blog_type_id,cramschool.blog.more_content,
                    to_char(cramschool.blog.last_edit_time, 'YYYY-MM-DD')last_edit_time, 
                    to_char(cramschool.blog.annoucement_time, 'YYYY-MM-DD')annoucement_time, 
                    to_char(cramschool.blog.top_edit_time, 'YYYY-MM-DD')top_edit_time, cramschool.blog.top, 
                    cramschool.blog.blog_index,cramschool.blog.background_color,cramschool.blog.font_color,cramschool.blog.enroll_status_background_color,
                    cramschool.blog.enroll_status_font_color,cramschool.blog.pic_background_color,
                    COALESCE(blog_file.file_id,'[]')file_id {$customize_select}
                    FROM cramschool.blog
                    LEFT JOIN (
                        SELECT cramschool.blog_file.blog_id,
                        JSON_AGG(
                                cramschool.blog_file.file_id
                                ORDER BY cramschool.blog_file.file_id DESC
                        ) file_id
                        FROM cramschool.blog_file
                        GROUP BY cramschool.blog_file.blog_id
                    )blog_file ON cramschool.blog.id = blog_file.blog_id
                    {$customize_table}
                )dt
                WHERE TRUE {$condition} AND NOW()::date - annoucement_time::date >= 0 {$select_condition}  
                {$order}
        ";
        // var_dump($sql_default);
        // exit(0);
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_blog($data, $blog_type_id, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $blog_bind_values = [
                "last_edit_user_id" => 0,
                "title" => "",
                "content" => "",
                "annoucement_time" => "NOW()",
                "file_id" => null,
                "blog_type_id" => $blog_type_id,
                "blog_index" => null,
                "more_content" => '',
                "background_color" => '',
                "font_color" => '',
                "enroll_status_background_color" => '',
                "enroll_status_font_color" => '',
                "order_stage_id" => "",
                "pic_background_color" => '',
            ];
            $blog_file_bind_values = [
                "blog_id" => "",
                "file_id" => null,
            ];
            $delete_blog_file_bind_values = [
                "blog_id" => "",
            ];

            $blog_insert_cond = "";
            $blog_values_cond = "";

            $select_blog_condition = "";
            $select_blog_condition_values = [
                "blog_type_id" => " AND cramschool.blog.blog_type_id = :blog_type_id",
                "title" => " AND cramschool.blog.title = :title"
            ];
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['blog_type_id'] = $blog_type_id;

            foreach ($select_blog_condition_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $select_blog_condition .= $value;
                }
            }

            foreach ($blog_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id') {
                        $blog_bind_values[$key] = $column[$key];
                    } else {
                        $blog_bind_values[$key] = $column[$key];
                        $blog_insert_cond .= "{$key},";
                        $blog_values_cond .= ":{$key},";
                    }
                } else {
                    unset($blog_bind_values[$key]);
                }
            }

            $blog_insert_cond .= "last_edit_time,";
            $blog_values_cond .= "NOW(),";

            $file_id = $blog_bind_values['file_id'];
            unset($blog_bind_values['file_id']);
            $blog_insert_cond = rtrim($blog_insert_cond, ',');
            $blog_values_cond = rtrim($blog_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.blog({$blog_insert_cond})
                VALUES ({$blog_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.blog_file
                WHERE cramschool.blog_file.blog_id = :blog_id
            ";
            $stmt_delete_blog_file = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($blog_bind_values)) {
                $blog_id = $stmt_insert->fetchColumn(0);
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }

            $blog_file_bind_values['blog_id'] = $blog_id;
            $blog_file_bind_values['file_id'] = $file_id;

            foreach ($delete_blog_file_bind_values as $key => $value) {
                if (array_key_exists($key, $blog_file_bind_values)) {
                    $delete_blog_file_bind_values[$key] = $blog_file_bind_values[$key];
                }
            }
            $stmt_delete_blog_file->execute($delete_blog_file_bind_values);
            if (array_key_exists('file_id', $column)) {
                if ($column['file_id'] != null) {
                    $this->multi_blog_file_insert($blog_file_bind_values);
                }
            }
            if ($blog_type_id == 2) {
                $column['blog_id'] = $blog_id;
                $this->post_surrounding_blog($column);
            } else if ($blog_type_id == 4) {
                $column['blog_id'] = $blog_id;
                $this->post_teacher_blog($column);
            } else if ($blog_type_id == 5) {
                $column['blog_id'] = $blog_id;
                $this->post_lesson_category_blog($column);
            } else if ($blog_type_id == 11) {
                $column['blog_id'] = $blog_id;
                $this->post_lesson_blog($column);
            } else if ($blog_type_id == 16) {
                $column['blog_id'] = $blog_id;
                $this->post_class_blog($column);
            } else {
                $column['blog_id'] = $blog_id;
                $this->post_lesson_blog($column);
            }
            $result = ["status" => "success"];
        }
        return $result;
    }

    public function patch_blog($data, $blog_type_id, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $blog_bind_values = [
                "last_edit_user_id" => 0,
                "blog_id" => "",
                "title" => "",
                "content" => "",
                "annoucement_time" => null,
                "file_id" => null,
                "blog_type_id" => $blog_type_id,
                "last_edit_time" => "NOW()",
                "top_edit_time" => "NOW()",
                "top" => false,
                "blog_index" => null,
                "more_content" => "",
                "background_color" => "",
                "font_color" => "",
                "enroll_status_background_color" => "",
                "enroll_status_font_color" => "",
                "order_stage_id" => "",
                "pic_background_color" => "",
            ];
            $delete_blog_file_bind_values = [
                "blog_id" => "",
            ];
            $insert_blog_file_bind_values = [
                "blog_id" => "",
                "file_id" => null,
            ];
            $blog_type_values = [
                "blog_type_id" => $blog_type_id,
                "background_color" => ""
            ];

            $blog_upadte_cond = "";
            $blog_fliter_cond = "";
            $blog_file_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = "NOW()";

            foreach ($blog_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'blog_id') {
                        $blog_bind_values[$key] = $column[$key];
                    } else {
                        if ($key === "top") {
                            if ($column[$key] === false)
                                $column[$key] = 0;
                            else
                                $column[$key] = 1;
                        }
                        $blog_bind_values[$key] = $column[$key];
                        $blog_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($blog_bind_values[$key]);
                }
            }

            $blog_fliter_cond .= "AND cramschool.blog.id = :blog_id";
            $blog_file_fliter_cond .= "AND cramschool.blog_file.blog_id = :blog_id";
            $blog_upadte_cond = rtrim($blog_upadte_cond, ',');

            $blog_type_upadte_cond = "";
            $blog_type_fliter_cond = "";

            foreach ($insert_blog_file_bind_values as $key => $value) {
                if (array_key_exists($key, $blog_bind_values)) {
                    $insert_blog_file_bind_values[$key] = $blog_bind_values[$key];
                }
            }

            foreach ($delete_blog_file_bind_values as $key => $value) {
                if (array_key_exists($key, $blog_bind_values)) {
                    $delete_blog_file_bind_values[$key] = $blog_bind_values[$key];
                }
            }

            foreach ($blog_type_values as $key => $value) {
                if (array_key_exists($key, $blog_bind_values)) {
                    $blog_type_values[$key] = $blog_bind_values[$key];
                    $blog_type_upadte_cond .= "{$key} = :{$key},";
                }
            }

            $file_id = $blog_bind_values['file_id'];
            unset($blog_bind_values['file_id']);
            $sql = "UPDATE cramschool.blog
                    SET {$blog_upadte_cond}
                    WHERE TRUE {$blog_fliter_cond}
            ";

            $blog_type_fliter_cond .= "AND cramschool.blog_type.id = :blog_type_id";
            $blog_type_upadte_cond = rtrim($blog_type_upadte_cond, ',');

            $sql_blog_type = "UPDATE cramschool.blog_type
                    SET {$blog_type_upadte_cond}
                    WHERE TRUE {$blog_type_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($blog_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.blog_file
                        WHERE TRUE {$blog_file_fliter_cond}
                    ";
                    $stmt_delete_blog_file = $this->db->prepare($sql_delete);
                    $stmt_delete_blog_file->execute($delete_blog_file_bind_values);
                    $stmt_blog_type = $this->db->prepare($sql_blog_type);
                    $stmt_blog_type->execute($blog_type_values);
                    if ($column['file_id'] != null) {
                        $this->multi_blog_file_insert($insert_blog_file_bind_values);
                    }
                }
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
    public function multi_lesson_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {

            $lesson_file_insert_cond = "";
            $lesson_file_values_cond = "";

            $per_lesson_file_bind_values = [
                "lesson_id" => 0,
                "file_id" => 0
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_lesson_file_bind_values[$key] = $per_file_id;
                    $lesson_file_insert_cond .= "{$key},";
                    $lesson_file_values_cond .= ":{$key},";
                } else {
                    $per_lesson_file_bind_values[$key] = $datas[$key];
                    $lesson_file_insert_cond .= "{$key},";
                    $lesson_file_values_cond .= ":{$key},";
                }
            }
            $lesson_file_insert_cond = rtrim($lesson_file_insert_cond, ',');
            $lesson_file_values_cond = rtrim($lesson_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.lesson_file({$lesson_file_insert_cond})
                VALUES ({$lesson_file_values_cond})
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($per_lesson_file_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
            }
        }
    }

    public function multi_blog_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $blog_file_insert_cond = "";
            $blog_file_values_cond = "";

            $per_blog_file_bind_values = [
                "blog_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_blog_file_bind_values[$key] = $per_file_id;
                    $blog_file_insert_cond .= "{$key},";
                    $blog_file_values_cond .= ":{$key},";
                } else {
                    $per_blog_file_bind_values[$key] = $datas[$key];
                    $blog_file_insert_cond .= "{$key},";
                    $blog_file_values_cond .= ":{$key},";
                }
            }
            $blog_file_insert_cond = rtrim($blog_file_insert_cond, ',');
            $blog_file_values_cond = rtrim($blog_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.blog_file({$blog_file_insert_cond})
                VALUES ({$blog_file_values_cond})
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($per_blog_file_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
            }
        }
    }

    public function delete_blog($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_blog_file_bind_values = [
                "blog_id" => "",
            ];

            foreach ($delete_blog_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_blog_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.blog
                WHERE cramschool.blog.id = :blog_id
            ";
            $stmt_delete_blog_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_blog_file->execute($delete_blog_file_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function get_learn_witness($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "learn_witness_type_id" => null,
            "learn_witness_id" => null
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
            "learn_witness_type_id" => " AND cramschool.learn_witness.learn_witness_type_id = :learn_witness_type_id",
            "learn_witness_id" => " AND cramschool.learn_witness.id = :learn_witness_id"
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

        $sql = "SELECT *
                FROM(
                    SELECT  learn_witness_type.id learn_witness_type_id, learn_witness_type.\"name\" learn_witness_type_name,
                    COALESCE(learn_witness_all.learn_witness, '[]') learn_witness,
                    ROW_NUMBER() OVER (ORDER BY learn_witness_type.id) \"key\"
                    FROM cramschool.learn_witness_type
                    LEFT JOIN  (
                        SELECT cramschool.learn_witness.learn_witness_type_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'learn_witness_id', cramschool.learn_witness.id,
                                    'title',  cramschool.learn_witness.title,
                                    'content', cramschool.learn_witness.content,
                                    'more_content',cramschool.learn_witness.more_content,
                                    'prove_stu', learn_witness_stu.learn_witness_stu,
                                    'last_edit_time', to_char(cramschool.learn_witness.last_edit_time, 'YYYY-MM-DD'),
                                    'annoucement_time', to_char(cramschool.learn_witness.annoucement_time, 'YYYY-MM-DD'),
                                    'file_id', COALESCE(learn_witness_file.file_id, '{}')
                                )
                                ORDER BY cramschool.learn_witness.id
                            ) learn_witness
                        FROM cramschool.learn_witness
                        LEFT JOIN (
                            SELECT cramschool.learn_witness_file.learn_witness_id,
                            ARRAY_AGG(
                                (CASE WHEN cramschool.learn_witness_file.file_id IS NOT NULL THEN file_id  END)
                                ORDER BY cramschool.learn_witness_file.file_id
                            ) file_id
                            FROM cramschool.learn_witness_file
                            GROUP BY cramschool.learn_witness_file.learn_witness_id
                        )learn_witness_file ON cramschool.learn_witness.id = learn_witness_file.learn_witness_id
                        LEFT JOIN (
                            SELECT cramschool.learn_witness_stu.learn_witness_id, 
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'prove_stu_id', cramschool.learn_witness_stu.student_id,
                                    'student_name', cramschool.student.\"name\"
                                )
                                ORDER BY cramschool.learn_witness_stu.learn_witness_id
                            ) learn_witness_stu
                            FROM cramschool.learn_witness_stu
                            LEFT JOIN cramschool.student ON cramschool.learn_witness_stu.student_id = cramschool.student.id
                            GROUP BY cramschool.learn_witness_stu.learn_witness_id
                        )learn_witness_stu ON cramschool.learn_witness.id = learn_witness_stu.learn_witness_id
                        WHERE TRUE {$condition}
                        GROUP BY cramschool.learn_witness.learn_witness_type_id
                    )learn_witness_all ON cramschool.learn_witness_type.id = learn_witness_all.learn_witness_type_id
                    LIMIT :length
                )dt
                WHERE \"key\" > :start            
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($bind_values)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_learn_witness($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $learn_witness_values = [
                "last_edit_user_id" => 0,
                "title" => "",
                "content" => "",
                "annoucement_time" => null,
                "prove_stu_id" => [],
                "file_id" => null,
                "learn_witness_type_id" => 0,
                "more_content" => "",
            ];
            $select_learn_witness_bind_values = [
                "title" => "",
                "learn_witness_type_id" => 0,
            ];
            $learn_witness_file_bind_values = [
                "learn_witness_id" => "",
                "file_id" => null,
            ];
            $delete_learn_witness_file_bind_values = [
                "learn_witness_id" => "",
            ];
            $learn_witness_stu_bind_values = [
                "learn_witness_id" => "",
                "prove_stu_id" => null,
            ];
            $delete_learn_witness_stu_bind_values = [
                "learn_witness_id" => "",
            ];

            $learn_witness_insert_cond = "";
            $learn_witness_values_cond = "";

            $select_learn_witness_condition = "";
            $select_learn_witness_condition_values = [
                "learn_witness_type_id" => " AND cramschool.learn_witness.learn_witness_type_id = :learn_witness_type_id",
                "title" => " AND cramschool.learn_witness.title = :title"
            ];
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($select_learn_witness_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $select_learn_witness_bind_values[$key] = $column[$key];
                }
            }

            foreach ($select_learn_witness_condition_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $select_learn_witness_condition .= $value;
                }
            }

            foreach ($learn_witness_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'prove_stu_id') {
                        $learn_witness_bind_values[$key] = $column[$key];
                    } else {
                        $learn_witness_bind_values[$key] = $column[$key];
                        $learn_witness_insert_cond .= "{$key},";
                        $learn_witness_values_cond .= ":{$key},";
                    }
                }
            }

            $learn_witness_insert_cond .= "last_edit_time,";
            $learn_witness_values_cond .= "NOW(),";

            $file_id = $learn_witness_bind_values['file_id'];
            $stu_id = $learn_witness_bind_values['prove_stu_id'];
            unset($learn_witness_bind_values['file_id']);
            unset($learn_witness_bind_values['prove_stu_id']);
            $learn_witness_insert_cond = rtrim($learn_witness_insert_cond, ',');
            $learn_witness_values_cond = rtrim($learn_witness_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.learn_witness({$learn_witness_insert_cond})
                VALUES ({$learn_witness_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.learn_witness_file
                WHERE cramschool.learn_witness_file.learn_witness_id = :learn_witness_id
            ";
            $stmt_delete_learn_witness_file = $this->db->prepare($sql_delete);

            $sql_delete = "DELETE FROM cramschool.learn_witness_stu
                WHERE cramschool.learn_witness_stu.learn_witness_id = :learn_witness_id
            ";
            $learn_witness_stu = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($learn_witness_bind_values)) {
                $learn_witness_id = $stmt_insert->fetchColumn(0);
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }

            $learn_witness_file_bind_values['learn_witness_id'] = $learn_witness_id;
            $learn_witness_file_bind_values['file_id'] = $file_id;
            $learn_witness_stu_bind_values['learn_witness_id'] = $learn_witness_id;
            $learn_witness_stu_bind_values['prove_stu_id'] = $stu_id;

            foreach ($delete_learn_witness_file_bind_values as $key => $value) {
                if (array_key_exists($key, $learn_witness_file_bind_values)) {
                    $delete_learn_witness_file_bind_values[$key] = $learn_witness_file_bind_values[$key];
                }
            }
            foreach ($delete_learn_witness_stu_bind_values as $key => $value) {
                if (array_key_exists($key, $learn_witness_stu_bind_values)) {
                    $delete_learn_witness_stu_bind_values[$key] = $learn_witness_stu_bind_values[$key];
                }
            }
            $stmt_delete_learn_witness_file->execute($delete_learn_witness_file_bind_values);
            $learn_witness_stu->execute($delete_learn_witness_stu_bind_values);
            if (array_key_exists('file_id', $column)) {
                if ($column['file_id'] != null) {
                    $this->multi_learn_witness_file_insert($learn_witness_file_bind_values);
                }
            }
            if (array_key_exists('prove_stu_id', $column)) {
                if ($column['prove_stu_id'] != null) {
                    $this->multi_learn_witness_stu_insert($learn_witness_stu_bind_values);
                }
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_learn_witness($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $learn_witness_values = [
                "last_edit_user_id" => 0,
                "learn_witness_id" => "",
                "title" => "",
                "content" => "",
                "annoucement_time" => null,
                "prove_stu_id" => [],
                "file_id" => null,
                "learn_witness_type_id" => 0,
                "more_content" => ""
            ];
            $delete_learn_witness_file_bind_values = [
                "learn_witness_id" => "",
            ];
            $insert_learn_witness_file_bind_values = [
                "learn_witness_id" => "",
                "file_id" => null,
            ];
            $learn_witness_stu_bind_values = [
                "learn_witness_id" => "",
                "prove_stu_id" => null,
            ];
            $delete_learn_witness_stu_bind_values = [
                "learn_witness_id" => "",
            ];


            $learn_witness_upadte_cond = "";
            $learn_witness_fliter_cond = "";
            $learn_witness_file_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($learn_witness_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'learn_witness_id' || $key == 'prove_stu_id') {
                        $learn_witness_bind_values[$key] = $column[$key];
                    } else {
                        $learn_witness_bind_values[$key] = $column[$key];
                        $learn_witness_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($learn_witness_bind_values[$key]);
                }
            }

            $learn_witness_upadte_cond .= "last_edit_time = NOW(),";
            $learn_witness_fliter_cond .= "AND cramschool.learn_witness.id = :learn_witness_id";
            $learn_witness_file_fliter_cond .= "AND cramschool.learn_witness_file.learn_witness_id = :learn_witness_id";
            $learn_witness_upadte_cond = rtrim($learn_witness_upadte_cond, ',');

            foreach ($insert_learn_witness_file_bind_values as $key => $value) {
                if (array_key_exists($key, $learn_witness_bind_values)) {
                    $insert_learn_witness_file_bind_values[$key] = $learn_witness_bind_values[$key];
                }
            }

            foreach ($delete_learn_witness_file_bind_values as $key => $value) {
                if (array_key_exists($key, $learn_witness_bind_values)) {
                    $delete_learn_witness_file_bind_values[$key] = $learn_witness_bind_values[$key];
                }
            }
            foreach ($delete_learn_witness_stu_bind_values as $key => $value) {
                if (array_key_exists($key, $learn_witness_stu_bind_values)) {
                    $delete_learn_witness_stu_bind_values[$key] = $learn_witness_stu_bind_values[$key];
                }
            }


            $file_id = $learn_witness_bind_values['file_id'];
            $stu_id = $learn_witness_bind_values['prove_stu_id'];
            unset($learn_witness_bind_values['file_id']);
            unset($learn_witness_bind_values['prove_stu_id']);
            $learn_witness_stu_bind_values['learn_witness_id'] = $learn_witness_bind_values['learn_witness_id'];
            $learn_witness_stu_bind_values['prove_stu_id'] = $stu_id;

            $sql = "UPDATE cramschool.learn_witness
                    SET {$learn_witness_upadte_cond}
                    WHERE TRUE {$learn_witness_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($learn_witness_bind_values)) {
                $sql_delete = "DELETE FROM cramschool.learn_witness_file
                    WHERE TRUE {$learn_witness_file_fliter_cond}
                ";
                $stmt_delete_learn_witness_file = $this->db->prepare($sql_delete);
                $stmt_delete_learn_witness_file->execute($delete_learn_witness_file_bind_values);
                $sql_delete = "DELETE FROM cramschool.learn_witness_stu
                    WHERE cramschool.learn_witness_stu.learn_witness_id = :learn_witness_id
                ";
                $learn_witness_stu = $this->db->prepare($sql_delete);
                $learn_witness_stu->execute($delete_learn_witness_stu_bind_values);

                $this->multi_learn_witness_file_insert($insert_learn_witness_file_bind_values);
                if (array_key_exists('prove_stu_id', $column)) {
                    if ($column['prove_stu_id'] != null) {
                        $this->multi_learn_witness_stu_insert($learn_witness_stu_bind_values);
                    }
                }
            } else {
                $result = ['status' => 'failure', 'info' => $learn_witness_bind_values];
                return $result;
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function multi_learn_witness_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $learn_witness_file_insert_cond = "";
            $learn_witness_file_values_cond = "";

            $per_learn_witness_file_bind_values = [
                "learn_witness_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_learn_witness_file_bind_values[$key] = $per_file_id;
                    $learn_witness_file_insert_cond .= "{$key},";
                    $learn_witness_file_values_cond .= ":{$key},";
                } else {
                    $per_learn_witness_file_bind_values[$key] = $datas[$key];
                    $learn_witness_file_insert_cond .= "{$key},";
                    $learn_witness_file_values_cond .= ":{$key},";
                }
            }
            $learn_witness_file_insert_cond = rtrim($learn_witness_file_insert_cond, ',');
            $learn_witness_file_values_cond = rtrim($learn_witness_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.learn_witness_file({$learn_witness_file_insert_cond})
                VALUES ({$learn_witness_file_values_cond})
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_learn_witness_file_bind_values);
        }
    }

    public function multi_learn_witness_stu_insert($datas)
    {
        foreach ($datas['prove_stu_id'] as $row => $per_stu_id) {
            $learn_witness_stu_insert_cond = "";
            $learn_witness_stu_values_cond = "";

            $per_learn_witness_stu_bind_values = [
                "learn_witness_id" => "",
                "prove_stu_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if (array_key_exists($key, $datas)) {
                    if ($key == 'prove_stu_id') {
                        $per_learn_witness_stu_bind_values['student_id'] = $per_stu_id;
                        $learn_witness_stu_insert_cond .= "student_id,";
                        $learn_witness_stu_values_cond .= ":student_id,";
                        unset($per_learn_witness_stu_bind_values[$key]);
                    } else {
                        $per_learn_witness_stu_bind_values[$key] = $datas[$key];
                        $learn_witness_stu_insert_cond .= "{$key},";
                        $learn_witness_stu_values_cond .= ":{$key},";
                    }
                }
            }
            $learn_witness_stu_insert_cond = rtrim($learn_witness_stu_insert_cond, ',');
            $learn_witness_stu_values_cond = rtrim($learn_witness_stu_values_cond, ',');

            $sql = "INSERT INTO cramschool.learn_witness_stu({$learn_witness_stu_insert_cond})
                VALUES ({$learn_witness_stu_values_cond})
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_learn_witness_stu_bind_values);
        }
    }

    public function delete_learn_witness($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_learn_witness_file_bind_values = [
                "learn_witness_id" => "",
            ];

            foreach ($delete_learn_witness_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_learn_witness_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.learn_witness
                WHERE cramschool.learn_witness.id = :learn_witness_id
            ";
            $stmt_delete_learn_witness_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_learn_witness_file->execute($delete_learn_witness_file_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
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

        $sql = "SELECT cramschool.file.file_name, cramschool.file.file_client_name
                FROM cramschool.file
                WHERE cramschool.file.id = :file_id
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
        $sql = "INSERT INTO cramschool.file(
            user_id, file_name, file_client_name, last_edit_user_id, last_edit_time, upload_time)
            VALUES (:user_id, :file_name, :file_client_name, :last_edit_user_id, NOW(), NOW());
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':file_name', $data['file_name'], PDO::PARAM_STR);
        $stmt->bindParam(':file_client_name', $data['file_client_name'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_STR);
        $stmt->bindParam(':last_edit_user_id', $data['last_edit_user_id'], PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function patch_file($data)
    {
        foreach ($data as $row => $column) {
            $blog_bind_values = [
                "file_id" => null,
                "file_client_name" => "",
                "last_edit_user_id" => 0,
                "last_edit_time" => "NOW()"
            ];

            $blog_upadte_cond = "";
            $blog_fliter_cond = "";

            foreach ($blog_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if (array_key_exists('file_id', $column)) {
                    } else {
                        $blog_bind_values[$key] = $column[$key];
                        $blog_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($blog_bind_values[$key]);
                }
            }

            // $blog_upadte_cond .= "last_edit_time = :last_edit_time,";
            $blog_fliter_cond .= "AND cramschool.file.id = :file_id";
            $blog_upadte_cond = rtrim($blog_upadte_cond, ',');

            unset($blog_bind_values['file_id']);
            $sql = "UPDATE cramschool.blog
                    SET {$blog_upadte_cond}
                    WHERE TRUE {$blog_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if (!$stmt->execute($blog_bind_values)) {
                $result = ['status' => 'failure', 'message' => 'patch name failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_student($params)
    {
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

        $values = [
            "student_id" => null,
            "study_time_start" => null,
            "study_time_end" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'study_time_start' || $key == 'study_time_end') {
                    if ($params[$key] == '') {
                        unset($values[$key]);
                    } else {
                        $values[$key] = $params[$key];
                    }
                } else {
                    $values[$key] = $params[$key];
                }
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $select_condition = "";
        $condition_values = [
            "student_id" => " AND student_id = :student_id",
            "study_time_start" => " AND (EXTRACT(DAY FROM study_time_start::timestamp - :study_time_start::timestamp) >= 0 AND study_time_start::timestamp IS NOT NULL)",
            "study_time_end" => " AND (EXTRACT(DAY FROM study_time_end::timestamp - :study_time_end::timestamp) <= 0 AND study_time_end::timestamp IS NOT NULL)",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'study_time_start' || $key == 'study_time_end') {
                    if ($params[$key] == '') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else {
                    $condition .= $value;
                }
            } else {
                unset($condition_values[$key]);
            }
        }

        $searchable_columns = ['grade_name', 'serial_name', 'name', 'name_english', 'phone', 'email', 'school', 'study_time_start', 'study_time_end', 'address', 'note'];
        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
            $select_condition = " AND (";
            foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
                if (in_array($select_filter_arr_data, $searchable_columns)) {
                    $select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
                }
            }
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }

        $values["start"] = $start;
        $values["length"] = $length;
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

        //預設排序
        $order = '';
        $default_order = ', student_id';

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
                    case 'study_time_start':
                        $order .= " to_char(study_time_start::timestamp, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    case 'study_time_end':
                        $order .= " to_char(study_time_end::timestamp, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }
        $order == '' ? $order = 'ORDER BY student_id' : $order .= $default_order;

        if ($excel_check) {
            $sql_default_inside = "SELECT cramschool.student.id student_id, cramschool.student.\"name\", cramschool.student.phone, cramschool.student.note,
                            to_char(cramschool.student.study_time_start, 'YYYY-MM-DD')study_time_start, to_char(cramschool.student.study_time_end, 'YYYY-MM-DD')study_time_end,
                            cramschool.grade.id grade_id, cramschool.grade.\"name\" grade_name, cramschool.student.\"address\",
                            cramschool.student.serial_name, cramschool.student.school, cramschool.student.json_data, \"system\".user.email,
                            cramschool.student.name_english, student_class.student_class,
                            emergency_contact.emergency_contact_name1, emergency_contact.emergency_contact_phone_number1,
                            emergency_contact.emergency_contact_name2, emergency_contact.emergency_contact_phone_number2,
                            emergency_contact.emergency_contact_name3, emergency_contact.emergency_contact_phone_number3
                            FROM cramschool.student
                            LEFT JOIN (
                                SELECT cramschool.student_file.student_id,
                                    JSON_AGG(
                                            cramschool.student_file.file_id
                                            ORDER BY cramschool.student_file.file_id DESC
                                    ) file_id
                                FROM cramschool.student_file
                                GROUP BY cramschool.student_file.student_id
                            )student_file ON cramschool.student.id = student_file.student_id
                            LEFT JOIN \"system\".user ON cramschool.student.user_id = \"system\".user.id
                            LEFT JOIN cramschool.grade ON cramschool.student.grade_id = cramschool.grade.id
                            LEFT JOIN (
                                SELECT cramschool.emergency_relation.relation_user_id,
                                    string_agg(cramschool.emergency_contact.\"name\", ',') \"emergency_contact_name1\",
                                    string_agg(cramschool.emergency_contact.phone_number, ',') \"emergency_contact_phone_number1\",
                                    string_agg('-', ',') \"emergency_contact_name2\",
                                    string_agg('-', ',') \"emergency_contact_phone_number2\",
                                    string_agg('-', ',') \"emergency_contact_name3\",
                                    string_agg('-', ',') \"emergency_contact_phone_number3\" 
                    -- 				JSON_AGG(
                    -- 					JSON_BUILD_OBJECT(
                    -- 						'emergency_contact_id', cramschool.emergency_contact.id,
                    -- 						'emergency_contact_name', cramschool.emergency_contact.\"name\",
                    -- 						'emergency_contact_phone_number', cramschool.emergency_contact.phone_number                           
                    -- 					)
                    -- 					ORDER BY cramschool.emergency_contact.\"name\" DESC
                    -- 				) emergency_contact
                                FROM cramschool.emergency_relation
                                LEFT JOIN cramschool.emergency_contact ON cramschool.emergency_relation.emergency_contact_user_id = cramschool.emergency_contact.\"user_id\"
                                GROUP BY cramschool.emergency_relation.relation_user_id
                            )emergency_contact ON cramschool.student.user_id = emergency_contact.relation_user_id
                            LEFT JOIN (
                                SELECT cramschool.grade_class_student.student_id, 
                                JSON_AGG(
                                    JSON_BUILD_OBJECT(
                                        'class_id', cramschool.class.id,
                                        'class_name', cramschool.class.\"name\",
                                        'class_name_serial', cramschool.class.name_serial                           
                                    )
                                    ORDER BY cramschool.class.\"name\" DESC
                                ) student_class
                                FROM cramschool.grade_class_student
                                LEFT JOIN cramschool.grade_class ON cramschool.grade_class_student.grade_class_id = cramschool.grade_class.id
                                LEFT JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                                GROUP BY cramschool.grade_class_student.student_id
                            )student_class ON cramschool.student.id = student_class.student_id
        ";
        } else {
            $sql_default_inside = "SELECT cramschool.student.id student_id, cramschool.student.\"name\", cramschool.student.phone, cramschool.student.note,
                        to_char(cramschool.student.study_time_start, 'YYYY-MM-DD')study_time_start, to_char(cramschool.student.study_time_end, 'YYYY-MM-DD')study_time_end,
                        cramschool.grade.id grade_id, cramschool.grade.\"name\" grade_name, cramschool.student.\"address\",
                        cramschool.student.serial_name, cramschool.student.school, cramschool.student.json_data, \"system\".user.email,
                        cramschool.student.name_english, student_class.student_class,
                        COALESCE(emergency_contact.emergency_contact,'[]')emergency_contact, COALESCE(student_file.file_id, '[]')file_id
                        FROM cramschool.student
                        LEFT JOIN (
                            SELECT cramschool.student_file.student_id,
                                JSON_AGG(
                                        cramschool.student_file.file_id
                                        ORDER BY cramschool.student_file.file_id DESC
                                ) file_id
                            FROM cramschool.student_file
                            GROUP BY cramschool.student_file.student_id
                        )student_file ON cramschool.student.id = student_file.student_id
                        LEFT JOIN \"system\".user ON cramschool.student.user_id = \"system\".user.id
                        LEFT JOIN cramschool.grade ON cramschool.student.grade_id = cramschool.grade.id
                        LEFT JOIN (
                            SELECT cramschool.emergency_relation.relation_user_id,
                                JSON_AGG(
                                    JSON_BUILD_OBJECT(
                                        'emergency_contact_id', cramschool.emergency_contact.id,
                                        'emergency_contact_name', cramschool.emergency_contact.\"name\",
                                        'emergency_contact_phone_number', cramschool.emergency_contact.phone_number                           
                                    )
                                    ORDER BY cramschool.emergency_contact.\"name\" DESC
                                ) emergency_contact
                            FROM cramschool.emergency_relation
                            LEFT JOIN cramschool.emergency_contact ON cramschool.emergency_relation.emergency_contact_user_id = cramschool.emergency_contact.user_id
                            GROUP BY cramschool.emergency_relation.relation_user_id
                        )emergency_contact ON cramschool.student.user_id = emergency_contact.relation_user_id
                        LEFT JOIN (
                            SELECT cramschool.grade_class_student.student_id, 
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'class_id', cramschool.class.id,
                                    'class_name', cramschool.class.\"name\",
                                    'class_name_serial', cramschool.class.name_serial                           
                                )
                                ORDER BY cramschool.class.\"name\" DESC
                            ) student_class
                            FROM cramschool.grade_class_student
                            LEFT JOIN cramschool.grade_class ON cramschool.grade_class_student.grade_class_id = cramschool.grade_class.id
                            LEFT JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                            GROUP BY cramschool.grade_class_student.student_id
                        )student_class ON cramschool.student.id = student_class.student_id
        ";
        }
        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                        FROM (
                            {$sql_default_inside}
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

        $sql_people = "SELECT COUNT(*)
            FROM(
                {$sql_default_inside}
            )sql_default_inside
        ";
        // var_dump($excel_check);
        // exit(0);
        if ($excel_check) {
            $values = [
                "serial_name" => "學號",
                "name" => "學生姓名",
                "name_english" => "英文姓名",
                "grade_name" => "當前年級",
                "phone" => "手機號碼",
                "email" => "電子信箱",
                "study_time_start" => "就學時間（起）",
                "study_time_end" => "就學時間（迄）",
                "address" => "地址",
                "emergency_contact_name" => "緊急聯絡人姓名",
                "emergency_contact_phone_number" => "緊急聯絡人電話",
                "school" => "學校",
                "note" => "備註",
            ];

            $excel_column = "COALESCE(\"serial_name\", '-') \"{$values["serial_name"]}\",";
            if (array_key_exists('language', $params)) {
                foreach ($params['language'] as $language) {
                    if ($this->isJson($language)) {
                        foreach (json_decode($language, true) as $key => $value) {
                            if (array_key_exists($key, $values) & $key !== 'serial_name') {
                                if ($key == 'emergency_contact_name' || $key == 'emergency_contact_phone_number') {
                                    //預設三筆
                                    for ($i = 1; $i < 4; $i++) {
                                        $new_key_name = "{$key}{$i}";
                                        $label_name = "{$values[$key]}{$i}";
                                        $excel_column .= "COALESCE(\"{$new_key_name}\", '-') \"{$label_name}\",";
                                    }
                                } else {
                                    $label = empty(@$value['zh-tw']) ? $values[$key] : $value['zh-tw'];
                                    $excel_column .= "COALESCE(\"{$key}\", '-') \"{$label}\",";
                                }
                            }
                        }
                    }
                }
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
                return $result;
            } else {
                var_dump($stmt->errorInfo());
                return [
                    "status" => "failed",
                    "message" => $stmt->errorInfo()
                ];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_people = $this->db->prepare($sql_people);
        if ($stmt->execute($values) && $stmt_count->execute($values_count) && $stmt_people->execute()) {
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_student($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $student_values = [
                "id" => 0,
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "name" => "",
                "name_english" => "",
                "phone" => "",
                "phone_home" => "",
                "birthday" => "",
                "emergency_contact" => [],
                "note" => "",
                "study_time_start" => "",
                "study_time_end" => "",
                "grade_id" => "",
                "gender_id" => "",
                "address" => "",
                "serial_name" => "",
                "school" => "",
                "school_scroe" => "",
                "json_data" => "",
                "file_id" => null
            ];
            $student_file_bind_values = [
                "student_id" => "",
                "file_id" => null,
            ];
            $delete_student_file_bind_values = [
                "student_id" => "",
            ];
            $student_emergency_contact_bind_values = [
                "user_id" => 0,
                "relation_user_id" => 0,
                "emergency_contact" => [],
                "serial_name" => "",
            ];
            $delete_student_emergency_contact_bind_values = [
                "user_id" => 0,
            ];
            $update_contact_bind_values = [
                "user_id" => 0,
                "id" => 0,
            ];

            $student_insert_cond = "";
            $student_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($student_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'emergency_contact') {
                        $student_bind_values[$key] = $column[$key];
                    } else if ($key == 'json_data') {
                        $column[$key] != null ? $student_bind_values[$key] = json_encode($column[$key]) : null;
                        $student_insert_cond .= "{$key},";
                        $student_values_cond .= ":{$key},";
                    } else {
                        $student_bind_values[$key] = $column[$key];
                        $student_insert_cond .= "{$key},";
                        $student_values_cond .= ":{$key},";
                    }
                } else {
                    unset($student_bind_values[$key]);
                }
            }

            $student_insert_cond .= "last_edit_time,";
            $student_values_cond .= "NOW(),";

            $file_id = $student_bind_values['file_id'];
            $emergency_contact = $student_bind_values['emergency_contact'];
            unset($student_bind_values['file_id']);
            unset($student_bind_values['emergency_contact']);
            $student_insert_cond = rtrim($student_insert_cond, ',');
            $student_values_cond = rtrim($student_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.student({$student_insert_cond})
                VALUES ({$student_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.student_file
                WHERE cramschool.student_file.student_id = :student_id
            ";
            $stmt_delete_student_file = $this->db->prepare($sql_delete);

            $sql_delete = "DELETE FROM cramschool.emergency_contact
                WHERE cramschool.emergency_contact.user_id = :user_id
            ";
            $stmt_delete_student_emergency_contact = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($student_bind_values)) {
                $student_id = $stmt_insert->fetchColumn(0);
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }

            $student_file_bind_values['student_id'] = $student_id;
            $student_file_bind_values['file_id'] = $file_id;

            $student_emergency_contact_bind_values['user_id'] = $column['user_id'];
            $student_emergency_contact_bind_values['emergency_contact'] = $emergency_contact;
            $student_emergency_contact_bind_values['serial_name'] = $student_bind_values['serial_name'];

            $update_contact_bind_values['user_id'] = $student_id;

            foreach ($delete_student_file_bind_values as $key => $value) {
                if (array_key_exists($key, $student_file_bind_values)) {
                    $delete_student_file_bind_values[$key] = $student_file_bind_values[$key];
                }
            }
            foreach ($delete_student_emergency_contact_bind_values as $key => $value) {
                if (array_key_exists($key, $student_file_bind_values)) {
                    $delete_student_emergency_contact_bind_values[$key] = $student_file_bind_values[$key];
                }
            }

            $stmt_delete_student_file->execute($delete_student_file_bind_values);
            $stmt_delete_student_emergency_contact->execute($delete_student_emergency_contact_bind_values);
            if (array_key_exists('file_id', $column)) {
                $this->multi_student_file_insert($student_file_bind_values);
            }
            if (array_key_exists('user_id', $column)) {
                $this->delete_emergency_contact($delete_student_emergency_contact_bind_values);
                $this->multi_student_emergency_contact_insert($student_emergency_contact_bind_values);
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function post_contact_student($data, $last_edit_user_id)
    {
        // var_dump($data);
        // var_dump($last_edit_user_id);
        // exit(0);
        foreach ($data as $row => $column) {
            $student_values = [
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "grade_id" => 0,
                // "id" => 0,
                "name" => "",
                "phone" => "",
                "school" => ""
            ];
            // $student_file_bind_values = [
            //     "student_id" => "",
            //     "file_id" => null,
            // ];
            // $delete_student_file_bind_values = [
            //     "student_id" => "",
            // ];
            // $student_emergency_contact_bind_values = [
            //     "user_id" => 0,
            //     "relation_user_id" => 0,
            //     "emergency_contact" => [],
            //     "serial_name" => "",
            // ];
            // $delete_student_emergency_contact_bind_values = [
            //     "user_id" => 0,
            // ];
            $update_contact_bind_values = [
                "user_id" => 0,
                "id" => 0,
            ];

            $student_insert_cond = "";
            $student_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($student_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'emergency_contact') {
                        $student_bind_values[$key] = $column[$key];
                    } else if ($key == 'json_data') {
                        $column[$key] != null ? $student_bind_values[$key] = json_encode($column[$key]) : null;
                        $student_insert_cond .= "{$key},";
                        $student_values_cond .= ":{$key},";
                    } else {
                        $student_bind_values[$key] = $column[$key];
                        $student_insert_cond .= "{$key},";
                        $student_values_cond .= ":{$key},";
                    }
                } else {
                    unset($student_bind_values[$key]);
                }
            }

            $student_insert_cond .= "last_edit_time,";
            $student_values_cond .= "NOW(),";

            // $file_id = $student_bind_values['file_id'];
            // $emergency_contact = $student_bind_values['emergency_contact'];
            unset($student_bind_values['file_id']);
            unset($student_bind_values['emergency_contact']);
            $student_insert_cond = rtrim($student_insert_cond, ',');
            $student_values_cond = rtrim($student_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.student({$student_insert_cond})
                VALUES ({$student_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            // $sql_delete = "DELETE FROM cramschool.student_file
            //     WHERE cramschool.student_file.student_id = :student_id
            // ";
            // $stmt_delete_student_file = $this->db->prepare($sql_delete);

            // $sql_delete = "DELETE FROM cramschool.emergency_contact
            //     WHERE cramschool.emergency_contact.user_id = :user_id
            // ";
            // $stmt_delete_student_emergency_contact = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($student_bind_values)) {
                $student_id = $stmt_insert->fetchColumn(0);
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }

            // $student_file_bind_values['student_id'] = $student_id;
            // $student_file_bind_values['file_id'] = $file_id;

            // $student_emergency_contact_bind_values['user_id'] = $column['user_id'];
            // $student_emergency_contact_bind_values['emergency_contact'] = $emergency_contact;
            // $student_emergency_contact_bind_values['serial_name'] = $student_bind_values['serial_name'];

            $update_contact_bind_values['user_id'] = $student_id;
            $update_contact_bind_values['id'] = $data[$row]['id'];

            // var_dump($update_contact_bind_values);
            // exit(0);
            // foreach ($delete_student_file_bind_values as $key => $value) {
            //     if (array_key_exists($key, $student_file_bind_values)) {
            //         $delete_student_file_bind_values[$key] = $student_file_bind_values[$key];
            //     }
            // }
            // foreach ($delete_student_emergency_contact_bind_values as $key => $value) {
            //     if (array_key_exists($key, $student_file_bind_values)) {
            //         $delete_student_emergency_contact_bind_values[$key] = $student_file_bind_values[$key];
            //     }
            // }

            // $stmt_delete_student_file->execute($delete_student_file_bind_values);
            // $stmt_delete_student_emergency_contact->execute($delete_student_emergency_contact_bind_values);
            // if (array_key_exists('file_id', $column)) {
            //     $this->multi_student_file_insert($student_file_bind_values);
            // }
            // if (array_key_exists('user_id', $column)) {
            //     $this->delete_emergency_contact($delete_student_emergency_contact_bind_values);
            //     $this->multi_student_emergency_contact_insert($student_emergency_contact_bind_values);
            // }

            $sql_update = "UPDATE cramschool.contact
                            SET \"user_id\" = :user_id
                            WHERE \"id\" = :id
            ";
            // var_dump($update_contact_bind_values);
            // exit(0);
            $stmt_update = $this->db->prepare($sql_update);
            if ($stmt_update->execute($update_contact_bind_values)) {
            } else {
                var_dump($update_contact_bind_values);
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_student($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $student_values = [
                "student_id" => "",
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "name" => "",
                "name_english" => "",
                "phone" => "",
                "phone_home" => "",
                "birthday" => "",
                "emergency_contact" => [],
                "note" => "",
                "study_time_start" => "",
                "study_time_end" => "",
                "grade_id" => "",
                "gender_id" => "",
                "address" => "",
                "serial_name" => "",
                "school" => "",
                "school_scroe" => "",
                "json_data" => null,
                "file_id" => null,
            ];
            $delete_student_file_bind_values = [
                "student_id" => "",
            ];
            $insert_student_file_bind_values = [
                "student_id" => "",
                "file_id" => null,
            ];
            $student_emergency_contact_bind_values = [
                "user_id" => 0,
                "emergency_contact" => [],
            ];
            $delete_student_emergency_contact_bind_values = [
                "user_id" => 0,
            ];

            $student_upadte_cond = "";
            $student_fliter_cond = "";
            $student_file_fliter_cond = "";
            $student_emergency_contact_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($student_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'student_id' || $key == 'emergency_contact') {
                        $student_bind_values[$key] = $column[$key];
                    } else if ($key == 'json_data' && $column[$key] != null) {
                        $student_bind_values[$key] = json_encode($column[$key]);
                        $student_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $student_bind_values[$key] = $column[$key];
                        $student_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($student_bind_values[$key]);
                }
            }

            $student_upadte_cond .= "last_edit_time = NOW(),";
            $student_fliter_cond .= "AND cramschool.student.id = :student_id";
            $student_file_fliter_cond .= "AND cramschool.student_file.student_id = :student_id";
            $student_emergency_contact_fliter_cond .= "AND cramschool.emergency_contact.user_id = :user_id";
            $student_upadte_cond = rtrim($student_upadte_cond, ',');

            foreach ($insert_student_file_bind_values as $key => $value) {
                if (array_key_exists($key, $student_bind_values)) {
                    $insert_student_file_bind_values[$key] = $student_bind_values[$key];
                }
            }

            foreach ($student_emergency_contact_bind_values as $key => $value) {
                if (array_key_exists($key, $student_bind_values)) {
                    $student_emergency_contact_bind_values[$key] = $student_bind_values[$key];
                }
            }

            foreach ($delete_student_file_bind_values as $key => $value) {
                if (array_key_exists($key, $student_bind_values)) {
                    $delete_student_file_bind_values[$key] = $student_bind_values[$key];
                }
            }

            foreach ($delete_student_emergency_contact_bind_values as $key => $value) {
                if (array_key_exists($key, $student_bind_values)) {
                    $delete_student_emergency_contact_bind_values[$key] = $student_bind_values[$key];
                }
            }

            $file_id = $student_bind_values['file_id'];
            $emergency_contact = $student_bind_values['emergency_contact'];
            unset($student_bind_values['file_id']);
            unset($student_bind_values['emergency_contact']);
            $sql = "UPDATE cramschool.student
                    SET {$student_upadte_cond}
                    WHERE TRUE {$student_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($student_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.student_file
                        WHERE TRUE {$student_file_fliter_cond}
                    ";
                    $stmt_delete_student_file = $this->db->prepare($sql_delete);
                    $stmt_delete_student_file->execute($delete_student_file_bind_values);
                    $this->multi_student_file_insert($insert_student_file_bind_values);
                }
                if (array_key_exists('user_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.emergency_contact
                        WHERE TRUE {$student_emergency_contact_fliter_cond}
                    ";
                    $stmt_delete_student_emergency_contact = $this->db->prepare($sql_delete);
                    $stmt_delete_student_emergency_contact->execute($delete_student_emergency_contact_bind_values);
                    $this->multi_student_emergency_contact_insert($student_emergency_contact_bind_values);
                }
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_student($data)
    {
        $user_id_arr = [];
        foreach ($data as $row => $delete_data) {
            $delete_student_file_bind_values = [
                "student_id" => "",
            ];

            foreach ($delete_student_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_student_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM \"system\".\"user\"
            WHERE \"system\".\"user\".id IN 
                (
                    SELECT cramschool.emergency_relation.emergency_contact_user_id
                    FROM cramschool.student
                    LEFT JOIN cramschool.emergency_relation ON cramschool.student.user_id = cramschool.emergency_relation.relation_user_id
                    WHERE cramschool.student.id = :student_id
                )
            ";
            $stmt_delete_student_emergency_contact = $this->db->prepare($sql_delete);
            $stmt_delete_student_emergency_contact->execute($delete_student_file_bind_values);

            $sql_delete = "DELETE FROM cramschool.student
                WHERE cramschool.student.id = :student_id
                RETURNING user_id
            ";
            $stmt_delete_student = $this->db->prepare($sql_delete);
            if ($stmt_delete_student->execute($delete_student_file_bind_values)) {
                $user_id = $stmt_delete_student->fetchColumn(0);
                array_push($user_id_arr, ['user_id' => $user_id]);
                $result = ["status" => "success", "user_id_arr" => $user_id_arr];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    function get_student_id($data)
    {
        $values = [
            "uid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT id
                FROM cramschool.student
                WHERE serial_name = :uid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function multi_student_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $student_file_insert_cond = "";
            $student_file_values_cond = "";

            $per_student_file_bind_values = [
                "student_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_student_file_bind_values[$key] = $per_file_id;
                    $student_file_insert_cond .= "{$key},";
                    $student_file_values_cond .= ":{$key},";
                } else {
                    $per_student_file_bind_values[$key] = $datas[$key];
                    $student_file_insert_cond .= "{$key},";
                    $student_file_values_cond .= ":{$key},";
                }
            }
            $student_file_insert_cond = rtrim($student_file_insert_cond, ',');
            $student_file_values_cond = rtrim($student_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.student_file({$student_file_insert_cond})
                VALUES ({$student_file_values_cond})
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_student_file_bind_values);
        }
    }

    public function multi_student_emergency_contact_insert($datas)
    {
        $role_id = 3;
        if (array_key_exists('emergency_contact', $datas))
            return;
        foreach ($datas['emergency_contact'] as $row => $per_emergency_contact) {
            if ($per_emergency_contact['emergency_contact_name'] != '') {
                $student_emergency_contact_insert_cond = "";
                $student_emergency_contact_values_cond = "";

                $per_student_emergency_contact_bind_values = [
                    "emergency_contact_name" => "",
                    "emergency_contact_phone_number" => "",
                    "emergency_contact_gender" => "",
                ];

                $per_student_emergency_contact_relation_bind_values = [
                    "emergency_contact_user_id" => 0,
                    "relation_user_id" => 0,
                ];

                $emergency_contact_bind_values = [
                    "name" => "",
                ];

                $emergency_contact_bind_values['name'] = $per_emergency_contact['emergency_contact_name'];
                $user_id_status = $this->post_user([$emergency_contact_bind_values], $role_id);
                if ($user_id_status['status'] == 'success') {
                    $per_emergency_contact['user_id'] = $user_id_status['user_id'];
                    $per_student_emergency_contact_bind_values['user_id'] = $user_id_status['user_id'];
                    $per_student_emergency_contact_relation_bind_values['emergency_contact_user_id'] = $user_id_status['user_id'];
                    $per_student_emergency_contact_relation_bind_values['relation_user_id'] = $datas['user_id'];
                }

                foreach ($per_student_emergency_contact_bind_values as $key => $value) {
                    if ($key == 'serial_name') {
                        $per_student_emergency_contact_bind_values[$key] = $datas[$key];
                    } else {
                        $per_student_emergency_contact_bind_values[$key] = $per_emergency_contact[$key];
                    }
                }

                $per_student_emergency_contact_bind_insert = [
                    "user_id" => "user_id",
                    "emergency_contact_name" => "name",
                    "emergency_contact_phone_number" => "phone_number",
                    "emergency_contact_gender" => "gender",
                ];

                $per_student_emergency_contact_bind_value = [
                    "user_id" => ":user_id",
                    "emergency_contact_name" => ":name",
                    "emergency_contact_phone_number" => ":phone_number",
                    "emergency_contact_gender" => ":gender",
                ];

                foreach ($per_student_emergency_contact_bind_insert as $key => $value) {
                    $student_emergency_contact_insert_cond .= "{$per_student_emergency_contact_bind_insert[$key]},";
                }

                foreach ($per_student_emergency_contact_bind_value as $key => $value) {
                    $student_emergency_contact_values_cond .= ":{$key},";
                }

                $student_emergency_contact_insert_cond = rtrim($student_emergency_contact_insert_cond, ',');
                $student_emergency_contact_values_cond = rtrim($student_emergency_contact_values_cond, ',');

                $sql = "INSERT INTO cramschool.emergency_contact({$student_emergency_contact_insert_cond})
                    VALUES ({$student_emergency_contact_values_cond})
                    RETURNING id
                ";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute($per_student_emergency_contact_bind_values)) {
                    $this->multi_student_emergency_contact_relation_insert([$per_student_emergency_contact_relation_bind_values]);
                } else {
                    var_dump($stmt->errorInfo());
                }
            }
        }
    }

    public function multi_student_emergency_contact_relation_insert($datas)
    {
        foreach ($datas as $row => $per_emergency_contact_relation) {
            $per_emergency_contact_relation_insert_cond = "";
            $per_emergency_contact_relation_values_cond = "";

            $per_student_emergency_contact_relation_bind_values = [
                "emergency_contact_user_id" => 0,
                "relation_user_id" => 0,
            ];

            foreach ($per_student_emergency_contact_relation_bind_values as $key => $value) {
                $per_emergency_contact_relation_bind_values[$key] = $per_emergency_contact_relation[$key];
                $per_emergency_contact_relation_insert_cond .= "{$key},";
                $per_emergency_contact_relation_values_cond .= ":{$key},";
            }

            $per_emergency_contact_relation_insert_cond = rtrim($per_emergency_contact_relation_insert_cond, ',');
            $per_emergency_contact_relation_values_cond = rtrim($per_emergency_contact_relation_values_cond, ',');

            $sql = "INSERT INTO cramschool.emergency_relation({$per_emergency_contact_relation_insert_cond})
                VALUES ({$per_emergency_contact_relation_values_cond})
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($per_emergency_contact_relation_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
            }
        }
    }

    public function get_blog_type($params)
    {
        // $values = $this->initialize_search();
        // foreach ($values as $key => $value) {
        //     array_key_exists($key, $params) && $values[$key] = $params[$key];
        // }
        if ($params['list']) {
            $list_check = true;
            unset($params['list']);
        }

        $blog_type_values = [
            "blog_type_id" => 0
        ];
        foreach ($blog_type_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }
        // var_dump($params);
        // var_dump($values);
        // exit(0);


        $condition = "";
        $condition_values = [
            "blog_type_id" => " AND blog_type_id = :blog_type_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($condition_values[$key]);
            }
        }
        $sql_inside = "SELECT cramschool.blog_type.id blog_type_id, cramschool.blog_type.name, 
                            cramschool.blog_type.background_color,cramschool.blog_type.file_show, 
                            cramschool.blog_type.color_show, COALESCE(blog_type_file.file_id, '[]')file_id
                        FROM cramschool.blog_type
                        LEFT JOIN (
                            SELECT cramschool.blog_type_file.blog_type_id,
                            JSON_AGG(
                                    cramschool.blog_type_file.file_id
                                    ORDER BY cramschool.blog_type_file.file_id DESC
                            ) file_id
                            FROM cramschool.blog_type_file
                            GROUP BY cramschool.blog_type_file.blog_type_id
                        )blog_type_file ON cramschool.blog_type.id = blog_type_file.blog_type_id
        ";

        $sql = "SELECT *
            FROM(
                {$sql_inside}
            )dt
            WHERE TRUE {$condition}
        ";

        $sql_list = "SELECT \"name\", background_color
                    FROM(
                        {$sql_inside}
                    )dt
                    WHERE blog_type_id IN (6,7,9,11,13)
        ";

        // var_dump($sql);
        // exit(0);

        if ($list_check) {
            $stmt_list = $this->db->prepare($sql_list);
            if ($stmt_list->execute()) {
                $result['data'] = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                var_dump($stmt_list->errorInfo());
                return [
                    "status" => "failed",
                    "message" => "blog_type_list failed!"
                ];
            }
        }
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result['data'] as $row_id => $row_value) {
                foreach ($row_value as $key => $value) {
                    if ($this->isJson($value)) {
                        $result['data'][$row_id][$key] = json_decode($value, true);
                    }
                }
            }
            return $result;
        } else {
            var_dump($stmt->errorInfo());
            return [
                "status" => "failed",
                "message" => "blog_type failed!"
            ];
        }
    }

    public function patch_blog_type($data)
    {
        foreach ($data as $row => $column) {
            $blog_type_values = [
                "blog_type_id" => "",
                "name" => "",
                "background_color" => "",
                "file_show" => false,
                "color_show" => false,
                "file_id" => null,
            ];
            $delete_blog_type_file_bind_values = [
                "blog_type_id" => "",
            ];
            $insert_blog_type_file_bind_values = [
                "blog_type_id" => "",
                "file_id" => null,
            ];

            $blog_type_upadte_cond = "";
            $blog_type_fliter_cond = "";
            $blog_type_file_fliter_cond = "";

            foreach ($blog_type_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'blog_type_id') {
                        $blog_type_bind_values[$key] = $column[$key];
                    } else {
                        if ($key === "color_show" || $key === "file_show") {
                            if ($column[$key] === false)
                                $column[$key] = 0;
                            else
                                $column[$key] = 1;
                        }
                        $blog_type_bind_values[$key] = $column[$key];
                        $blog_type_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($blog_type_bind_values[$key]);
                }
            }

            $blog_type_fliter_cond .= "AND cramschool.blog_type.id = :blog_type_id";
            $blog_type_file_fliter_cond .= "AND cramschool.blog_type_file.blog_type_id = :blog_type_id";
            $blog_type_upadte_cond = rtrim($blog_type_upadte_cond, ',');

            foreach ($insert_blog_type_file_bind_values as $key => $value) {
                if (array_key_exists($key, $blog_type_bind_values)) {
                    $insert_blog_type_file_bind_values[$key] = $blog_type_bind_values[$key];
                }
            }

            foreach ($delete_blog_type_file_bind_values as $key => $value) {
                if (array_key_exists($key, $blog_type_bind_values)) {
                    $delete_blog_type_file_bind_values[$key] = $blog_type_bind_values[$key];
                }
            }

            $file_id = $blog_type_bind_values['file_id'];
            unset($blog_type_bind_values['file_id']);
            $sql = "UPDATE cramschool.blog_type
                    SET {$blog_type_upadte_cond}
                    WHERE TRUE {$blog_type_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($blog_type_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.blog_type_file
                        WHERE TRUE {$blog_type_file_fliter_cond}
                    ";
                    $stmt_delete_blog_type_file = $this->db->prepare($sql_delete);
                    $stmt_delete_blog_type_file->execute($delete_blog_type_file_bind_values);
                    $this->multi_blog_type_file_insert($insert_blog_type_file_bind_values);
                }
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_teacher($params)
    {
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

        $values = [
            "teacher_id" => null,
            "employment_time_start" => null,
            "employment_time_end" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'employment_time_start' || $key == 'employment_time_end') {
                    if ($params[$key] == '') {
                        unset($values[$key]);
                    } else {
                        $values[$key] = $params[$key];
                    }
                } else {
                    $values[$key] = $params[$key];
                }
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $select_condition = "";
        $condition_values = [
            "teacher_id" => " AND teacher_id = :teacher_id",
            "employment_time_start" => " AND (EXTRACT(DAY FROM employment_time_start::timestamp - :employment_time_start::timestamp) >= 0 AND employment_time_start::timestamp IS NOT NULL)",
            "employment_time_end" => " AND (EXTRACT(DAY FROM employment_time_end::timestamp - :employment_time_end::timestamp) <= 0 AND employment_time_end::timestamp IS NOT NULL)",
            "is_resign" => " AND (EXTRACT(DAY FROM NOW() - employment_time_end::timestamp) <= 0 OR employment_time_end::timestamp IS NULL)",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'employment_time_start' || $key == 'employment_time_end') {
                    if ($params[$key] == '') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else if ($key == 'is_resign') {
                    if ($params[$key] == 'false') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else {
                    $condition .= $value;
                }
            } else {
                unset($condition_values[$key]);
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
        $values["start"] = $start;
        $values["length"] = $length;
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

        //預設排序
        $order = '';
        // var_dump($params);
        // exit(0);

        if (array_key_exists('order', $params)) {
            $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                // var_dump($column_data);
                // exit(0);
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
                        $order .= " to_char(employment_time_start::timestamp, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    case 'employment_time_end':
                        $order .= " to_char(employment_time_end::timestamp, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_inside = "SELECT cramschool.teacher.id teacher_id, cramschool.teacher.\"name\", cramschool.teacher.expersite,
                    to_char(cramschool.teacher.employment_time_start, 'YYYY-MM-DD')employment_time_start, to_char(cramschool.teacher.employment_time_end, 'YYYY-MM-DD')employment_time_end,
                    cramschool.teacher.phone, cramschool.teacher.\"address\", cramschool.teacher.note, COALESCE(teacher_file.file_id, '[]')file_id,
                    cramschool.teacher.name_english, cramschool.teacher.mobile_phone, cramschool.teacher.school, cramschool.teacher.serial_name,
                    COALESCE(teacher_blog.blog_data, '[]')blog_data, \"system\".user.id user_id, \"system\".user.email,
                    COALESCE(emergency_contact.emergency_contact,'[]')emergency_contact
                    FROM cramschool.teacher
                    LEFT JOIN (
                        SELECT cramschool.teacher_file.teacher_id,
                        JSON_AGG(
                                cramschool.teacher_file.file_id
                                ORDER BY cramschool.teacher_file.file_id DESC
                        ) file_id
                        FROM cramschool.teacher_file
                        GROUP BY cramschool.teacher_file.teacher_id
                    )teacher_file ON cramschool.teacher.id = teacher_file.teacher_id
                    LEFT JOIN (
                        SELECT cramschool.teacher_blog.teacher_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'blog_id', cramschool.blog.id,
                                'blog_title', cramschool.blog.title,
                                'blog_content', cramschool.blog.content,
                                'blog_file_id', COALESCE(blog_file.file_id, '[]')
                            )
                            ORDER BY cramschool.blog.id
                        ) blog_data
                        FROM cramschool.teacher_blog
                        LEFT JOIN cramschool.blog ON cramschool.teacher_blog.blog_id = cramschool.blog.id
                        LEFT JOIN (
                            SELECT cramschool.blog_file.blog_id,
                            JSON_AGG(
                                cramschool.blog_file.file_id
                                ORDER BY cramschool.blog_file.file_id DESC
                            ) file_id
                            FROM cramschool.blog_file
                            GROUP BY cramschool.blog_file.blog_id
                        )blog_file ON cramschool.teacher_blog.blog_id = blog_file.blog_id
                        GROUP BY cramschool.teacher_blog.teacher_id
                    )teacher_blog ON cramschool.teacher.id = teacher_blog.teacher_id
                    LEFT JOIN (
                        SELECT cramschool.emergency_contact.user_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'emergency_contact_id', cramschool.emergency_contact.id,
                                    'emergency_contact_name', cramschool.emergency_contact.\"name\",
                                    'emergency_contact_phone_number', cramschool.emergency_contact.phone_number                           
                                )
                                ORDER BY cramschool.emergency_contact.\"name\" DESC
                            ) emergency_contact
                        FROM cramschool.emergency_contact
                        GROUP BY cramschool.emergency_contact.user_id
                    )emergency_contact ON cramschool.teacher.user_id = emergency_contact.user_id
                    LEFT JOIN \"system\".user ON cramschool.teacher.user_id = \"system\".user.id 
        ";

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                        FROM(
                            {$sql_inside}
                        )teacher_data
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

        $sql_people = "SELECT COUNT(*)
            FROM(
                {$sql_inside}
            )sql_inside
        ";

        if ($excel_check) {
            $values = [
                "serial_name" => "編號",
                "name" => "老師姓名",
                "name_english" => "英文姓名",
                "expersite" => "專長",
                "phone" => "手機號碼",
                "email" => "電子信箱",
                "employment_time_start" => "入職時間",
                "employment_time_end" => "離職時間",
                "address" => "地址",
                "blog_data" => "形象網站",
                "note" => "備註",
            ];

            $excel_column = "COALESCE(\"serial_name\", '-') \"{$values["serial_name"]}\",";
            if (array_key_exists('language', $params)) {
                foreach ($params['language'] as $language) {
                    if ($this->isJson($language)) {
                        foreach (json_decode($language, true) as $key => $value) {
                            if (array_key_exists($key, $values) & $key !== 'serial_name') {
                                if ($key == 'emergency_contact_name' || $key == 'emergency_contact_phone_number') {
                                    //預設三筆
                                    for ($i = 1; $i < 4; $i++) {
                                        $new_key_name = "{$key}{$i}";
                                        $label_name = "{$values[$key]}{$i}";
                                        $excel_column .= "COALESCE(\"{$new_key_name}\", '-') \"{$label_name}\",";
                                    }
                                } else if ($key == 'blog_data') {
                                    $excel_column .= "(CASE WHEN CAST(db.\"blog_data\" AS varchar) = '[]' THEN '無' ELSE '有' END)";
                                } else {
                                    $label = empty(@$value['zh-tw']) ? $values[$key] : $value['zh-tw'];
                                    $excel_column .= "COALESCE(\"{$key}\", '-') \"{$label}\",";
                                }
                            }
                        }
                    }
                }
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
                return $result;
            } else {
                var_dump($stmt->errorInfo());
                return [
                    "status" => "failed",
                    "message" => $stmt->errorInfo()
                ];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_people = $this->db->prepare($sql_people);
        if ($stmt->execute($values) && $stmt_count->execute($values_count) && $stmt_people->execute()) {
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_teacher($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $teacher_values = [
                "id" => 0,
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "name" => "",
                "serial_name" => "",
                "expersite" => "",
                "employment_time_start" => "",
                "employment_time_end" => "",
                "phone" => "",
                "address" => "",
                "note" => "",
                "file_id" => null,
                "blog_data" => [],
            ];
            $teacher_file_bind_values = [
                "teacher_id" => "",
                "file_id" => null,
            ];
            $delete_teacher_file_bind_values = [
                "teacher_id" => "",
            ];

            $teacher_insert_cond = "";
            $teacher_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($teacher_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'blog_data') {
                        $teacher_bind_values[$key] = $column[$key];
                    } else {
                        $teacher_bind_values[$key] = $column[$key];
                        $teacher_insert_cond .= "{$key},";
                        $teacher_values_cond .= ":{$key},";
                    }
                } else {
                    unset($teacher_bind_values[$key]);
                }
            }

            $teacher_insert_cond .= "last_edit_time,";
            $teacher_values_cond .= "NOW(),";

            $file_id = $teacher_bind_values['file_id'];
            $blog_data = $teacher_bind_values['blog_data'];
            unset($teacher_bind_values['file_id']);
            unset($teacher_bind_values['blog_data']);
            $teacher_insert_cond = rtrim($teacher_insert_cond, ',');
            $teacher_values_cond = rtrim($teacher_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.teacher({$teacher_insert_cond})
                VALUES ({$teacher_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.teacher_file
                WHERE cramschool.teacher_file.teacher_id = :teacher_id
            ";
            $stmt_delete_teacher_file = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($teacher_bind_values)) {
                $teacher_id = $stmt_insert->fetchColumn(0);
            } else {
                return ['status' => 'failure'];
            }

            $teacher_file_bind_values['teacher_id'] = $teacher_id;
            $teacher_file_bind_values['file_id'] = $file_id;

            foreach ($delete_teacher_file_bind_values as $key => $value) {
                if (array_key_exists($key, $teacher_file_bind_values)) {
                    $delete_teacher_file_bind_values[$key] = $teacher_file_bind_values[$key];
                }
            }
            $stmt_delete_teacher_file->execute($delete_teacher_file_bind_values);
            if (array_key_exists('file_id', $column)) {
                $this->multi_teacher_file_insert($teacher_file_bind_values);
            }
            if (array_key_exists('blog', $column) && $column['blog'] == 'check') {
                $blog_type_id = 4; //teacher
                $blog_data['teacher_id'] = $teacher_id;
                $this->post_blog([$blog_data], $blog_type_id, $last_edit_user_id);
            }
            if (array_key_exists('administration', $column) && $column['administration'] == 'check') {
                $this->post_administration([$column], $last_edit_user_id);
            }
            $result = ["status" => "success"];
        }
        return $result;
    }

    public function patch_teacher($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $teacher_values = [
                "teacher_id" => "",
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "name" => "",
                "serial_name" => "",
                "expersite" => "",
                "employment_time_start" => "",
                "employment_time_end" => "",
                "phone" => "",
                "address" => "",
                "note" => "",
                "file_id" => null,
                "blog_data" => [],
            ];
            $delete_teacher_file_bind_values = [
                "teacher_id" => "",
            ];
            $insert_teacher_file_bind_values = [
                "teacher_id" => "",
                "file_id" => null,
            ];

            $teacher_upadte_cond = "";
            $teacher_fliter_cond = "";
            $teacher_file_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($teacher_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'teacher_id' || $key == 'blog_data') {
                        $teacher_bind_values[$key] = $column[$key];
                    } else {
                        $teacher_bind_values[$key] = $column[$key];
                        $teacher_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($teacher_bind_values[$key]);
                }
            }

            $teacher_upadte_cond .= "last_edit_time = NOW(),";
            $teacher_fliter_cond .= "AND cramschool.teacher.id = :teacher_id";
            $teacher_file_fliter_cond .= "AND cramschool.teacher_file.teacher_id = :teacher_id";
            $teacher_upadte_cond = rtrim($teacher_upadte_cond, ',');

            foreach ($insert_teacher_file_bind_values as $key => $value) {
                if (array_key_exists($key, $teacher_bind_values)) {
                    $insert_teacher_file_bind_values[$key] = $teacher_bind_values[$key];
                }
            }

            foreach ($delete_teacher_file_bind_values as $key => $value) {
                if (array_key_exists($key, $teacher_bind_values)) {
                    $delete_teacher_file_bind_values[$key] = $teacher_bind_values[$key];
                }
            }

            $file_id = $teacher_bind_values['file_id'];
            $blog_data = $teacher_bind_values['blog_data'];
            unset($teacher_bind_values['file_id']);
            unset($teacher_bind_values['blog_data']);
            $sql = "UPDATE cramschool.teacher
                    SET {$teacher_upadte_cond}
                    WHERE TRUE {$teacher_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($teacher_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.teacher_file
                        WHERE TRUE {$teacher_file_fliter_cond}
                    ";
                    $stmt_delete_teacher_file = $this->db->prepare($sql_delete);
                    $stmt_delete_teacher_file->execute($delete_teacher_file_bind_values);
                    $this->multi_teacher_file_insert($insert_teacher_file_bind_values);
                }
                if (array_key_exists('blog', $column) && $column['blog'] == 'check') {
                    $blog_type_id = 4; //teacher
                    $blog_data['teacher_id'] = $teacher_values['teacher_id'];
                    $this->post_blog([$blog_data], $blog_type_id, $last_edit_user_id);
                }
                if (array_key_exists('administration', $column) && $column['administration'] == 'check') {
                    $this->post_administration([$column], $last_edit_user_id);
                }
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_teacher($data)
    {
        $user_id_arr = [];
        foreach ($data as $row => $delete_data) {
            $delete_teacher_file_bind_values = [
                "teacher_id" => "",
            ];

            foreach ($delete_teacher_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_teacher_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.teacher
                WHERE cramschool.teacher.id = :teacher_id
                RETURNING user_id
            ";
            $stmt_delete_teacher_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_teacher_file->execute($delete_teacher_file_bind_values)) {
                $data_user = [];
                $user_id = $stmt_delete_teacher_file->fetchColumn(0);
                $data_user['user_id'] = $user_id;
                array_push($user_id_arr, $data_user);
                $result = ["status" => "success", "user_id_arr" => $user_id_arr];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function multi_teacher_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $teacher_file_insert_cond = "";
            $teacher_file_values_cond = "";

            $per_teacher_file_bind_values = [
                "teacher_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_teacher_file_bind_values[$key] = $per_file_id;
                    $teacher_file_insert_cond .= "{$key},";
                    $teacher_file_values_cond .= ":{$key},";
                } else {
                    $per_teacher_file_bind_values[$key] = $datas[$key];
                    $teacher_file_insert_cond .= "{$key},";
                    $teacher_file_values_cond .= ":{$key},";
                }
            }
            $teacher_file_insert_cond = rtrim($teacher_file_insert_cond, ',');
            $teacher_file_values_cond = rtrim($teacher_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.teacher_file({$teacher_file_insert_cond})
                VALUES ({$teacher_file_values_cond})
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_teacher_file_bind_values);
        }
    }

    public function multi_blog_type_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $blog_type_file_insert_cond = "";
            $blog_type_file_values_cond = "";

            $per_blog_type_file_bind_values = [
                "blog_type_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_blog_type_file_bind_values[$key] = $per_file_id;
                    $blog_type_file_insert_cond .= "{$key},";
                    $blog_type_file_values_cond .= ":{$key},";
                } else {
                    $per_blog_type_file_bind_values[$key] = $datas[$key];
                    $blog_type_file_insert_cond .= "{$key},";
                    $blog_type_file_values_cond .= ":{$key},";
                }
            }
            $blog_type_file_insert_cond = rtrim($blog_type_file_insert_cond, ',');
            $blog_type_file_values_cond = rtrim($blog_type_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.blog_type_file({$blog_type_file_insert_cond})
                VALUES ({$blog_type_file_values_cond})
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_blog_type_file_bind_values);
        }
    }

    public function get_chatroom($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];


        $values = [
            "type_name" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "type_name" => " AND \"position\" = :type_name",
        ];

        $select_condition = "";

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

                $order .= " {$column_data['column']} {$sort_type},";
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *,  ROW_NUMBER() OVER (ORDER BY \"name\") \"key\"
                        FROM (
                            SELECT cramschool.teacher.\"name\", '老師' \"position\", cramschool.teacher.user_id
                            FROM cramschool.teacher
                            UNION ALL(
                                SELECT administration_data.*
                                FROM \"system\".user
                                INNER JOIN (
                                    SELECT cramschool.administration.\"name\", COALESCE(cramschool.administration.position, '行政人員')position, cramschool.administration.user_id
                                    FROM cramschool.administration
                                )administration_data ON \"system\".user.id = administration_data.user_id
                            )
                        )permisstion_data
                        WHERE TRUE AND user_id IS NOT NULL {$condition} {$select_condition} 
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function get_administration_list()
    {
        $sql = "SELECT  cramschool.administration.user_id \"UID\"
                FROM cramschool.administration
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } else {
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function get_surrounding_admin_list($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $values["start"] = $start;
        $values["length"] = $length;
        unset($values['cur_page']);
        unset($values['size']);
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY user_id) \"key\"
                FROM (
                    SELECT  cramschool.administration.user_id, cramschool.administration.\"name\",
                    '行政人員' \"position\"
                    FROM cramschool.administration
                    UNION ALL 
                    (
                        SELECT cramschool.teacher.user_id, cramschool.teacher.\"name\",
                        '老師' \"position\" 
                        FROM cramschool.teacher
                    )
                )admin_list
                WHERE user_id IS NOT NULL
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function get_chatroom_position_list($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $values["start"] = $start;
        $values["length"] = $length;
        unset($values['cur_page']);
        unset($values['size']);
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

        $sql_default = "SELECT *,  permisstion_data.position \"label\", permisstion_data.position \"value\",
                        ROW_NUMBER() OVER (ORDER BY permisstion_data.position) \"key\"
                        FROM (
                            SELECT COALESCE(cramschool.administration.position, '行政人員')position
                            FROM cramschool.administration
                            GROUP BY cramschool.administration.position
                            UNION ALL(
                                SELECT '老師' \"position\"                                    
                            )
                        )permisstion_data
                        WHERE TRUE 
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function get_administration($params)
    {
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

        $values = [
            "administration_id" => null,
            "employment_time_start" => null,
            "employment_time_end" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'employment_time_start' || $key == 'employment_time_end') {
                    if ($params[$key] == '') {
                        unset($values[$key]);
                    } else {
                        $values[$key] = $params[$key];
                    }
                } else {
                    $values[$key] = $params[$key];
                }
            } else {
                unset($values[$key]);
            }
        }


        $condition = "";
        $condition_values = [
            "administration_id" => " AND administration_id = :administration_id",
            "employment_time_start" => " AND (EXTRACT(DAY FROM employment_time_start::timestamp - :employment_time_start::timestamp) >= 0 AND employment_time_start::timestamp IS NOT NULL)",
            "employment_time_end" => " AND (EXTRACT(DAY FROM employment_time_end::timestamp - :employment_time_end::timestamp) <= 0 AND employment_time_end::timestamp IS NOT NULL)",
            "is_resign" => " AND (EXTRACT(DAY FROM NOW() - employment_time_end::timestamp) <= 0 OR employment_time_end::timestamp IS NULL)",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'employment_time_start' || $key == 'employment_time_end') {
                    if ($params[$key] == '') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else if ($key == 'is_resign') {
                    if ($params[$key] == 'false') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else {
                    $condition .= $value;
                }
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
        $values["start"] = $start;
        $values["length"] = $length;
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

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'employment_time_start':
                        $order .= " to_char(employment_time_start::timestamp, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    case 'employment_time_end':
                        $order .= " to_char(employment_time_end::timestamp, 'yyyy-MM-dd') {$sort_type},";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default_inside = "SELECT cramschool.administration.id administration_id, cramschool.administration.\"name\", cramschool.administration.serial_name,
        cramschool.administration.position, to_char(cramschool.administration.employment_time_start, 'YYYY-MM-DD')employment_time_start,
        to_char(cramschool.administration.employment_time_end, 'YYYY-MM-DD')employment_time_end, cramschool.administration.phone,
        cramschool.administration.\"address\", cramschool.administration.note, COALESCE(administration_file.file_id, '[]')file_id,
        cramschool.administration.position_work, \"system\".user.email, \"system\".user.id user_id
        FROM cramschool.administration
        LEFT JOIN (
            SELECT cramschool.administration_file.administration_id,
            JSON_AGG(
                    cramschool.administration_file.file_id
                    ORDER BY cramschool.administration_file.file_id DESC
            ) file_id
            FROM cramschool.administration_file
            GROUP BY cramschool.administration_file.administration_id
        )administration_file ON cramschool.administration.id = administration_file.administration_id
        LEFT JOIN \"system\".user ON cramschool.administration.user_id = \"system\".user.id
        ";

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                    FROM(
                        {$sql_default_inside}
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

        $sql_people = "SELECT COUNT(*)
            FROM(
                {$sql_default_inside}
            )sql_default_inside
        ";

        if ($excel_check) {

            $sql_excel = "SELECT COALESCE(db.\"serial_name\", '-') \"編號\"
                            , COALESCE(db.\"name\", '-') \"老師姓名\"
                            , COALESCE(\"position\", '-') \"職位\"
                            , COALESCE(db.\"position_work\", '-') \"職務\"
                            , COALESCE(db.\"phone\", '-') \"電話\"
                            , COALESCE(db.\"email\", '-') \"電子信箱\"
                            , COALESCE(db.\"employment_time_start\", '-') \"入職時間\"
                            , COALESCE(db.\"employment_time_end\", '-') \"離職時間\"
                            , COALESCE(db.\"address\", '-') \"地址\"
                            , COALESCE(db.\"note\", '-') \"說明\"
                        FROM (
                            {$sql_default}
                        )db
            ";
            $stmt = $this->db->prepare($sql_excel);
            if ($stmt->execute($values_count)) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                var_dump($stmt->errorInfo());
                // var_dump('123');
                // var_dump($sql_excel);
                // var_dump($values_count);
                return [
                    "status" => "failed",
                    "message" => $stmt->errorInfo()
                ];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_people = $this->db->prepare($sql_people);
        if ($stmt->execute($values) && $stmt_count->execute($values_count) && $stmt_people->execute()) {
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
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

    public function post_administration($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $administration_values = [
                "id" => 0,
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "name" => "",
                "serial_name" => "",
                "position" => "",
                "position_work" => "",
                "employment_time_start" => "",
                "employment_time_end" => "",
                "phone" => "",
                "address" => "",
                "note" => "",
                "file_id" => null,
            ];
            $administration_file_bind_values = [
                "administration_id" => "",
                "file_id" => null,
            ];
            $delete_administration_file_bind_values = [
                "administration_id" => "",
            ];

            $administration_insert_cond = "";
            $administration_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($administration_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id') {
                        $administration_bind_values[$key] = $column[$key];
                    } else {
                        $administration_bind_values[$key] = $column[$key];
                        $administration_insert_cond .= "{$key},";
                        $administration_values_cond .= ":{$key},";
                    }
                } else {
                    unset($administration_bind_values[$key]);
                }
            }

            $administration_insert_cond .= "last_edit_time,";
            $administration_values_cond .= "NOW(),";

            $file_id = $administration_bind_values['file_id'];
            unset($administration_bind_values['file_id']);
            $administration_insert_cond = rtrim($administration_insert_cond, ',');
            $administration_values_cond = rtrim($administration_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.administration({$administration_insert_cond})
                VALUES ({$administration_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.administration_file
                WHERE cramschool.administration_file.administration_id = :administration_id
            ";
            $stmt_delete_administration_file = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($administration_bind_values)) {
                $administration_id = $stmt_insert->fetchColumn(0);
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }

            $administration_file_bind_values['administration_id'] = $administration_id;
            $administration_file_bind_values['file_id'] = $file_id;

            foreach ($delete_administration_file_bind_values as $key => $value) {
                if (array_key_exists($key, $administration_file_bind_values)) {
                    $delete_administration_file_bind_values[$key] = $administration_file_bind_values[$key];
                }
            }
            $stmt_delete_administration_file->execute($delete_administration_file_bind_values);
            if (array_key_exists('file_id', $column)) {
                $this->multi_administration_file_insert($administration_file_bind_values);
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_administration($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $administration_values = [
                "administration_id" => "",
                "last_edit_user_id" => 0,
                "user_id" => 0,
                "name" => "",
                "serial_name" => "",
                "position" => "",
                "position_work" => "",
                "employment_time_start" => "",
                "employment_time_end" => "",
                "phone" => "",
                "address" => "",
                "note" => "",
                "file_id" => null,
            ];
            $delete_administration_file_bind_values = [
                "administration_id" => "",
            ];
            $insert_administration_file_bind_values = [
                "administration_id" => "",
                "file_id" => null,
            ];

            $administration_upadte_cond = "";
            $administration_fliter_cond = "";
            $administration_file_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($administration_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'administration_id') {
                        $administration_bind_values[$key] = $column[$key];
                    } else {
                        $administration_bind_values[$key] = $column[$key];
                        $administration_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($administration_bind_values[$key]);
                }
            }

            $administration_upadte_cond .= "last_edit_time = NOW(),";
            $administration_fliter_cond .= "AND cramschool.administration.id = :administration_id";
            $administration_file_fliter_cond .= "AND cramschool.administration_file.administration_id = :administration_id";
            $administration_upadte_cond = rtrim($administration_upadte_cond, ',');

            foreach ($insert_administration_file_bind_values as $key => $value) {
                if (array_key_exists($key, $administration_bind_values)) {
                    $insert_administration_file_bind_values[$key] = $administration_bind_values[$key];
                }
            }

            foreach ($delete_administration_file_bind_values as $key => $value) {
                if (array_key_exists($key, $administration_bind_values)) {
                    $delete_administration_file_bind_values[$key] = $administration_bind_values[$key];
                }
            }

            $file_id = $administration_bind_values['file_id'];
            unset($administration_bind_values['file_id']);
            $sql = "UPDATE cramschool.administration
                    SET {$administration_upadte_cond}
                    WHERE TRUE {$administration_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($administration_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.administration_file
                        WHERE TRUE {$administration_file_fliter_cond}
                    ";
                    $stmt_delete_administration_file = $this->db->prepare($sql_delete);
                    $stmt_delete_administration_file->execute($delete_administration_file_bind_values);
                    $this->multi_administration_file_insert($insert_administration_file_bind_values);
                }
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_administration($data)
    {
        $user_id_arr = [];
        foreach ($data as $row => $delete_data) {
            $delete_administration_file_bind_values = [
                "administration_id" => "",
            ];

            foreach ($delete_administration_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_administration_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.administration
                WHERE cramschool.administration.id = :administration_id
                RETURNING user_id
            ";
            $stmt_delete_administration_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_administration_file->execute($delete_administration_file_bind_values)) {
                $data_user = [];
                $user_id = $stmt_delete_administration_file->fetchColumn(0);
                $data_user['user_id'] = $user_id;
                array_push($user_id_arr, $data_user);
                $result = ["status" => "success", "user_id_arr" => $user_id_arr];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function multi_administration_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $administration_file_insert_cond = "";
            $administration_file_values_cond = "";

            $per_administration_file_bind_values = [
                "administration_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_administration_file_bind_values[$key] = $per_file_id;
                    $administration_file_insert_cond .= "{$key},";
                    $administration_file_values_cond .= ":{$key},";
                } else {
                    $per_administration_file_bind_values[$key] = $datas[$key];
                    $administration_file_insert_cond .= "{$key},";
                    $administration_file_values_cond .= ":{$key},";
                }
            }
            $administration_file_insert_cond = rtrim($administration_file_insert_cond, ',');
            $administration_file_values_cond = rtrim($administration_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.administration_file({$administration_file_insert_cond})
                VALUES ({$administration_file_values_cond})
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_administration_file_bind_values);
        }
    }

    public function get_surrounding($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else if ($value == null) {
                unset($values[$key]);
            }
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $values = [
            "surrounding_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }


        $condition = "";
        $condition_values = [
            "surrounding_id" => " AND surrounding_id = :surrounding_id",
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

        $values["start"] = $start;
        $values["length"] = $length;
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY \"name\") \"key\"
            FROM(
                SELECT cramschool.surrounding.id surrounding_id, cramschool.surrounding.\"name\", cramschool.surrounding.name_serial,
                cramschool.surrounding.capacity, cramschool.surrounding.manage_user_id, COALESCE(\"system\".user.name, '')user_name,
                cramschool.surrounding.note, COALESCE(surrounding_file.file_id, '[]')file_id,
                COALESCE(surrounding_blog.blog_id, '[]')blog_id
                FROM cramschool.surrounding
                LEFT JOIN (
                    SELECT cramschool.surrounding_file.surrounding_id,
                    JSON_AGG(
                            cramschool.surrounding_file.file_id
                            ORDER BY cramschool.surrounding_file.file_id DESC
                    ) file_id
                    FROM cramschool.surrounding_file
                    GROUP BY cramschool.surrounding_file.surrounding_id
                )surrounding_file ON cramschool.surrounding.id = surrounding_file.surrounding_id
                LEFT JOIN (
                    SELECT cramschool.surrounding_blog.surrounding_id,
                        JSON_AGG(
                                cramschool.blog.id
                        )  blog_id
                    FROM cramschool.surrounding_blog
                    LEFT JOIN cramschool.blog ON cramschool.surrounding_blog.blog_id = cramschool.blog.id
                    GROUP BY cramschool.surrounding_blog.surrounding_id
                )surrounding_blog ON cramschool.surrounding.id = surrounding_blog.surrounding_id
                LEFT JOIN \"system\".user ON  cramschool.surrounding.manage_user_id = \"system\".user.id
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_surrounding($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $surrounding_values = [
                "last_edit_user_id" => 0,
                "name" => "",
                "name_serial" => "",
                "manage_user_id" => null,
                "note" => "",
                "capacity" => "",
                "file_id" => null,
            ];
            $surrounding_file_bind_values = [
                "surrounding_id" => "",
                "file_id" => null,
            ];
            $delete_surrounding_file_bind_values = [
                "surrounding_id" => "",
            ];

            $surrounding_insert_cond = "";
            $surrounding_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($surrounding_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id') {
                        $surrounding_bind_values[$key] = $column[$key];
                    } else {
                        $surrounding_bind_values[$key] = $column[$key];
                        $surrounding_insert_cond .= "{$key},";
                        $surrounding_values_cond .= ":{$key},";
                    }
                } else {
                    unset($surrounding_bind_values[$key]);
                }
            }

            $surrounding_insert_cond .= "last_edit_time,";
            $surrounding_values_cond .= "NOW(),";

            $file_id = $surrounding_bind_values['file_id'];
            unset($surrounding_bind_values['file_id']);
            $surrounding_insert_cond = rtrim($surrounding_insert_cond, ',');
            $surrounding_values_cond = rtrim($surrounding_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.surrounding({$surrounding_insert_cond})
                VALUES ({$surrounding_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.surrounding_file
                WHERE cramschool.surrounding_file.surrounding_id = :surrounding_id
            ";
            $stmt_delete_surrounding_file = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($surrounding_bind_values)) {
                $surrounding_id = $stmt_insert->fetchColumn(0);
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }

            $surrounding_file_bind_values['surrounding_id'] = $surrounding_id;
            $surrounding_file_bind_values['file_id'] = $file_id;

            foreach ($delete_surrounding_file_bind_values as $key => $value) {
                if (array_key_exists($key, $surrounding_file_bind_values)) {
                    $delete_surrounding_file_bind_values[$key] = $surrounding_file_bind_values[$key];
                }
            }
            $stmt_delete_surrounding_file->execute($delete_surrounding_file_bind_values);
            if (array_key_exists('file_id', $column)) {
                $this->multi_surrounding_file_insert($surrounding_file_bind_values);
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function post_surrounding_blog($data)
    {
        $surrounding_values = [
            "blog_id" => "",
            "surrounding_id" => "",
        ];

        $surrounding_insert_cond = "";
        $surrounding_values_cond = "";

        foreach ($surrounding_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $surrounding_bind_values[$key] = $data[$key];
                $surrounding_insert_cond .= "{$key},";
                $surrounding_values_cond .= ":{$key},";
            }
        }

        $surrounding_insert_cond = rtrim($surrounding_insert_cond, ',');
        $surrounding_values_cond = rtrim($surrounding_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.surrounding_blog({$surrounding_insert_cond})
                VALUES ({$surrounding_values_cond})
            ";

        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($surrounding_bind_values)) {
            $result = ["status" => "success"];
        } else {
            var_dump($stmt_insert->errorInfo());
            $result = ["status" => "failure"];
        }

        return $result;
    }

    public function post_teacher_blog($data)
    {
        $teacher_values = [
            "blog_id" => "",
            "teacher_id" => "",
        ];

        $teacher_insert_cond = "";
        $teacher_values_cond = "";

        foreach ($teacher_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $teacher_bind_values[$key] = $data[$key];
                $teacher_insert_cond .= "{$key},";
                $teacher_values_cond .= ":{$key},";
            }
        }

        $teacher_insert_cond = rtrim($teacher_insert_cond, ',');
        $teacher_values_cond = rtrim($teacher_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.teacher_blog({$teacher_insert_cond})
                VALUES ({$teacher_values_cond})
            ";

        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($teacher_bind_values)) {
            $result = ["status" => "success"];
        } else {
            var_dump($stmt_insert->errorInfo());
            $result = ["status" => "failure"];
        }

        return $result;
    }

    public function post_lesson_category_blog($data)
    {
        $lesson_category_values = [
            "blog_id" => "",
            "lesson_category_id" => "",
        ];

        $lesson_category_insert_cond = "";
        $lesson_category_values_cond = "";

        foreach ($lesson_category_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $lesson_category_bind_values[$key] = $data[$key];
                $lesson_category_insert_cond .= "{$key},";
                $lesson_category_values_cond .= ":{$key},";
            }
        }

        $lesson_category_insert_cond = rtrim($lesson_category_insert_cond, ',');
        $lesson_category_values_cond = rtrim($lesson_category_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.lesson_category_blog({$lesson_category_insert_cond})
                VALUES ({$lesson_category_values_cond})
            ";

        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($lesson_category_bind_values)) {
            $result = ["status" => "success"];
        } else {
            var_dump($stmt_insert->errorInfo());
            $result = ["status" => "failure"];
        }

        return $result;
    }

    public function post_lesson_blog($data)
    {
        $lesson_values = [
            "blog_id" => "",
            "lesson_id" => "",
        ];

        $lesson_insert_cond = "";
        $lesson_values_cond = "";

        foreach ($lesson_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $lesson_bind_values[$key] = $data[$key];
                $lesson_insert_cond .= "{$key},";
                $lesson_values_cond .= ":{$key},";
            }
        }

        $lesson_insert_cond = rtrim($lesson_insert_cond, ',');
        $lesson_values_cond = rtrim($lesson_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.lesson_blog({$lesson_insert_cond})
                VALUES ({$lesson_values_cond})
            ";

        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($lesson_bind_values)) {
            $result = ["status" => "success"];
        } else {
            var_dump($stmt_insert->errorInfo());
            $result = ["status" => "failure"];
        }

        return $result;
    }

    public function patch_surrounding($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $surrounding_values = [
                "surrounding_id" => "",
                "last_edit_user_id" => 0,
                "name" => "",
                "name_serial" => "",
                "manage_user_id" => null,
                "note" => "",
                "capacity" => "",
                "file_id" => null,
            ];
            $delete_surrounding_file_bind_values = [
                "surrounding_id" => "",
            ];
            $insert_surrounding_file_bind_values = [
                "surrounding_id" => "",
                "file_id" => null,
            ];

            $surrounding_upadte_cond = "";
            $surrounding_fliter_cond = "";
            $surrounding_file_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($surrounding_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'surrounding_id') {
                        $surrounding_bind_values[$key] = $column[$key];
                    } else {
                        $surrounding_bind_values[$key] = $column[$key];
                        $surrounding_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($surrounding_bind_values[$key]);
                }
            }

            $surrounding_upadte_cond .= "last_edit_time = NOW(),";
            $surrounding_fliter_cond .= "AND cramschool.surrounding.id = :surrounding_id";
            $surrounding_file_fliter_cond .= "AND cramschool.surrounding_file.surrounding_id = :surrounding_id";
            $surrounding_upadte_cond = rtrim($surrounding_upadte_cond, ',');

            foreach ($insert_surrounding_file_bind_values as $key => $value) {
                if (array_key_exists($key, $surrounding_bind_values)) {
                    $insert_surrounding_file_bind_values[$key] = $surrounding_bind_values[$key];
                }
            }

            foreach ($delete_surrounding_file_bind_values as $key => $value) {
                if (array_key_exists($key, $surrounding_bind_values)) {
                    $delete_surrounding_file_bind_values[$key] = $surrounding_bind_values[$key];
                }
            }

            $file_id = $surrounding_bind_values['file_id'];
            unset($surrounding_bind_values['file_id']);
            $sql = "UPDATE cramschool.surrounding
                    SET {$surrounding_upadte_cond}
                    WHERE TRUE {$surrounding_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($surrounding_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.surrounding_file
                        WHERE TRUE {$surrounding_file_fliter_cond}
                    ";
                    $stmt_delete_surrounding_file = $this->db->prepare($sql_delete);
                    $stmt_delete_surrounding_file->execute($delete_surrounding_file_bind_values);
                    $this->multi_surrounding_file_insert($insert_surrounding_file_bind_values);
                }
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_surrounding($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_surrounding_file_bind_values = [
                "surrounding_id" => "",
            ];

            foreach ($delete_surrounding_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_surrounding_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.surrounding
                WHERE cramschool.surrounding.id = :surrounding_id
            ";
            $stmt_delete_surrounding_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_surrounding_file->execute($delete_surrounding_file_bind_values)) {
                $result = ["status" => "success"];
            } else {
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function multi_surrounding_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $surrounding_file_insert_cond = "";
            $surrounding_file_values_cond = "";

            $per_surrounding_file_bind_values = [
                "surrounding_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_surrounding_file_bind_values[$key] = $per_file_id;
                    $surrounding_file_insert_cond .= "{$key},";
                    $surrounding_file_values_cond .= ":{$key},";
                } else {
                    $per_surrounding_file_bind_values[$key] = $datas[$key];
                    $surrounding_file_insert_cond .= "{$key},";
                    $surrounding_file_values_cond .= ":{$key},";
                }
            }
            $surrounding_file_insert_cond = rtrim($surrounding_file_insert_cond, ',');
            $surrounding_file_values_cond = rtrim($surrounding_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.surrounding_file({$surrounding_file_insert_cond})
                    VALUES ({$surrounding_file_values_cond})
                ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_surrounding_file_bind_values);
        }
    }

    public function get_learn_witness_type($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "learn_witness_type_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values["start"] = $start;
        $values["length"] = $length;

        $condition = "";
        $condition_values = [
            "learn_witness_type_id" => " AND cramschool.learn_witness_type.id = :learn_witness_type_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $sql = "SELECT *
                FROM(
                    SELECT cramschool.learn_witness_type.id learn_witness_type_id, cramschool.learn_witness_type.\"name\",
                    ROW_NUMBER() OVER (ORDER BY cramschool.learn_witness_type.\"name\") \"key\"
                    FROM cramschool.learn_witness_type
                    WHERE TRUE {$condition}
                    LIMIT :length
                )dt
                WHERE \"key\" > :start          
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_learn_witness_type($data)
    {
        foreach ($data as $row => $column) {
            $learn_witness_type_values = [
                "name" => "",
            ];

            $learn_witness_type_insert_cond = "";
            $learn_witness_type_values_cond = "";

            foreach ($learn_witness_type_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id') {
                        $learn_witness_type_bind_values[$key] = $column[$key];
                    } else {
                        $learn_witness_type_bind_values[$key] = $column[$key];
                        $learn_witness_type_insert_cond .= "{$key},";
                        $learn_witness_type_values_cond .= ":{$key},";
                    }
                }
            }

            unset($learn_witness_type_bind_values['file_id']);
            $learn_witness_type_insert_cond = rtrim($learn_witness_type_insert_cond, ',');
            $learn_witness_type_values_cond = rtrim($learn_witness_type_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.learn_witness_type({$learn_witness_type_insert_cond})
                VALUES ({$learn_witness_type_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($learn_witness_type_bind_values)) {
            } else {
                return ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_learn_witness_type($data)
    {
        foreach ($data as $row => $column) {
            $learn_witness_type_bind_values = [
                "learn_witness_type_id" => "",
                "name" => "",
            ];

            $learn_witness_type_upadte_cond = "";
            $learn_witness_type_fliter_cond = "";

            foreach ($learn_witness_type_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'learn_witness_type_id') {
                        $learn_witness_type_bind_values[$key] = $column[$key];
                    } else {
                        $learn_witness_type_bind_values[$key] = $column[$key];
                        $learn_witness_type_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $learn_witness_type_fliter_cond .= "AND cramschool.learn_witness_type.id = :learn_witness_type_id";
            $learn_witness_type_upadte_cond = rtrim($learn_witness_type_upadte_cond, ',');

            $sql = "UPDATE cramschool.learn_witness_type
                    SET {$learn_witness_type_upadte_cond}
                    WHERE TRUE {$learn_witness_type_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($learn_witness_type_bind_values)) {
            } else {
                var_dump($sql);
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_learn_witness_type($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_learn_witness_type_file_bind_values = [
                "learn_witness_type_id" => "",
            ];

            foreach ($delete_learn_witness_type_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_learn_witness_type_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.learn_witness_type
                WHERE cramschool.learn_witness_type.id = :learn_witness_type_id
            ";
            $stmt_delete_learn_witness_type_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_learn_witness_type_file->execute($delete_learn_witness_type_file_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_lesson_category($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "lesson_category_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values_default[$key] = $params[$key];
            }
        }

        $condition = "";
        $condition_values = [
            "lesson_category_id" => " AND lesson_category_id = :lesson_category_id",
        ];
        $select_condition = "";

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

        $sql_default = "SELECT *,  ROW_NUMBER() OVER (ORDER BY dt.\"name\") \"key\"
                FROM(
                    SELECT cramschool.lesson_category.id lesson_category_id, cramschool.lesson_category.\"name\",
                    COALESCE(grade_class_teacher.teachers, '[]')teachers, lesson_category_blog.blog_id, lesson_category_blog.order_stage_id,
                    COALESCE(class_data.lesson_category_class, '[]')class_data
                    FROM cramschool.lesson_category
                    LEFT JOIN (
                        SELECT cramschool.lesson_category_lesson.lesson_category_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'teacher_id', cramschool.teacher.id,
                                'teacher_name', cramschool.teacher.\"name\"
                            )
                        )  teachers
                        FROM cramschool.grade_class_teacher
                        INNER JOIN cramschool.teacher ON cramschool.grade_class_teacher.teacher_id = cramschool.teacher.id
                        INNER JOIN cramschool.grade_class ON cramschool.grade_class_teacher.grade_class_id = cramschool.grade_class.id
                        INNER JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                        INNER JOIN cramschool.lesson_class ON cramschool.class.id = cramschool.lesson_class.class_id
                        INNER JOIN cramschool.lesson ON cramschool.lesson_class.lesson_id = cramschool.lesson.id
                        INNER JOIN cramschool.lesson_category_lesson ON cramschool.lesson.id = cramschool.lesson_category_lesson.lesson_category_id
                        GROUP BY cramschool.lesson_category_lesson.lesson_category_id
                    )grade_class_teacher ON cramschool.lesson_category.id = grade_class_teacher.lesson_category_id
                    LEFT JOIN (
                        SELECT cramschool.lesson_category_blog.lesson_category_id,
                         cramschool.blog.id blog_id,cramschool.blog.order_stage_id
                        FROM cramschool.lesson_category_blog
                        LEFT JOIN cramschool.blog ON cramschool.lesson_category_blog.blog_id = cramschool.blog.id
                    )lesson_category_blog ON cramschool.lesson_category.id = lesson_category_blog.lesson_category_id
                    LEFT JOIN (
                        SELECT cramschool.lesson_category.id lesson_category_id, COALESCE(lesson_class_data.lesson_category_class, '[]')lesson_category_class
                        FROM cramschool.lesson_category
                        LEFT JOIN (
                            SELECT cramschool.lesson_category_lesson.lesson_category_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'class_id', cramschool.class.id,
                                    'class_name', cramschool.class.\"name\",
                                    'class_enroll_status', cramschool.class.enroll_status
                                )
                            )lesson_category_class
                            FROM cramschool.lesson_class
                            LEFT JOIN cramschool.class ON cramschool.lesson_class.class_id = cramschool.class.id
                            LEFT JOIN cramschool.lesson ON cramschool.lesson_class.lesson_id = cramschool.lesson.id
                            LEFT JOIN cramschool.lesson_category_lesson ON cramschool.lesson.id = cramschool.lesson_category_lesson.lesson_id
                            GROUP BY cramschool.lesson_category_lesson.lesson_category_id
                        )lesson_class_data ON cramschool.lesson_category.id = lesson_class_data.lesson_category_id
                    )class_data ON cramschool.lesson_category.id = class_data.lesson_category_id
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
            var_dump($stmt->errorInfo());
            // var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_lesson_category($data)
    {
        foreach ($data as $row => $column) {
            $lesson_category_values = [
                "name" => "",
            ];

            $lesson_category_insert_cond = "";
            $lesson_category_values_cond = "";

            foreach ($lesson_category_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id') {
                        $lesson_category_bind_values[$key] = $column[$key];
                    } else {
                        $lesson_category_bind_values[$key] = $column[$key];
                        $lesson_category_insert_cond .= "{$key},";
                        $lesson_category_values_cond .= ":{$key},";
                    }
                }
            }

            unset($lesson_category_bind_values['file_id']);
            $lesson_category_insert_cond = rtrim($lesson_category_insert_cond, ',');
            $lesson_category_values_cond = rtrim($lesson_category_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.lesson_category({$lesson_category_insert_cond})
                VALUES ({$lesson_category_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($lesson_category_bind_values)) {
                $lesson_category_id = $stmt_insert->fetchColumn(0);
                $data[$row]["lesson_category_id"] = $lesson_category_id;
                $data[$row]["title"] = $lesson_category_bind_values['name'];
            } else {
                return ['status' => 'failure'];
            }
        }
        return ["status" => "success", "data_return" => $data];
    }

    public function patch_lesson_category($data)
    {
        foreach ($data as $row => $column) {
            $lesson_category_bind_values = [
                "lesson_category_id" => "",
                "name" => "",
            ];

            $lesson_category_upadte_cond = "";
            $lesson_category_fliter_cond = "";

            foreach ($lesson_category_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'lesson_category_id') {
                        $lesson_category_bind_values[$key] = $column[$key];
                    } else {
                        $lesson_category_bind_values[$key] = $column[$key];
                        $lesson_category_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $lesson_category_fliter_cond .= "AND cramschool.lesson_category.id = :lesson_category_id";
            $lesson_category_upadte_cond = rtrim($lesson_category_upadte_cond, ',');

            $sql = "UPDATE cramschool.lesson_category
                    SET {$lesson_category_upadte_cond}
                    WHERE TRUE {$lesson_category_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($lesson_category_bind_values)) {
                $data[$row]["title"] = $lesson_category_bind_values['name'];
            } else {
                return ['status' => 'failure'];
            }
        }
        return ["status" => "success", "data_return" => $data];
    }

    public function delete_lesson_category($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_lesson_category_file_bind_values = [
                "lesson_category_id" => "",
            ];

            foreach ($delete_lesson_category_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_lesson_category_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.lesson_category
                WHERE cramschool.lesson_category.id = :lesson_category_id
            ";
            $stmt_delete_lesson_category_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_lesson_category_file->execute($delete_lesson_category_file_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_grade($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "grade_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];
        $select_condition = '';

        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
            $select_condition = " AND (";
            foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
                $select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
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

        $condition = "";
        $condition_values = [
            "grade_id" => " AND cramschool.grade.id = :grade_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        //預設排序
        $order = 'ORDER BY serial_num';

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
                    default:
                        $order .= " {$column_data['column']} {$sort_type},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *
                FROM(
                    SELECT cramschool.grade.id, cramschool.grade.\"name\", CAST (cramschool.grade.serial_num AS integer) serial_num,
                    ROW_NUMBER() OVER (ORDER BY cramschool.grade.serial_num) \"key\"
                    FROM cramschool.grade
                    WHERE TRUE {$condition} {$select_condition}
                )dt
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
            var_dump($stmt->errorInfo());
            // var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_grade($data)
    {
        foreach ($data as $row => $column) {
            $grade_values = [
                "name" => "",
                "serial_num" => "",
            ];

            $grade_insert_cond = "";
            $grade_values_cond = "";

            foreach ($grade_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $grade_bind_values[$key] = $column[$key];
                    $grade_insert_cond .= "{$key},";
                    $grade_values_cond .= ":{$key},";
                }
            }

            unset($grade_bind_values['file_id']);
            $grade_insert_cond = rtrim($grade_insert_cond, ',');
            $grade_values_cond = rtrim($grade_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.grade({$grade_insert_cond})
                VALUES ({$grade_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($grade_bind_values)) {
            } else {
                return ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_grade($data)
    {
        foreach ($data as $row => $column) {
            $grade_bind_values = [
                "grade_id" => "",
                "name" => "",
                "serial_num" => "",
            ];

            $grade_upadte_cond = "";
            $grade_fliter_cond = "";

            foreach ($grade_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'grade_id') {
                        $grade_bind_values[$key] = $column[$key];
                    } else {
                        $grade_bind_values[$key] = $column[$key];
                        $grade_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $grade_fliter_cond .= "AND cramschool.grade.id = :grade_id";
            $grade_upadte_cond = rtrim($grade_upadte_cond, ',');

            $sql = "UPDATE cramschool.grade
                    SET {$grade_upadte_cond}
                    WHERE TRUE {$grade_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($grade_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_grade($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_grade_file_bind_values = [
                "grade_id" => "",
            ];

            foreach ($delete_grade_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_grade_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.grade
                WHERE cramschool.grade.id = :grade_id
            ";
            $stmt_delete_grade_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_grade_file->execute($delete_grade_file_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function getUserPermission($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "user_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values["start"] = $start;
        $values["length"] = $length;


        $condition = "";
        $condition_values = [
            "user_id" => " AND \"system\".user.id = :user_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($condition_values[$key]);
            }
        }

        $sql = "  SELECT *
            FROM(
                SELECT \"system\".user.id user_id, \"system\".user.name user_name,
                CASE WHEN cramschool.permission_group.\"name\" IS NULL THEN '其他' ELSE cramschool.permission_group.\"name\" END \"permission_group_name\",
                cramschool.role.id role_id, cramschool.role.name role_name,
                to_char(cramschool.user_permission.permission_time_start, 'YYYY-MM-DD')permission_time_start,
                to_char(cramschool.user_permission.permission_time_end, 'YYYY-MM-DD')permission_time_end,
                JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'permission_id', \"permission\".id,
                        'permission_name', \"permission\".\"name\",
                        'permission_url', \"permission\".\"url\",
                        'permission_icon', \"permission\".icon,
                        'permission_index', \"permission\".\"index\",
                        'permission_level', \"permission_level\".\"name\"
                    )
                    ORDER BY \"permission\".\"index\"
                ) permission_list, 
                ROW_NUMBER() OVER (ORDER BY \"system\".user.id) \"key\"
                FROM \"system\".user
                INNER JOIN cramschool.user_permission ON \"system\".user.id = cramschool.user_permission.user_id
                LEFT JOIN cramschool.permission ON user_permission.permission_id = permission.id
                LEFT JOIN cramschool.permission_level ON cramschool.user_permission.permission_level_id = cramschool.permission_level.id
                LEFT JOIN cramschool.permission_group ON cramschool.permission.permission_group_id = cramschool.permission_group.id
                LEFT JOIN cramschool.user_role ON cramschool.user_permission.user_id = cramschool.user_role.user_id
                LEFT JOIN cramschool.role ON cramschool.user_role.role_id = cramschool.role.id
                WHERE TRUE {$condition}
                GROUP BY \"system\".user.id, \"system\".user.name, cramschool.permission_group.\"name\", cramschool.role.id, cramschool.role.\"name\",
                cramschool.user_permission.permission_time_start, cramschool.user_permission.permission_time_end
                LIMIT :length
            )dt
            WHERE \"key\" > :start          
        ";
        $stmt = $this->db->prepare($sql);
        $result = [];
        if ($stmt->execute($values)) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $key => $row) {
                foreach ($row as $row_key => $value) {
                    if ($this->isJson($value)) {
                        $result[$key][$row_key] = json_decode($value, true);
                    }
                }
            }
            return $result;
        } else {
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
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

    public function get_lesson($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "lesson_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

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

                $order .= " {$column_data['column']} {$sort_type},";
            }
            $order = rtrim($order, ',');
        }

        $condition = "";
        $condition_values = [
            "lesson_id" => " AND cramschool.lesson.id = :lesson_id",
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

        $sql_default = "SELECT cramschool.lesson.id lesson_id, cramschool.lesson.\"name\", cramschool.lesson.outline,
                        to_char(cramschool.lesson.last_edit_time, 'YYYY-MM-DD') last_edit_time, cramschool.lesson.last_edit_user_id,
                        cramschool.lesson_category.id lesson_category_id, cramschool.lesson_category.\"name\" lesson_category_name, 
                        COALESCE(lesson_blog.blog_id, '[]')blog_id, ROW_NUMBER() OVER (ORDER BY cramschool.lesson.id) \"key\"
                        FROM cramschool.lesson
                        LEFT JOIN cramschool.lesson_category_lesson ON cramschool.lesson.id = cramschool.lesson_category_lesson.lesson_id
                        LEFT JOIN cramschool.lesson_category ON cramschool.lesson_category_lesson.lesson_category_id = cramschool.lesson_category.id
                        LEFT JOIN (
                            SELECT cramschool.lesson_blog.lesson_id,
                                JSON_AGG(
                                        cramschool.blog.id
                                )  blog_id
                            FROM cramschool.lesson_blog
                            LEFT JOIN cramschool.blog ON cramschool.lesson_blog.blog_id = cramschool.blog.id
                            GROUP BY cramschool.lesson_blog.lesson_id
                        )lesson_blog ON cramschool.lesson.id = lesson_blog.lesson_id
                        WHERE TRUE {$condition}
                        {$order}
        ";

        $sql = "SELECT *
        FROM(
            {$sql_default}
            LIMIT :length
        )dt
        WHERE \"key\" > :start {$select_condition}       
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_lesson($data, $last_edit_user_id)
    {
        $result = "";
        foreach ($data as $row => $column) {
            $lesson_values = [
                "name" => "",
                "outline" => "",
            ];
            $lesson_category_lesson_bind_values = [
                "lesson_id" => null,
                "lesson_category_id" => null,
            ];

            $lesson_insert_cond = "";
            $lesson_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['last_edit_time'] = 'NOW()';

            foreach ($lesson_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $lesson_bind_values[$key] = $column[$key];
                    $lesson_insert_cond .= "{$key},";
                    $lesson_values_cond .= ":{$key},";
                }
            }

            $lesson_insert_cond = rtrim($lesson_insert_cond, ',');
            $lesson_values_cond = rtrim($lesson_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.lesson({$lesson_insert_cond})
                VALUES ({$lesson_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($lesson_bind_values)) {
                $lesson_id = $stmt_insert->fetchColumn(0);
                // var_dump($column['lesson_category_data']);
                // var_dump($lesson_id);
                // exit(0);
                if (array_key_exists('lesson_category_data', $column)) {
                    $this->multi_lesson_category_lesson_insert($column['lesson_category_data'], $lesson_id);
                    $result = ["status" => "success", "lesson_id" => $lesson_id];
                } else {
                    return ['status' => 'failure', "info" => "realtion_faild"];
                }
            } else {
                return ['status' => 'failure', 'info' => $stmt_insert->errorInfo()];
            }
        }
        return $result;
    }

    public function patch_lesson($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $lesson_bind_values = [
                "lesson_id" => 0,
                "name" => "",
                "outline" => "",
                "file_id" => null,
                "last_edit_user_id" => 0,
            ];
            $delete_lesson_file_bind_values = [
                "lesson_id" => "",
            ];
            $insert_lesson_file_bind_values = [
                "lesson_id" => "",
                "file_id" => null,
            ];
            $lesson_category_delete_values = [
                "lesson_id" => 0,
            ];
            $patch_lesson_category_bind_values = [
                "lesson_category_id" => 0,
                "lesson_id" => 0
            ];

            $lesson_upadte_cond = "";
            $lesson_fliter_cond = "";
            $lesson_file_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($lesson_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'lesson_id') {
                        $lesson_bind_values[$key] = $column[$key];
                    } else {
                        $lesson_bind_values[$key] = $column[$key];
                        $lesson_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($lesson_bind_values[$key]);
                }
            }

            foreach ($lesson_category_delete_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $lesson_category_delete_bind_values[$key] = $column[$key];
                }
            }
            foreach ($patch_lesson_category_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $lesson_category_patch_bind_values[$key] = $column[$key];
                }
            }

            $lesson_fliter_cond .= "AND cramschool.lesson.id = :lesson_id";
            $lesson_file_fliter_cond .= "AND cramschool.lesson_file.lesson_id = :lesson_id";
            $lesson_upadte_cond .= "last_edit_time = NOW(),";
            $lesson_upadte_cond = rtrim($lesson_upadte_cond, ',');

            foreach ($insert_lesson_file_bind_values as $key => $value) {
                if (array_key_exists($key, $lesson_bind_values)) {
                    $insert_lesson_file_bind_values[$key] = $lesson_bind_values[$key];
                }
            }

            foreach ($delete_lesson_file_bind_values as $key => $value) {
                if (array_key_exists($key, $lesson_bind_values)) {
                    $delete_blog_file_bind_values[$key] = $lesson_bind_values[$key];
                }
            }
            $file_id = $lesson_bind_values['file_id'];
            unset($lesson_bind_values['file_id']);

            $sql = "UPDATE cramschool.lesson
                    SET {$lesson_upadte_cond}
                    WHERE TRUE {$lesson_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);

            if ($stmt->execute($lesson_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.lesson_file
                        WHERE TRUE {$lesson_file_fliter_cond}
                    ";
                    $stmt_delete_blog_file = $this->db->prepare($sql_delete);
                    $stmt_delete_blog_file->execute($delete_lesson_file_bind_values);
                    // $stmt_blog_type = $this->db->prepare($sql_lesson_type);
                    // $stmt_blog_type->execute($blog_type_values);
                    if ($column['file_id'] != null) {
                        $this->multi_lesson_file_insert($insert_lesson_file_bind_values);
                    }
                }
                if (array_key_exists('lesson_category_data', $column)) {
                    $sql_delete = "DELETE FROM cramschool.lesson_category_lesson
                        WHERE cramschool.lesson_category_lesson.lesson_id = :lesson_id
                    ";
                    $stmt_insert = $this->db->prepare($sql_delete);
                    $stmt_insert->execute($lesson_category_delete_bind_values);
                    foreach ($column['lesson_category_data'] as $key => $values) {
                        foreach ($patch_lesson_category_bind_values as $key => $value) {
                            if (array_key_exists($key, $values)) {
                                $lesson_category_patch_bind_values[$key] = $values[$key];
                                $this->multi_lesson_category_lesson_insert(
                                    array($lesson_category_patch_bind_values),
                                    $lesson_category_patch_bind_values['lesson_id']
                                );
                            }
                        }
                    }
                }
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_lesson($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_lesson_file_bind_values = [
                "lesson_id" => "",
            ];

            foreach ($delete_lesson_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_lesson_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.lesson
                WHERE cramschool.lesson.id = :lesson_id
            ";
            $stmt_delete_lesson_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_lesson_file->execute($delete_lesson_file_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function multi_lesson_category_lesson_insert($column, $lesson_id)
    {
        // var_dump($column);
        // var_dump($lesson_id);
        // exit(0);
        foreach ($column as $row => $row_column) {
            $row_column['lesson_id'] = $lesson_id;
            $lesson_category_values = [
                "lesson_id" => "",
                "lesson_category_id" => "",
            ];

            $lesson_category_insert_cond = "";
            $lesson_category_values_cond = "";

            foreach ($lesson_category_values as $key => $value) {
                if (array_key_exists($key, $row_column)) {
                    $lesson_category_bind_values[$key] = $row_column[$key];
                    $lesson_category_insert_cond .= "{$key},";
                    $lesson_category_values_cond .= ":{$key},";
                }
            }

            $lesson_category_insert_cond = rtrim($lesson_category_insert_cond, ',');
            $lesson_category_values_cond = rtrim($lesson_category_values_cond, ',');



            $sql_insert = "INSERT INTO cramschool.lesson_category_lesson({$lesson_category_insert_cond})
                    VALUES ({$lesson_category_values_cond})
                ";
            $stmt_insert = $this->db->prepare($sql_insert);
            $stmt_insert->execute($lesson_category_bind_values);
        }
    }

    public function get_contact($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "contact_id" => null,
            "first_insert_time" => ""
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'first_insert_time') {
                    if ($params[$key] == '') {
                        unset($values[$key]);
                    } else {
                        $values[$key] = $params[$key];
                    }
                } else {
                    $values[$key] = $params[$key];
                }
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values_default[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $condition = "";
        $condition_values = [
            "contact_id" => " AND contact_id = :contact_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($values[$key]);
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

        $time_condition = "";
        $time_condition_values = [
            "first_insert_time" => " AND 
                EXTRACT(DAY FROM first_insert_time::timestamp - :first_insert_time_0::timestamp) >= 0
                AND
                EXTRACT(DAY FROM first_insert_time::timestamp - :first_insert_time_1::timestamp) <= 0
            ",
        ];

        foreach ($time_condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'first_insert_time') {
                    if ($params[$key] == '') {
                    } else {
                        $time_condition .= $value;
                        foreach ($params[$key] as $value_key => $value_value) {
                            $values[$key . '_' . $value_key] = $value_value;
                        }
                        unset($values[$key]);
                    }
                } else {
                    $time_condition .= $value;
                }
            } else {
                unset($values[$key]);
            }
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

                $order .= " {$column_data['column']} {$sort_type},";
            }
            $order = rtrim($order, ',');
        }


        $sql_inside = "SELECT cramschool.contact.id contact_id, cramschool.contact.client,
                            cramschool.contact.phone, cramschool.contact.school,
                            cramschool.grade.\"name\" grade_name, cramschool.grade.id grade_id,
                            cramschool.contact.e_mail, cramschool.contact.question,
                            cramschool.contact.test_name, cramschool.contact.school_score,
                            cramschool.learn_english_year.id learn_english_year_id, 
                            cramschool.learn_english_year.\"name\" learn_english_year_name,
                            to_char(cramschool.contact.first_insert_time, 'YYYY-MM-DD') first_insert_time,
                            CASE WHEN cramschool.contact.user_id IS NULL THEN FALSE ELSE TRUE END is_student
                        FROM cramschool.contact
                        LEFT JOIN cramschool.grade ON cramschool.contact.grade_id = cramschool.grade.id 
                        LEFT JOIN cramschool.learn_english_year ON cramschool.contact.learn_english_year_id = cramschool.learn_english_year.id 
        ";
        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY dt.client) \"key\"
                FROM(
                    {$sql_inside}
                )dt
                WHERE TRUE {$condition} {$select_condition} {$time_condition}
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

        $sql_people = "SELECT COUNT(*)
        FROM(
            {$sql_inside}
        )sql_inside
        ";

        $stmt = $this->db->prepare($sql);
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_people = $this->db->prepare($sql_people);
        if ($stmt->execute($values) && $stmt_count->execute($values_count) && $stmt_people->execute()) {
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_contact($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $contact_values = [
                "client" => "",
                "phone" => "",
                "school" => "",
                "grade_id" => 0,
                "e_mail" => "",
                "question" => "",
                "school_score" => "",
                "learn_english_year_id" => "",
                "test_name" => "",
                "last_edit_user_id" => 0,
                "first_insert_time" => "",
            ];

            $contact_insert_cond = "";
            $contact_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($contact_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $contact_bind_values[$key] = $column[$key];
                    $contact_insert_cond .= "{$key},";
                    $contact_values_cond .= ":{$key},";
                } else {
                    unset($contact_bind_values[$key]);
                }
            }

            $contact_insert_cond .= "last_edit_time,";
            $contact_values_cond .= "NOW(),";

            if (!array_key_exists('first_insert_time', $column)) {
                $contact_insert_cond .= "first_insert_time,";
                $contact_values_cond .= "NOW(),";
            }

            $contact_insert_cond = rtrim($contact_insert_cond, ',');
            $contact_values_cond = rtrim($contact_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.contact({$contact_insert_cond})
                VALUES ({$contact_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($contact_bind_values)) {
                $contact_id = $stmt_insert->fetchColumn(0);
                $result = ["status" => "success", "contact_id" => $contact_id];
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function patch_contact($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $contact_bind_values = [
                "contact_id" => "",
                "client" => "",
                "phone" => "",
                "school" => "",
                "grade_id" => 0,
                "e_mail" => "",
                "question" => "",
                "school_score" => "",
                "learn_english_year_id" => "",
                "test_name" => "",
                "last_edit_user_id" => 0,
                "first_insert_time" => null,
            ];

            $contact_upadte_cond = "";
            $contact_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($contact_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'contact_id') {
                        $contact_bind_values[$key] = $column[$key];
                    } else {
                        $contact_bind_values[$key] = $column[$key];
                        $contact_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $contact_fliter_cond .= "AND cramschool.contact.id = :contact_id";
            $contact_upadte_cond .= "last_edit_time = NOW(),";
            $contact_upadte_cond = rtrim($contact_upadte_cond, ',');

            $sql = "UPDATE cramschool.contact
                    SET {$contact_upadte_cond}
                    WHERE TRUE {$contact_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($contact_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_contact($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_contact_file_bind_values = [
                "contact_id" => "",
            ];

            foreach ($delete_contact_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_contact_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.contact
                WHERE cramschool.contact.id = :contact_id
            ";
            $stmt_delete_contact_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_contact_file->execute($delete_contact_file_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_class($params)
    {
        $values_default = $this->initialize_search();
        $values = [
            "class_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values_default[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values["start"] = $start;
        $values["length"] = $length;
        unset($values['cur_page']);
        unset($values['size']);
        $values_count = $values;
        unset($values_count['start']);
        unset($values_count['length']);

        $condition = "";
        $condition_values = [
            "class_id" => " AND class_id = :class_id",
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

                $order .= " {$column_data['column']} {$sort_type},";
            }
            $order = rtrim($order, ',');
        } else {
            $order = 'ORDER BY class_id DESC';
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT cramschool.class.id class_id, cramschool.class.\"name\", cramschool.class.name_serial, cramschool.class.upper_limit_people::text,
                    cramschool.class.enroll_status::text, cramschool.class.weekend_start, cramschool.class.weekend_end,
                    cramschool.class.class_time_start_default, cramschool.class.class_time_end_default, cramschool.surrounding.id surrounding_id,
                    cramschool.surrounding.\"name\" surrounding_name, cramschool.lesson.id lesson_id, cramschool.lesson.\"name\" lesson_name,
                    cramschool.blog.id blog_id, cramschool.blog.content,
                    COALESCE(student_count.student_count::text, '0')student_count, COALESCE(class_file_data.file_id, '[]')file_id, COALESCE(teacher_data.teacher_data, '[]')teacher_data
                    FROM cramschool.class
                    LEFT JOIN cramschool.surrounding ON cramschool.class.surrounding_id = cramschool.surrounding.id
                    LEFT JOIN cramschool.lesson_class ON cramschool.class.id = cramschool.lesson_class.class_id
                    LEFT JOIN cramschool.lesson ON cramschool.lesson_class.lesson_id = cramschool.lesson.id
                    LEFT JOIN cramschool.class_blog ON cramschool.class.id = cramschool.class_blog.class_id
                    LEFT JOIN cramschool.blog ON cramschool.class_blog.blog_id = cramschool.blog.id
                    LEFT JOIN (
                        SELECT cramschool.class_file.class_id, 
                        JSON_AGG(
                            cramschool.class_file.file_id
                        )file_id
                        FROM cramschool.class_file
                        GROUP BY cramschool.class_file.class_id
                    )class_file_data ON cramschool.class.id = class_file_data.class_id
                    LEFT JOIN (
                        SELECT cramschool.grade_class.class_id, 
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'teacher_id', cramschool.teacher.id,
                                'teacher_name', cramschool.teacher.\"name\"
                            )
                        )teacher_data
                        FROM cramschool.grade_class
                        LEFT JOIN cramschool.grade_class_teacher ON cramschool.grade_class.id = cramschool.grade_class_teacher.grade_class_id
                        LEFT JOIN cramschool.teacher ON cramschool.grade_class_teacher.teacher_id = cramschool.teacher.id
                        GROUP BY cramschool.grade_class.class_id
                    )teacher_data ON cramschool.class.id = teacher_data.class_id
                    LEFT JOIN (
                        SELECT cramschool.grade_class.class_id, COUNT(CASE WHEN cramschool.student.id IS NOT NULL THEN 1 END)student_count
                        FROM cramschool.grade_class
                        LEFT JOIN cramschool.grade_class_student ON cramschool.grade_class.id = cramschool.grade_class_student.grade_class_id
                        LEFT JOIN cramschool.student ON cramschool.grade_class_student.student_id = cramschool.student.id
                        GROUP BY cramschool.grade_class.class_id
                    )student_count ON cramschool.class.id = student_count.class_id
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_class($data, $blog_type_id, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $class_values = [
                "last_edit_user_id" => 0,
                "name" => null,
                "name_serial" => null,
                "surrounding_id" => 0,
                "upper_limit_people" => 0,
                "enroll_status" => null,
                "file_id" => null,
            ];
            $lesson_class_bind_values = [
                "lesson_id" => null,
                "class_id" => null,
            ];
            $delete_lesson_class_bind_values = [
                "class_id" => "",
            ];
            $class_file_bind_values = [
                "class_id" => "",
                "file_id" => null,
            ];
            $class_blog_bind_values = [
                "class_id" => null,
                "content" => "",
            ];
            $delete_class_file_bind_values = [
                "class_id" => "",
            ];
            $delete_class_blog_bind_values = [
                "class_id" => "",
            ];

            $class_insert_cond = "";
            $class_values_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;
            $column['blog_type_id'] = $blog_type_id;
            // $column['content'] = '';

            foreach ($class_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id') {
                        $class_bind_values[$key] = $column[$key];
                    } else {
                        $class_bind_values[$key] = $column[$key];
                        $class_insert_cond .= "{$key},";
                        $class_values_cond .= ":{$key},";
                    }
                } else {
                    unset($class_bind_values[$key]);
                }
            }

            $class_insert_cond .= "last_edit_time,";
            $class_values_cond .= "NOW(),";
            $file_id = $class_bind_values['file_id'];
            unset($class_bind_values['file_id']);
            $class_insert_cond = rtrim($class_insert_cond, ',');
            $class_values_cond = rtrim($class_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.class({$class_insert_cond})
                VALUES ({$class_values_cond})
                RETURNING id
            ";
            $stmt_insert = $this->db->prepare($sql_insert);

            $sql_delete = "DELETE FROM cramschool.class_file
            WHERE cramschool.class_file.class_id = :class_id
            ";
            $stmt_delete_class_file = $this->db->prepare($sql_delete);

            $sql_delete = "DELETE FROM cramschool.class_blog
            WHERE cramschool.class_blog.class_id = :class_id
            ";
            $stmt_delete_class_blog = $this->db->prepare($sql_delete);

            $sql_delete = "DELETE FROM cramschool.lesson_class
            WHERE cramschool.lesson_class.class_id = :class_id
            ";
            $stmt_delete_lesson_class = $this->db->prepare($sql_delete);

            if ($stmt_insert->execute($class_bind_values)) {
                $class_id = $stmt_insert->fetchColumn(0);
                $column['class_id'] = $class_id;
            } else {
                return ['status' => 'failure'];
            }

            foreach ($class_file_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $class_file_bind_values[$key] = $column[$key];
                }
            }
            foreach ($class_blog_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $class_blog_bind_values[$key] = $column[$key];
                }
            }

            foreach ($column['lesson_class_data'] as $key => $inside) {
                foreach ($lesson_class_bind_values as $key => $value) {

                    if (array_key_exists($key, $inside)) {
                        $lesson_class_bind_values[$key] = $inside[$key];
                        $lesson_class_bind_values['class_id'] = $class_id;
                    }
                }
            }

            foreach ($delete_class_file_bind_values as $key => $value) {
                if (array_key_exists($key, $class_file_bind_values)) {
                    $delete_class_file_bind_values[$key] = $class_file_bind_values[$key];
                }
            }
            foreach ($delete_class_blog_bind_values as $key => $value) {
                if (array_key_exists($key, $class_file_bind_values)) {
                    $delete_class_blog_bind_values[$key] = $class_file_bind_values[$key];
                }
            }
            foreach ($delete_lesson_class_bind_values as $key => $value) {
                if (array_key_exists($key, $lesson_class_bind_values)) {
                    $delete_lesson_class_bind_values[$key] = $lesson_class_bind_values[$key];
                }
            }
            $stmt_delete_class_file->execute($delete_class_file_bind_values);
            $stmt_delete_class_blog->execute($delete_class_file_bind_values);
            $stmt_delete_lesson_class->execute($delete_lesson_class_bind_values);
            if (array_key_exists('file_id', $column)) {
                $this->multi_class_file_insert($class_file_bind_values);
            }

            $column['title'] = $column['name'];
            $column['name'] = null;
            if (array_key_exists('lesson_class_data', $column)) {
                $this->multi_lesson_class_insert($lesson_class_bind_values);
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', "info" => $column];
            }
            if (array_key_exists('content', $column)) {
                $this->post_blog([$class_blog_bind_values], $blog_type_id, $last_edit_user_id);
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', "info" => $column];
            }
            /* 未使用年級 */
            $column['grade_id'] = 1;
            $column['teacher_id'] = $column['teacher_data'];
            /*  */
            $post_grade_class_return = $this->post_grade_class([$column]);
            if ($post_grade_class_return['status'] === 'success') {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', "info" => 'post_grade_class'];
            }
        }
        return $result;
    }

    public function patch_class($data, $blog_type_id, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $class_bind_values = [
                "last_edit_user_id" => 0,
                "class_id" => 0,
                "name" => "",
                "name_serial" => "",
                "weekend_start" => null,
                "weekend_end" => null,
                "surrounding_id" => 0,
                "upper_limit_people" => 0,
                "enroll_status" => null,
                "file_id" => null,
            ];
            $patch_lesson_class_bind_values = [
                "lesson_id" => null,
                "class_id" => null,
            ];
            $delete_lesson_class_bind_values = [
                "class_id" => "",
            ];
            $delete_class_file_bind_values = [
                "class_id" => "",
            ];
            $insert_class_file_bind_values = [
                "class_id" => "",
                "file_id" => null,
            ];
            $patch_class_blog_bind_values = [
                "blog_id" => "",
                "content" => "",
                "file_id" => "",
            ];

            $class_upadte_cond = "";
            $class_fliter_cond = "";
            $class_file_fliter_cond = "";
            $blog_fliter_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($class_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'file_id' || $key == 'class_id') {
                        $class_bind_values[$key] = $column[$key];
                    } else if ($key == 'enroll_status') {
                        if ($column[$key]) {
                            $class_bind_values[$key] = 1; /* true */
                        } else {
                            $class_bind_values[$key] = 0; /* false */
                        }
                        $class_upadte_cond .= "{$key} = :{$key},";
                    } else {
                        $class_bind_values[$key] = $column[$key];
                        $class_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($class_bind_values[$key]);
                }
            }

            $class_fliter_cond .= "AND cramschool.class.id = :class_id";
            $class_upadte_cond .= "last_edit_time = NOW(),";
            $class_file_fliter_cond .= "AND cramschool.class_file.class_id = :class_id";
            $blog_fliter_cond .= "AND cramschool.blog.id = :blog_id";
            $class_upadte_cond = rtrim($class_upadte_cond, ',');

            foreach ($insert_class_file_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $insert_class_file_bind_values[$key] = $column[$key];
                }
            }
            foreach ($delete_class_file_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $delete_class_file_bind_values[$key] = $column[$key];
                }
            }

            foreach ($delete_lesson_class_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $delete_lesson_class_bind_values[$key] = $column[$key];
                }
            }

            foreach ($patch_lesson_class_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $patch_lesson_class_bind_values[$key] = $column[$key];
                }
            }
            foreach ($patch_class_blog_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $patch_class_blog_bind_values[$key] = $column[$key];
                }
            }

            $file_id = $class_bind_values['file_id'];
            unset($class_bind_values['file_id']);

            $sql = "UPDATE cramschool.class
                    SET {$class_upadte_cond}
                    WHERE TRUE {$class_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($class_bind_values)) {
                if (array_key_exists('file_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.class_file
                        WHERE TRUE {$class_file_fliter_cond}
                    ";
                    $stmt_delete_class_file = $this->db->prepare($sql_delete);
                    $stmt_delete_class_file->execute($delete_class_file_bind_values);
                    $this->multi_class_file_insert($insert_class_file_bind_values);
                }
                if (array_key_exists('blog_id', $column)) {
                    $this->patch_blog([$patch_class_blog_bind_values], $blog_type_id, $last_edit_user_id);
                }
                if (array_key_exists('lesson_class_data', $column)) {
                    foreach ($column['lesson_class_data'] as $key => $values) {
                        foreach ($patch_lesson_class_bind_values as $key => $value) {
                            if (array_key_exists($key, $values)) {
                                $patch_lesson_class_bind_values[$key] = $values[$key];
                                $this->multi_lesson_class_insert($patch_lesson_class_bind_values);
                            }
                        }
                    }
                    // $sql_delete = "DELETE FROM cramschool.lesson_class
                    //             WHERE cramschool.lesson_class.class_id = :class_id
                    // ";
                    // $stmt_delete_lesson_class = $this->db->prepare($sql_delete);
                    // $stmt_delete_lesson_class->execute($delete_lesson_class_bind_values);
                    // $this->multi_lesson_class_insert($patch_lesson_class_bind_values);
                    $result = ["status" => "success"];
                } else {
                    return ['status' => 'failure', "info" => $column];
                }

                // $this->patch_lesson_category_class([$column]);
                // $this->patch_lesson_class_outline([$column], $last_edit_user_id);
                $result = ["status" => "success"];
            } else {
                var_dump($stmt->errorInfo());
                $result = ['status' => 'failure'];
            }

            /* 未使用年級 */
            $column['grade_id'] = 1;
            $column['teacher_id'] = $column['teacher_data'];
            if (is_null($column['teacher_id'])) {
                $column['teacher_id'] = [];
            }
            /*  */
            /* 取得grade_class_id */
            if (!array_key_exists('grade_class_id', $column)) {
                $sql = "SELECT id
                    FROM cramschool.grade_class
                    WHERE grade_id=:grade_id AND class_id=:class_id
                ";
                $stmt = $this->db->prepare($sql);
                if (
                    $stmt->execute([
                        "grade_id" => $column['grade_id'],
                        "class_id" => $class_bind_values['class_id'],
                    ])
                ) {
                    $grade_class_id = $stmt->fetchColumn(0);
                    $column['grade_class_id'] = $grade_class_id;
                    foreach ($column['teacher_id'] as $column_key => &$column_value) {
                        $column_value['grade_class_id'] = $grade_class_id;
                    }
                }
            }
            /*  */
            $post_grade_class_return = $this->patch_grade_class([$column]);
            if ($post_grade_class_return['status'] === 'success') {
                $result = ["status" => "success"];
            } else {
                return ['status' => 'failure', "info" => 'patch_grade_class'];
            }
        }
        return $result;
    }

    public function delete_class($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_class_bind_values = [
                "class_id" => "",
            ];
            $delete_class_blog_bind_values = [
                "class_id" => "",
            ];

            foreach ($delete_class_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_class_bind_values[$key] = $delete_data[$key];
                }
            }
            foreach ($delete_class_blog_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_class_blog_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.class
                WHERE cramschool.class.id = :class_id
            ";
            $stmt_delete_class = $this->db->prepare($sql_delete);

            $sql_delete = "DELETE FROM cramschool.blog
                WHERE cramschool.blog.id IN (
                    SELECT cramschool.blog.id
                    FROM cramschool.class
                    INNER JOIN cramschool.class_blog ON cramschool.class.id = cramschool.class_blog.class_id
                    INNER JOIN cramschool.blog ON cramschool.class_blog.blog_id = cramschool.blog.id
                    WHERE cramschool.class.id = :class_id
                )
            ";
            $stmt_delete_class_blog = $this->db->prepare($sql_delete);
            if ($stmt_delete_class_blog->execute($delete_class_blog_bind_values) && $stmt_delete_class->execute($delete_class_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function multi_class_file_insert($datas)
    {
        foreach ($datas['file_id'] as $row => $per_file_id) {
            $class_file_insert_cond = "";
            $class_file_values_cond = "";

            $per_class_file_bind_values = [
                "class_id" => "",
                "file_id" => null,
            ];
            foreach ($datas as $key => $value) {
                if ($key == 'file_id') {
                    $per_class_file_bind_values[$key] = $per_file_id;
                    $class_file_insert_cond .= "{$key},";
                    $class_file_values_cond .= ":{$key},";
                } else {
                    $per_class_file_bind_values[$key] = $datas[$key];
                    $class_file_insert_cond .= "{$key},";
                    $class_file_values_cond .= ":{$key},";
                }
            }
            $class_file_insert_cond = rtrim($class_file_insert_cond, ',');
            $class_file_values_cond = rtrim($class_file_values_cond, ',');

            $sql = "INSERT INTO cramschool.class_file({$class_file_insert_cond})
                VALUES ({$class_file_values_cond})
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($per_class_file_bind_values);
        }
    }

    public function multi_lesson_class_insert($column)
    {
        $lesson_values = [
            "class_id" => 0,
            "lesson_id" => 0,
        ];
        $lesson_class_delete_values = [
            "class_id" => 0,
        ];

        $lesson_insert_cond = "";
        $lesson_values_cond = "";

        foreach ($lesson_values as $key => $value) {
            if (array_key_exists($key, $column)) {
                $lesson_bind_values[$key] = $column[$key];
                $lesson_insert_cond .= "{$key},";
                $lesson_values_cond .= ":{$key},";
            }
        }
        foreach ($lesson_class_delete_values as $key => $value) {
            if (array_key_exists($key, $column)) {
                $lesson_delete_bind_values[$key] = $column[$key];
            }
        }

        $lesson_insert_cond = rtrim($lesson_insert_cond, ',');
        $lesson_values_cond = rtrim($lesson_values_cond, ',');

        $sql_delete = "DELETE FROM cramschool.lesson_class
                WHERE cramschool.lesson_class.class_id = :class_id
            ";
        $stmt_insert = $this->db->prepare($sql_delete);
        $stmt_insert->execute($lesson_delete_bind_values);

        $sql_insert = "INSERT INTO cramschool.lesson_class({$lesson_insert_cond})
                VALUES ({$lesson_values_cond})
            ";
        $stmt_insert = $this->db->prepare($sql_insert);
        $stmt_insert->execute($lesson_bind_values);
    }

    public function post_class_blog($column)
    {
        // foreach ($column as $row => $row_column) {
        $class_blog_values = [
            "class_id" => 0,
            "blog_id" => 0,
        ];

        $class_blog_insert_cond = "";
        $class_blog_values_cond = "";

        foreach ($class_blog_values as $key => $value) {
            // if (array_key_exists($key, $row_column)) {
            //     $class_blog_bind_values[$key] = $row_column[$key];
            if (array_key_exists($key, $column)) {
                $class_blog_bind_values[$key] = $column[$key];
                $class_blog_insert_cond .= "{$key},";
                $class_blog_values_cond .= ":{$key},";
            }
        }

        $class_blog_insert_cond = rtrim($class_blog_insert_cond, ',');
        $class_blog_values_cond = rtrim($class_blog_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.class_blog({$class_blog_insert_cond})
                    VALUES ({$class_blog_values_cond})
                ";
        $stmt_insert = $this->db->prepare($sql_insert);
        $stmt_insert->execute($class_blog_bind_values);
        // }
    }

    public function get_grade_class($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "grade_class_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values["start"] = $start;
        $values["length"] = $length;

        $condition = "";
        $condition_values = [
            "grade_class_id" => " AND cramschool.grade_class.id = :grade_class_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }
        $sql = "SELECT *
                FROM(
                    SELECT cramschool.grade_class.id grade_class_id, cramschool.lesson_category.id lesson_category_id,
                    cramschool.lesson_category.\"name\" lesson_category_name, cramschool.grade.id grade_id,
                    cramschool.grade.\"name\" grade_name, COALESCE(grade_class_teacher.teachers, '[]')teachers, 
                    ROW_NUMBER() OVER (ORDER BY cramschool.lesson_category.\"name\") \"key\"
                    FROM cramschool.grade_class
                    LEFT JOIN (
                        SELECT  cramschool.grade_class.id grade_class_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'teacher_id', cramschool.grade_class_teacher.teacher_id,
                                'teacher_name', cramschool.teacher.\"name\"
                            )
                        )  teachers
                        FROM cramschool.grade_class
                        LEFT JOIN cramschool.grade_class_teacher ON cramschool.grade_class.id = cramschool.grade_class_teacher.grade_class_id
                        LEFT JOIN cramschool.teacher ON cramschool.grade_class_teacher.teacher_id = cramschool.teacher.id
                        GROUP BY cramschool.grade_class.id
                    )grade_class_teacher ON cramschool.grade_class.id = grade_class_teacher.grade_class_id
                    LEFT JOIN cramschool.class ON cramschool.grade_class.class_id = cramschool.class.id
                    LEFT JOIN cramschool.lesson_class ON cramschool.class.id = cramschool.lesson_class.class_id
                    LEFT JOIN cramschool.lesson ON cramschool.lesson_class.lesson_id = cramschool.lesson.id
                    LEFT JOIN cramschool.lesson_category_lesson ON cramschool.lesson.id = cramschool.lesson_category_lesson.lesson_id
                    LEFT JOIN cramschool.lesson_category ON  cramschool.lesson_category_lesson.lesson_category_id = cramschool.lesson_category.id 
                    LEFT JOIN cramschool.grade ON cramschool.grade_class.grade_id = cramschool.grade.id
                    WHERE TRUE {$condition}
                    LIMIT :length
                )dt
                WHERE \"key\" > :start          
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $key => $row) {
                foreach ($row as $row_key => $value) {
                    if ($this->isJson($value)) {
                        $result[$key][$row_key] = json_decode($value, true);
                    }
                }
            }
            return $result;
        } else {
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_grade_class($data)
    {
        foreach ($data as $row => $column) {
            $grade_class_values = [
                "class_id" => 0,
                "grade_id" => 0,
                "enroll_time_start" => "",
                "enroll_time_end" => "",
            ];
            $grade_class_teacher_bind_values = [
                "grade_class_id" => "",
                "teacher_id" => [],
            ];

            $grade_class_insert_cond = "";
            $grade_class_values_cond = "";

            foreach ($grade_class_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $grade_class_bind_values[$key] = $column[$key];
                    $grade_class_insert_cond .= "{$key},";
                    $grade_class_values_cond .= ":{$key},";
                } else {
                    unset($grade_class_bind_values[$key]);
                }
            }

            foreach ($grade_class_teacher_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $grade_class_teacher_bind_values[$key] = $column[$key];
                }
            }

            $grade_class_insert_cond = rtrim($grade_class_insert_cond, ',');
            $grade_class_values_cond = rtrim($grade_class_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.grade_class({$grade_class_insert_cond})
                VALUES ({$grade_class_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);
            if ($stmt_insert->execute($grade_class_bind_values)) {
                $grade_class_id = $stmt_insert->fetchColumn(0);
                if (array_key_exists('teacher_id', $grade_class_teacher_bind_values) && $grade_class_teacher_bind_values['teacher_id'] != null) {
                    foreach ($grade_class_teacher_bind_values['teacher_id'] as $grade_class_teacher_bind_values_key => &$grade_class_teacher_bind_values_value) {
                        $grade_class_teacher_bind_values_value['grade_class_id'] = $grade_class_id;
                    }
                    $this->multi_grade_class_teacher_insert($grade_class_teacher_bind_values);
                }
            } else {
                return ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_grade_class($data)
    {
        foreach ($data as $row => $column) {
            $grade_class_bind_values = [
                "grade_class_id" => 0,
                "class_id" => 0,
                "grade_id" => 0,
                "enroll_time_start" => null,
                /* 目前沒有用到 */
                "enroll_time_end" => null, /* 目前沒有用到 */
            ];
            $grade_class_teacher_bind_values = [
                "grade_class_id" => "",
                "teacher_id" => 0,
            ];
            $delete_grade_class_teacher_bind_values = [
                "grade_class_id" => 0,
            ];

            $grade_class_upadte_cond = "";
            $grade_class_fliter_cond = "";
            $grade_class_teacher_fliter_cond = "";
            foreach ($grade_class_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'grade_class_id') {
                        $grade_class_bind_values[$key] = $column[$key];
                        continue;
                    } else {
                        $grade_class_bind_values[$key] = $column[$key];
                    }
                } else {
                    if (is_null($grade_class_bind_values[$key])) {
                        unset($grade_class_bind_values[$key]);
                        continue;
                    }
                }
                $grade_class_upadte_cond .= "{$key} = :{$key},";
            }
            foreach ($grade_class_teacher_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $insert_grade_class_teacher_bind_values[$key] = $column[$key];
                }
            }
            foreach ($delete_grade_class_teacher_bind_values as $key => $value) {
                if (array_key_exists($key, $grade_class_bind_values)) {
                    $delete_grade_class_teacher_bind_values[$key] = $grade_class_bind_values[$key];
                }
            }

            $grade_class_fliter_cond .= "AND cramschool.grade_class.id = :grade_class_id";
            $grade_class_upadte_cond = rtrim($grade_class_upadte_cond, ',');
            $grade_class_teacher_fliter_cond .= "AND cramschool.grade_class_teacher.grade_class_id = :grade_class_id";


            $sql = "UPDATE cramschool.grade_class
                    SET {$grade_class_upadte_cond}
                    WHERE TRUE {$grade_class_fliter_cond}
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($grade_class_bind_values)) {
                if (array_key_exists('teacher_id', $column)) {
                    $sql_delete = "DELETE FROM cramschool.grade_class_teacher
                            WHERE TRUE {$grade_class_teacher_fliter_cond}
                        ";
                    $stmt_delete_grade_class_teacher = $this->db->prepare($sql_delete);
                    $stmt_delete_grade_class_teacher->execute($delete_grade_class_teacher_bind_values);
                    if ($column['teacher_id'] != null) {
                        $this->multi_grade_class_teacher_insert($insert_grade_class_teacher_bind_values);
                    }
                }
            } else {
                var_dump($stmt->errorInfo());
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_grade_class($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_grade_class_bind_values = [
                "grade_class_id" => "",
            ];

            foreach ($delete_grade_class_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_grade_class_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.grade_class
                WHERE cramschool.grade_class.id = :grade_class_id
            ";
            $stmt_delete_grade_class = $this->db->prepare($sql_delete);
            if ($stmt_delete_grade_class->execute($delete_grade_class_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function multi_grade_class_teacher_insert($datas)
    {
        foreach ($datas['teacher_id'] as $row => $per_teacher_id) {
            $grade_class_teacher_insert_cond = "";
            $grade_class_teacher_values_cond = "";

            $per_grade_class_teacher_bind_values = [
                "grade_class_id" => "",
                "teacher_id" => null,
            ];
            foreach ($per_grade_class_teacher_bind_values as $key => $value) {
                if (array_key_exists($key, $per_teacher_id)) {
                    $per_grade_class_teacher_bind_values[$key] = $per_teacher_id[$key];
                }
                $grade_class_teacher_insert_cond .= "{$key},";
                $grade_class_teacher_values_cond .= ":{$key},";
            }
            $grade_class_teacher_insert_cond = rtrim($grade_class_teacher_insert_cond, ',');
            $grade_class_teacher_values_cond = rtrim($grade_class_teacher_values_cond, ',');

            $sql = "INSERT INTO cramschool.grade_class_teacher({$grade_class_teacher_insert_cond})
                VALUES ({$grade_class_teacher_values_cond})
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($per_grade_class_teacher_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
            }
        }
    }

    public function get_user($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $values = [
            "user_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "user_id" => " AND system.user.id = :user_id",
            "role_id" => " AND system.role.id = :role_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }
        $values["start"] = $start;
        $values["length"] = $length;

        $sql = "SELECT *
            FROM(
                SELECT system.user.id user_id, system.user.\"name\" user_name, 
                COALESCE(user_role.role_data, '[]')role_data,
                ROW_NUMBER() OVER (ORDER BY cramschool.user.\"name\") \"key\"
                FROM system.user
                INNER JOIN (
                    SELECT cramschool.user_role.user_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'role_id', cramschool.role.id,
                                'role_name', cramschool.role.\"name\"
                            )
                        ) role_data,
                    FROM cramschool.user_role
                    LEFT JOIN cramschool.role ON cramschool.user_role.role_id = cramschool.role.id
                    GROUP BY cramschool.user_role.user_id
                )user_role ON cramschool.user.id = user_role.user_id
                WHERE TRUE {$condition}
                LIMIT :length
            )dt
            WHERE \"key\" > :start            
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
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

    public function create_user_role($user_id, $role_id)
    {
        $bind_values = [
            'user_id' => $user_id === NULL ? 0 : $user_id,
            'role_id' => $role_id === NULL ? 0 : $role_id
        ];
        $sql = "INSERT INTO cramschool.user_role(user_id, role_id)
                VALUES (:user_id, :role_id)
                ON CONFLICT (user_id, role_id) DO NOTHING  
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

    public function delete_user_role($params)
    {
        $bind_values = ['user_id' => 0];
        foreach ($bind_values as $key => $value) {
            array_key_exists($key, $params) && ($bind_values[$key] = $params[$key]);
        }
        $sql = "DELETE FROM cramschool.user_role
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

    public function get_custom_page_setting($params)
    {
        $bind_values = [
            'user_id' => null,
            'page_name' => null
        ];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            }
        }

        $sql = "SELECT custom_page_setting.custom_setting
                FROM cramschool.custom_page_setting
                WHERE user_id = :user_id AND page_name = :page_name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_custom_page_setting($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $bind_values = [
                'user_id' => 0,
                'page_name' => null,
                'custom_setting' => null
            ];

            foreach ($bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'custom_setting') {
                        $bind_values['custom_setting'] = json_encode($column['custom_setting']);
                    } else {
                        $bind_values[$key] = $column[$key];
                    }
                }
            }

            $bind_values['user_id'] = $last_edit_user_id;

            $sql = "INSERT INTO cramschool.custom_page_setting(
                        user_id, page_name, custom_setting
                    )
                    VALUES (
                        :user_id, :page_name, :custom_setting
                    )
                    ON CONFLICT(user_id, page_name) 
                    DO UPDATE SET custom_setting = :custom_setting
            ";

            $stmt = $this->db->prepare($sql);
            if (!$stmt->execute($bind_values))
                return $stmt->errorInfo();
        }
        return ["status" => "success"];
    }

    public function patch_custom_page_setting($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $custom_page_setting_bind_values = [
                'user_id' => 0,
                'page_name' => null,
                'custom_setting' => null
            ];

            $custom_page_setting_upadte_cond = "";
            $custom_page_setting_fliter_cond = "";

            foreach ($custom_page_setting_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'user_id' || $key == 'page_name') {
                        $custom_page_setting_bind_values[$key] = $column[$key];
                    } else {
                        $custom_page_setting_bind_values[$key] = json_encode($column[$key]);
                        $custom_page_setting_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $custom_page_setting_fliter_cond .= " AND cramschool.custom_page_setting.user_id = :user_id";
            $custom_page_setting_fliter_cond .= " AND cramschool.custom_page_setting.page_name = :page_name";
            $custom_page_setting_upadte_cond = rtrim($custom_page_setting_upadte_cond, ',');
            $custom_page_setting_bind_values['user_id'] = $last_edit_user_id;

            $sql = "UPDATE cramschool.custom_page_setting
                    SET {$custom_page_setting_upadte_cond}
                    WHERE TRUE {$custom_page_setting_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($custom_page_setting_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_custom_page_setting($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_custom_page_setting_bind_values = [
                "user_id" => "",
                "page_name" => "",
            ];

            foreach ($delete_custom_page_setting_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_custom_page_setting_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.custom_page_setting
                WHERE cramschool.custom_page_setting.user_id = :user_id
                AND cramschool.custom_page_setting.page_name = :page_name
            ";
            $stmt_delete_custom_page_setting = $this->db->prepare($sql_delete);
            if ($stmt_delete_custom_page_setting->execute($delete_custom_page_setting_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_learn_english_year($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "learn_english_year_id" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values["start"] = $start;
        $values["length"] = $length;

        $condition = "";
        $condition_values = [
            "learn_english_year_id" => " AND cramschool.learn_english_year.id = :learn_english_year_id",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $sql = "SELECT *
                FROM(
                    SELECT cramschool.learn_english_year.id learn_english_year_id, cramschool.learn_english_year.\"name\",
                    ROW_NUMBER() OVER (ORDER BY cramschool.learn_english_year.\"name\") \"key\"
                    FROM cramschool.learn_english_year
                    WHERE TRUE {$condition}
                    LIMIT :length
                )dt
                WHERE \"key\" > :start          
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return ["status" => "failed"];
        }
    }

    public function post_learn_english_year($data)
    {
        foreach ($data as $row => $column) {
            $learn_english_year_values = [
                "name" => "",
            ];

            $learn_english_year_insert_cond = "";
            $learn_english_year_values_cond = "";

            foreach ($learn_english_year_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $learn_english_year_bind_values[$key] = $column[$key];
                    $learn_english_year_insert_cond .= "{$key},";
                    $learn_english_year_values_cond .= ":{$key},";
                }
            }

            unset($learn_english_year_bind_values['file_id']);
            $learn_english_year_insert_cond = rtrim($learn_english_year_insert_cond, ',');
            $learn_english_year_values_cond = rtrim($learn_english_year_values_cond, ',');

            $sql_insert = "INSERT INTO cramschool.learn_english_year({$learn_english_year_insert_cond})
                VALUES ({$learn_english_year_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($learn_english_year_bind_values)) {
            } else {
                return ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_learn_english_year($data)
    {
        foreach ($data as $row => $column) {
            $learn_english_year_bind_values = [
                "learn_english_year_id" => "",
                "name" => "",
            ];

            $learn_english_year_upadte_cond = "";
            $learn_english_year_fliter_cond = "";

            foreach ($learn_english_year_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key == 'learn_english_year_id') {
                        $learn_english_year_bind_values[$key] = $column[$key];
                    } else {
                        $learn_english_year_bind_values[$key] = $column[$key];
                        $learn_english_year_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $learn_english_year_fliter_cond .= "AND cramschool.learn_english_year.id = :learn_english_year_id";
            $learn_english_year_upadte_cond = rtrim($learn_english_year_upadte_cond, ',');

            $sql = "UPDATE cramschool.learn_english_year
                    SET {$learn_english_year_upadte_cond}
                    WHERE TRUE {$learn_english_year_fliter_cond}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($learn_english_year_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_learn_english_year($data)
    {
        foreach ($data as $row => $delete_data) {
            $delete_learn_english_year_file_bind_values = [
                "learn_english_year_id" => "",
            ];

            foreach ($delete_learn_english_year_file_bind_values as $key => $value) {
                if (array_key_exists($key, $delete_data)) {
                    $delete_learn_english_year_file_bind_values[$key] = $delete_data[$key];
                }
            }

            $sql_delete = "DELETE FROM cramschool.learn_english_year
                WHERE cramschool.learn_english_year.id = :learn_english_year_id
            ";
            $stmt_delete_learn_english_year_file = $this->db->prepare($sql_delete);
            if ($stmt_delete_learn_english_year_file->execute($delete_learn_english_year_file_bind_values)) {
            } else {
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_learn_witness_per($params)
    {
        $bind_values = [
            "learn_witness_id" => null
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
            "learn_witness_id" => " AND cramschool.learn_witness.id = :learn_witness_id"
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $sql = "SELECT cramschool.learn_witness.id learn_witness_id, cramschool.learn_witness.title, cramschool.learn_witness.content,
                cramschool.learn_witness.more_content, cramschool.learn_witness.prove_stu_id, cramschool.student.\"name\" student_name,
                to_char(cramschool.learn_witness.last_edit_time, 'YYYY-MM-DD') last_edit_time,
                to_char(cramschool.learn_witness.annoucement_time, 'YYYY-MM-DD') annoucement_time,
                COALESCE(learn_witness_file.file_id, '[]') file_id
                FROM cramschool.learn_witness
                LEFT JOIN (
                    SELECT cramschool.learn_witness_file.learn_witness_id, 
                    JSON_AGG(
                        (
                            CASE WHEN cramschool.learn_witness_file.file_id IS NOT NULL THEN file_id  END
                        )
                        ORDER BY cramschool.learn_witness_file.file_id DESC
                    )
                    file_id
                    FROM cramschool.learn_witness_file
                    GROUP BY cramschool.learn_witness_file.learn_witness_id
                )learn_witness_file ON cramschool.learn_witness.id = learn_witness_file.learn_witness_id
                LEFT JOIN cramschool.student ON cramschool.learn_witness.prove_stu_id = cramschool.student.id
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

    public function post_surrounding_per($data, $last_edit_user_id)
    {
        $surrounding_values = [
            "last_edit_user_id" => 0,
            "name" => "",
            "name_serial" => "",
            "manage_user_id" => null,
            "note" => "",
            "capacity" => "",
            "file_id" => null,
        ];
        $surrounding_file_bind_values = [
            "surrounding_id" => "",
            "file_id" => null,
        ];
        $delete_surrounding_file_bind_values = [
            "surrounding_id" => "",
        ];

        $surrounding_insert_cond = "";
        $surrounding_values_cond = "";
        $data['last_edit_user_id'] = $last_edit_user_id;

        foreach ($surrounding_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                if ($key == 'file_id') {
                    $surrounding_bind_values[$key] = $data[$key];
                } else {
                    $surrounding_bind_values[$key] = $data[$key];
                    $surrounding_insert_cond .= "{$key},";
                    $surrounding_values_cond .= ":{$key},";
                }
            } else {
                unset($surrounding_bind_values[$key]);
            }
        }

        $surrounding_insert_cond .= "last_edit_time,";
        $surrounding_values_cond .= "NOW(),";

        $file_id = $surrounding_bind_values['file_id'];
        unset($surrounding_bind_values['file_id']);
        $surrounding_insert_cond = rtrim($surrounding_insert_cond, ',');
        $surrounding_values_cond = rtrim($surrounding_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.surrounding({$surrounding_insert_cond})
                VALUES ({$surrounding_values_cond})
                RETURNING id
            ";

        $stmt_insert = $this->db->prepare($sql_insert);

        $sql_delete = "DELETE FROM cramschool.surrounding_file
                WHERE cramschool.surrounding_file.surrounding_id = :surrounding_id
            ";
        $stmt_delete_surrounding_file = $this->db->prepare($sql_delete);

        if ($stmt_insert->execute($surrounding_bind_values)) {
            $surrounding_id = $stmt_insert->fetchColumn(0);
        } else {
            var_dump($stmt_insert->errorInfo());
            return ['status' => 'failure'];
        }

        $surrounding_file_bind_values['surrounding_id'] = $surrounding_id;
        $surrounding_file_bind_values['file_id'] = $file_id;

        foreach ($delete_surrounding_file_bind_values as $key => $value) {
            if (array_key_exists($key, $surrounding_file_bind_values)) {
                $delete_surrounding_file_bind_values[$key] = $surrounding_file_bind_values[$key];
            }
        }
        $stmt_delete_surrounding_file->execute($delete_surrounding_file_bind_values);
        if (array_key_exists('file_id', $data)) {
            $this->multi_surrounding_file_insert($surrounding_file_bind_values);
        }
        $result = ["status" => "success", "surrounding_id" => $surrounding_id];
        return $result;
    }

    public function get_custom_table_setting($params)
    {
        $values_default = $this->initialize_search();

        $values = [
            "custom_table_setting_table_name" => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        foreach ($values_default as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            }
        }

        $length = $values_default['cur_page'] * $values_default['size'];
        $start = $length - $values_default['size'];

        $values["start"] = $start;
        $values["length"] = $length;

        $condition = "";
        $condition_values = [
            "custom_table_setting_table_name" => " AND cramschool.custom_table_setting.table_name = :custom_table_setting_table_name",
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }

        $sql = "SELECT *
                FROM(
                    SELECT cramschool.custom_table_setting.id custom_table_setting_id, cramschool.custom_table_setting.table_name,
                    cramschool.custom_table_setting.column_key , cramschool.custom_table_setting.column_name,
                    ROW_NUMBER() OVER (ORDER BY cramschool.custom_table_setting.table_name) \"key\"
                    FROM cramschool.custom_table_setting
                    WHERE TRUE {$condition}
                    LIMIT :length
                )dt
                WHERE \"key\" > :start          
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function post_custom_table_setting($data)
    {
        foreach ($data as $row => $column) {
            $this->delete_custom_table_setting($column);
            foreach ($column['table_data'] as $table_data_row => $table_data_col) {
                $table_data_col['table_name'] = $column['table_name'];
                $custom_table_setting_values = [
                    "table_name" => "",
                    "column_key" => "",
                    "column_name" => "",
                ];

                $custom_table_setting_insert_cond = "";
                $custom_table_setting_values_cond = "";

                foreach ($custom_table_setting_values as $key => $value) {
                    if (array_key_exists($key, $table_data_col)) {
                        $custom_table_setting_bind_values[$key] = $table_data_col[$key];
                        $custom_table_setting_insert_cond .= "{$key},";
                        $custom_table_setting_values_cond .= ":{$key},";
                    }
                }

                $custom_table_setting_insert_cond = rtrim($custom_table_setting_insert_cond, ',');
                $custom_table_setting_values_cond = rtrim($custom_table_setting_values_cond, ',');

                $sql_insert = "INSERT INTO cramschool.custom_table_setting({$custom_table_setting_insert_cond})
                    VALUES ({$custom_table_setting_values_cond})
                    RETURNING id
                ";

                $stmt_insert = $this->db->prepare($sql_insert);

                if ($stmt_insert->execute($custom_table_setting_bind_values)) {
                } else {
                    return ['status' => 'failure'];
                }
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function delete_custom_table_setting($delete_data)
    {
        $delete_custom_table_setting_bind_values = [
            "table_name" => "",
        ];

        foreach ($delete_custom_table_setting_bind_values as $key => $value) {
            if (array_key_exists($key, $delete_data)) {
                $delete_custom_table_setting_bind_values[$key] = $delete_data[$key];
            }
        }

        $sql_delete = "DELETE FROM cramschool.custom_table_setting
                    WHERE cramschool.custom_table_setting.table_name = :table_name
                ";
        $stmt_delete_custom_table_setting_file = $this->db->prepare($sql_delete);
        if ($stmt_delete_custom_table_setting_file->execute($delete_custom_table_setting_bind_values)) {
        } else {
            $result = ['status' => 'failure'];
        }
        return $result;
    }

    public function delete_emergency_contact($data)
    {
        $delete_emergency_contact_bind_values = [
            "user_id" => "",
        ];

        foreach ($delete_emergency_contact_bind_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $delete_emergency_contact_bind_values[$key] = $data[$key];
            }
        }

        $sql_delete = "DELETE FROM cramschool.emergency_contact
                WHERE cramschool.emergency_contact.user_id = :user_id
            ";
        $stmt_delete_emergency_contact_file = $this->db->prepare($sql_delete);
        if ($stmt_delete_emergency_contact_file->execute($delete_emergency_contact_bind_values)) {
            $result = ["status" => "success"];
        } else {
            $result = ['status' => 'failure'];
        }
        return $result;
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
                                SELECT cramschool.teacher.\"name\", '老師' \"position\",  cramschool.teacher.serial_name,
                                to_char(cramschool.teacher.employment_time_start, 'YYYY-MM-DD')employment_time_start,
                                to_char(cramschool.teacher.employment_time_end, 'YYYY-MM-DD')employment_time_end,
                                cramschool.teacher.user_id, COALESCE(teacher_file.file_id, '[]')file_id, '老師端' position_type
                                FROM cramschool.teacher
                                LEFT JOIN (
                                    SELECT cramschool.teacher_file.teacher_id, 
                                    JSON_AGG(
                                        (
                                            CASE WHEN cramschool.teacher_file.file_id IS NOT NULL THEN file_id  END
                                        )
                                        ORDER BY cramschool.teacher_file.file_id DESC
                                    )file_id
                                    FROM cramschool.teacher_file
                                    GROUP BY cramschool.teacher_file.teacher_id
                                )teacher_file ON cramschool.teacher.id = teacher_file.teacher_id
                                UNION ALL(
                                    SELECT administration_data.*
                                    FROM \"system\".user
                                    INNER JOIN (
                                        SELECT cramschool.administration.\"name\", COALESCE(cramschool.administration.position, '行政人員')position, cramschool.administration.serial_name,
                                        to_char(cramschool.administration.employment_time_start, 'YYYY-MM-DD')employment_time_start,
                                        to_char(cramschool.administration.employment_time_end, 'YYYY-MM-DD')employment_time_end,
                                        cramschool.administration.user_id, COALESCE(administration_file.file_id, '[]')file_id, '行政端' position_type
                                        FROM cramschool.administration
                                        LEFT JOIN (
                                            SELECT cramschool.administration_file.administration_id, 
                                            JSON_AGG(
                                                (
                                                    CASE WHEN cramschool.administration_file.file_id IS NOT NULL THEN file_id  END
                                                )
                                                ORDER BY cramschool.administration_file.file_id DESC
                                            )file_id
                                            FROM cramschool.administration_file
                                            GROUP BY cramschool.administration_file.administration_id
                                        )administration_file ON cramschool.administration.id = administration_file.administration_id
                                    )administration_data ON \"system\".user.id = administration_data.user_id
                                )
                            )permisstion_data
                            LEFT JOIN (
                                SELECT cramschool.user_permission.user_id, 
                                to_char(cramschool.user_permission.permission_time_start, 'YYYY-MM-DD')permission_time_start,
                                to_char(cramschool.user_permission.permission_time_end, 'YYYY-MM-DD')permission_time_end,
                                JSON_AGG(
                                    JSON_BUILD_OBJECT(
                                        'permission_id', cramschool.permission.id,
                                        'permission_name', cramschool.permission.\"name\",
                                        'permission_url', cramschool.permission.\"url\",
                                        'permission_level_id', cramschool.user_permission.permission_level_id
                                    )
                                    ORDER BY cramschool.permission.id
                                )role_permission
                                FROM cramschool.user_permission
                                LEFT JOIN system.user ON cramschool.user_permission.user_id = system.user.id
                                LEFT JOIN cramschool.permission ON cramschool.user_permission.permission_id = cramschool.permission.id
                                WHERE cramschool.permission.is_default IS NOT true
                                GROUP BY cramschool.user_permission.user_id, cramschool.user_permission.permission_time_start, cramschool.user_permission.permission_time_end
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
            var_dump($stmt->errorInfo());
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

                $sql_insert = "INSERT INTO cramschool.user_permission({$user_permission_insert_cond})
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

        $sql_delete = "DELETE FROM cramschool.user_permission
                    WHERE cramschool.user_permission.user_id = :user_id
                ";
        $stmt_delete_user_permission_file = $this->db->prepare($sql_delete);
        if ($stmt_delete_user_permission_file->execute($delete_user_permission_bind_values)) {
            $result = ['status' => 'success'];
        } else {
            $result = ['status' => 'failure'];
        }
        return $result;
    }

    public function patch_lesson_category_class($data)
    {
        foreach ($data as $row => $column) {
            $learn_english_year_bind_values = [
                "lesson_category_id" => "",
                "class_id" => "",
            ];

            $learn_english_year_upadte_cond = "";
            $learn_english_year_fliter_cond = "";

            foreach ($learn_english_year_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    if ($key != 'lesson_category_id') {
                        $learn_english_year_bind_values[$key] = $column[$key];
                    } else {
                        $learn_english_year_bind_values[$key] = $column[$key];
                        $learn_english_year_upadte_cond .= "{$key} = :{$key},";
                    }
                }
            }

            $learn_english_year_fliter_cond .= "AND cramschool.lesson_class.class_id = :class_id";
            $learn_english_year_upadte_cond = rtrim($learn_english_year_upadte_cond, ',');

            $sql = "UPDATE cramschool.lesson_category_lesson
                    SET {$learn_english_year_upadte_cond}
                    WHERE TRUE AND cramschool.lesson_category_lesson.lesson_id IN (
                        SELECT cramschool.lesson.id
                        FROM cramschool.class
                        LEFT JOIN cramschool.lesson_class ON cramschool.class.id = cramschool.lesson_class.class_id
                        LEFT JOIN cramschool.lesson ON cramschool.lesson_class.lesson_id = cramschool.lesson.id
                        WHERE TRUE {$learn_english_year_fliter_cond}
                    )
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($learn_english_year_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function patch_lesson_class_outline($data, $last_edit_user_id)
    {
        foreach ($data as $row => $column) {
            $lesson_bind_values = [
                "last_edit_user_id" => 0,
                "class_id" => 0,
                "outline" => ""
            ];

            $lesson_upadte_cond = "";
            $column['last_edit_user_id'] = $last_edit_user_id;

            foreach ($lesson_bind_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $lesson_bind_values[$key] = $column[$key];
                    if ($key !== 'class_id') {
                        $lesson_upadte_cond .= "{$key} = :{$key},";
                    }
                } else {
                    unset($lesson_bind_values[$key]);
                }
            }
            $lesson_upadte_cond .= "last_edit_time = NOW(),";
            $lesson_upadte_cond = rtrim($lesson_upadte_cond, ',');

            // var_dump($lesson_upadte_cond);

            $sql = "UPDATE cramschool.lesson
                    SET {$lesson_upadte_cond}
                    WHERE TRUE 
                    AND cramschool.lesson.id IN (
                        SELECT lesson_id
                        FROM cramschool.lesson_class
                        WHERE TRUE 
                        AND class_id = :class_id
                    )
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($lesson_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
                $result = ['status' => 'failure'];
            }
        }
        $result = ["status" => "success"];
        return $result;
    }

    public function get_visit_statistics($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];


        $values = [
            "urls" => [],
            "user_id" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $values[$key] = $params[$key];
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $condition_values = [
            "urls" => " AND \"url\" IN (:urls)",
            "user_id" => " AND user_id = :user_id",
        ];

        $select_condition = "";

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($condition_values[$key]);
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

        $select_string = "";
        $select_string_values = [
            "user_id" => ", cramschool.visit_statistics.user_id",
        ];

        foreach ($select_string_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $select_string .= $value;
            } else {
                unset($select_string_values[$key]);
            }
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

                $order .= " {$column_data['column']} {$sort_type},";
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *,  ROW_NUMBER() OVER (ORDER BY visit_statistics.\"url\") \"key\"
                        FROM (
                            SELECT cramschool.visit_statistics.\"url\",
                            COUNT(cramschool.visit_statistics.visit_time)visit_time {$select_string}
                            FROM cramschool.visit_statistics
                            GROUP BY cramschool.visit_statistics.\"url\", cramschool.visit_statistics.user_id
                        )visit_statistics
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

    public function get_permission($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $select_condition = "";

        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
            $select_condition = " AND (";
            foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
                $select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
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

                $order .= " {$column_data['column']} {$sort_type},";
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *,  ROW_NUMBER() OVER (ORDER BY permission.permission_id) \"key\"
                        FROM (
                            SELECT cramschool.permission.id permission_id, cramschool.permission.\"name\",
                            cramschool.permission.\"url\", cramschool.permission.is_default
                            FROM cramschool.permission
                            WHERE cramschool.permission.is_default IS NOT true
                        )permission
                        WHERE TRUE {$select_condition}
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

    public function post_visit_statistics($data, $user_id)
    {
        $data['visit_time'] = "NOW()";
        $data['user_id'] = $user_id;
        $visit_statistics_values = [
            "url" => "",
            "user_id" => 0,
            "visit_time" => '',
        ];

        $visit_statistics_insert_cond = "";
        $visit_statistics_values_cond = "";

        foreach ($visit_statistics_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $visit_statistics_bind_values[$key] = $data[$key];
                $visit_statistics_insert_cond .= "{$key},";
                $visit_statistics_values_cond .= ":{$key},";
            }
        }

        $visit_statistics_insert_cond = rtrim($visit_statistics_insert_cond, ',');
        $visit_statistics_values_cond = rtrim($visit_statistics_values_cond, ',');

        $sql_insert = "INSERT INTO cramschool.visit_statistics({$visit_statistics_insert_cond})
                    VALUES ({$visit_statistics_values_cond})
                    RETURNING id
                ";

        $stmt_insert = $this->db->prepare($sql_insert);

        if ($stmt_insert->execute($visit_statistics_bind_values)) {
            $result = ["status" => "success"];
        } else {
            $result = ["status" => "failure"];
            return $result;
        }
        return $result;
    }

    public function get_encode($role_id)
    {
        $role_encode_default = "";
        $role_table = "";
        $role_table_from = "";
        $serial_pad = 0;
        // $now_year = intval(date("Y")) - 1911;
        $now_year = 107;
        foreach ($role_id as $key => $value) {
            if ($value == 1) {
                $role_encode_default = "MERCYADMIN";
                $role_table = "cramschool.administration_id_seq";
                $role_table_from = "cramschool.administration";
                $serial_pad = 3;
            } else if ($value == 2) {
                $role_encode_default = "MERCY";
                $role_table = "cramschool.teacher_id_seq";
                $role_table_from = "cramschool.teacher";
                $serial_pad = 3;
            } else {
                $role_encode_default = "M{$now_year}";
                $role_table = "cramschool.student_id_seq";
                $role_table_from = "cramschool.student";
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
                return $result;
            } else {
                var_dump($stmt->errorInfo());
            }
        }
    }

    public function get_now_encode($role_id)
    {
        $role_table = "";
        if ($role_id == 1) {
            $role_table = "cramschool.administration";
        } else if ($role_id == 2) {
            $role_table = "cramschool.teacher";
        } else {
            $role_table = "cramschool.student";
        }
        $sql = "SELECT {$role_table}.serial_name
                FROM {$role_table}
            WHERE {$role_table}.id IN (
                SELECT MAX(id) id
                FROM {$role_table}
            )
        ";
        $stmt_user_data = $this->db->prepare($sql);

        $sql = "SELECT (\"system\".\"user\".id::integer + 1) next_id, \"system\".\"user\".id::integer now_id
            FROM \"system\".\"user\"
            WHERE \"system\".\"user\".id IN (
                SELECT MAX(id) id
                FROM \"system\".\"user\"
            )
        ";
        $stmt_user_id_data = $this->db->prepare($sql);

        if ($stmt_user_data->execute() && $stmt_user_id_data->execute()) {
            $encode_result = "";
            $user_serial_name_data = $stmt_user_data->fetchAll(PDO::FETCH_ASSOC);
            $user_id_data_result = $stmt_user_id_data->fetchAll(PDO::FETCH_ASSOC);
            $user_id = $user_id_data_result[0]['next_id'];
            $user_serial_name = $user_serial_name_data[0]['serial_name'];
            $encode_result .= $user_serial_name . $user_id;
            $result = ["now_id" => $user_id, "serial_name" => $encode_result];
            return $result;
        } else {
            var_dump($stmt_user_id_data->errorInfo());
        }
    }

    public function post_line_notify($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: Bearer VtFzsswfgrxXHhQYboiNPs5Gsu68fLRl1UhCqZELaIr"
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        // In real life you should use something like:
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query([
                "message" => $data["message"]
            ])
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        $result = json_decode($head, true);
        return $result;
    }

    public function post_user($data, $role_id)
    {
        foreach ($data as $row => $column) {
            $serial_data = $this->get_now_encode($role_id);
            $column['id'] = $serial_data['now_id'];
            $column['uid'] = $serial_data['serial_name'];

            $user_values = [
                "id" => "",
                "uid" => "",
                "name" => "",
            ];

            $user_insert_cond = "";
            $user_values_cond = "";

            foreach ($user_values as $key => $value) {
                if (array_key_exists($key, $column)) {
                    $user_bind_values[$key] = $column[$key];
                    $user_insert_cond .= "{$key},";
                    $user_values_cond .= ":{$key},";
                } else {
                    unset($user_bind_values[$key]);
                }
            }

            $user_insert_cond = rtrim($user_insert_cond, ',');
            $user_values_cond = rtrim($user_values_cond, ',');

            $sql_insert = "INSERT INTO system.user({$user_insert_cond})
                VALUES ({$user_values_cond})
                RETURNING id
            ";

            $stmt_insert = $this->db->prepare($sql_insert);

            if ($stmt_insert->execute($user_bind_values)) {
                $user_id = $stmt_insert->fetchColumn(0);
                $result = ["status" => "success", "user_id" => $user_id];
            } else {
                var_dump($stmt_insert->errorInfo());
                return ['status' => 'failure'];
            }
        }
        return $result;
    }

    public function post_student_cover_email($data)
    {
        foreach ($data as $key => $row) {
            $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
            $email = sprintf('%s%0.8s', $basename, '');
            $data[$key]['email'] = $email;
        }
        return $data;
    }
    public function get_manual($data)
    {
        $values = [
            "manual_id" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT id, manual_class, manual_name
                FROM cramschool.manual
                WHERE id = :manual_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    //數位典藏拿形象網站檔案
    public function get_blog_file($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "blog_type_id" => null,
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
        $order = "ORDER BY blog_id";

        // var_dump($params['order']);
        // exit(0);
        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                // var_dump($this->isJson($column_data));
                // exit(0);
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
        // var_dump($order);
        // exit(0);

        $condition = "";
        $condition_values = [
            "blog_type_id" => " AND blog_type_id = :blog_type_id",
            "blog_id" => " AND blog_id = :blog_id",
            "upload_time_start" => " AND (EXTRACT(DAY FROM upload_time_start::timestamp - :upload_time_start::timestamp) >= 0 AND upload_time_start::timestamp IS NOT NULL)",
            "upload_time_end" => " AND (EXTRACT(DAY FROM upload_time_end::timestamp - :upload_time_end::timestamp) <= 0 AND upload_time_end::timestamp IS NOT NULL)",
            "file_client_name" => " AND file_client_name = :file_client_name",
            "blog_title" => " AND blog_title = :blog_title"
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $condition .= $value;
            } else {
                unset($bind_values[$key]);
            }
        }
        $searchable_columns = ['upload_time', 'file_client_name', 'blog_title'];
        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
            $select_condition = " AND (";
            foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
                if (in_array($select_filter_arr_data, $searchable_columns)) {
                    $select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
                }
            }
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }

        $bind_values["start"] = $start;
        $bind_values["length"] = $length;
        unset($bind_values['cur_page']);
        unset($bind_values['size']);
        $values_count = $bind_values;
        unset($values_count['start']);
        unset($values_count['length']);

        // $order = "ORDER BY to_char(annoucement_time::timestamp, 'yyyy-MM-dd') DESC";
        // $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY blog_id) \"key\"
        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT cramschool.blog_type.id AS blog_type_id, 
                            cramschool.blog.id AS blog_id, 
                            cramschool.blog.title AS blog_title, 
                            cramschool.file.id AS file_id, 
                            cramschool.file.file_client_name AS file_client_name,  
                            to_char(cramschool.file.upload_time, 'YYYY-MM-DD') AS upload_time,
                            to_char(cramschool.file.last_edit_time, 'YYYY-MM-DD') AS last_edit_time, 
                            system.\"user\".name AS upload_user_name,
                            last_edit_user.name AS last_edit_user_name
                            {$customize_select}
                            
                        
                    FROM cramschool.blog_type
                    LEFT JOIN cramschool.blog ON blog_type.id = blog.blog_type_id
                    LEFT JOIN cramschool.blog_file ON cramschool.blog.id = cramschool.blog_file.blog_id
                    LEFT JOIN cramschool.file ON blog_file.file_id = file.id
                    LEFT JOIN system.user ON cramschool.file.user_id = system.\"user\".id
                    LEFT JOIN system.user AS last_edit_user ON cramschool.file.last_edit_user_id = system.\"user\".id
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }

    public function get_media_learn_witness($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "learn_witness_type_id" => null,
            "learn_witness_id" => null
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
        $order = "ORDER BY learn_witness_id";

        // var_dump($params['order']);
        // exit(0);
        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                // var_dump($this->isJson($column_data));
                // exit(0);
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
                // $order .= ", {$column_data['column']} {$sort_type}";

            }
            // $order = rtrim($order, ',');
        }
        // var_dump($order);
        // exit(0);

        $condition = "";
        $condition_values = [
            "learn_witness_type_id" => " AND learn_witness_type_id = :learn_witness_type_id",
            "upload_time_start" => " AND (EXTRACT(DAY FROM upload_time_start::timestamp - :upload_time_start::timestamp) >= 0 AND upload_time_start::timestamp IS NOT NULL)",
            "upload_time_end" => " AND (EXTRACT(DAY FROM upload_time_end::timestamp - :upload_time_end::timestamp) <= 0 AND upload_time_end::timestamp IS NOT NULL)",
            "learn_witness_id" => " AND learn_witness_id = :learn_witness_id",
            "file_client_name" => " AND file_client_name = :file_client_name",
            "learn_witness_title" => " AND learn_witness_title = :learn_witness_title"
        ];

        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'upload_time_start' || $key == 'upload_time_end') {
                    if ($params[$key] == '') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else {
                    $condition .= $value;
                }
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

        $bind_values["start"] = $start;
        $bind_values["length"] = $length;
        unset($bind_values['cur_page']);
        unset($bind_values['size']);
        $values_count = $bind_values;
        unset($values_count['start']);
        unset($values_count['length']);

        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT cramschool.learn_witness_type.id AS learn_witness_type_id, 
                            cramschool.learn_witness.id AS learn_witness_id, 
                            cramschool.learn_witness.title AS learn_witness_title, 
                            cramschool.file.id AS file_id, 
                            cramschool.file.file_client_name AS file_client_name,  
                            to_char(cramschool.file.upload_time, 'YYYY-MM-DD') AS upload_time,
                            system.\"user\".name AS user_name
                            {$customize_select}
                    FROM cramschool.learn_witness_type
                    LEFT JOIN cramschool.learn_witness ON learn_witness_type.id = learn_witness.learn_witness_type_id
                    LEFT JOIN cramschool.learn_witness_file ON cramschool.learn_witness.id = cramschool.learn_witness_file.learn_witness_id
                    LEFT JOIN cramschool.file ON learn_witness_file.file_id = file.id
                    LEFT JOIN system.user ON cramschool.file.user_id = system.\"user\".id
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
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

        // var_dump($params['order']);
        // exit(0);
        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                // var_dump($this->isJson($column_data));
                // exit(0);
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
        // var_dump($order);
        // exit(0);
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

        $bind_values["start"] = $start;
        $bind_values["length"] = $length;
        unset($bind_values['cur_page']);
        unset($bind_values['size']);
        $values_count = $bind_values;
        unset($values_count['start']);
        unset($values_count['length']);

        // $order = "ORDER BY to_char(annoucement_time::timestamp, 'yyyy-MM-dd') DESC";
        // $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY blog_id) \"key\"
        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(
                    SELECT classify_structure.classify_structure_type_file.classify_structure_type_id,
                        classify_structure.classify_structure_type_file.file_id,
                        cramschool.file.file_client_name AS file_client_name,  
                        to_char(cramschool.file.upload_time, 'YYYY-MM-DD') AS upload_time, 
                        system.\"user\".name AS upload_user_name,
                        to_char(cramschool.file.upload_time, 'YYYY-MM-DD') AS last_edit_time,
                        last_edit_user.name AS last_edit_user_name
                        {$customize_select}

                    FROM classify_structure.classify_structure_type_file
                    LEFT JOIN cramschool.file ON classify_structure.classify_structure_type_file.file_id = cramschool.file.id
                    LEFT JOIN system.\"user\" ON cramschool.file.user_id = system.\"user\".id
                    LEFT JOIN system.\"user\" AS last_edit_user ON cramschool.file.last_edit_user_id = last_edit_user.id
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
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
            $classify_structure_type_file_insert_cond = rtrim($classify_structure_type_file_insert_cond, ',');
            $classify_structure_type_file_values_cond = rtrim($classify_structure_type_file_values_cond, ',');

            $sql = "INSERT INTO classify_structure.classify_structure_type_file({$classify_structure_type_file_insert_cond})
                VALUES ({$classify_structure_type_file_values_cond})
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute($per_classify_structure_type_file_bind_values)) {
            } else {
                var_dump($stmt->errorInfo());
            }
        }
        return ["status" => "success"];
    }
    //拿資料夾
    public function get_classify_structure_type_folder($params)
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

        // var_dump($params['order']);
        // exit(0);
        if (array_key_exists('order', $params)) {
            // $order = 'ORDER BY ';
            foreach ($params['order'] as $key => $column_data) {
                // var_dump($this->isJson($column_data));
                // exit(0);
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
        // var_dump($order);
        // exit(0);
        $condition = "";
        $condition_values = [
            "classify_structure_type_id" => " AND classify_structure_type_parent_id = :classify_structure_type_id",

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

        $bind_values["start"] = $start;
        $bind_values["length"] = $length;
        unset($bind_values['cur_page']);
        unset($bind_values['size']);
        $values_count = $bind_values;
        unset($values_count['start']);
        unset($values_count['length']);

        // $order = "ORDER BY to_char(annoucement_time::timestamp, 'yyyy-MM-dd') DESC";
        // $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY blog_id) \"key\"
        $sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                FROM(

                    SELECT classify_structure.classify_structure_type.classify_structure_type_id, 
                    classify_structure.classify_structure_type.classify_structure_type_parent_id, 
                    classify_structure.classify_structure_type.name,
                    classify_structure.classify_structure_type.index,
                    classify_structure.classify_structure_type.background_color,
                    classify_structure.classify_structure_type.font_color
                    {$customize_select}

                    FROM classify_structure.classify_structure_type
                    -- WHERE classify_structure.classify_structure_type.classify_structure_type_parent_id=classify_structure.classify_structure_type_id
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
    }
    //新增資料夾
    public function post_classify_structure_type_folder($data, $last_edit_user_id)
    {
        $folder_values = [
            "name" => "",
            "classify_structure_type_id" => "",
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
            }
        }

        $folder_insert_cond = rtrim($folder_insert_cond, ',');
        $folder_values_cond = rtrim($folder_values_cond, ',');

        $sql_insert = "INSERT INTO classify_structure.classify_structure_type({$folder_insert_cond})
                VALUES ({$folder_values_cond})
            ";
        $stmt_insert = $this->db->prepare($sql_insert);
        if ($stmt_insert->execute($folder_bind_values)) {
            $result = [
                "status" => "success"
            ];
        } else {
            var_dump($stmt_insert->errorInfo());
            $result = ["status" => "failure"];
        }

        return $result;
    }
    //拿資料夾順序
    public function get_folder_index($params)
    {
        $bind_values = [
            "classify_structure_type_id" => null,
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
            "classify_structure_type_id" => " AND classify_structure_type_parent_id = :classify_structure_type_id",
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
            var_dump($stmt->errorInfo());
            return ["status" => "failed"];
        }
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

            foreach ($folder_bind_values as $key => $value) {
                if ($key == "index") {
                    $folder_bind_values[$key] = $this->get_folder_index(["classify_structure_type_id" => $column['classify_structure_type_id']])[0]['index'];
                    $column[$key] = $folder_bind_values[$key];
                    // var_dump($this->get_folder_index(["classify_structure_type_id" => $data['classify_structure_type_id']])[0]['index']);
                }
                if (array_key_exists($key, $column)) {
                    if ($key == 'classify_structure_type_id') {
                        $folder_bind_values[$key] = $column[$key];
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
                // var_dump($sql);
                var_dump($stmt->errorInfo());
                $result = ['status' => 'failure'];
            }
        }
        return $result;
    }
}
