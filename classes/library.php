<?php

use GuzzleHttp\Promise\Is;
use nknu\extend\xBool;
use nknu\extend\xDateTime;
use nknu\extend\xFloat;
use nknu\extend\xInt;
use PhpOffice\PhpSpreadsheet\Helper\Size;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Content;
use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;
use function PHPSTORM_META\map;
use nknu\base\xBaseWithDbop;
use nknu\utility\xStatic;
use nknu\extend\xString;
use nknu\utility\xFile;

class library extends xBaseWithDbop
{
	protected $container;
	protected $db; protected $db_koha;
    protected $branch_schema_organization_structure;
	// constructor receives container instance
	public function __construct()
	{
		parent::__construct();
		global $container;
		$this->container = $container;
		$this->db = $container->db;
		$this->db_koha = $container->db_koha;
        $this->branch_schema_organization_structure = 'organization_structure';
	}

	#region patron
	public function get_borrower($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["cardnumber"]);
		#endregion

		$values = [
			"user_id" => null,
			"borrowernumber" => null,
			"cardnumber" => null,
			"name" => null,
			"categorycode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		//if (count($values) == 0) { $this->SetError("讀取讀者資料失敗. 沒有相關參數."); return; }

		$user_id = array_key_exists("user_id", $values) ? $values["user_id"] : null;
		unset($values["user_id"]);
		$borrowernumber = array_key_exists("borrowernumber", $values) ? $values["borrowernumber"] : null;
		unset($values["borrowernumber"]);

		if ($user_id != null || $borrowernumber != null) {
			$values4map = [
				"user_id" => $user_id ?? -1,
				"borrowernumber" => $borrowernumber ?? -1
			];
			$cSql = <<<EOD
			SELECT borrowernumber, user_id
			FROM library.borrower_map2user
			WHERE borrowernumber = :borrowernumber OR user_id = :user_id;
EOD;
			$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values4map);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
			if (count($aRows) == 0) { return ["data" => [], "total" => 0]; }
			$oRow = array_values($aRows)[0];
			$values["borrowernumber"] = $oRow["borrowernumber"];
			$user_id = $oRow["user_id"];
		}
		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND p.borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(p.cardnumber) = UPPER(:cardnumber)",
				"name" => " AND CONCAT_WS('', p.surname, p.firstname) LIKE CONCAT('%', :name, '%')",
				"categorycode" => " AND p.categorycode = :categorycode"
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$default_user_id = $user_id ?? "null";
		$cSql_Inner = <<<EOD
            SELECT
				{$default_user_id} AS user_id,
				p.borrowernumber,
				p.cardnumber,
				p.surname,
				p.firstname,
				p.middle_name,
				p.title,
				p.othernames,
				p.initials,
				p.pronouns,
				p.streetnumber,
				p.streettype,
				p.address,
				p.address2,
				p.city,
				p.state,
				p.zipcode,
				p.country,
				p.email,
				p.phone,
				p.mobile,
				p.fax,
				p.emailpro,
				p.phonepro,
				p.B_streetnumber,
				p.B_streettype,
				p.B_address,
				p.B_address2,
				p.B_city,
				p.B_state,
				p.B_zipcode,
				p.B_country,
				p.B_email,
				p.B_phone,
				p.dateofbirth,
				p.branchcode, b.branchname,
				p.categorycode,
				p.dateenrolled,
				p.dateexpiry,
				p.password_expiration_date,
				p.date_renewed,
				p.gonenoaddress,
				CAST(p.lost AS int) AS lost,
				p.debarredcomment, p.debarred AS debarredexpriation,
				p.contactname,
				p.contactfirstname,
				p.contacttitle,
				p.borrowernotes,
				p.relationship,
				p.sex,
				p.password,
				p.secret,
				p.auth_method,
				p.flags,
				p.userid,
				p.opacnote,
				p.contactnote,
				p.sort1, sort2,
				p.altcontactfirstname,
				p.altcontactsurname,
				p.altcontactaddress1,
				p.altcontactaddress2,
				p.altcontactaddress3,
				p.altcontactstate,
				p.altcontactzipcode,
				p.altcontactcountry,
				p.altcontactphone,
				p.smsalertnumber,
				p.sms_provider_id,
				p.privacy,
				CAST(p.privacy_guarantor_fines AS int) AS privacy_guarantor_fines,
				CAST(p.privacy_guarantor_checkouts AS int) AS privacy_guarantor_checkouts,
				p.checkprevcheckout,
				p.updated_on,
				p.lastseen,
				p.lang,
				p.login_attempts,
				p.overdrive_auth_token,
				CAST(p.anonymized AS int) AS anonymized,
				CAST(p.autorenew_checkouts AS int) AS autorenew_checkouts,
				p.primary_contact_method,
				CAST(p.protected AS int) AS protected,

				c.description AS categoryname,
				(SELECT COUNT(*) FROM issues WHERE borrowernumber = p.borrowernumber) AS issue_count,
				(SELECT COUNT(*) FROM reserves WHERE borrowernumber = p.borrowernumber) AS reserve_count,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"borrower_message_preference_id", mp.borrower_message_preference_id,
							"message_attribute_id", m.message_attribute_id,
							"message_name", m.message_name,
							"takes_days", m.takes_days,
							"canset_digest", IFNULL(mt1.is_digest, mt0.is_digest),
							"days_in_advance", mp.days_in_advance,
							"email", CASE WHEN mtp.borrower_message_preference_id IS NULL THEN 0 ELSE 1 END,
							"wants_digest", mp.wants_digest
						))
					FROM message_attributes AS m
						INNER JOIN message_transports AS mt0 ON mt0.message_attribute_id = m.message_attribute_id AND mt0.message_transport_type = 'email' AND mt0.is_digest = 0
						LEFT JOIN message_transports AS mt1 ON mt1.message_attribute_id = m.message_attribute_id AND mt1.message_transport_type = 'email' AND mt1.is_digest = 1
						LEFT JOIN borrower_message_preferences AS mp ON mp.message_attribute_id = m.message_attribute_id AND mp.borrowernumber = p.borrowernumber
						LEFT JOIN borrower_message_transport_preferences AS mtp ON mtp.borrower_message_preference_id = mp.borrower_message_preference_id AND mtp.message_transport_type = 'email'
					WHERE  m.message_attribute_id IN (1, 2, 4, 5, 6, 10)
				), '[]') AS message,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"debarred_id", borrower_debarment_id,
							"type", `type`,
							"comment", `comment`,
							"expiration", expiration,
							"created", created,
							"manager_id", manager_id,
							"wantDelete", 0
						))
					FROM borrower_debarments
					WHERE borrowernumber = p.borrowernumber AND `type` = 'MANUAL'
				), '[]') AS debarred
			FROM borrowers AS p
				INNER JOIN branches AS b ON b.branchcode = p.branchcode
				INNER JOIN categories AS c ON c.categorycode = p.categorycode
			WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dtInner
            {$order}
EOD;

		$sql = <<<EOD
		SELECT *
        FROM (
			{$cSql_AllRow}
			LIMIT :length
		) L
		WHERE `key` > :start
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_AllRow}
			) AS C
