<?php

use \Psr\Container\ContainerInterface;
use \Slim\Views\PhpRenderer;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UtilityFormGeneratorController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }
    public function render($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/PMS/index.html');
    }
    public function output_server_side($request, $response, $args, $data)
    {
        $params = $request->getQueryParams();

        $result = [
            "data" => []
        ];
        $result['recordsTotal'] = 0;
        $result['recordsFiltered'] = 0;
        $length = $params['size'];
        $start = ($params['cur_page'] - 1) * $params['size'];
        foreach ($data as $key => $value) {
            $result['recordsTotal'] += 1;
            $result['recordsFiltered'] += 1;
            if ($length > 0 && $key >= $start) {
                array_push($result['data'], $value);
                $length--;
            }
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function response_return($response, $data)
    {
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($data);
        return $response;
    }
    public function get_all_utility_form($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $data = $request->getQueryParams();
        $result = [];
        $forms = $utility_form->get_apply_work_unit($data);
        foreach ($forms as $form) {
            $content = $utility_form->get_utility_form_content(["id" => $form["utility_form_id"]]);
            array_push($result, [
                "form" => $form,
                "content" => $content
            ]);
        }
        return $this->response_return($response, $result);
    }
    public function get_utility_form($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $data = $request->getQueryParams();
        $form = $utility_form->get_utility_form($data);
        $content = $utility_form->get_utility_form_content($data);
        $result = [
            "form" => $form,
            "content" => $content
        ];
        return $this->response_return($response, $result);
    }
    public function get_form_name($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $data = $request->getQueryParams();
        $result = $utility_form->get_utility_form($data);
        return $this->response_return($response, $result);
    }
    public function post_utility_form($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $data = $request->getParsedBody();
        // $data = [
        //     "form" => [
        //         "code" => "form code test 1",
        //         "name" => "form name test 1",
        //     ],
        //     "content" => [
        //         [
        //             "name" => "elementName test 1",
        //             "code" => "index test 1",
        //             "position" => ["x" => 1, "y" => 1],
        //             "connect_db" => null,
        //             "property" => [
        //                 "style" => [
        //                     "width" => 300
        //                 ], "cover" => "<img alt='example' src='https://gw.alipayobjects.com/zos/rmsportal/JiqGstEfoWAOHiTxclqi.png'/>", "actions" => [
        //                     "content" => "[<SettingOutlined key='setting' />,<EditOutlined key='edit' />,<EllipsisOutlined key='ellipsis' />,]"
        //                 ]
        //             ],
        //             "type" => "Card"
        //         ],
        //         [
        //             "name" => "elementName test 2",
        //             "code" => "index test 2",
        //             "position" => ["x" => 5, "y" => 1],
        //             "connect_db" => null,
        //             "property" => [
        //                 "style" => [
        //                     "width" => 300
        //                 ], "cover" => "<img alt='example' src='https://gw.alipayobjects.com/zos/rmsportal/JiqGstEfoWAOHiTxclqi.png'/>", "actions" => [
        //                     "content" => "[<SettingOutlined key='setting' />,<EditOutlined key='edit' />,<EllipsisOutlined key='ellipsis' />,]"
        //                 ]
        //             ],
        //             "type" => "Card"
        //         ]
        //     ]
        // ];
        $form = [];
        $content = [];
        $form = $utility_form->post_utility_form($data[0]["form"]);
        $content = $utility_form->post_utility_form_content($form, $data[1]["content"]);
        if ($content["status"] === "success") {
            $result = $utility_form->post_utility_form_element($form, $content["content"]);
        } else {
            $result = [
                "status" => "fail"
            ];
        }
        return $this->response_return($response, $result);
    }
    public function get_all_of_db($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $result = $utility_form->get_all_of_db();
        return $this->response_return($response, $result);
    }
    public function get_work_unit($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $result = $utility_form->get_work_unit();
        return $this->response_return($response, $result);
    }

    public function get_apply_work_unit($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $data = $request->getQueryParams();
        $result = $utility_form->get_apply_work_unit($data);
        return $this->response_return($response, $result);
    }

    public function test_function($request, $response, $args)
    {
        $utility_form = new UtilityFormGenerator($this->container->db);
        $data = $request->getQueryParams();
        $result = $utility_form->test($data);
        return $this->response_return($response, $result);
    }
}
