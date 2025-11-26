<?php

use \Slim\Views\PhpRenderer;


class KohaController
{
	protected $container;
	public function __construct()
	{
		global $container;
		$this->container = $container;
	}
	public function renderTest($request, $response, $args) {
		$renderer = new PhpRenderer($this->container->view);
		return $renderer->render($response, '/sierra/apiDemo.html', []);
	}
	public function renderKoha($request, $response, $args)
	{
		$renderer = new PhpRenderer($this->container->view);
		return $renderer->render($response, '/koha/index.html', []);
	}
	#region ibrary
	public function get_library($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_library($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_library($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_library($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_library($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_library($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_library($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_library($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region borrower
	public function get_borrower($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_borrower($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_borrower($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_borrower($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_borrower($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_borrower($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_borrower($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_borrower_password($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_borrower_password($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_borrower_validate($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_validate($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#region borrower_category
	public function get_borrower_category($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_borrower_category($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_borrower_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_borrower_category($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_borrower_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_borrower_category($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_borrower_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region borrower_debarred
	public function get_borrower_debarred($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_debarred($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_borrower_debarred($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_borrower_debarred($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_borrower_debarred($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_borrower_debarred($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_borrower_debarred($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_borrower_debarred($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	public function get_borrower_permissions($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_permissions($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_borrower_permissions($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_borrower_permissions($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#region attribute_types
	public function get_borrower_attribute_types($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_attribute_types($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_borrower_attribute_types($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_borrower_attribute_types($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_borrower_attribute_types($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_borrower_attribute_types($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_borrower_attribute_types($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_borrower_attribute_types($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_borrower_attribute_types_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_attribute_types_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion

	#region coverImages_map2file
	public function get_borrower_map2user($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_borrower_map2user($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_borrower_map2user($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_borrower_map2user($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_borrower_map2user($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_borrower_map2user($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_borrower_map2user($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_borrower_map2user($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#endregion
	#region category
	public function get_category_type($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_category_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region authorised_values
	public function get_authorised_values($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_authorised_values($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_authorised_values($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_authorised_values($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_authorised_values($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_authorised_values($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_authorised_values($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_authorised_values($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#region category
	public function get_authorised_values_category($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_authorised_values_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_authorised_values_category($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_authorised_values_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_authorised_values_category($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_authorised_values_category($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#endregion
	#region authorised_values
	public function get_itemtypes($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_itemtypes($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_itemtypes($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_itemtypes($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_itemtypes($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_itemtypes($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_itemtypes($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_itemtypes($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region checkin
	public function post_checkin($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_checkin($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region checkout
	public function get_checkout($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_checkout($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_checkout($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_checkout_renew($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_checkout_renew($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_checkout_renew_history($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout_renew_history($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_checkout_renew_history($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_checkout_renew_history($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_checkout_history($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout_history($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_checkout_history($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_checkout_history($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_checkout_overdue($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout_overdue($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_checkout_statistic_top100($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout_statistic_top100($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_checkout_statistic_pcode3($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout_statistic_pcode3($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_checkout_statistic_ptype($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_checkout_statistic_ptype($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region hold
	public function get_hold($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_hold($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_hold_arrive($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_hold_arrive($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_hold($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_hold($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_hold($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_hold($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_hold($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_hold($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_hold_priority($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_hold_priority($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region fine
	public function get_fine($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_fine($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_fine_outstanding($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_fine_outstanding($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region bib
	public function get_bib($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_bib($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_bib_z3950($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_bib_z3950($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_bib($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_bib($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_bib_marcFile2Json($request, $response, $args) {
		$params = $_FILES;
		$oController = new Koha();
		$aData = $oController->post_bib_marcFile2Json($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_bib($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_bib($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_bib($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_bib($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region item
	public function get_item($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_item($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_item_api($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_item_api($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_item($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_item($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_item($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_item($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_item($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_item($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_item_label($request, $response, $args)
	{
		$params = $request->getParsedBody();
		$oController = new Koha();
		$oController->post_item_label($params);
	}
	#endregion

	#region message
	public function get_message_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_message_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region authority �v��
	public function get_authority($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_authority($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_authority($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_authority($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_authority($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_authority($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_authority($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_authority($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_authority_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_authority_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region circulation_rules �y�q�W�h
	public function get_circulation_rules($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_circulation_rules($params);

		$aTypeData = $oController->get_circulation_rules_type($params);
		// 將所有 rules 進行 decode，不然傳去前端都是字串
		foreach ($aTypeData['data'] as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($key === "rules" && json_decode($value) !== false && json_last_error() === JSON_ERROR_NONE) {
					$aTypeData['data'][$row_id][$key] = json_decode($value, true);
					foreach($aTypeData['data'][$row_id][$key] as $inside_key => $inside_value) {
						if(is_array($inside_value["options"])) continue;
						if (json_decode($inside_value["options"]) !== false && json_last_error() === JSON_ERROR_NONE) {
							$aTypeData['data'][$row_id][$key][$inside_key]["options"] = json_decode($inside_value["options"], true);
						}
					}
				}
			}
		}

		foreach($aData["data"] as $key => $value) {
			foreach($aTypeData['data'] as $type_key => $type_value) {
				if($value["p"] === $type_value["p"]) {
					foreach($aTypeData['data'][$type_key]["rules"] as $inside_key => $inside_value) {
						// 對 P2 的 returnbranch 做特殊處理，因為不知道為什麼 Koha 拉出來的 array string 最後面會出現逗號，這樣會無法 Decode string
						if($type_value["p"] === "P2" && $inside_value["rule_name"] === "returnbranch") {
							// 找到最後一個特定字元的位置
							$lastPos = strrpos($aTypeData['data'][$type_key]["rules"][$inside_key]["options"], ",");
							if ($lastPos !== false) {
								// 替換最後一個字元
								$aTypeData['data'][$type_key]["rules"][$inside_key]["options"] = substr_replace($aTypeData['data'][$type_key]["rules"][$inside_key]["options"], "", $lastPos, 1);
								$aTypeData['data'][$type_key]["rules"][$inside_key]["options"] = json_decode($aTypeData['data'][$type_key]["rules"][$inside_key]["options"], true);
							}
						}
					}
					$aData["data"][$key]["type_data"] = $aTypeData['data'][$type_key]["rules"];
				}
			}
		}

		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_circulation_rules($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData_delete = $oController->delete_circulation_rules($params);
		$aData = $oController->post_circulation_rules($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_circulation_rules($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_circulation_rules($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_circulation_rules($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_circulation_rules($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_circulation_rules_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_circulation_rules_type($params);
		// 將所有 rules 進行 decode，不然傳去前端都是字串
		foreach ($aData['data'] as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($key === "rules" && json_decode($value) !== false && json_last_error() === JSON_ERROR_NONE) {
					$aData['data'][$row_id][$key] = json_decode($value, true);
					foreach($aData['data'][$row_id][$key] as $inside_key => $inside_value) {
						if(is_array($inside_value["options"])) continue;
						if (json_decode($inside_value["options"]) !== false && json_last_error() === JSON_ERROR_NONE) {
							$aData['data'][$row_id][$key][$inside_key]["options"] = json_decode($inside_value["options"], true);
						}
					}
				}
			}
		}

		foreach($aData['data'] as $type_key => $type_value) {
			foreach($aData['data'][$type_key]["rules"] as $inside_key => $inside_value) {
				// 對 P2 的 returnbranch 做特殊處理，因為不知道為什麼 Koha 拉出來的 array string 最後面會出現逗號，這樣會無法 Decode string
				if($type_value["p"] === "P2" && $inside_value["rule_name"] === "returnbranch") {
					// 找到最後一個特定字元的位置
					$lastPos = strrpos($aData['data'][$type_key]["rules"][$inside_key]["options"], ",");
					if ($lastPos !== false) {
						// 替換最後一個字元
						$aData['data'][$type_key]["rules"][$inside_key]["options"] = substr_replace($aData['data'][$type_key]["rules"][$inside_key]["options"], "", $lastPos, 1);
						$aData['data'][$type_key]["rules"][$inside_key]["options"] = json_decode($aData['data'][$type_key]["rules"][$inside_key]["options"], true);
					}
				}
			}
		}
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region vendor �Ѱ�
	public function get_vendor_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_vendor_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}

	#endregion
	#region serial 期刊
	public function get_serial($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_serial($params);
		// 將所有 rules 進行 decode，不然傳去前端都是字串堃
		foreach ($aData['data'] as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($key === "rules" && json_decode($value) !== false && json_last_error() === JSON_ERROR_NONE) {
					$aData['data'][$row_id][$key] = json_decode($value, true);
				}
			}
		}
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_serial($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_serial($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_serial($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_serial($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_serial($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_serial($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_serial_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_serial_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#region periodicity
	public function get_serial_periodicity($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_serial_periodicity($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_serial_periodicity($request, $response, $args) {
		$params = $request->getParsedBody();

		$transform_array = [];
		foreach($params as $key => $value) {
			$tmp_array = [];
			$tmp_array["id"] = $value["id"];
			$tmp_array["phrases"] = [];
			$column_tmp_array = [];
			$column_tmp_array["phrase"] = $value["phrase"];
			$column_tmp_array["columns"] = [];
			$column_tmp_array["columns"]["description"] = $value["description"];
			$column_tmp_array["columns"]["unit"] = $value["unit"];
			$column_tmp_array["columns"]["issuesperunit"] = $value["issuesperunit"];
			$column_tmp_array["columns"]["unitsperissue"] = $value["unitsperissue"];
			$column_tmp_array["columns"]["displayorder"] = $value["displayorder"];
			$tmp_array["phrases"][] = $column_tmp_array;
			$transform_array[] = $tmp_array;
		}
		$oController = new Koha();
		$aData = $oController->post_serial_periodicity($transform_array);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_serial_periodicity($request, $response, $args) {
		$params = $request->getParsedBody();

		$transform_array = [];
		foreach($params as $key => $value) {
			$tmp_array = [];
			$tmp_array["id"] = $value["id"];
			$tmp_array["phrases"] = [];
			$column_tmp_array = [];
			$column_tmp_array["phrase"] = $value["phrase"];
			$column_tmp_array["columns"] = [];
			$column_tmp_array["columns"]["description"] = $value["description"];
			$column_tmp_array["columns"]["unit"] = $value["unit"];
			$column_tmp_array["columns"]["issuesperunit"] = $value["issuesperunit"];
			$column_tmp_array["columns"]["unitsperissue"] = $value["unitsperissue"];
			$column_tmp_array["columns"]["displayorder"] = $value["displayorder"];
			$tmp_array["phrases"][] = $column_tmp_array;
			$transform_array[] = $tmp_array;
		}
		$oController = new Koha();
		$aData = $oController->patch_serial_periodicity($transform_array);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_serial_periodicity($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_serial_periodicity($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_serial_periodicity_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_serial_periodicity_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#region numberingpattern
	public function get_serial_numberingpattern($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_serial_numberingpattern($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_serial_numberingpattern($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_serial_numberingpattern($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_serial_numberingpattern($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_serial_numberingpattern($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_serial_numberingpattern($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_serial_numberingpattern($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function get_serial_numberingpattern_type($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_serial_numberingpattern_type($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#endregion
	#region coverImages
	#region coverImages_map2file
	public function get_cover_images_map2file($request, $response, $args) {
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_cover_images_map2file($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function post_cover_images_map2file($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->post_cover_images_map2file($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_cover_images_map2file($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_cover_images_map2file($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function delete_cover_images_map2file($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->delete_cover_images_map2file($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	#endregion
	#endregion
	public function get_marc_tagStructure($request, $response, $args)
	{
		$params = $request->getQueryParams();
		$oController = new Koha();
		$aData = $oController->get_marc_tagStructure($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}
	public function patch_refresh_biblio_search_ts($request, $response, $args) {
		$params = $request->getParsedBody();
		$oController = new Koha();
		$aData = $oController->patch_refresh_biblio_search_ts($params);
		$response = $oController->MakeResponse($response, $aData);
		return $response;
	}



	public function response_return($response, $data)
    {
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($data);
        return $response;
    }
	
	// 館藏資料匯出
	public function get_item_export_excel($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
		$oController = new Koha();
        $params['excel'] = true;
        $result = [
            "data" => $oController->get_item($params),
            "response" => $response,
            "name" => '館藏資料',
        ];
        $response = $oController->getExcel($result);
        return $response;
    }
	// 館藏資料匯入範例
	public function get_item_import_manual($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $organization_structure = new organization_structure($this->container->db);
        $oController = new Koha();

		$item_api = $oController->get_item_api(["itemnumber" => "0"])["data"];
		$selection = [];
		foreach($item_api as $key => $value) $selection = $value;
		
		$withdrawn_0 = "";
		foreach($selection["withdrawn_options"] as $key => $value) $withdrawn_0 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$withdrawn_0 = "(" . rtrim($withdrawn_0, ", ") . ")";
		$call_number_source_2 = "";
		foreach($selection["call_number_source_options"] as $key => $value) $call_number_source_2 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$call_number_source_2 = "(" . rtrim($call_number_source_2, ", ") . ")";
		$damaged_4 = "";
		foreach($selection["damaged_options"] as $key => $value) $damaged_4 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$damaged_4 = "(" . rtrim($damaged_4, ", ") . ")";
		$restricted_5 = "";
		foreach($selection["restricted_options"] as $key => $value) $restricted_5 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$restricted_5 = "(" . rtrim($restricted_5, ", ") . ")";
		$notforloan_7 = "";
		foreach($selection["notforloan_options"] as $key => $value) $notforloan_7 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$notforloan_7 = "(" . rtrim($notforloan_7, ", ") . ")";
		$collection_8 = "";
		foreach($selection["collection_options"] as $key => $value) $collection_8 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$collection_8 = "(" . rtrim($collection_8, ", ") . ")";
		$library_a_b = "";
		foreach($selection["library_options"] as $key => $value) $library_a_b .= "{$value['lib']}: {$value['authorised_value']}, ";
		$library_a_b = "(" . rtrim($library_a_b, ", ") . ")";
		$shelving_control_number_c_j = "";
		foreach($selection["shelving_control_number_options"] as $key => $value) $shelving_control_number_c_j .= "{$value['lib']}: {$value['authorised_value']}, ";
		$shelving_control_number_c_j = "(" . rtrim($shelving_control_number_c_j, ", ") . ")";
		$item_type_y = "";
		foreach($selection["item_type_options"] as $key => $value) $item_type_y .= "{$value['lib']}: {$value['authorised_value']}, ";
		$item_type_y = "(" . rtrim($item_type_y, ", ") . ")";

        $excel = [
            "data" => [
                [
					" 0 - 停用狀態 Withdrawn status\n{$withdrawn_0} " => "", 
					" 2 - 分類或上架方案來源 Source of classification or shelving scheme\n{$call_number_source_2} " => "", 
					" 3 - 指定材料（裝訂冊或其他部分）Materials specified (bound volume or other part) " => "", 
					" 4 - 損壞狀態 Damaged status\n{$damaged_4} " => "", 
					" 5 - 使用限制 Use restrictions\n{$restricted_5} " => "", 
					" 7 - 不外借 Not for loan\n{$notforloan_7} " => "", 
					" 8 - 館藏 Collection\n{$collection_8} " => "", 
					" a - 所屬圖書館 Home library\n此欄位必填!\n{$library_a_b} " => "", 
					" b - 當前所在圖書館 Current library\n此欄位必填!\n{$library_a_b} " => "", 
					" c - 擱位位置 Shelving location\n{$shelving_control_number_c_j} " => "", 
					" d - 入館日期 Date acquired\n範例格式：2050-01-01 " => "", 
					" e - 取資來源 Source of acquisition " => "", 
					" f - 地點代碼 Coded location qualifier " => "", 
					" g - 成本，正常購買價格 Cost, normal purchase price(請輸入數值) " => "", 
					" h - 期刊期數/時序 Serial enumeration/chronology " => "", 
					" i - 庫存號 Inventory number " => "", 
					" j - 架位控制號 Shelving control number\n{$shelving_control_number_c_j} " => "", 
					" o - 完整索書號 Full call number " => "", 
					" p - 條碼 Barcode " => "", 
					" t - 複製 Copy number " => "", 
					" u - 統一資源標識符 Uniform resource identifier " => "", 
					" v - 成本，替換價格 Cost, replacement price " => "", 
					" w - 價格生效日期 Price effective from\n範例格式：2050-01-01 " => "", 
					" x - 內部備註 Non-public note " => "", 
					" y - 館藏類型 Item type\n此欄位必填!\n{$item_type_y} " => "", 
					" z - 公開備註 Public note " => "", 
                ]
            ],
            "response" => $response,
            "name" => '館藏匯入範例',
        ];
        $response = $oController->getExcel($excel);
        return $response;
    }
	// 館藏匯入檔案上傳
    public function get_item_import_data($request, $response, $args)
    {
        $data = $request->getParsedBody();
		$biblionumber = null;
		if(isset($data["biblionumber"])) {
			$biblionumber = $data["biblionumber"];
			unset($data["biblionumber"]);
		}
		$biblio_id = null;
		if(isset($data["biblio_id"])) {
			$biblio_id = $data["biblio_id"];
			unset($data["biblio_id"]);
		}
        $oController = new Koha();

        $item_api = $oController->get_item_api(["itemnumber" => "0"])["data"];
		$selection = [];
		foreach($item_api as $key => $value) $selection = $value;
		
		$withdrawn_0 = "";
		foreach($selection["withdrawn_options"] as $key => $value) $withdrawn_0 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$withdrawn_0 = "(" . rtrim($withdrawn_0, ", ") . ")";
		$call_number_source_2 = "";
		foreach($selection["call_number_source_options"] as $key => $value) $call_number_source_2 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$call_number_source_2 = "(" . rtrim($call_number_source_2, ", ") . ")";
		$damaged_4 = "";
		foreach($selection["damaged_options"] as $key => $value) $damaged_4 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$damaged_4 = "(" . rtrim($damaged_4, ", ") . ")";
		$restricted_5 = "";
		foreach($selection["restricted_options"] as $key => $value) $restricted_5 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$restricted_5 = "(" . rtrim($restricted_5, ", ") . ")";
		$notforloan_7 = "";
		foreach($selection["notforloan_options"] as $key => $value) $notforloan_7 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$notforloan_7 = "(" . rtrim($notforloan_7, ", ") . ")";
		$collection_8 = "";
		foreach($selection["collection_options"] as $key => $value) $collection_8 .= "{$value['lib']}: {$value['authorised_value']}, ";
		$collection_8 = "(" . rtrim($collection_8, ", ") . ")";
		$library_a_b = "";
		foreach($selection["library_options"] as $key => $value) $library_a_b .= "{$value['lib']}: {$value['authorised_value']}, ";
		$library_a_b = "(" . rtrim($library_a_b, ", ") . ")";
		$shelving_control_number_c_j = "";
		foreach($selection["shelving_control_number_options"] as $key => $value) $shelving_control_number_c_j .= "{$value['lib']}: {$value['authorised_value']}, ";
		$shelving_control_number_c_j = "(" . rtrim($shelving_control_number_c_j, ", ") . ")";
		$item_type_y = "";
		foreach($selection["item_type_options"] as $key => $value) $item_type_y .= "{$value['lib']}: {$value['authorised_value']}, ";
		$item_type_y = "(" . rtrim($item_type_y, ", ") . ")";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $last_edit_user_id = $_SESSION['id'];
            $data_arr = $oController->read_excel($uploadedFile, $data);

            $parser_decode = [
                "withdrawn" => "0 - 停用狀態 Withdrawn status\n{$withdrawn_0}", 
				"call_number_source" => "2 - 分類或上架方案來源 Source of classification or shelving scheme\n{$call_number_source_2}", 
				"materials_notes" => "3 - 指定材料（裝訂冊或其他部分）Materials specified (bound volume or other part)", 
				"damaged_status" => "4 - 損壞狀態 Damaged status\n{$damaged_4}", 
				"restricted_status" => "5 - 使用限制 Use restrictions\n{$restricted_5}", 
				"not_for_loan_status" => "7 - 不外借 Not for loan\n{$notforloan_7}", 
				"collection_code" => "8 - 館藏 Collection\n{$collection_8}", 
				"home_library_id" => "a - 所屬圖書館 Home library\n此欄位必填!\n{$library_a_b}", 
				"holding_library_id" => "b - 當前所在圖書館 Current library\n此欄位必填!\n{$library_a_b}", 
				"location" => "c - 擱位位置 Shelving location\n{$shelving_control_number_c_j}", 
				"acquisition_date" => "d - 入館日期 Date acquired\n範例格式：2050-01-01", 
				"acquisition_source" => "e - 取資來源 Source of acquisition", 
				"coded_location_qualifier" => "f - 地點代碼 Coded location qualifier", 
				"purchase_price" => "g - 成本，正常購買價格 Cost, normal purchase price(請輸入數值)", 
				"serial_issue_number" => "h - 期刊期數/時序 Serial enumeration/chronology", 
				"inventory_number" => "i - 庫存號 Inventory number", 
				"shelving_control_number" => "j - 架位控制號 Shelving control number\n{$shelving_control_number_c_j}", 
				"callnumber" => "o - 完整索書號 Full call number", 
				"external_id" => "p - 條碼 Barcode", 
				"copy_number" => "t - 複製 Copy number", 
				"uri" => "u - 統一資源標識符 Uniform resource identifier", 
				"replacement_price" => "v - 成本，替換價格 Cost, replacement price", 
				"replacement_price_date" => "w - 價格生效日期 Price effective from\n範例格式：2050-01-01", 
				"internal_notes" => "x - 內部備註 Non-public note", 
				"item_type_id" => "y - 館藏類型 Item type\n此欄位必填!\n{$item_type_y}", 
				"internal_notes" => "z - 公開備註 Public note", 
			];
            $data_import = $oController->decodestaffData($data_arr, $parser_decode, ["department_role_data" => []]);

            $result = [
                'data' => $data_import,
                "status" => "success", 
				"body" => $data, 
            ];
            foreach ($result['data'] as $item_key => $item_value) {
				$post_data = $item_value;
				$post_data["biblionumber"] = $biblionumber;
				$post_data["biblio_id"] = $biblio_id;
				$aData = $oController->post_item([$post_data]);
            }
        } else {
            $result = array(
                "status" => "failure",
                "message" => "匯入失敗"
            );
        }
		return $this->response_return($response, $result);
    }
}