EOD;

		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		if (!$stmt->execute() || !$stmt_count->execute()) {
			return $this->GetDatabaseErrorMessage($stmt);
		}
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		$aBorrowerNumbers = [];
		foreach ($aRows as $row_id => $row_value) {
			$aBorrowerNumbers[] = $row_value["borrowernumber"];
			$aRows[$row_id]["autorenew_checkouts"] = (int)$row_value["autorenew_checkouts"];
			$aRows[$row_id]["protected"] = (int)$row_value["protected"];

			foreach ($row_value as $key => $value) {
				if (xString::IsJson($value)) {
					$aRows[$row_id][$key] = json_decode($value, true);
				}
			}
		}
		if ($user_id == null) {
			$aRows = $this->fillUserID($aRows, $aBorrowerNumbers);
		}

		$result['data'] = $aRows; $result['total'] = $result_count;
		$this->SetOK();
		return $result;
	}
	function fillUserID($aRows, $aBorrowerNumbers) {
		if (count($aBorrowerNumbers) == 0) { return $aRows; }
		$cList = implode(",", $aBorrowerNumbers);
		$cSql = <<<EOD
			SELECT borrowernumber, user_id
			FROM library.borrower_map2user
			WHERE borrowernumber IN ({$cList});
EOD;
		$stmt = $this->db->prepare($cSql);
		if (!$stmt->execute()) {
			return $aRows;
		}
		$aMap = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		foreach ($aMap as $drMap) {
			$borrowernumber = $drMap["borrowernumber"];
			$user_id = $drMap["user_id"];
			foreach ($aRows as $iRowIndex => $oRow) {
				if ($oRow["borrowernumber"] == $borrowernumber) {
					$aRows[$iRowIndex]["user_id"] = $user_id;
				}
			}
		}
		return $aRows;
	}

	public function post_borrower($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_borrower_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_borrower_single($data) {
		$data = $this->prepare_borrower_data($data); if ($this->bErrorOn) { return; };
		//$manager_id = $data["manager_id"];
		$values = $data["values"];
		$debarred = $data["debarred"];
		$message = $data["message"];
		$primary_contact_method = $data["primary_contact_method"];
		$password = $data["password"];

		$bIncludeDebarred = count($debarred) > 0;
		$bIncludePrimary_Contact_Method = !xString::IsEmpty($primary_contact_method);
		$bIncludeMessage = !xString::IsEmpty($password);
		$bIncludePassword = !xString::IsEmpty($password);

		//
		$callBack = $this->callKohaApi("post", "/patrons/", $values);
		if ($callBack->status == "success") {
			$borrowernumber = $callBack->data->patron_id;
			#region set password
			if ($bIncludePassword) {
				$oData4SetPassword = ["borrowernumber" => $borrowernumber, "password" => $password];
				$callBackBySetPassword = $this->post_borrower_password($oData4SetPassword);
				if ($callBackBySetPassword->status == "failed") {
					$callBack->status = $callBackBySetPassword->status;
					$callBack->message = $callBackBySetPassword->message;
				}
			}
			#endregion
			#region set primary contact method
			if ($bIncludePrimary_Contact_Method) {
				$oData4SetPrimaryContactMethod = ["borrowernumber" => $borrowernumber, "primary_contact_method" => $primary_contact_method];
				$callBackBySetPrimaryContactMethod = $this->post_borrower_primary_contact_method($oData4SetPrimaryContactMethod);
				if ($callBackBySetPrimaryContactMethod->status == "failed") {
					$callBack->status = $callBackBySetPrimaryContactMethod->status;
					$callBack->message = $callBackBySetPrimaryContactMethod->message;
				}
			}
			#endregion
			#region set debarred
			if ($bIncludeDebarred) {
				$debarred["borrowernumber"] = $borrowernumber;
				$callBackBySetDebarred = $this->post_borrower_debarred_single($debarred);
				if ($callBackBySetDebarred->status == "failed") {
					$callBack->status = $callBackBySetDebarred->status;
					$callBack->message = $callBackBySetDebarred->message;
				}
			}
			#endregion
			#region set message
			if ($bIncludeMessage) {
				//在呼叫新增讀者 API後，KOHA 自動新增了 message 資料(依預設的讀者類型)，所以要先取得舊的 message 資料，再呼叫 patch_message_type
				$result = $this->get_message_type(["borrowernumber" => $borrowernumber]);
				if ($this->bErrorOn) { $callBack->status == "failed"; $callBack->cMessage == $this->cMessage; return $callBack; }
				$message_old = $result["data"];
				$callBackBySetMessage = $this->patch_message_type($message_old, $message);
				if ($callBackBySetMessage->status == "failed") {
					$callBack->status = $callBackBySetMessage->status;
					$callBack->message = $callBackBySetMessage->message;
				}
			}
			#endregion
		}
		return $callBack;
	}
	public function patch_borrower($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_borrower_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_borrower_single($data) {
        $borrowernumber = $this->CheckArrayData("borrowernumber", $data, true, false, "i"); if ($this->bErrorOn) { return; }
		$data = $this->prepare_borrower_data($data); if ($this->bErrorOn) { return; };
		//$callBack = new \stdClass(); $callBack->status = "failed"; $callBack->message = json_encode($data); $callBack->data = null;
		//return $callBack;

		$values = $data["values"];
		$debarred = $data["debarred"];
		$message = $data["message"];
		$primary_contact_method = $data["primary_contact_method"];
		$password = $data["password"];

		$bIncludeDebarred = count($debarred) > 0;
		$bIncludePrimary_Contact_Method = !xString::IsEmpty($primary_contact_method);
		$bIncludeMessage = count($message) > 0;
		$bIncludePassword = !xString::IsEmpty($password);

		//$callBack = new \stdClass(); $callBack->status = "success"; $callBack->message = null; $callBack->data = null;
		$callBack = $this->callKohaApi("put", "/patrons/" . $borrowernumber, $values);
		if ($callBack->status == "success") {
			#region set password
			if ($bIncludePassword) {
				$oData4SetPassword = ["borrowernumber" => $borrowernumber, "password" => $password];
				$callBackBySetPassword = $this->post_borrower_password($oData4SetPassword);
				if ($callBackBySetPassword->status == "failed") {
					$callBack->status = $callBackBySetPassword->status;
					$callBack->message = $callBackBySetPassword->message;
				}
			}
			#endregion
			#region set primary contact method
			if ($bIncludePrimary_Contact_Method) {
				$oData4SetPrimaryContactMethod = ["borrowernumber" => $borrowernumber, "primary_contact_method" => $primary_contact_method];
				$callBackBySetPrimaryContactMethod = $this->post_borrower_primary_contact_method($oData4SetPrimaryContactMethod);
				if ($callBackBySetPrimaryContactMethod->status == "failed") {
					$callBack->status = $callBackBySetPrimaryContactMethod->status;
					$callBack->message = $callBackBySetPrimaryContactMethod->message;
				}
			}
			#endregion
			#region set debarred
			if ($bIncludeDebarred) {
				foreach ($debarred as $key => $value) {
					if ($value["wantDelete"] == 1) {
						$callBackBySetDebarred = $this->delete_borrower_debarred_single(["id" => $value["debarred_id"]]);
						if ($callBackBySetDebarred->status == "failed") {
							$callBack->status = $callBackBySetDebarred->status;
							$callBack->message = $callBackBySetDebarred->message;
						}
						unset($debarred[$key]);
					}
				}
				foreach ($debarred as $debarred_single) {
					if ($debarred_single["wantDelete"] == null) {
						$debarred_single["borrowernumber"] = $borrowernumber;
						$callBackBySetDebarred = $this->post_borrower_debarred_single($debarred_single);
						if ($callBackBySetDebarred->status == "failed") {
							$callBack->status = $callBackBySetDebarred->status;
							$callBack->message = $callBackBySetDebarred->message;
						}
					}
				}
			}
			#endregion
			#region set message
			if ($bIncludeMessage) {
				//在呼叫新增讀者 API後，KOHA 自動新增了 message 資料(依預設的讀者類型)，所以要先取得舊的 message 資料，再呼叫 patch_message_type
				$result = $this->get_message_type(["borrowernumber" => $borrowernumber]);
				if ($this->bErrorOn) { $callBack->status == "failed"; $callBack->cMessage == $this->cMessage; return $callBack; }
				$message_old = $result["data"];
				$callBackBySetMessage = $this->patch_message_type($message_old, $message);
				if ($callBackBySetMessage->status == "failed") {
					$callBack->status = $callBackBySetMessage->status;
					$callBack->message = $callBackBySetMessage->message;
				}
			}
			#endregion

		}
		return $callBack;
	}
	public function delete_borrower($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_borrower_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_borrower_single($data) {
		$values = [
			"borrowernumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		$cKey1 = "borrowernumber";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 borrowernumber";
			return $callBack;
		}
		$borrowernumber = $values[$cKey1];
		unset($values[$cKey1]);

		$callBack = $this->callKohaApi("delete", "/patrons/" . $borrowernumber, $values);
		return $callBack;
	}
	public function post_borrower_password($data) {
		$values = [
			"borrowernumber" => null,
			"password" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		$cKey1 = "borrowernumber";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 borrowernumber";
			return $callBack;
		}
		$borrowernumber = $values[$cKey1];
		unset($values[$cKey1]);

		$cKey1 = "password";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 password";
			return $callBack;
		}
		$values["password_2"] = $values["password"];

		$callBack = $this->callKohaApi("post", "/patrons/" . $borrowernumber . "/password", $values);
		return $callBack;
	}
	private function post_borrower_primary_contact_method($data) {
		$values = [
			"borrowernumber" => null,
			"primary_contact_method" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		$primary_contact_method = $this->CheckArrayData("primary_contact_method", $values, false, false, "c"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			UPDATE borrowers SET
				primary_contact_method = :primary_contact_method
			WHERE borrowernumber = :borrowernumber;
EOD;

		$callBack = new stdClass();
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$callBack->status = "failed"; $callBack->message = $cMessage;
			return;
		}
		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private function prepare_borrower_data($data) {
		$values = [
			"manager_id" => null,
			"user_id" => null,
			"userid" => null,
			"password" => null,
			"cardnumber" => null,
			"surname" => null,
			"firstname" => null,
			"middle_name" => null,
			"title" => null,
			"othernames" => null,
			"initials" => null,
			"pronouns" => null,
			"streetnumber" => null,
			//"streettype" => null,
			"address" => null,
			"address2" => null,
			"city" => null,
			"state" => null,
			"zipcode" => null,
			"emailpro" => null,
			"phonepro" => null,
			"country" => null,
			"email" => null,
			"phone" => null,		//
			"mobile" => null,
			"fax" => null,
			"mobilepro" => null,
			"phonerpo" => null,
			"B_streetnumber" => null,
			//"B_streettype" => null,
			"B_address" => null,
			"B_address2" => null,
			"B_city" => null,
			"B_state" => null,
			"B_zipcode" => null,
			"B_country" => null,
			"B_email" => null,
			"B_phone" => null,
			"dateofbirth" => null,
			"branchcode" => null,
			"categorycode" => null,
			"dateenrolled" => null,
			"dateexpiry" => null,
			"date_renewed" => null,
			"borrowernotes" => null,
			"sex" => null,
			"opacnote" => null,
			"contactnote" => null,
			"sort1" => null,
			"sort2" => null,
			"autorenew_checkouts" => null,
			"altcontactfirstname" => null,
			"altcontactsurname" => null,
			"altcontactaddress1" => null,
			"altcontactaddress2" => null,
			"altcontactaddress3" => null,
			"altcontactstate" => null,
			"altcontactzipcode" => null,
			"altcontactcountry" => null,
			"altcontactphone" => null,

			"debarred" => null,
			"debarredcomment" => null,
			"debarredexpriation" => null,

			"primary_contact_method" => null,
			"protected" => null,
			"message" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$manager_id = $this->CheckArrayData("manager_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["manager_id"]);

		$aChange = [
			"othernames" => "other_name",
			"streetnumber" => "street_number",
			//"streettype" => "street_type",
			"zipcode" => "postal_code",
			"emailpro" => "secondary_email",
			"phonepro" => "secondary_phone",
			"B_streetnumber" => "altaddress_street_number",
			//"B_streettype" => "altaddress_street_type",
			"B_address" => "altaddress_address",
			"B_address2" => "altaddress_address2",
			"B_city" => "altaddress_city",
			"B_state" => "altaddress_state",
			"B_zipcode" => "altaddress_postal_code",
			"B_country" => "altaddress_country",
			"B_email" => "altaddress_email",
			"B_phone" => "altaddress_phone",
			"dateofbirth" => "date_of_birth",
			"branchcode" => "library_id",
			"categorycode" => "category_id",
			"dateenrolled" => "date_enrolled",
			"dateexpiry" => "expiry_date",
			"borrowernotes" => "staff_notes",
			"sex" => "gender",
			"opacnote" => "opac_notes",
			"contactnote" => "altaddress_notes",
			"sort1" => "statistics_1",
			"sort2" => "statistics_2",
			"altcontactfirstname" => "altcontact_firstname",
			"altcontactsurname" => "altcontact_surname",
			"altcontactaddress1" => "altcontact_address",
			"altcontactaddress2" => "altcontact_address2",
			"altcontactaddress3" => "altcontact_city",
			"altcontactstate" => "altcontact_state",
			"altcontactzipcode" => "altcontact_postal_code",
			"altcontactcountry" => "altcontact_country",
			"altcontactphone" => "altcontact_phone",
		];
		foreach ($aChange as $cKey1 => $cKey2) {
			$values = xStatic::KeyExistThenReplaceValue($values, $cKey2, $bNoPatchThenRemove = false, $values, $cKey1, $bReplaceThenRemove = true);
		}
		if (array_key_exists("date_of_birth", $values)) {
			$date_of_birth = $this->CheckArrayData("date_of_birth", $values, true, false, "d"); if ($this->bErrorOn) { return; }
			$values["date_of_birth"] = $date_of_birth->format("Y-m-d");
		}
		if (array_key_exists("date_enrolled", $values)) {
			$date_enrolled = $this->CheckArrayData("date_enrolled", $values, true, false, "d"); if ($this->bErrorOn) { return; }
			$values["date_enrolled"] = $date_enrolled->format("Y-m-d");
		}
		if (array_key_exists("expiry_date", $values)) {
			$expiry_date = $this->CheckArrayData("expiry_date", $values, true, false, "d"); if ($this->bErrorOn) { return; }
			$values["expiry_date"] = $expiry_date->format("Y-m-d");
		}
		if (array_key_exists("gender", $values)) {
			$gender = $this->CheckArrayData("gender", $values, true, true, "c"); if ($this->bErrorOn) { return; }
			$values["gender"] = $gender == "N" ? "" : $gender;
		}

		$debarred = [];
		if (array_key_exists("debarred", $values)) {
			$debarred = $values["debarred"];
			unset($values["debarred"]);
		}
		if (array_key_exists("debarredcomment", $values)) {
			$comment = $this->CheckArrayData("debarredcomment", $values, true, false, "c"); if ($this->bErrorOn) { return; }
			$expiration = $this->CheckArrayData("debarredexpriation", $values, true, false, "d"); if ($this->bErrorOn) { return; }
			$debarred[] = [
				"comment" => $comment,
				"expiration" => $expiration->format("Y-m-d"),
				"manager_id" => $manager_id,
				"wantDelete" => null
			];
			unset($values["debarredcomment"]);
			unset($values["debarredexpriation"]);
		}

		$bIncludeMessage = array_key_exists("message", $values);
		if ($bIncludeMessage) {
			$message = $values["message"];
			unset($values["message"]);
		}

		$primary_contact_method = "";
		$bIncludePrimary_Contact_Method = array_key_exists("primary_contact_method", $values);
		if ($bIncludePrimary_Contact_Method) {
			$primary_contact_method = $values["primary_contact_method"];
			unset($values["primary_contact_method"]);
		}

		$password = "";
		$cKey1 = "password";
		if (array_key_exists($cKey1, $values)) {
			$password = $values[$cKey1];
			unset($values[$cKey1]);
		}

		return [
			"manager_id" => $manager_id,
			"values" => $values, "debarred" => $debarred, "message" => $message,
			"primary_contact_method" => $primary_contact_method, "password" => $password
		];
	}
	public function get_borrower_validate($data) {
		$values = [
			"user_id" => null,
			"cardnumber" => null,
			"password" => null,
			"borrowerDataWhenValidated" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$user_id = array_key_exists("user_id", $values) ? $values["user_id"] : null;
		unset($values["user_id"]);

		$cardnumber = array_key_exists("cardnumber", $values) ? $values["cardnumber"] : null;
		if ($user_id == null && $cardnumber == null) { $this->SetError("user_id 與 cardnumber至少擇一傳入."); return; }

		$password = array_key_exists("password", $values) ? $values["password"] : null;
		if ($password == null) { $this->SetError("password 未傳入."); return; }
		unset($values["password"]);

		$borrowerDataWhenValidated = false;
		if (array_key_exists("borrowerDataWhenValidated", $values)) {
			$borrowerDataWhenValidated = $values["borrowerDataWhenValidated"] == "true";
			unset($values["borrowerDataWhenValidated"]);
		}

		if ($user_id != null) {
			$values4map = [
				"user_id" => $user_id
			];
			$cSql = <<<EOD
				SELECT borrowernumber, user_id
				FROM library.borrower_map2user
				WHERE user_id = :user_id;
EOD;
			$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values4map);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
			if (count($aRows) == 0) { $this->SetError("帳號或密碼錯誤."); return; }
			$oRow = array_values($aRows)[0];
			$values["borrowernumber"] = $oRow["borrowernumber"];
		}

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(cardnumber) = UPPER(:cardnumber)"
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion
		#region koha data
		$default_user_id = $user_id ?? "null";
		$sql = <<<EOD
            SELECT
				{$default_user_id} AS user_id, borrowernumber, `password`
			FROM borrowers
			WHERE TRUE {$cWhere_Inner}
EOD;
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			return $this->GetDatabaseErrorMessage($stmt);
		}
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		if (count($aRows) == 0) { $this->SetError("帳號或密碼錯誤.{$sql}$"); return; }
		$oRow = array_values($aRows)[0];
		#endregion

		$password_hash = $oRow["password"];
		if (!password_verify($password, $password_hash)) { $this->SetError("帳號或密碼錯誤."); return; }
		if ($borrowerDataWhenValidated) {
			$values = ["borrowernumber" => $oRow["borrowernumber"]];
		}
		if (!$borrowerDataWhenValidated) {
			return "OK";
		}

		return $this->get_borrower($values);
	}
	#endregion
	#region borrower_category
	public function get_borrower_category($data) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($data, ["categorycode"]);
		#endregion

		$values = [
			"categorycode" => null,
			"description" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		//if (count($values) == 0) { $this->SetError("讀取讀者類型失敗. 沒有相關參數."); return; }

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"categorycode" => " AND c.categorycode = :categorycode",
				"description" => " AND c.description LIKE CONCAT('%', :description, '%')"
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
            SELECT
				c.categorycode,							/* unique primary key used to idenfity the patron category */
				c.description,							/* description of the patron category */
				c.enrolmentperiod,						/* number of months the patron is enrolled for (will be NULL if enrolmentperioddate is set) */
				c.enrolmentperioddate,					/* date the patron is enrolled until (will be NULL if enrolmentperiod is set) */
				c.password_expiry_days,					/* number of days after which the patron must reset their password */
				c.upperagelimit,						/* age limit for the patron */
				c.dateofbirthrequired,					/* (Age required)years -- the minimum age required for the patron category */
				c.enrolmentfee,							/* enrollment fee for the patron */
				c.overduenoticerequired,				/* are overdue notices sent to this patron category (1 for yes, 0 for no) */

				c.hidelostitems,						/* are lost items shown to this category (1 for Shown, 0 for Hidden by default) */
				c.reservefee,					        /* cost to place holds */

				c.category_type,						/* type of Koha patron (A:Adult, C:Child, P:Professional, I:Organizational, X:Statistical, S:Staff) */
				c.can_be_guarantee,			            /* if patrons of this category can be guarantees */

				/* c.checkprevcheckout,		            "inherit" "produce a warning for this patron category if this item has previously been checked out to this patron if 'yes', not if 'no', defer to syspref setting if 'inherit'." */
				/* c.can_place_ill_in_opac,	            1 can this patron category place interlibrary loan requests */

				c.reset_password,				        /* if patrons of this category can do the password reset flow, */
				c.change_password,				        /* if patrons of this category can change their passwords in the OAPC */

				c.min_password_length,			        /* set minimum password length for patrons in this category */
				c.require_strong_password,				/* set required password strength for patrons in this category */
				c.BlockExpiredPatronOpacActions,		/* wheither or not a patron of this category can renew books or place holds once their card has expired. 0:No Block, 1:Block, -1: Follow system preference BlockExpiredPatronOpacActions  */
				c.default_privacy,		                /* "enum('default','never','forever')"		Default privacy setting for this patron category */
				c.exclude_from_local_holds_priority,	/* Exclude patrons of this category from local holds priority 1:Yes, 0:No */
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"branchcode", cb.branchcode,
							"branchname", b.branchname
						))
					FROM categories_branches AS cb
						LEFT JOIN branches AS b ON b.branchcode = cb.branchcode
					WHERE cb.categorycode = c.categorycode
				), '[]') AS branches,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"borrower_message_preference_id", mp.borrower_message_preference_id,
							"message_attribute_id", m.message_attribute_id,
							"message_name", m.message_name,
							"takes_days", m.takes_days,
							"canset_digest", IFNULL(mt1.is_digest, mt0.is_digest),
							"days_in_advance", mp.days_in_advance,
							"email", CASE WHEN mtp.borrower_message_preference_id IS NULL THEN 0 ELSE 1 END,
							"wants_digest", mp.wants_digest
						))
					FROM message_attributes AS m
						INNER JOIN message_transports AS mt0 ON mt0.message_attribute_id = m.message_attribute_id AND mt0.message_transport_type = 'email' AND mt0.is_digest = 0
						LEFT JOIN message_transports AS mt1 ON mt1.message_attribute_id = m.message_attribute_id AND mt1.message_transport_type = 'email' AND mt1.is_digest = 1
						LEFT JOIN borrower_message_preferences AS mp ON mp.message_attribute_id = m.message_attribute_id AND mp.categorycode = c.categorycode
						LEFT JOIN borrower_message_transport_preferences AS mtp ON mtp.borrower_message_preference_id = mp.borrower_message_preference_id AND mtp.message_transport_type = 'email'
					WHERE m.message_attribute_id IN (1, 2, 4, 5, 6, 10)
				), '[]') AS message
			FROM categories AS c
			WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dtInner
            {$order}
