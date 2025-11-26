<?php

use \Slim\Views\PhpRenderer;


class SierraController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }
    public function renderSierra($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/sierra/index.html', []);
    }

    #region patron
    public function get_patron($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_patron($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function post_patron($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->post_patron($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function patch_patron($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->patch_patron($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
	public function delete_patron($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->delete_patron($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_patron_validate($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_patron_validate($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region hold
    public function get_hold($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_hold($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_hold_arrive($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_hold_arrive($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function post_hold($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->post_hold($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function patch_hold($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->patch_hold($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
	public function delete_hold($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->delete_hold($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region checkout
    public function get_checkout($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_checkout($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_checkout_history($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_checkout_history($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_checkout_overdue($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_checkout_overdue($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_checkout_statistic_top100($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_checkout_statistic_top100($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_checkout_statistic_pcode3($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_checkout_statistic_pcode3($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_checkout_statistic_ptype($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_checkout_statistic_ptype($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function post_checkout($request, $response, $args) {
        $params = $request->getParsedBody();
        $oController = new Sierra();
        $aData = $oController->post_checkout($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region fine
    public function get_fine($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_fine($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    public function get_fine_outstanding($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_fine_outstanding($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region bib
    public function get_bib($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_bib($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region item
    public function get_item($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_item($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region token
    public function get_token($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_token($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region token
    public function get_mail($request, $response, $args) {
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->get_mail($params);
        $response = $oController->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    public function check_card_reader_data($request, $response, $args){
        $params = $request->getQueryParams();
        $oController = new Sierra();
        $aData = $oController->check_card_reader_data($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($aData);
        return $response;
    }
}
