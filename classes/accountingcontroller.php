<?php

use \Slim\Views\PhpRenderer;

class AccountingController {
    protected $container;
    public function __construct() {
        global $container;
        $this->container = $container;
    }
    public function renderAccounting($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/accounting/index.html');
    }

    #region Lv0
    public function groupDataLoad($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->groupDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }

    public function groupDataSave($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->groupDataSave($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region Lv1
    public function kindDataLoad($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->kindDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }

    public function kindDataSave($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->kindDataSave($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region Lv2
    public function subjectDataLoad($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->subjectDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }

    public function subjectDataSave($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->subjectDataSave($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region Voucher
    public function voucherMainDataLoad($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->voucherMainDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    public function voucherMainDataSave($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->voucherMainDataSave($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    public function voucherDetailDataLoad($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->voucherDetailDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    public function voucherDetailDataSave($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->voucherDetailDataSave($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
    #region Type
    public function typeDataLoad($request, $response, $args){
        $aRequest = $request->getQueryParams();
        if ($aRequest == null) { $aRequest = $request->getParsedBody(); }

        $business = new Accounting();
        $aData = $business->typeDataLoad($aRequest);
        $response = $business->MakeResponse($response, $aData);
        return $response;
    }
    #endregion
}