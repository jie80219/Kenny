<?php

use \Psr\Container\ContainerInterface;
use \Slim\Views\PhpRenderer;
use Slim\Http\UploadedFile;

class dgxcontroller
{
	protected $container;
	public function __construct()
	{
		global $container;
		$this->container = $container;
	}

    public function get_result($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $dgx = new dgx($this->container->db);
        $result = $dgx->get_result($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_result($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $dgx = new dgx($this->container->db);
        $result = $dgx->post_result($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_result($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $dgx = new dgx($this->container->db);
        $result = $dgx->patch_result($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