EOD;

		$sql = <<<EOD
		SELECT *
        FROM (
			{$cSql_AllRow}
			LIMIT :length
		) L
		WHERE `key` > :start
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_AllRow}
			) AS C
EOD;

		$stmt = $this->db_koha->prepare($sql);
		xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count);
		xStatic::BindValue($stmt_count, $values_count);
		if ($stmt->execute() && $stmt_count->execute()) {
			$result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$result_count = $stmt_count->fetchColumn(0);
			foreach ($result['data'] as $row_id => $row_value) {
				foreach ($row_value as $key => $value) {
					if (xString::IsJson($value)) {
						$result['data'][$row_id][$key] = json_decode($value, true);
					}
				}
			}
			$result['total'] = $result_count;
			$this->SetOK();
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			var_dump($oInfo);
			$cMessage = $oInfo[2];
			if ($cMessage == null) {
				$cMessage = "error";
			}
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	public function post_borrower_category($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_borrower_category_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_borrower_category_single($data) {
		$values = [
			"categorycode" => null,
			"description" => null,
			"enrolmentperiod" => null,
			"enrolmentperioddate" => null,
			"password_expiry_days" => null,
			"upperagelimit" => null,
			"dateofbirthrequired" => null,
			"enrolmentfee" => null,
			"overduenoticerequired" => null,
			"hidelostitems" => null,
			"reservefee" => null,
			"category_type" => null,
			"can_be_guarantee" => null,
			"reset_password" => null,
			"change_password" => null,
			"min_password_length" => null,
			"require_strong_password" => null,
			"BlockExpiredPatronOpacActions" => null,
			"default_privacy" => null,
			"exclude_from_local_holds_priority" => null,
			"branches" => null,
			"message" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		//$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		//$callBack->message = json_encode($values);
		//return $callBack;

		$callBack = $this->checkValues_borrower_category($values); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;

		$categorycode = $values["categorycode"];
		$message = $values["message"];
		unset($values["message"]);

		#region 新增主資料
		$cSql = <<<EOD
\n			INSERT INTO categories (
\n				categorycode, description, enrolmentperiod, enrolmentperioddate, password_expiry_days, dateofbirthrequired, upperagelimit, enrolmentfee, overduenoticerequired, hidelostitems, reservefee, category_type, can_be_guarantee, reset_password, change_password, min_password_length, require_strong_password, BlockExpiredPatronOpacActions, default_privacy, exclude_from_local_holds_priority, checkprevcheckout, can_place_ill_in_opac
\n			) VALUES (
\n				:categorycode, :description, :enrolmentperiod, :enrolmentperioddate, :password_expiry_days, :dateofbirthrequired, :upperagelimit, :enrolmentfee, :overduenoticerequired, :hidelostitems, :reservefee, :category_type, :can_be_guarantee, :reset_password, :change_password, :min_password_length, :require_strong_password, :BlockExpiredPatronOpacActions, :default_privacy, :exclude_from_local_holds_priority, :checkprevcheckout, :can_place_ill_in_opac
\n			);
EOD;
		#endregion

		#region 有無限定分館
		$cKey2 = "branches";
		if (array_key_exists($cKey2, $values)) {
			$branches = $values[$cKey2]; if (!is_array($branches)) { $callBack->status == "failed"; $callBack->message == "{$cKey2}必須為陣列."; return $callBack; }
			foreach ($branches as $key => $branchCode) {
				$values["categorycode_cb_{$key}"] = $categorycode;
				$values["branchcode_cb_{$key}"] = $branchCode;
				$cSql .= <<<EOD
		\n			INSERT INTO categories_branches (categorycode, branchcode) VALUES (:categorycode_cb_{$key}, :branchcode_cb_{$key});
EOD;
			}
			unset($values[$cKey2]);
		}
		#endregion
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo(); $cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$callBack->status == "failed"; $callBack->cMessage = $cMessage;
			return $callBack;
		}
		//my sql 執行後，要清空 buffer，關閉游標
		$temp = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		$callBack = $this->post_message_type(null, $categorycode, $message); if ($callBack->status == "failed") { return $callBack;}

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function patch_borrower_category($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_borrower_category_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_borrower_category_single($data) {
		$values = [
			"categorycode" => null,
			"description" => null,
			"enrolmentperiod" => null,
			"enrolmentperioddate" => null,
			"password_expiry_days" => null,
			"upperagelimit" => null,
			"dateofbirthrequired" => null,
			"enrolmentfee" => null,
			"overduenoticerequired" => null,
			"hidelostitems" => null,
			"reservefee" => null,
			"category_type" => null,
			"can_be_guarantee" => null,
			"reset_password" => null,
			"change_password" => null,
			"min_password_length" => null,
			"require_strong_password" => null,
			"BlockExpiredPatronOpacActions" => null,
			"default_privacy" => null,
			"exclude_from_local_holds_priority" => null,
			"branches" => null,
			"message" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = $this->checkValues_borrower_category($values); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;

		$categorycode = $values["categorycode"];
		$result = $this->get_message_type(["categorycode" => $categorycode]);
		if ($this->bErrorOn) { $callBack->status == "failed"; $callBack->cMessage == $this->cMessage; return $callBack; }
		$message_old = $result["data"];
		$message_new = $values["message"]; unset($values["message"]);

		#region 新增主資料
		$cSql = <<<EOD
			UPDATE categories SET
				description = :description,
				enrolmentperiod = :enrolmentperiod,
				enrolmentperioddate = :enrolmentperioddate,
				password_expiry_days = :password_expiry_days,
				dateofbirthrequired = :dateofbirthrequired,
				upperagelimit = :upperagelimit,
				enrolmentfee = :enrolmentfee,
				overduenoticerequired = :overduenoticerequired,
				hidelostitems = :hidelostitems,
				reservefee = :reservefee,
				category_type = :category_type,
				can_be_guarantee = :can_be_guarantee,
				reset_password = :reset_password,
				change_password = :change_password,
				min_password_length = :min_password_length,
				require_strong_password = :require_strong_password,
				BlockExpiredPatronOpacActions = :BlockExpiredPatronOpacActions,
				default_privacy = :default_privacy,
				exclude_from_local_holds_priority = :exclude_from_local_holds_priority,
				checkprevcheckout = :checkprevcheckout,
				can_place_ill_in_opac = :can_place_ill_in_opac
			WHERE categorycode = :categorycode;
EOD;
		#endregion

		#region 有無限定分館
		$cKey2 = "branches";
		if (array_key_exists($cKey2, $values)) {
			$values["categorycode_cb"] = $categorycode;
			$cSql .= <<<EOD
	\n			DELETE FROM categories_branches WHERE categorycode = :categorycode_cb;
EOD;
			$branches = $values[$cKey2]; if (!is_array($branches)) { $callBack->status == "failed"; $callBack->message == "{$cKey2}必須為陣列."; return $callBack; }
			foreach ($branches as $key => $branchCode) {
				$values["categorycode_cb_{$key}"] = $categorycode;
				$values["branchcode_cb_{$key}"] = $branchCode;
				$cSql .= <<<EOD
		\n			INSERT INTO categories_branches (categorycode, branchcode) VALUES (:categorycode_cb_{$key}, :branchcode_cb_{$key});
EOD;
			}
			unset($values[$cKey2]);
		}
		#endregion

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo(); $cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$callBack->cMessage = $cMessage;
			return $callBack;
		}
		//my sql 執行後，要清空 buffer，關閉游標
		$temp = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		$callBack = $this->patch_message_type($message_old, $message_new); if ($callBack->status == "failed") { return $callBack; }

		$callBack->status = "success";
		$callBack->data = true;
		return $callBack;
	}
	public function delete_borrower_category($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_borrower_category_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_borrower_category_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;

		$values = [
			"categorycode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$cKey2 = "categorycode";
		if (!array_key_exists($cKey2, $values)) { $callBack->message = "必須包含 {$cKey2}"; return $callBack; }
		$categorycode = $values[$cKey2];

		$result = $this->get_borrower(["categorycode" => $categorycode]); if ($this->bErrorOn) { $callBack->cMessage = $this->cMessage; return $callBack; }
		if ($result["total"] > 0) { $callBack->cMessage = "此類型尚有讀者在裡面，不允許刪除."; return $callBack; }

		$values = [];
		$values["categorycode_mtp"] = $categorycode;
		$values["categorycode_mp"] = $categorycode;
		$values["categorycode_cb"] = $categorycode;
		$values["categorycode"] = $categorycode;

		#region 新增主資料
		$cSql = <<<EOD
			DELETE mtp
			FROM borrower_message_transport_preferences AS mtp
				INNER JOIN borrower_message_preferences AS mp ON mp.borrower_message_preference_id = mtp.borrower_message_preference_id
			WHERE mp.categorycode = :categorycode_mtp;

			DELETE FROM borrower_message_preferences WHERE categorycode = :categorycode_mp;

			DELETE FROM categories_branches WHERE categorycode = :categorycode_cb;

			DELETE FROM categories WHERE categorycode = :categorycode;
EOD;
		#endregion

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			var_dump($oInfo);
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$callBack->cMessage = $cMessage;
			$this->SetError($cMessage);
			return $callBack;
		}

		$callBack->status = "success";
		$callBack->data = true;
		return $callBack;
	}
	private function checkValues_borrower_category($values) {
		$values["checkprevcheckout"] = "inherit";
		$values["can_place_ill_in_opac"] = 1;

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		$cKey2 = "categorycode";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}

		$cKey2 = "description";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$cKey2 = "category_type";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$category_type = $values[$cKey2];
		if (!in_array($category_type, ["A", "C", "P", "I", "X", "S"])) {
			$callBack->message = "category_type 必須為 A, C, P, I, X, S 其中之一";
			return $callBack;
		}

		#region enrolmentperiod 與 enrolmentperioddate 必須二擇一
		$enrolmentperiod = null;
		$enrolmentperioddate = null;
		$cKey2 = "enrolmentperiod";
		if (array_key_exists($cKey2, $values)) {
			$enrolmentperiod = $values[$cKey2];
			if (!xInt::IsNullOrTo($enrolmentperiod)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}
		$cKey2 = "enrolmentperioddate";
		if (array_key_exists($cKey2, $values)) {
			$enrolmentperioddate = $values[$cKey2];
			if (!nknu\extend\xDateTime::IsNullOrToDate($enrolmentperioddate)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}
		if (!($enrolmentperiod == null xor $enrolmentperioddate == null)) {
			$callBack->message = "enrolmentperiod 與 enrolmentperioddate 必須二擇一";
			return $callBack;
		}
		#endregion

		$cKey2 = "password_expiry_days";
		if (array_key_exists($cKey2, $values)) {
			if (!xInt::IsNullOrTo($values[$cKey2])) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "dateofbirthrequired";
		if (array_key_exists($cKey2, $values)) {
			if (!xInt::IsNullOrTo($values[$cKey2])) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "upperagelimit";
		if (array_key_exists($cKey2, $values)) {
			if (!xInt::IsNullOrTo($values[$cKey2])) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "enrolmentfee";
		if (array_key_exists($cKey2, $values)) {
			if (!xFloat::IsNullOrTo($values[$cKey2])) {
				$callBack->message = "{$cKey2} 必須為浮點數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = 0;
		}

		$cKey2 = "overduenoticerequired";
		if (array_key_exists($cKey2, $values)) {
			$overduenoticerequired = $values[$cKey2];
			if (!xInt::IsNullOrTo($overduenoticerequired)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($overduenoticerequired, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = 0;
		}

		$cKey2 = "hidelostitems";
		if (array_key_exists($cKey2, $values)) {
			$hidelostitems = $values[$cKey2];
			if (!xInt::IsNullOrTo($hidelostitems)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($hidelostitems, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = 0;
		}

		$cKey2 = "reservefee";
		if (array_key_exists($cKey2, $values)) {
			if (!xFloat::IsNullOrTo($values[$cKey2])) {
				$callBack->message = "{$cKey2} 必須為浮點數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = 0;
		}

		$cKey2 = "can_be_guarantee";
		if (array_key_exists($cKey2, $values)) {
			$can_be_guarantee = $values[$cKey2];
			if (!xInt::IsNullOrTo($can_be_guarantee)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($can_be_guarantee, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = 0;
		}

		$cKey2 = "reset_password";
		if (array_key_exists($cKey2, $values)) {
			$reset_password = $values[$cKey2];
			if (!xInt::IsNullOrTo($reset_password)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($reset_password, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "change_password";
		if (array_key_exists($cKey2, $values)) {
			$change_password = $values[$cKey2];
			if (!xInt::IsNullOrTo($change_password)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($change_password, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "min_password_length";
		if (array_key_exists($cKey2, $values)) {
			if (!xInt::IsNullOrTo($values[$cKey2])) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "require_strong_password";
		if (array_key_exists($cKey2, $values)) {
			$require_strong_password = $values[$cKey2];
			if (!xInt::IsNullOrTo($require_strong_password)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($require_strong_password, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "BlockExpiredPatronOpacActions";
		if (array_key_exists($cKey2, $values)) {
			$BlockExpiredPatronOpacActions = $values[$cKey2];
			if (!xInt::IsNullOrTo($BlockExpiredPatronOpacActions)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($BlockExpiredPatronOpacActions, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "default_privacy";
		if (array_key_exists($cKey2, $values)) {
			if (!in_array($values[$cKey2], ["default", "never", "forever"])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = "default";
		}

		$cKey2 = "exclude_from_local_holds_priority";
		if (array_key_exists($cKey2, $values)) {
			$exclude_from_local_holds_priority = $values[$cKey2];
			if (!xInt::IsNullOrTo($exclude_from_local_holds_priority)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}
			if (!in_array($exclude_from_local_holds_priority, [0, 1])) {
				$callBack->message = "{$cKey2} 必須為 0 或 1.";
				return $callBack;
			}
		} else {
			$values[$cKey2] = null;
		}

		$cKey2 = "message";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$message = $values[$cKey2];
		foreach ($message as $value) {
			$cKey2 = "message_attribute_id";
			if (!array_key_exists($cKey2, $value)) {
				$callBack->message = "必須包含 {$cKey2}";
				return $callBack;
			}
			$message_attribute_id = $value[$cKey2];
			if (!xInt::IsNullOrTo($message_attribute_id)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}

			$cKey2 = "days_in_advance";
			if (!array_key_exists($cKey2, $value)) {
				$callBack->message = "必須包含 {$cKey2}";
				return $callBack;
			}
			$days_in_advance = $value[$cKey2];
			if (!xInt::IsNullOrTo($days_in_advance)) {
				$callBack->message = "{$cKey2} 必須為數值.";
				return $callBack;
			}

			$cKey2 = "email";
			if (!array_key_exists($cKey2, $value)) {
				$callBack->message = "必須包含 {$cKey2}";
				return $callBack;
			}
			$email = $value[$cKey2];
			if ($email != null) {
				if (!xInt::IsNullOrTo($email)) {
					$callBack->message = "{$cKey2} 必須為數值.";
					return $callBack;
				}
				if (!in_array($email, [0, 1])) {
					$callBack->message = "{$cKey2} 必須為 0 或 1.";
					return $callBack;
				}
			}
			$cKey2 = "wants_digest";
			if (!array_key_exists($cKey2, $value)) {
				$callBack->message = "必須包含 {$cKey2}";
				return $callBack;
			}
			$wants_digest = $value[$cKey2];
			if ($wants_digest != null) {
				if (!xInt::IsNullOrTo($email)) {
					$callBack->message = "{$cKey2} 必須為數值.";
					return $callBack;
				}
				if (!in_array($wants_digest, [0, 1])) {
					$callBack->message = "{$cKey2} 必須為 0 或 1.";
					return $callBack;
				}
			}
		}
		$message[] = ["message_attribute_id" => 7, "days_in_advance" => null, "email" => 0, "wants_digest" => 0];
		$message[] = ["message_attribute_id" => 8, "days_in_advance" => null, "email" => 0, "wants_digest" => 0];
		$message[] = ["message_attribute_id" => 9, "days_in_advance" => null, "email" => 0, "wants_digest" => 0];
		$message[] = ["message_attribute_id" => 11, "days_in_advance" => null, "email" => 0, "wants_digest" => 0];

		$callBack->status = "success";
		$callBack->data = $values;
		return $callBack;
	}
	#endregion
	#region category
	public function get_category_type($params) {
		#region page control
		//$values = $this->initialize_search();
		//$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		//$length = $values['cur_page'] * $values['size'];
		//$start = $length - $values['size'];
		#endregion
		//
		$aCategoryType = [
			["value" => "A", "text" => "Adult", "key" => 1],
			["value" => "C", "text" => "Child", "key" => 2],
			["value" => "P", "text" => "Professional", "key" => 3],
			["value" => "I", "text" => "Organizational", "key" => 4],
			["value" => "X", "text" => "Statistical", "key" => 5],
			["value" => "S", "text" => "Staff", "key" => 6]
		];
		$result = ["data" => $aCategoryType, "total" => count($aCategoryType)];
		return $result;
	}
	#endregion


	
	public function initialize_search()
    {
        $default_value = [
            "cur_page" => 1,
            "size" => 100000,
        ];
        return $default_value;
    }
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
                if ($value == null) {
                    unset($custom_filter_arr[$key]);
                }
            }
            $default_arr = array_merge($default_arr, $custom_filter_arr);
            $select_condition = rtrim($select_condition, 'OR');
            $select_condition .= ")";
        }
        return ["select_condition" => $select_condition, "bind_values" => $default_arr];
    }
	public function isJson($string)
    {
        return json_decode($string) !== false && json_last_error() === JSON_ERROR_NONE;
    }
	private function isNullOrEmptyArray($data) {
		if ($data == null) { $this->SetError("無傳入任何參數."); return true; }
		if (!is_array($data)) { $this->SetError("無傳入參數非陣列."); return true; }
		if (count($data) == 0) { $this->SetError("無傳入任何參數."); return true; }
		return false;
	}


	
	// 部門單位/讀者群組串接
	public function get_department($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "department_id" => null,
            "classify_structure_id" => null,
            "department_depth_id" => null,
        ];
        $custom_filter_bind_values = [
            "department_depth_data" => null,
            "department_language_data" => null,
            "count_role" => null,
            "count_user" => null,
            "create_user_name" => null,
            "create_time" => null,
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
            "department_id" => " AND department_id = :department_id",
            "classify_structure_id" => " AND classify_structure_id = :classify_structure_id",
            "department_depth_id" => " AND department_depth_id = :department_depth_id",
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

        $sql_default = "SELECT *, ROW_NUMBER() OVER (ORDER BY department_id) \"key\"
                FROM(
                    SELECT {$this->branch_schema_organization_structure}.\"department\".department_id, {$this->branch_schema_organization_structure}.\"department\".department_index,
                    {$this->branch_schema_organization_structure}.department_classify_structure_type.classify_structure_type_id, classify_structure.classify_structure_type.classify_structure_id, 
                    all_department_data.department_depth_id,
                    classify_structure.classify_structure_type.classify_structure_type_parent_id, 
                    {$this->branch_schema_organization_structure}.\"department\".create_user_id, {$this->branch_schema_organization_structure}.\"department\".create_time,
                    {$this->branch_schema_organization_structure}.staff.staff_name create_user_name,
                    COALESCE({$this->branch_schema_organization_structure}.department_depth.property_json::jsonb || {$this->branch_schema_organization_structure}.department.property_json::jsonb,'{}') property_json,
                    COALESCE(department_language_data.department_language_data, '[]')department_language_data,
                    COALESCE(department_depth_data.department_depth_data, '[]')department_depth_data,
                    \"user_role\".count_user, \"user_role\".count_role
                    {$customize_select}
                    FROM {$this->branch_schema_organization_structure}.\"department\"
                    LEFT JOIN {$this->branch_schema_organization_structure}.staff ON {$this->branch_schema_organization_structure}.\"department\".create_user_id = {$this->branch_schema_organization_structure}.staff.user_id
                    LEFT JOIN {$this->branch_schema_organization_structure}.department_classify_structure_type ON {$this->branch_schema_organization_structure}.\"department\".department_id = {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id
                    LEFT JOIN classify_structure.classify_structure_type ON {$this->branch_schema_organization_structure}.department_classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type.classify_structure_type_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema_organization_structure}.all_department_zero.department_id root_id,
                            {$this->branch_schema_organization_structure}.all_department_zero.parent_id department_id, 
                            {$this->branch_schema_organization_structure}.all_department_zero.depth,
                            {$this->branch_schema_organization_structure}.department_depth.\"department_depth_id\",
                            {$this->branch_schema_organization_structure}.department_depth.\"name\" depth_name
                        FROM {$this->branch_schema_organization_structure}.all_department_zero
                        LEFT JOIN {$this->branch_schema_organization_structure}.department_depth ON {$this->branch_schema_organization_structure}.department_depth.department_id = {$this->branch_schema_organization_structure}.all_department_zero.department_id AND {$this->branch_schema_organization_structure}.all_department_zero.depth = {$this->branch_schema_organization_structure}.department_depth.depth_id
                    ) all_department_data ON {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id = all_department_data.department_id
                    LEFT JOIN {$this->branch_schema_organization_structure}.department_depth ON {$this->branch_schema_organization_structure}.department_depth.department_id = all_department_data.root_id AND all_department_data.depth = {$this->branch_schema_organization_structure}.department_depth.depth_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema_organization_structure}.\"department\".department_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'language_manage_id', department_language_json.language_manage_id,
                                'language_culture_code', department_language_json.language_culture_code,
                                'language_culture_value', department_language_json.language_value
                            )
                        )department_language_data
                        FROM {$this->branch_schema_organization_structure}.\"department\"
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema_organization_structure}' AND language.language_manage.table_name = 'department'
                        )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema_organization_structure}.\"department\".department_id
                        GROUP BY {$this->branch_schema_organization_structure}.\"department\".department_id
                    )department_language_data ON {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id = department_language_data.department_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema_organization_structure}.department_depth.department_id,
                            JSON_AGG(
                                JSON_BUILD_OBJECT(
                                    'department_depth_id',{$this->branch_schema_organization_structure}.department_depth.department_depth_id,
                                    'department_id',{$this->branch_schema_organization_structure}.department_depth.department_id,
                                    'depth_id',{$this->branch_schema_organization_structure}.department_depth.depth_id,
                                    'name',{$this->branch_schema_organization_structure}.department_depth.\"name\",
                                    'property_json',{$this->branch_schema_organization_structure}.department_depth.property_json
                                )
                                ORDER BY {$this->branch_schema_organization_structure}.department_depth.depth_id
                            ) department_depth_data
                        FROM {$this->branch_schema_organization_structure}.department_depth
                        GROUP BY {$this->branch_schema_organization_structure}.department_depth.department_id
                    ) department_depth_data ON {$this->branch_schema_organization_structure}.\"department\".department_id = department_depth_data.department_id
                    LEFT JOIN (
                        SELECT {$this->branch_schema_organization_structure}.\"user_role\".department_id,
                            COUNT(DISTINCT {$this->branch_schema_organization_structure}.\"user_role\".user_id) count_user,
                            COUNT(DISTINCT {$this->branch_schema_organization_structure}.\"user_role\".role_id) count_role
                        FROM {$this->branch_schema_organization_structure}.\"user_role\"
                        GROUP BY {$this->branch_schema_organization_structure}.\"user_role\".department_id
                    )\"user_role\" ON department_language_data.department_id = \"user_role\".department_id
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
            $result['relation_select'] = ",{$this->branch_schema_organization_structure}.department_classify_structure_type.department_id
                , COALESCE(department_language_data.department_language_data, '[]')department_language_data
                , COALESCE(department_role.department_role, '[]')department_role
                , COALESCE(department_depth_data.department_depth_data, '[]')department_depth_data
                , COALESCE(user_role.count_role, 0)count_role
                , COALESCE(user_role.count_user, 0)count_user
                , COALESCE({$this->branch_schema_organization_structure}.department_depth.property_json::jsonb || {$this->branch_schema_organization_structure}.department.property_json::jsonb,'{}') property_json
                , all_department_data.department_depth_id
                , all_department_data.depth_name
                , {$this->branch_schema_organization_structure}.staff.staff_name create_user_name
                , {$this->branch_schema_organization_structure}.department.create_time
            ";
            $result['relation_order'] = " ORDER BY \"index\" ASC";
            $result['relation_from'] = "
                INNER JOIN {$this->branch_schema_organization_structure}.department_classify_structure_type ON classify_structure.classify_structure_type.classify_structure_type_id = {$this->branch_schema_organization_structure}.department_classify_structure_type.classify_structure_type_id
                INNER JOIN {$this->branch_schema_organization_structure}.department ON {$this->branch_schema_organization_structure}.department.department_id = {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id
                LEFT JOIN {$this->branch_schema_organization_structure}.staff ON {$this->branch_schema_organization_structure}.department.create_user_id = {$this->branch_schema_organization_structure}.staff.user_id
                LEFT JOIN (
                    SELECT {$this->branch_schema_organization_structure}.all_department_zero.department_id root_id,
                        {$this->branch_schema_organization_structure}.all_department_zero.parent_id department_id, 
                        {$this->branch_schema_organization_structure}.all_department_zero.depth,
                        {$this->branch_schema_organization_structure}.department_depth.\"department_depth_id\",
                        {$this->branch_schema_organization_structure}.department_depth.\"name\" depth_name
                    FROM {$this->branch_schema_organization_structure}.all_department_zero
                    LEFT JOIN {$this->branch_schema_organization_structure}.department_depth ON {$this->branch_schema_organization_structure}.department_depth.department_id = {$this->branch_schema_organization_structure}.all_department_zero.department_id AND {$this->branch_schema_organization_structure}.all_department_zero.depth = {$this->branch_schema_organization_structure}.department_depth.depth_id
                ) all_department_data ON {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id = all_department_data.department_id
                LEFT JOIN {$this->branch_schema_organization_structure}.department_depth ON {$this->branch_schema_organization_structure}.department_depth.department_id = all_department_data.root_id AND all_department_data.depth = {$this->branch_schema_organization_structure}.department_depth.depth_id
                LEFT JOIN (
                    SELECT {$this->branch_schema_organization_structure}.department_role.department_id,
                    COUNT(DISTINCT {$this->branch_schema_organization_structure}.\"department_role\".role_id) count_role,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'department_role_id', {$this->branch_schema_organization_structure}.department_role.id,
                            'role_id', {$this->branch_schema_organization_structure}.\"role\".id,
                            'classify_structure_type_id', role_language_data.classify_structure_type_id,
                            'role_language_data', COALESCE(role_language_data.role_language_data, '[]')
                        )
                    )department_role
                    FROM {$this->branch_schema_organization_structure}.department_role
                    LEFT JOIN {$this->branch_schema_organization_structure}.\"role\" ON {$this->branch_schema_organization_structure}.department_role.role_id = {$this->branch_schema_organization_structure}.\"role\".id
                    LEFT JOIN (
                        SELECT {$this->branch_schema_organization_structure}.\"role\".id role_id, {$this->branch_schema_organization_structure}.role_classify_structure_type.classify_structure_type_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'language_manage_id', role_language_json.language_manage_id,
                                'language_culture_code', role_language_json.language_culture_code,
                                'language_culture_value', role_language_json.language_value
                            )
                        )role_language_data
                        FROM {$this->branch_schema_organization_structure}.\"role\"
                        LEFT JOIN {$this->branch_schema_organization_structure}.role_classify_structure_type ON {$this->branch_schema_organization_structure}.\"role\".id = {$this->branch_schema_organization_structure}.role_classify_structure_type.role_id
                        LEFT JOIN (
                            SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                            language.language_manage.table_name, language.language_manage.column_name,
                            language.language_manage.table_primary_id, language.language_manage.language_value,
                            language.language_culture.language_culture_code
                            FROM language.language_manage
                            LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                            WHERE language.language_manage.schema_name = '{$this->branch_schema_organization_structure}' AND language.language_manage.table_name = 'role'
                        ) role_language_json ON role_language_json.table_primary_id = {$this->branch_schema_organization_structure}.\"role\".id
                        GROUP BY {$this->branch_schema_organization_structure}.\"role\".id, {$this->branch_schema_organization_structure}.role_classify_structure_type.classify_structure_type_id
                    )role_language_data ON {$this->branch_schema_organization_structure}.\"role\".id = role_language_data.role_id
                    GROUP BY {$this->branch_schema_organization_structure}.department_role.department_id
                )department_role ON {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id = department_role.department_id
                LEFT JOIN (
                    SELECT {$this->branch_schema_organization_structure}.\"department\".department_id,
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'language_manage_id', department_language_json.language_manage_id,
                            'language_culture_code', department_language_json.language_culture_code,
                            'language_culture_value', department_language_json.language_value
                        )
                    )department_language_data
                    FROM {$this->branch_schema_organization_structure}.\"department\"
                    LEFT JOIN (
                        SELECT language.language_manage.language_manage_id, language.language_manage.schema_name,
                        language.language_manage.table_name, language.language_manage.column_name,
                        language.language_manage.table_primary_id, language.language_manage.language_value,
                        language.language_culture.language_culture_code
                        FROM language.language_manage
                        LEFT JOIN language.language_culture ON language.language_manage.language_culture_id = language.language_culture.language_culture_id
                        WHERE language.language_manage.schema_name = '{$this->branch_schema_organization_structure}' AND language.language_manage.table_name = 'department'
                    )department_language_json ON department_language_json.table_primary_id = {$this->branch_schema_organization_structure}.\"department\".department_id
                    GROUP BY {$this->branch_schema_organization_structure}.\"department\".department_id
                )department_language_data ON {$this->branch_schema_organization_structure}.department_classify_structure_type.department_id = department_language_data.department_id
                LEFT JOIN (
                    SELECT {$this->branch_schema_organization_structure}.\"user_role\".department_id,
                        COUNT(DISTINCT {$this->branch_schema_organization_structure}.\"user_role\".user_id) count_user,
                        COUNT(DISTINCT {$this->branch_schema_organization_structure}.\"user_role\".role_id) count_role
                    FROM {$this->branch_schema_organization_structure}.\"user_role\"
                    GROUP BY {$this->branch_schema_organization_structure}.\"user_role\".department_id
                )\"user_role\" ON department_language_data.department_id = \"user_role\".department_id
                LEFT JOIN (
                    SELECT {$this->branch_schema_organization_structure}.department_depth.department_id,
                        JSON_AGG(
                            JSON_BUILD_OBJECT(
                                'department_depth_id',{$this->branch_schema_organization_structure}.department_depth.department_depth_id,
                                'department_id',{$this->branch_schema_organization_structure}.department_depth.department_id,
                                'depth_id',{$this->branch_schema_organization_structure}.department_depth.depth_id,
                                'name',{$this->branch_schema_organization_structure}.department_depth.\"name\",
                                'property_json',{$this->branch_schema_organization_structure}.department_depth.property_json
                            )
                            ORDER BY {$this->branch_schema_organization_structure}.department_depth.depth_id
                        ) department_depth_data
                    FROM {$this->branch_schema_organization_structure}.department_depth
                    GROUP BY {$this->branch_schema_organization_structure}.department_depth.department_id
                ) department_depth_data ON department_language_data.department_id = department_depth_data.department_id
            ";
            return $result;
        } else {           
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }
	public function get_borrower_category_map2department($data)
    {
        $sql = "SELECT * FROM library.borrower_category_map2department WHERE department_id = :department_id";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute(["department_id" => $data["department_id"]])) return ["status" => "failure", "errorInfo" => $stmt->errorInfo()];
		return $stmt->fetch(PDO::FETCH_ASSOC);
        
    }
	public function get_department_from_base_department_to_koha_borrower_category($base_department, $koha_borrower_category)
    {
		$base_department_string = json_encode($base_department);
		$koha_borrower_category_string = json_encode($koha_borrower_category);
        $sql = "SELECT *, ROW_NUMBER() OVER (ORDER BY base_department.department_id) \"key\", koha_borrower_category.*
                FROM json_to_recordset('{$base_department_string}') AS base_department (classify_structure_type_id integer, \"value\" integer, classify_structure_type_parent_id integer, classify_structure_id integer, \"name\" text, title text, \"index\" integer, background_color text, font_color text, blog_data json, children json, classify_structure_type_file_data json, department_id integer, department_language_data json, department_role json, department_depth_data json, count_role integer, count_user integer, property_json json, department_depth_id integer, depth_name text, create_user_name text, create_time text, depth integer)
				LEFT JOIN 
				(
					SELECT borrower_category_map2department_id, department_id, koha_borrower_category.*
					FROM library.borrower_category_map2department
					LEFT JOIN json_to_recordset('{$koha_borrower_category_string}') AS koha_borrower_category (categorycode text, \"description\" text, enrolmentperiod integer, enrolmentperioddate text, password_expiry_days integer, upperagelimit integer, dateofbirthrequired integer, enrolmentfee float, overduenoticerequired integer, hidelostitems integer, reservefee float, category_type text, can_be_guarantee integer, reset_password integer, change_password integer, min_password_length integer, require_strong_password integer, BlockExpiredPatronOpacActions integer, default_privacy text, exclude_from_local_holds_priority integer, branches json, \"message\" json)
					ON koha_borrower_category.categorycode = borrower_category_map2department.categorycode
					ORDER BY borrower_category_map2department_id
				) AS koha_borrower_category ON koha_borrower_category.department_id = base_department.department_id
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) return ["status" => "failure", "errorInfo" => $stmt->errorInfo()];
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($this->isJson($value)) $result[$row_id][$key] = json_decode($value, true);
			}
		}
		return $result;
        
    }
	public function post_borrower_category_map2department($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_borrower_category_map2department_single($value); 
			if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function post_borrower_category_map2department_single($data) {
		$values = [
			"categorycode" => null,
			"department_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");
        // $categorycode = $this->CheckArrayData("categorycode", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        // $this->CheckArrayData("department_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			INSERT INTO library.borrower_category_map2department (
				categorycode, department_id
			) VALUES (
				:categorycode, :department_id
			)
			RETURNING borrower_category_map2department_id
EOD;

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$id = $stmt->fetchColumn(0); $stmt->closeCursor();
		return $id;
	}
	// 人員基本資料維護/讀者串接
	public function get_borrower_map2user($base_staff)
    {
		$user_id_string = "";
		foreach($base_staff as $key => $value) $user_id_string .= "{$value['user_id']},";
		if($user_id_string !== "") {
			$user_id_string = rtrim($user_id_string, ",");
			$user_id_string = "WHERE user_id IN ({$user_id_string})";
		}

        $sql = "SELECT * 
				FROM library.borrower_map2user
				{$user_id_string}
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) return ["status" => "failure", "errorInfo" => $stmt->errorInfo()];
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($this->isJson($value)) $result[$row_id][$key] = json_decode($value, true);
			}
		}
		return $result;
    }
	// 人員基本資料維護/讀者串接 - 透過 borrowernumber
	public function get_borrower_map2user_by_borrowernumber($base_staff)
    {
		$borrowernumber_string = "";
		foreach($base_staff as $key => $value) $borrowernumber_string .= "{$value['borrowernumber']},";
		if($borrowernumber_string !== "") {
			$borrowernumber_string = rtrim($borrowernumber_string, ",");
			$borrowernumber_string = "WHERE borrowernumber IN ({$borrowernumber_string})";
		}

        $sql = "SELECT * 
				FROM library.borrower_map2user
				{$borrowernumber_string}
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) return ["status" => "failure", "errorInfo" => $stmt->errorInfo()];
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($this->isJson($value)) $result[$row_id][$key] = json_decode($value, true);
			}
		}
		return $result;
    }
	public function get_staff_from_base_staff_to_koha_borrower($base_staff, $koha_borrower)
    {
		$base_staff_string = json_encode($base_staff);
		$koha_borrower_string = json_encode($koha_borrower);
        $sql = "SELECT *, ROW_NUMBER() OVER (ORDER BY base_staff.user_id) \"key\", koha_borrower.*
                FROM json_to_recordset('{$base_staff_string}') AS base_staff 
				(staff_id integer, staff_name text, staff_serial_name text, \"address\" text, gender_id integer, gender_name text, position_name text, user_id integer, is_login integer, staff_english_name text, last_edit_time timestamp, last_edit_user_id integer, custom_line_id text, property_json jsonb, last_edit_user_name text, serial_name text, email text, have_line_token integer, create_user_id integer, create_time timestamp, create_user_name text, department_id_arr jsonb, role_id_arr jsonb, user_department_role_data jsonb, user_department_role_language_data jsonb, notify_preference_data jsonb, employment_time_start timestamp, employment_time_end timestamp, user_external_token_data jsonb, \"key\" integer)
				-- (classify_structure_type_id integer, \"value\" integer, classify_structure_type_parent_id integer, classify_structure_id integer, \"name\" text, title text, \"index\" integer, background_color text, font_color text, blog_data json, children json, classify_structure_type_file_data json, department_id integer, department_language_data json, department_role json, department_depth_data json, count_role integer, count_user integer, property_json json, department_depth_id integer, depth_name text, create_user_name text, create_time text, depth integer)
				LEFT JOIN 
				(
					SELECT id, borrower_map2user.borrowernumber, borrower_map2user.user_id AS borrower_map2user_user_id, koha_borrower.*
					FROM library.borrower_map2user
					LEFT JOIN json_to_recordset('{$koha_borrower_string}') AS koha_borrower 
					(user_id integer, borrowernumber text, cardnumber text, surname text, firstname text, middle_name text, title text, othernames text, initials text, pronouns text, streetnumber text, streettype text, address text, address2 text, city text, state text, zipcode text, country text, email text, phone text, mobile text, fax text, emailpro text, phonepro text, B_streetnumber text, B_streettype text, B_address text, B_address2 text, B_city text, B_state text, B_zipcode text, B_country text, B_email text, B_phone text, dateofbirth date, branchcode text, branchname text, categorycode text, dateenrolled date, dateexpiry date, password_expiration_date date, date_renewed date, gonenoaddress text, lost text, debarredcomment text, debarredexpriation date, contactname text, contactfirstname text, contacttitle text, borrowernotes text, relationship text, sex text, password text, secret text, auth_method text, flags text, userid text, opacnote text, contactnote text, sort1 text, sort2 text, altcontactfirstname text, altcontactsurname text, altcontactaddress1 text, altcontactaddress2 text, altcontactaddress3 text, altcontactstate text, altcontactzipcode text, altcontactcountry text, altcontactphone text, smsalertnumber text, sms_provider_id integer, privacy text, privacy_guarantor_fines text, privacy_guarantor_checkouts text, checkprevcheckout text, updated_on timestamp, lastseen timestamp, lang text, login_attempts integer, overdrive_auth_token text, anonymized text, autorenew_checkouts integer, primary_contact_method text, protected integer, categoryname text, issue_count integer, reserve_count integer)
					-- (categorycode text, \"description\" text, enrolmentperiod integer, enrolmentperioddate text, password_expiry_days integer, upperagelimit integer, dateofbirthrequired integer, enrolmentfee float, overduenoticerequired integer, hidelostitems integer, reservefee float, category_type text, can_be_guarantee integer, reset_password integer, change_password integer, min_password_length integer, require_strong_password integer, BlockExpiredPatronOpacActions integer, default_privacy text, exclude_from_local_holds_priority integer, branches json, \"message\" json)
					ON CAST(koha_borrower.borrowernumber AS TEXT) = CAST(borrower_map2user.borrowernumber AS TEXT)
					ORDER BY id
				) AS koha_borrower ON koha_borrower.borrower_map2user_user_id = base_staff.user_id
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) return ["status" => "failure", "errorInfo" => $stmt->errorInfo()];
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if ($this->isJson($value)) $result[$row_id][$key] = json_decode($value, true);
			}
		}
		return $result;
        
    }



	//解碼器
    function decodeStaffData($inputData, $encoder)
    {
        $decodedData = [];
        foreach ($inputData as $row => $column) {
            // 先處理基本欄位的解碼
            foreach ($encoder as $decodedKey => $encodedKey) {
                if (isset($inputData[$row][$encodedKey])) {
                    if ($decodedKey === 'staff_name') {
                        $decodedData[$row]['name'] = $inputData[$row][$encodedKey];
                    } else if ($decodedKey === 'staff_serial_name') {
                        $decodedData[$row]['serial_name'] = $inputData[$row][$encodedKey];
                    }
                    $decodedData[$row][$decodedKey] = $inputData[$row][$encodedKey];
                }
            }

            //處理porperty_json
            if (array_key_exists('更多資訊', $inputData[$row])) {
                $decodedData[$row]['property_json'] = $this->parseKeyValueString($inputData[$row]['更多資訊']);
            }

            // 處理選項資料，假設最多有3個個單位 - 職位
            $user_languages = [];
            for ($i = 1; $i <= 4; $i++) {
                if (isset($inputData[$row]["單位 - 職稱{$i}（可自行新增，新增依序為：單位 - 職稱2、單位 - 職稱3）"])) {
                    $inputData[$row]["單位 - 職稱$i"] = $this->parseKeyValueString($inputData[$row]["單位 - 職稱{$i}（可自行新增，新增依序為：單位 - 職稱2、單位 - 職稱3）"]);
                    foreach ($inputData[$row]["單位 - 職稱$i"] as $user_languages_row => $user_languages_column) {
                        foreach ($user_languages_column as $user_languages_column_key => $user_languages_column_value) {
                            $user_languages[] = [
                                'department_name' => $user_languages_column_key,
                                'role_name' => $user_languages_column_value,
                            ];
                        }
                    }
                }
            }

            if (!empty($user_languages)) {
                $decodedData[$row]['user_language_data'] = $user_languages;
            }
        }
        return $decodedData;
    }

	// 處理字串『 - 』的左邊變成key右邊變成value，組成一整個array
    public function parseKeyValueString($input)
    {
        $result = [];
        // 將逗號分隔的部分拆分
        $pairs = explode('、', $input);

        foreach ($pairs as $pair) {
            // 將每組 key-value 用 " - " 分隔
            $keyValue = explode(' - ', $pair);

            if (count($keyValue) === 2) {
                $result[] = [
                    trim($keyValue[0]) => trim($keyValue[1])
                ];
            }
        }

        return $result;
    }

}