<?php

use \Psr\Container\ContainerInterface;
use \Slim\Views\PhpRenderer;
use Slim\Http\UploadedFile;

class SystemController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }
    public function renderSetting($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/system/setting.html', []);
    }
    // public function renderStaffManagement($request, $response, $args)
    // {
    //     $renderer = new PhpRenderer($this->container->view);
    //     return $renderer->render($response, '/system/StaffManagement.html', []);
    // }


    public function postModulesAllPermissions($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $system = new System();
        $result = $system->postModulesAllPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getModulesAllPermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $system = new System();
        $result = $system->getModulesAllPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    public function postUserPermission($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $system = new System();
        $result = $system->postUserPermission($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getUserPermission($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $system = new System();
        $result = $system->getUserPermission($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getPermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $system = new System();
        $result = $system->getPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getModulesPermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $system = new System();
        $result = $system->getModulesPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getModulePermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $system = new System();
        $result = $system->getModulePermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patchModulePermissions($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $system = new System();
        $result = $system->patchModulePermissions($data);
        if($result){
            $result = [
                "status"=>"success"
            ];
        }else{
            $result = [
                "status"=>"failed"
            ];
            $response = $response->withStatus(500);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getOwnPermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $system = new System();
        $data['user_id'] = $_SESSION['id'];
        $result = $system->getOwnPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function postModule($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $system = new System();
        $result = $system->insertModule($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patchModule($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $system = new System();
        foreach ($data as $key => $value) {
            $result = $system->updateModule($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function deleteModule($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $system = new System();
        foreach ($data as $key => $value) {
            $result = $system->deleteModule($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    #region User Relationship
    function getRequestData($request) {
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }
        return $aRequest;
    }
    public function userRelationshipDataLoad($request, $response, $args){
        $aRequest = $this->getRequestData($request);

        $business = new System();
        $aData = $business->userRelationshipDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }

    public function userRelationshipDataSave($request, $response, $args){
        $aRequest = $this->getRequestData($request);

        $business = new System();
        $aData = $business->userRelationshipDataSave($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
}