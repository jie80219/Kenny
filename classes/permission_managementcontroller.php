<?php

use \Slim\Views\PhpRenderer;


class permission_managementcontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

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


    public function get_permission_list($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new permission_management($this->container->db);
        $result = $CramSchool->get_permission_list($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission_group($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new permission_management($this->container->db);
        $result = $CramSchool->get_permission_group($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission_level($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $CramSchool = new permission_management($this->container->db);
        $result = $CramSchool->get_permission_level($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $permission_management = new permission_management($this->container->db);
        $result = $permission_management->get_permission($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_permission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $permission_management = new permission_management($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $permission_management->post_permission($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_permission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $permission_management = new permission_management($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $permission_management->patch_permission($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_permission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $permission_management = new permission_management($this->container->db);
        $result = $permission_management->delete_permission($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission_manage($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $permission_management = new permission_management($this->container->db);
        $result = $permission_management->get_permission_manage($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_permission_manage_self($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $permission_management = new permission_management($this->container->db);
        $params['user_id'] = $_SESSION['id'];
        $result = $permission_management->get_permission_manage($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_permission_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $permission_management = new permission_management($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $permission_management->post_permission_manage($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_permission_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $permission_management = new permission_management($this->container->db);
        $result = $permission_management->delete_permission_manage($data);
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

}
