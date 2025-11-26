<?php

use \Slim\Views\PhpRenderer;


class CramSchoolController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }
    public function renderCramSchool($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/cramschool/index.html', []);
    }

    public function get_lastest($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 1;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_lastest($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 1;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_lastest($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 1;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_lastest($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 9;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_header_lastest($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 9;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_lastest($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 9;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_lesson_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 14;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_header_lesson_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 14;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_lesson_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 14;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_news_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 15;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_header_news_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 15;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_news_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 15;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_blog($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_file($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->get_file_name($params);
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

    public function post_file($request, $response, $args)
    {
        $data = $request->getParams();
        $data['files'] = $request->getUploadedFiles();
        $CramSchool = new CramSchool($this->container->db);
        $file = $CramSchool->uploadFile($data);
        unset($data['files']);
        $file['user_id'] = $_SESSION['id'];


        $data['file_id'] = $CramSchool->insertFile($file);
        if ($data['file_id'] == '' || $data['file_id'] == null) {
            $result['status'] = 'failed';
        } else {
            $result['file_id'] = $data['file_id'];
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_file($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->patch_file($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_surrounding_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 2;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_surrounding_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 2;
        $last_edit_user_id = $_SESSION['id'];
        foreach ($data as $row => $column) {
            if ($column['surrounding_id'] == 0) {
                $data[$row]['surrounding_id'] = $CramSchool->post_surrounding_per($data, $last_edit_user_id);
            }
        }
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_surrounding_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 2;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_tidbits($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 3;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_tidbits($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 3;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_tidbits($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 3;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_tidbits($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 7;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_header_tidbits($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 7;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_tidbits($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 7;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_teacher_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 4;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_teacher_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 4;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_teacher_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 4;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_teacher_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 6;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_header_teacher_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 6;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_teacher_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 6;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_lesson_category_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        // $blog_type_id = 5;
        // $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_lesson_category_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 5;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_lesson_category_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 5;
        // $blog_type_id = $data['$blog_type_id'];
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_lesson_category_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 8;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_header_lesson_category_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 8;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_lesson_category_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 8;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_header_logo_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 12;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_header_logo_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 12;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_logo_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 12;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_header_environment_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 17;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_header_environment_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 17;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_environment_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 17;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_header_sharelearning_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 18;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_header_sharelearning_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 18;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_header_sharelearning_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 18;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_student($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_student($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_student_excel($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $data['excel'] = true;
        $result = [
            "data" => $CramSchool->get_student($data),
            "response" => $response,
            "name" => '學生資料',
        ];
        // var_dump($result['data']);
        // exit(0);
        $response = $CramSchool->getExcel($result);
        return $response;
    }

    public function get_student_import_data($request, $response, $args)
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $role_id = [3]; //teacher
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            // Increment the highest column letter
            $highestColumn++;
            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    // $tmp[] = strval($worksheet->getCell($col . $row)->getValue());
                    if (trim(strval($worksheet->getCell('A' . $row)->getValue())) != '') {
                        $tmp["uid"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
                        $tmp["name"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
                        $tmp["name_english"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
                        $tmp["gender_id"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
                        $tmp["birthday"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
                        $tmp["address"] = trim(strval($worksheet->getCell('F' . $row)->getValue()));
                        $tmp["email"] = trim(strval($worksheet->getCell('G' . $row)->getValue()));
                        $tmp["class"][0]["class_name"] = trim(strval($worksheet->getCell('H' . $row)->getValue()));
                        $tmp["class"][1]["class_name"] = trim(strval($worksheet->getCell('I' . $row)->getValue()));
                        $tmp["class"][2]["class_name"] = trim(strval($worksheet->getCell('J' . $row)->getValue()));
                        $tmp["school"] = trim(strval($worksheet->getCell('K' . $row)->getValue()));
                        $tmp["grade_serial_num"] = trim(strval($worksheet->getCell('L' . $row)->getValue()));
                        $tmp["emergency_contact"][0]["emergency_contact_name"] = trim(strval($worksheet->getCell('M' . $row)->getValue()));
                        $tmp['emergency_contact'][0]["emergency_contact_phone_number"] = trim(strval($worksheet->getCell('N' . $row)->getValue()));
                        $tmp['emergency_contact'][0]["emergency_contact_gender"] = trim(strval($worksheet->getCell('O' . $row)->getValue()));
                        $tmp["emergency_contact"][1]["emergency_contact_name"] = trim(strval($worksheet->getCell('P' . $row)->getValue()));
                        $tmp['emergency_contact'][1]["emergency_contact_phone_number"] = trim(strval($worksheet->getCell('Q' . $row)->getValue()));
                        $tmp['emergency_contact'][1]["emergency_contact_gender"] = trim(strval($worksheet->getCell('R' . $row)->getValue()));
                        $tmp["emergency_contact"][2]["emergency_contact_name"] = trim(strval($worksheet->getCell('S' . $row)->getValue()));
                        $tmp['emergency_contact'][2]["emergency_contact_phone_number"] = trim(strval($worksheet->getCell('T' . $row)->getValue()));
                        $tmp['emergency_contact'][2]["emergency_contact_gender"] = trim(strval($worksheet->getCell('U' . $row)->getValue()));
                        $tmp["phone"] = trim(strval($worksheet->getCell('V' . $row)->getValue()));
                        $tmp["phone_home"] = trim(strval($worksheet->getCell('W' . $row)->getValue()));
                        $tmp["student_time_start"] = trim(strval($worksheet->getCell('X' . $row)->getValue()));
                        $tmp["student_time_end"] = trim(strval($worksheet->getCell('Y' . $row)->getValue()));
                        $tmp["school_score"] = trim(strval($worksheet->getCell('Z' . $row)->getValue()));
                        $tmp["learn_english_year_name"] = trim(strval($worksheet->getCell('AA' . $row)->getValue()));
                        $tmp["note"] = trim(strval($worksheet->getCell('AB' . $row)->getValue()));
                    }
                }
                if (count($tmp) != 0) {
                    $data[] = $tmp;
                }
            }
            // 檢查編號、名字跟郵件，有編號就update
            // 回傳的資料要有包含重複的資料，每一組包一個array
            $duplicate_user_data = [];
            $single_duplicate_user_data = [];
            if (!is_null($data['uid']) || strlen($data['uid']) != 0) {
                foreach ($data as $key => $value) {
                    $check_user_data = $home->check_user_duplicate_uid($value);
                    if ($check_user_data['status'] == 'failure') {
                        // $duplicate_user_data + $data[$key];
                        // unset($data[$key]);
                        $last_edit_user_id = $_SESSION['id'];
                        $student_id = $CramSchool->get_student_id($value); //找學生id回來 serial_name
                        $value['student_id'] = $student_id;
                        unset($value['uid']);
                        $result = $CramSchool->patch_student([$value], $last_edit_user_id);
                    }
                }
            } else {
                $result_user_data = $home->cramschool_register($data, $role_id);
                if ($result_user_data['status'] == 'success') {
                    $last_edit_user_id = $_SESSION['id'];
                    $result = $CramSchool->post_student($result_user_data['data'], $last_edit_user_id);
                } else {
                    $result = array(
                        "status" => "failure",
                        "message" => "註冊失敗"
                    );
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

    public function get_student_import_data_new($request, $response, $args)
    {
        $CramSchool = new CramSchool($this->container->db);
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
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
                for ($col = 1; $col < $highestColumnIndex; ++$col) {
                    $tmp[trim(strval($worksheet->getCellByColumnAndRow($col, 1)))] = trim(strval($worksheet->getCellByColumnAndRow($col, $row))) == '-' ? '' : trim(strval($worksheet->getCellByColumnAndRow($col, $row)));
                }
                if (count($tmp) != 0) {
                    $data[] = $tmp;
                }
            }
            $values = $request->getParsedBody();
            $default_column = json_decode($values['default_column'], true);
            $data_arr = [];
            for ($index = 0; $index < count($data); $index++) {
                $each_item = $data[$index];
                $data_tmp = [];
                for ($default_column_index = 0; $default_column_index < count($default_column); $default_column_index++) {
                    $each_item_item = $default_column[$default_column_index];
                    if (array_key_exists($each_item_item['title'], $each_item)) {
                        if ($each_item[$each_item_item['title']] !== '') {
                            $data_tmp +=
                                [$each_item_item['dataIndex'] => $each_item[$each_item_item['title']]];
                        }
                    }
                    if (array_key_exists('multiple', $each_item_item) && $each_item_item['multiple']) {
                        if (array_key_exists('key', $each_item_item)) {
                            for ($multiple_index = 1; $multiple_index < 100000; $multiple_index++) {
                                if (array_key_exists($each_item_item['title'] . '' . $multiple_index, $each_item)) {
                                    if ($each_item[$each_item_item['title'] . '' . $multiple_index] !== '') {
                                        if (empty($data_tmp[$each_item_item['key'][0]])) {
                                            $data_tmp[$each_item_item['key'][0]] = [];
                                        }
                                        if (empty($data_tmp[$each_item_item['key'][0]][$multiple_index - 1])) {
                                            $data_tmp[$each_item_item['key'][0]][$multiple_index - 1] = [];
                                        }
                                        $data_tmp[$each_item_item['key'][0]][$multiple_index - 1][$each_item_item['key'][1]] = $each_item[$each_item_item['title'] . '' . $multiple_index];
                                    }
                                } else {
                                    break;
                                }
                            }
                        }
                    }
                }
                // $emergency_contact = $data_tmp['emergency_contact']?$data_tmp['emergency_contact']:[];
                // $data_checked = handle_pre_post(data_tmp,emergency_contact,[]);
                // if(data_checked==undefined){
                //     console.log(index,data_tmp)
                //     break outerloop;
                // }else{
                $data_tmp['uid'] = $data_tmp['serial_name'];
                $data_tmp['index'] = count($data_arr);
                $data_arr[] = $data_tmp;
                // }
            }
        }
        $data = $data_arr;
        $home = new Home($this->container->db);
        $role_id = [3]; //teacher
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            //     $worksheet = $spreadsheet->getActiveSheet();
            //     // Get the highest row number and column letter referenced in the worksheet
            //     $highestRow = $worksheet->getHighestRow(); // e.g. 10
            //     $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            //     // Increment the highest column letter
            //     $highestColumn++;
            // $data = [];
            // for ($row = 2; $row <= $highestRow; ++$row) {
            //     $tmp = [];
            //     for ($col = 'A'; $col != $highestColumn; ++$col) {
            //         // $tmp[] = strval($worksheet->getCell($col . $row)->getValue());
            //         if (trim(strval($worksheet->getCell('A' . $row)->getValue())) != '') {
            //             $tmp["uid"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
            //             $tmp["name"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
            //             $tmp["name_english"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
            //             $tmp["gender_id"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
            //             $tmp["birthday"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
            //             $tmp["address"] = trim(strval($worksheet->getCell('F' . $row)->getValue()));
            //             $tmp["email"] = trim(strval($worksheet->getCell('G' . $row)->getValue()));
            //             $tmp["class"][0]["class_name"] = trim(strval($worksheet->getCell('H' . $row)->getValue()));
            //             $tmp["class"][1]["class_name"] = trim(strval($worksheet->getCell('I' . $row)->getValue()));
            //             $tmp["class"][2]["class_name"] = trim(strval($worksheet->getCell('J' . $row)->getValue()));
            //             $tmp["school"] = trim(strval($worksheet->getCell('K' . $row)->getValue()));
            //             $tmp["grade_serial_num"] = trim(strval($worksheet->getCell('L' . $row)->getValue()));
            //             $tmp["emergency_contact"][0]["emergency_contact_name"] = trim(strval($worksheet->getCell('M' . $row)->getValue()));
            //             $tmp['emergency_contact'][0]["emergency_contact_phone_number"] = trim(strval($worksheet->getCell('N' . $row)->getValue()));
            //             $tmp['emergency_contact'][0]["emergency_contact_gender"] = trim(strval($worksheet->getCell('O' . $row)->getValue()));
            //             $tmp["emergency_contact"][1]["emergency_contact_name"] = trim(strval($worksheet->getCell('P' . $row)->getValue()));
            //             $tmp['emergency_contact'][1]["emergency_contact_phone_number"] = trim(strval($worksheet->getCell('Q' . $row)->getValue()));
            //             $tmp['emergency_contact'][1]["emergency_contact_gender"] = trim(strval($worksheet->getCell('R' . $row)->getValue()));
            //             $tmp["emergency_contact"][2]["emergency_contact_name"] = trim(strval($worksheet->getCell('S' . $row)->getValue()));
            //             $tmp['emergency_contact'][2]["emergency_contact_phone_number"] = trim(strval($worksheet->getCell('T' . $row)->getValue()));
            //             $tmp['emergency_contact'][2]["emergency_contact_gender"] = trim(strval($worksheet->getCell('U' . $row)->getValue()));
            //             $tmp["phone"] = trim(strval($worksheet->getCell('V' . $row)->getValue()));
            //             $tmp["phone_home"] = trim(strval($worksheet->getCell('W' . $row)->getValue()));
            //             $tmp["student_time_start"] = trim(strval($worksheet->getCell('X' . $row)->getValue()));
            //             $tmp["student_time_end"] = trim(strval($worksheet->getCell('Y' . $row)->getValue()));
            //             $tmp["school_score"] = trim(strval($worksheet->getCell('Z' . $row)->getValue()));
            //             $tmp["learn_english_year_name"] = trim(strval($worksheet->getCell('AA' . $row)->getValue()));
            //             $tmp["note"] = trim(strval($worksheet->getCell('AB' . $row)->getValue()));
            //         }
            //     }
            //     if (count($tmp) != 0) {
            //         $data[] = $tmp;
            //     }
            // }

            $result = [
                "status" => "success",
                "duplicate" => [],
                "success" => 0
            ];
            // 檢查編號、名字跟郵件，有編號就update
            // 回傳的資料要有包含重複的資料，每一組包一個array
            /* start 檢查相同email與name */
            $data_only_name_email = json_encode($data);
            $sql = "WITH tmp AS (
                SELECT *,ROW_NUMBER() OVER (PARTITION BY \"name\",email) times
                FROM json_to_recordset('$data_only_name_email')
                AS tmp (\"name\" text, email text, index integer)
            )
            SELECT index
            FROM tmp
            WHERE (\"name\",COALESCE(email,''))IN(
                SELECT \"name\",COALESCE(email,'')
                FROM tmp
                WHERE times > 1
            )
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $data_arr[$row['index']]['type'] = 'file';
                $result['file'][] = $data_arr[$row['index']];
            }
            /* end 檢查相同email與name */
            foreach ($data as $key => $value) {
                if (empty($value['name'])) {
                    // $data[$key]['type'] = 'none';
                    // $result['duplicate'][] = $data[$key];
                    unset($data[$key]);
                }
            }
            /* 
            1.先判斷
            */
            $duplicate_user_data = [];
            $single_duplicate_user_data = [];
            foreach ($data as $key => $value) {
                if (!is_null($value['uid']) || strlen($value['uid']) != 0) {
                    $check_user_data = $home->check_user_duplicate_uid($value);
                    if ($check_user_data['status'] == 'failure') {
                        if (empty($value['email'])) {
                            $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
                            $email = sprintf('%s%0.8s', $basename, '');
                            while (json_encode(json_decode($email)) === FALSE) {
                                $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
                                $email = sprintf('%s%0.8s', $basename, '');
                            }
                            $data[$key]['email'] = $email;
                        }
                        $home->updateUserDetail([
                            "user_name" => $value['name'],
                            "email" => $data[$key]['email'],
                            "user_id" => $home->getUserByUid($value)[0]['id']
                        ]);
                        // $duplicate_user_data + $data[$key];
                        // unset($data[$key]);
                        $last_edit_user_id = $_SESSION['id'];
                        $student_id = $CramSchool->get_student_id($value); //找學生id回來 serial_name
                        $value['student_id'] = $student_id;
                        unset($value['uid']);
                        $CramSchool->patch_student([$value], $last_edit_user_id);
                        unset($data[$key]);
                    }
                }
            }
            foreach ($data as $key => $value) {
                /* 檢查同姓名 */
                $check_user_data = $home->check_user_duplicate_name($value);
                if ($check_user_data['status'] == 'failure') {
                    /* 檢查同姓名與email */
                    $check_user_data = $home->check_user_duplicate($value);
                    if ($check_user_data['status'] == 'failure') {
                        $value['type'] = "both";
                        $result["duplicate"][] = $value;
                        unset($data[$key]);
                        continue;
                    } else {
                        /* 檢查同姓名 為空 email */
                        if (empty($value['email'])) {
                            $value['type'] = "name";
                            $result["duplicate"][] = $value;
                            unset($data[$key]);
                            continue;
                        }
                    }
                }
                if (!is_null($value['uid']) || strlen($value['uid']) != 0) {
                    $check_user_data = $home->check_user_duplicate_uid($value);
                    if ($check_user_data['status'] == 'failure') {
                    } else {
                        goto register;
                    }
                } else {
                    register:
                    $result_user_data = $home->cramschool_register([$value], $role_id);
                    if ($result_user_data['status'] == 'success') {
                        $last_edit_user_id = $_SESSION['id'];
                        $CramSchool->post_student($result_user_data['data'], $last_edit_user_id);
                    } else {
                        $value['type'] = "uid";
                        $result["duplicate"][] = $value;
                        unset($data[$key]);
                    }
                }
            }
        } else {
            $result = array(
                "status" => "failure",
                "message" => "匯入失敗"
            );
        }
        $result["success"] = count($data_arr) - count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_student_cover_email($request, $response, $args)
    {

        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $role_id = [3]; //teacher
        $data = $CramSchool->post_student_cover_email($data);
        $result_user_data = $home->cramschool_register($data, $role_id);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_student($result_user_data['data'], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_student($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $role_id = [3]; //teacher
        $result_user_data = $home->cramschool_register($data, $role_id);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_student($result_user_data['data'], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_admin_contact_to_student($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $role_id = [3]; //teacher
        $result_user_data = $home->cramschool_register($data, $role_id);
        // var_dump($data);
        // var_dump($result_user_data);
        // exit(0);
        $last_edit_user_id = $_SESSION['id'];
        // $result = $CramSchool->post_contact_student($result_user_data['data'], $last_edit_user_id);
        $result = $CramSchool->post_contact_student($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_student($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $result = [];
        foreach ($data as $key => $value) {
            $value['session_user'] = $_SESSION['id'];
            $value['user_name'] = $value['name'];
            $value['edit_time'] = "NOW()";
            $value['role_id'] = [3]; //student
            $value['uid'] = array_key_exists('uid', $value) ? $value['uid'] : $value['serial_name'];
            $value['user_id'] = $home->check_user_duplicate_uid($value)['user_id'];
            $updateUserDetail = $home->updateUserDetail($value);
            if (array_key_exists('password', $value) && array_key_exists('password1', $value) && array_key_exists('oldpassword', $value)) {
                $updatePassword = $home->updatePassword($value);
            } else {
                $updatePassword['status'] = 'success';
            }
            if ($updateUserDetail['status'] === 'success' && $updatePassword['status'] === 'success') {
                $delete_user_role = $CramSchool->delete_user_role($value);
                if ($delete_user_role['status'] === 'success') {
                    foreach ($value['role_id'] as $key2 => $value2) {
                        $create_user_role = $CramSchool->create_user_role($value['user_id'], $value2);
                        if ($create_user_role['status'] === 'success') {
                            $readUserDetailEditorName = $home->readUserDetailEditorName($value['user_id']);
                        } else {
                            $result = ['create_user_role' => $create_user_role];
                            goto exceptionError;
                        }
                    }
                } else {
                    $result = ['delete_user_role' => $delete_user_role];
                    goto exceptionError;
                }
            } else {
                $result = [
                    'updateUserDetail' => $updateUserDetail,
                    'updatePassword' => $updatePassword
                ];
                goto exceptionError;
            }
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
        $result = $CramSchool->patch_student($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
        exceptionError:
        $response = $response->withStatus(500);
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_student($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $result = $CramSchool->delete_student($data);
        if (array_key_exists('user_id_arr', $result)) {
            foreach ($result['user_id_arr'] as $row => $row_data) {
                $deleteUserDetail = $home->deleteUserDetail($row_data);
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

    public function get_teacher($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_teacher($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_teacher_excel($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $data['excel'] = true;
        $result = [
            "data" => $CramSchool->get_teacher($data),
            "response" => $response,
            "name" => '老師資料',
        ];
        // var_dump($result['data']);
        // exit(0);
        $response = $CramSchool->getExcel($result);
        return $response;
    }

    public function post_teacher($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $role_id = [2]; //teacher
        $result_user_data = $home->cramschool_register($data, $role_id);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_teacher($result_user_data['data'], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_teacher($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $home = new Home($this->container->db);
        $result = [];
        foreach ($data as $key => $value) {
            $value['session_user'] = $_SESSION['id'];
            $value['user_name'] = $value['name'];
            $value['edit_time'] = "NOW()";
            $value['role_id'] = [2]; //teacher
            $updateUserDetail = $home->updateUserDetail($value);
            if (array_key_exists('password', $value) && array_key_exists('password1', $value) && array_key_exists('oldpassword', $value)) {
                $updatePassword = $home->updatePassword($value);
            } else {
                $updatePassword['status'] = 'success';
            }
            if ($updateUserDetail['status'] === 'success' && $updatePassword['status'] === 'success') {
                $delete_user_role = $CramSchool->delete_user_role($value);
                if ($delete_user_role['status'] === 'success') {
                    foreach ($value['role_id'] as $key2 => $value2) {
                        $create_user_role = $CramSchool->create_user_role($value['user_id'], $value2);
                        if ($create_user_role['status'] === 'success') {
                            $readUserDetailEditorName = $home->readUserDetailEditorName($value['user_id']);
                        } else {
                            $result = ['create_user_role' => $create_user_role];
                            goto exceptionError;
                        }
                    }
                } else {
                    $result = ['delete_user_role' => $delete_user_role];
                    goto exceptionError;
                }
            } else {
                $result = [
                    'updateUserDetail' => $updateUserDetail,
                    'updatePassword' => $updatePassword
                ];
                goto exceptionError;
            }
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
        $result = $CramSchool->patch_teacher($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
        exceptionError:
        $response = $response->withStatus(500);
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_teacher($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $result = $CramSchool->delete_teacher($data);
        if (array_key_exists('user_id_arr', $result)) {
            foreach ($result['user_id_arr'] as $row => $row_data) {
                $deleteUserDetail = $home->deleteUserDetail($row_data);
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

    public function get_lesson_category($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_lesson_category($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_lesson_category($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 5;
        $last_edit_user_id = $_SESSION['id'];
        $result_lesson_category = $CramSchool->post_lesson_category($data, $last_edit_user_id);
        if ($result_lesson_category['status'] == 'success') {
            $result = $CramSchool->post_blog($result_lesson_category['data_return'], $blog_type_id, $last_edit_user_id);
        } else {
            $result = $result_lesson_category;
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_lesson_category($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $blog_type_id = 5;
        $result_lesson_category = $CramSchool->patch_lesson_category($data, $last_edit_user_id);
        if ($result_lesson_category['status'] == 'success') {
            foreach ($result_lesson_category['data_return'] as $return_row_index => $return_row_value) {
                foreach ($return_row_value['blog_id'] as $per_blog_id_index => $per_blog_id_value) {
                    $return_row_value['blog_id'] = $per_blog_id_value;
                    $result = $CramSchool->patch_blog([$return_row_value], $blog_type_id, $last_edit_user_id);
                }
            }
        } else {
            $result = $result_lesson_category;
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_lesson_category($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_lesson_category($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_chatroom($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_chatroom($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_chatroom_position_list($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_chatroom_position_list($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_chatroom($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $Chat = new Chat($this->container->db);
        $data['data'] = json_encode($data['data']);
        $data['data'] = json_decode($data['data'], true);
        $data['data']['member'] = $CramSchool->get_administration_list();
        $data['data'] = json_encode($data['data']);
        $result = $Chat->createChatroom($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_administration($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_administration($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_administration_excel($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $data['excel'] = true;
        $result = [
            "data" => $CramSchool->get_administration($data),
            "response" => $response,
            "name" => '行政人員資料',
        ];
        // var_dump($result['data']);
        // exit(0);
        $response = $CramSchool->getExcel($result);
        return $response;
    }

    public function post_administration($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $role_id = [1]; //administration
        $result_user_data = $home->cramschool_register($data, $role_id);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_administration($result_user_data['data'], $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_administration($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $result = [];
        foreach ($data as $key => $value) {
            $value['session_user'] = $_SESSION['id'];
            $value['user_name'] = $value['name'];
            $value['edit_time'] = "NOW()";
            $value['role_id'] = [1]; //administration
            $updateUserDetail = $home->updateUserDetail($value);
            if (array_key_exists('password', $value) && array_key_exists('password1', $value) && array_key_exists('oldpassword', $value)) {
                $updatePassword = $home->updatePassword($value);
            } else {
                $updatePassword['status'] = 'success';
            }
            if ($updateUserDetail['status'] === 'success' && $updatePassword['status'] === 'success') {
                $delete_user_role = $CramSchool->delete_user_role($value);
                if ($delete_user_role['status'] === 'success') {
                    foreach ($value['role_id'] as $key2 => $value2) {
                        $create_user_role = $CramSchool->create_user_role($value['user_id'], $value2);
                        if ($create_user_role['status'] === 'success') {
                            $readUserDetailEditorName = $home->readUserDetailEditorName($value['user_id']);
                        } else {
                            $result = ['create_user_role' => $create_user_role];
                            goto exceptionError;
                        }
                    }
                } else {
                    $result = ['delete_user_role' => $delete_user_role];
                    goto exceptionError;
                }
            } else {
                $result = [
                    'updateUserDetail' => $updateUserDetail,
                    'updatePassword' => $updatePassword
                ];
                goto exceptionError;
            }
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
        $result = $CramSchool->patch_administration($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
        exceptionError:
        $response = $response->withStatus(500);
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_administration($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $home = new Home($this->container->db);
        $result = $CramSchool->delete_administration($data);
        if (array_key_exists('user_id_arr', $result)) {
            foreach ($result['user_id_arr'] as $row => $row_data) {
                $deleteUserDetail = $home->deleteUserDetail($row_data);
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

    public function get_surrounding($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_surrounding($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_surrounding($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 2; //surrounding
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_surrounding($data, $last_edit_user_id);
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_surrounding($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_surrounding($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_surrounding($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_surrounding($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_witness_type($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->get_learn_witness_type($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_learn_witness_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_learn_witness_type($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_witness_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->patch_learn_witness_type($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_learn_witness_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_learn_witness_type($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_witness($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_learn_witness($params);
        $result_new_key_arr = [];
        foreach ($result as $row_id => $row_value) {
            foreach ($row_value as $key => $value) {
                if ($CramSchool->isJson($value)) {
                    $result[$row_id][$key] = json_decode($value, true);
                }
            }
        }
        foreach ($result as $row_id => $row_value) {
            foreach ($row_value as $key => $value) {
                if ($key == 'learn_witness_type_name') {
                    unset($row_value['learn_witness_type_name']);
                    $result_new_key_arr[$value] = $row_value;
                }
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_new_key_arr);
        return $response;
    }

    public function post_learn_witness($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_learn_witness($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_witness($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_learn_witness($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_learn_witness($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_learn_witness($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_grade($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_grade($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_grade($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_grade($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_grade($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_grade($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_grade($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_grade($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getUserPermission($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $params['user_id'] = $_SESSION['id'];
        $CramSchool = new CramSchool();
        $result = $CramSchool->getUserPermission($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_lesson($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_lesson($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_lesson($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 11; //lesson
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_lesson($data, $last_edit_user_id);
        // $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_lesson($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_lesson($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_lesson($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_lesson($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_contact($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_contact($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_contact($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $data_message['message'] = "您有一個新的預約提醒！\n\n";
        $last_edit_user_id = $_SESSION['id'];
        $post_contact_result = $CramSchool->post_contact($data, $last_edit_user_id);
        if (array_key_exists('contact_id', $post_contact_result)) {
            $params['contact_id'] = $post_contact_result['contact_id'];
            $result = $CramSchool->get_contact($params);
            if (array_key_exists('data', $result)) {
                foreach ($result['data'] as $row => $column) {
                    $data_message_values =
                        [
                            "client" => "詢問者姓名：client",
                            "phone" => "電話：phone",
                            "school" => "就讀學校：school",
                            "grade_name" => "年級：grade_name",
                            "school_score" => "在校成績：school_score分",
                            "learn_english_year_name" => "學習英文多久：learn_english_year_name",
                            "have_test" => "有無考取檢定：have_test",
                            "test_name" => "證照：test_name",
                            "e_mail" => "信箱：e_mail",
                            "question" => "想暸解的事情：question",
                        ];
                    foreach ($data_message_values as $key => $value) {
                        if (array_key_exists($key, $column) && $column[$key] != null) {
                            $data_message_values[$key] = str_replace("{$key}", "{$column[$key]}", $data_message_values[$key]);
                            if ($key == 'test_name') {
                                $column[$key] != null ? $data_message_values['have_test'] = str_replace("have_test", "有", $data_message_values['have_test']) : $data_message_values['have_test'] = str_replace("have_test", "無", $data_message_values['have_test']);
                            }
                        } else {
                            unset($data_message_values[$key]);
                        }
                    }
                    foreach ($data_message_values as $row => $row_value) {
                        $data_message['message'] .= "{$row_value}\n";
                    }
                }
            }
        }
        $CramSchool->post_line_notify($data_message);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_contact($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_contact($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_contact($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_contact($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_class($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_class($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_class($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 16;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_class($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_class($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 16;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_class($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_class($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_grade_class($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_grade_class($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_grade_class($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_grade_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_grade_class($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->patch_grade_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_grade_class($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_grade_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function batchRegister($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Home = new Home($this->container->db);
        $result = $Home->register($data);
        if ($result['status'] == 'success') {
            $res = $result;
        } else {
            $res = $response->withStatus(500)->withHeader('Content-Type', 'application/json')->write(json_encode($result));
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($res);
        return $response;
    }

    public function get_custom_page_setting($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $params['user_id'] = $_SESSION['id'];

        $result = $CramSchool->get_custom_page_setting($params);
        foreach ($result as $key => $value) {
            if (array_key_exists('custom_setting', $value)) {
                $result[$key]['custom_setting'] = json_decode($value['custom_setting']);
            }
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_custom_page_setting($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_custom_page_setting($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_custom_page_setting($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_custom_page_setting($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_custom_page_setting($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_custom_page_setting($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_english_year($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->get_learn_english_year($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_learn_english_year($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_learn_english_year($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_english_year($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->patch_learn_english_year($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_learn_english_year($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_learn_english_year($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_witness_per($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_learn_witness_per($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_surrounding_per($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_surrounding_per($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_custom_table_setting($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_custom_table_setting($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_custom_table_setting($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_custom_table_setting($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission_manage($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_permission_manage($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_permission_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_permission_manage($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_permission_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_permission_manage($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_permission_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->delete_permission_manage($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_contact_us_blog($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 10;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_contact_us_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 10;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_contact_us_blog($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 10;
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_blog($data, $blog_type_id, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_blog_type($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        // $blog_type_id = 10;
        // $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog_type($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_blog_type_list($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        // $blog_type_id = 10;
        // $params['blog_type_id'] = $blog_type_id;
        $params['list'] = true;
        $result = $CramSchool->get_blog_type($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_blog_type($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->patch_blog_type($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_visit_statistics($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_visit_statistics($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_permission($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_visit_statistics($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $user_id = $_SESSION['id'];
        $result = $CramSchool->post_visit_statistics($data, $user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function broadcast_cram_school_line_notify($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->post_line_notify($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson(["status" => "success"]);
        return $response;
    }

    public function get_surrounding_admin_list($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_surrounding_admin_list($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_contact($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_contact($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_admin_contact($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_contact($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_admin_contact($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_contact($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_admin_contact($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);

        $result = $CramSchool->delete_contact($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_manual($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $result = $CramSchool->get_manual($data);
        foreach ($result as $key => $values) {
            $string = $values['manual_name'];

            if ((pathinfo($string, PATHINFO_BASENAME) === 'png') || (pathinfo($string, PATHINFO_BASENAME) === 'jpg')) {
                $file = $this->container->manual_directory . DIRECTORY_SEPARATOR . $values["manual_name"];
                $source = $this->compressImage($file, $file, 100);
                imagejpeg($source);
                $response = $response->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Disposition', 'attachment;filename="' . $values["manual_name"] . '"')
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate')
                    ->withHeader('Pragma', 'public');
                return $response;
            } else {

                $file = $this->container->manual_directory . $values["manual_name"];
                readfile($file);
                $response = $response->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Disposition', "attachment;filename={$values["manual_name"]}")
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate')
                    ->withHeader('Pragma', 'public');
                return $response;
            }
        }
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
    public function get_media_lastest($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 1;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog_file($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_media_lesson_category($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 5;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog_file($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_media_tidbits($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        $blog_type_id = 3;
        $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_blog_file($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_media_learn_witness($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        // $blog_type_id = 1;
        // $params['blog_type_id'] = $blog_type_id;
        $result = $CramSchool->get_media_learn_witness($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_classify_structure_type_file($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        // $classify_structure_type_id = ;
        // $params['classify_structure_type_id'] = $classify_structure_type_id;
        $result = $CramSchool->get_classify_structure_type_file($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_classify_structure_type_file($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        // $classify_structure_type_id = ;
        // $params['classify_structure_type_id'] = $classify_structure_type_id;
        $result = $CramSchool->post_multi_classify_structure_type_file_insert($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    //拿資料夾
    public function get_classify_structure_type_folder($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new CramSchool($this->container->db);
        // $classify_structure_type_id = ;
        // $params['classify_structure_type_id'] = $classify_structure_type_id;
        $result = $CramSchool->get_classify_structure_type_folder($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    //拿資料夾順序
    // public function get_folder_index($request, $response, $args)
    // {
    //     $params = $request->getQueryParams();
    //     $CramSchool = new CramSchool($this->container->db);
    //     // $classify_structure_type_id = ;
    //     // $params['classify_structure_type_id'] = $classify_structure_type_id;
    //     $result = $CramSchool->get_folder_index($params);
    //     $response = $response->withHeader('Content-type', 'application/json');
    //     $response = $response->withJson($result);
    //     return $response;
    // }

    //新增資料夾
    public function post_classify_structure_type_folder($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->post_classify_structure_type_folder($params, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_classify_structure_type_folder($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $CramSchool = new CramSchool($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $CramSchool->patch_classify_structure_type_folder($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
