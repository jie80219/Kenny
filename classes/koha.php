<?php

use nknu\extend\xDateTime;
use nknu\extend\xFloat;
use nknu\extend\xInt;
use nknu\base\xBaseWithDbop;
use nknu\utility\xStatic;
use nknu\extend\xString;
use nknu\utility\xFile;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Koha extends xBaseWithDbop
{
	protected $container;
	protected $db; protected $db_koha;
	// constructor receives container instance
	public function __construct()
	{
		parent::__construct();
		global $container;
		$this->container = $container;
		$this->db = $container->db;
		$this->db_koha = $container->db_koha;
	}
	#region library
	public function get_library($data)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($data, ["branchcode"]);
		#endregion

		$values = [
			"branchcode" => null,
			"branchname" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"branchcode" => " AND branchcode = :branchcode",
				"branchname" => " AND branchname LIKE CONCAT('%', :branchname, '%')"
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
			    *
		    FROM branches
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
	public function post_library($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->post_library_single($value);
			if ($this->bErrorOn) {
				break;
			}
			if ($callBack->status == "failed") {
				$this->SetError($callBack->message);
				break;
			}
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_library_single($data)
	{
		$values = [
			"branchcode" => null,
			"branchname" => null,
			"address1" => null,
			"address2" => null,
			"address3" => null,
			"postal_code" => null,
			"city" => null,
			"state" => null,
			"country" => null,
			"phone" => null,
			"fax" => null,
			"email" => null,
			"illemail" => null,
			"reply_to_email" => null,
			"return_path_email" => null,
			"url" => null,
			"ip" => null,
			"notes" => null,
			"geolocation" => null,
			"marc_org_code" => null,
			"pickup_location" => null,
			"public" => null,
			"smtp_server" => null,
			"needs_override" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		//將右側的鍵值換成左邊的鍵值，不換 value
		$cKey1 = "library_id";
		$cKey2 = "branchcode";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$cKey1 = "name";
		$cKey2 = "branchname";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$callBack = $this->callKohaApi("post", "/libraries", $values);
		return $callBack;
	}
	public function patch_library($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->patch_library_single($value);
			if ($this->bErrorOn) {
				break;
			}
			if ($callBack->status == "failed") {
				$this->SetError($callBack->message);
				break;
			}
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_library_single($data)
	{
		$values = [
			"branchcode" => null,
			"branchname" => null,
			"address1" => null,
			"address2" => null,
			"address3" => null,
			"postal_code" => null,
			"city" => null,
			"state" => null,
			"country" => null,
			"phone" => null,
			"fax" => null,
			"email" => null,
			"illemail" => null,
			"reply_to_email" => null,
			"return_path_email" => null,
			"url" => null,
			"ip" => null,
			"notes" => null,
			"geolocation" => null,
			"marc_org_code" => null,
			"pickup_location" => null,
			"public" => null,
			"smtp_server" => null,
			"needs_override" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		//將右側的鍵值換成左邊的鍵值，不換 value
		$cKey1 = "library_id";
		$cKey2 = "branchcode";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);
		$library_id = $values[$cKey1];

		$cKey1 = "name";
		$cKey2 = "branchname";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$callBack = $this->callKohaApi("put", "/libraries/{$library_id}", $values);
		return $callBack;
	}
	public function delete_library($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->delete_library_single($value);
			if ($this->bErrorOn) {
				break;
			}
			if ($callBack->status == "failed") {
				$this->SetError($callBack->message);
				break;
			}
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_library_single($data)
	{
		$values = [
			"branchcode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		//將右側的鍵值換成左邊的鍵值，不換 value
		$cKey1 = "library_id";
		$cKey2 = "branchcode";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);
		$library_id = $values[$cKey1];

		$callBack = $this->callKohaApi("delete", "/libraries/{$library_id}", $values);
		return $callBack;
	}
	#endregion
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

		$borrowernumber = null; if (array_key_exists("borrowernumber", $values)) {
			$borrowernumber = $values["borrowernumber"]; unset($values["borrowernumber"]);
			if ($borrowernumber != null && !is_array($borrowernumber)) {
				$borrowernumber = [$borrowernumber];
			}
		} if ($borrowernumber == null) { $borrowernumber = []; }

		$user_id = null; if (array_key_exists("user_id", $values)) {
			$user_id = $values["user_id"]; unset($values["user_id"]);
			if ($user_id != null && !is_array($user_id)) {
				$user_id = [$user_id];
			}
		} if ($user_id == null) { $user_id = []; }

		#region 從 pgsql 讀取 user_id 對應的 borrowernumber
		if (count($user_id) > 0) {
			$cList = ""; {
				$aList = [];
				foreach ($user_id as $key => $value) {
					$aList[] = ":" . $key;
				}
				$cList = implode(",", $aList);
			}

			$cSql = <<<EOD
				SELECT borrowernumber, user_id
				FROM library.borrower_map2user
				WHERE user_id IN ({$cList});
EOD;
			$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $user_id);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$user_id = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
			if (count($user_id) == 0) { return ["data" => [], "total" => 0]; }
			foreach ($user_id as $value) {
				$borrowernumber[] = $value["borrowernumber"];
			}
		}
		#endregion
		#region where condition
		$cWhere_Inner = ""; {
			if (count($borrowernumber) > 0) {
				$cList = ""; {
					$aList = [];
					foreach ($borrowernumber as $key => $value) {
						$aList[] = ":" . $key;
					}
					$cList = implode(",", $aList);
				}
				$cWhere_Inner = " AND p.borrowernumber IN ({$cList})";
				$values = $borrowernumber;
			} else {
				$aCondition = [
					"cardnumber" => " AND UPPER(p.cardnumber) = UPPER(:cardnumber)",
					"name" => " AND CONCAT_WS('', p.surname, p.firstname) LIKE CONCAT('%', :name, '%')",
					"categorycode" => " AND p.categorycode = :categorycode"
				];
				$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
			}
		}
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
            SELECT
				NULL AS user_id,
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
				), '[]') AS debarred,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT(
							"code", bat.code,
							"description", bat.description,
							"repeatable", bat.`repeatable`,
							"id", ba.id,
							"attribute", ba.`attribute`,
							"options", IFNULL((
								SELECT JSON_ARRAYAGG(JSON_OBJECT(
									"value", authorised_value, "text", lib
								))
								FROM authorised_values AS av
									LEFT JOIN authorised_values_branches AS avb ON avb.av_id = av.id
								WHERE category = IFNULL(bat.authorised_value_category, '')
									AND (batr.b_branchcode IS NULL OR batr.b_branchcode = avb.branchcode)
							), '[]'),
							"wantDelete", 0
						))
					FROM borrower_attribute_types AS bat
						LEFT JOIN borrower_attribute_types_branches AS batr ON batr.bat_code = bat.code
						LEFT JOIN borrower_attributes AS ba ON ba.code = bat.code AND ba.borrowernumber = p.borrowernumber
					WHERE (batr.b_branchcode IS NULL OR batr.b_branchcode = p.branchcode)
						AND (bat.category_code IS NULL OR bat.category_code = p.categorycode)
				), '[]') AS additional_attributes
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

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute() || !$stmt_count->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		$aBorrowerNumbers = [];
		foreach ($aRows as $row_id => $row_value) {
			$aBorrowerNumbers[] = $row_value["borrowernumber"];

			$additional_attributes = json_decode($aRows[$row_id]["additional_attributes"], true);
			foreach ($additional_attributes as $key => $attribute) {
				$additional_attributes[$key]["options"] = json_decode($attribute["options"], true);
			}
			$aRows[$row_id]["additional_attributes"] = $additional_attributes;

			$aRows[$row_id]["debarred"] = json_decode($aRows[$row_id]["debarred"], true);
			$aRows[$row_id]["message"] = json_decode($aRows[$row_id]["message"], true);
		}
		$aRows = $this->fillUserID($aRows, $aBorrowerNumbers);

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
		if (!$stmt->execute()) { return $aRows; }
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
			$callBack = $this->post_borrower_single($value); 
			if ($this->bErrorOn) { 
				var_dump($callBack);
				break; 
			}
			if ($callBack->status == "failed") { 
				var_dump($callBack);
				$this->SetError($callBack->message); 
				break; 
			}
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_borrower_single($data) {
		$data = $this->prepare_borrower_data($data); if ($this->bErrorOn) { return; };
		//$manager_id = $data["manager_id"];
		$values = $data["values"];
		$additional_attributes = $data["additional_attributes"];
		$debarred = $data["debarred"];
		$message = $data["message"];
		$primary_contact_method = $data["primary_contact_method"];
		$password = $data["password"];

		$bIncludeAdditionalAttributes = count($additional_attributes) > 0;
		$bIncludeDebarred = count($debarred) > 0;
		$bIncludePrimary_Contact_Method = !xString::IsEmpty($primary_contact_method);
		$bIncludeMessage = count($message) > 0;
		$bIncludePassword = !xString::IsEmpty($password);

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
			#region set additional_attributes
			if ($bIncludeAdditionalAttributes) {
				$callBackBySetAdditionalAttributes = $this->post_borrower_attributes($borrowernumber, $additional_attributes);
				if ($callBackBySetAdditionalAttributes->status == "failed") { return $callBackBySetAdditionalAttributes; }
			}
			#endregion
			#region set debarred
			if ($bIncludeDebarred) {
				$debarred["borrowernumber"] = $borrowernumber;
				$callBackBySetDebarred = $this->post_borrower_debarred_single($debarred);
				if ($callBackBySetDebarred->status == "failed") { return $callBackBySetDebarred; }
			}
			#endregion
			#region set message
			if ($bIncludeMessage) {
				//在呼叫新增讀者 API後，KOHA 自動新增了 message 資料(依預設的讀者類型)，所以要先取得舊的 message 資料，再呼叫 patch_message_type
				$result = $this->get_message_type(["borrowernumber" => $borrowernumber]);
				if ($this->bErrorOn) { $callBack->status == "failed"; $callBack->cMessage == $this->cMessage; return $callBack; }
				$message_old = $result["data"];
				$callBackBySetMessage = $this->patch_message_type($message_old, $message);
				if ($callBackBySetMessage->status == "failed") { return $callBackBySetMessage; }
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
		$additional_attributes = $data["additional_attributes"];
		$debarred = $data["debarred"];
		$message = $data["message"];
		$primary_contact_method = $data["primary_contact_method"];
		$password = $data["password"];

		$bIncludeAdditionalAttributes = count($additional_attributes) > 0;
		$bIncludeDebarred = count($debarred) > 0;
		$bIncludePrimary_Contact_Method = !xString::IsEmpty($primary_contact_method);
		$bIncludeMessage = count($message) > 0;
		$bIncludePassword = !xString::IsEmpty($password);

		//$callBack = new \stdClass(); $callBack->status = "success"; $callBack->data = true;
		$callBack = $this->callKohaApi("put", "/patrons/" . $borrowernumber, $values);
		if ($callBack->status == "success") {
			#region set attribute_types
			if ($bIncludeAdditionalAttributes) {
				$deleteList = [];
				foreach ($additional_attributes as $key => $attribute) {
					if ($attribute["wantDelete"] == 1) {
						$deleteList[] = $attribute["id"];
						unset($additional_attributes[$key]);
					}
				}
				if (count($deleteList) > 0) {
					$callBackBySetAttributeTypes = $this->delete_borrower_attributes($deleteList);
					if ($callBackBySetAttributeTypes->status == "failed") { return $callBackBySetAttributeTypes; }
				}
				if (count($additional_attributes) > 0) {
					$callBackBySetAttributeTypes = $this->patch_borrower_attributes($borrowernumber, $additional_attributes);
					if ($callBackBySetAttributeTypes->status == "failed") { return $callBackBySetAttributeTypes; }
				}
			}
			#endregion
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
						if ($callBackBySetDebarred->status == "failed") { return $callBackBySetDebarred; }
						unset($debarred[$key]);
					}
				}
				foreach ($debarred as $debarred_single) {
					if ($debarred_single["wantDelete"] !== 1) {
						$debarred_single["borrowernumber"] = $borrowernumber;
						$callBackBySetDebarred = $this->post_borrower_debarred_single($debarred_single);
						if ($callBackBySetDebarred->status == "failed") { return $callBackBySetDebarred; }
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
				if ($callBackBySetMessage->status == "failed") {return $callBackBySetMessage; }
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
		$values = xStatic::KeyMatchThenReplaceValue($values, true, $data, false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;

		$cKey1 = "borrowernumber"; if (!array_key_exists($cKey1, $values)) { $callBack->message = "必須包含 borrowernumber"; return $callBack; }
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

		$cKey1 = "borrowernumber"; if (!array_key_exists($cKey1, $values)) { $callBack->message = "必須包含 borrowernumber"; return $callBack; }
		$borrowernumber = $values[$cKey1];
		unset($values[$cKey1]);

		$cKey1 = "password";
		if (!array_key_exists($cKey1, $values)) { $callBack->message = "必須包含 password"; return $callBack; }
		$values["password_2"] = $values["password"];

		$callBack = $this->callKohaApi("post", "/patrons/" . $borrowernumber . "/password", $values);
		return $callBack;
	}
	private function post_borrower_primary_contact_method($data) {
		$values = [
			"borrowernumber" => null,
			"primary_contact_method" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, true, $data, false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		$this->CheckArrayData("primary_contact_method", $values, false, false, "c"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			UPDATE borrowers SET
				primary_contact_method = :primary_contact_method
			WHERE borrowernumber = :borrowernumber;
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack = new stdClass(); $callBack->status = "success"; $callBack->data = true;
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
			"phone" => null,
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

			"additional_attributes" => null,
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

		$bIncludeAdditionalAttributes = array_key_exists("additional_attributes", $values);
		if ($bIncludeAdditionalAttributes) {
			$additional_attributes = $values["additional_attributes"];
			unset($values["additional_attributes"]);
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
			"primary_contact_method" => $primary_contact_method, "password" => $password,
			"additional_attributes" => $additional_attributes
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
	#region borrower_debarred: 這裡是讀者新增、修改頁面的「Patron restrictions」，設定禁止借閱的相關操作
	public function get_borrower_debarred($data) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($data, ["id"]);
		#endregion

		$values = [
			"borrowernumber" => null,
			"type" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

        $borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND borrowernumber = :borrowernumber",
				"type" => " AND type = :type",
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
				borrower_debarment_id AS id,
				`type`,
				`comment`,
				expiration,
				created,
				(
					SELECT
						JSON_ARRAY(
							JSON_OBJECT(
							"column_name", 'type', "options", (
								SELECT JSON_ARRAYAGG(JSON_OBJECT(
									"value", code, "text", display_text, "is_default", is_default
								))
								FROM restriction_types
								WHERE is_system = 0
							)
						))
				) AS options
			FROM borrower_debarments
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
		if ($stmt->execute() && $stmt_count->execute()) {
			$result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$result_count = $stmt_count->fetchColumn(0);
			foreach ($result['data'] as $row_id => $row_value) {
				$result['data'][$row_id]["options"] = json_decode($row_value["options"], true);
			}
			$result['total'] = $result_count;
			$this->SetOK();
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	public function post_borrower_debarred($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_borrower_debarred_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_borrower_debarred_single($data) {
		$values = [
			"borrowernumber" => null,
			"comment" => null,
			"expiration" => null,
			"manager_id" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		$type = $this->CheckArrayData("type", $values, false, false, "c"); if ($this->bErrorOn) { return; }
		$comment = $this->CheckArrayData("comment", $values, true, false, "c"); if ($this->bErrorOn) { return; }
		$expiration = $this->CheckArrayData("expiration", $values, true, false, "d"); if ($this->bErrorOn) { return; }
		$manager_id = $this->CheckArrayData("manager_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$values["type"] = $type ?? "MANUAL";
		date_default_timezone_set('Asia/Taipei');
		$values["created"] = date("Y-m-d H:i:s");

		$cSql = <<<EOD
			INSERT INTO borrower_debarments (
				borrowernumber, `type`, `comment`, expiration, manager_id, created
			) VALUES (
				:borrowernumber, :type, :comment, :expiration, :manager_id, :created
			);
			UPDATE borrowers SET
				debarred = :expiration, debarredcomment = :comment
			WHERE borrowernumber = :borrowernumber AND IFNULL(debarred, now()) < :expiration;
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
	public function delete_borrower_debarred($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_borrower_debarred_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_borrower_debarred_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;

		$values = [
			"id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$id = $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		#region 新增主資料
		$cSql = <<<EOD
			UPDATE borrowers AS p
				LEFT JOIN (
					SELECT borrowernumber, expiration, `comment`
					FROM borrower_debarments
					WHERE borrowernumber = (SELECT borrowernumber FROM borrower_debarments WHERE borrower_debarment_id = :id)
						AND borrower_debarment_id <> :id
						AND expiration > now()
					ORDER BY expiration DESC
					limit 1
				) AS m ON m.borrowernumber = p.borrowernumber
			SET
				p.debarred = m.expiration,
				p.debarredcomment = m.`comment`;
			DELETE FROM borrower_debarments WHERE borrower_debarment_id = :id;
EOD;
		#endregion

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return;
		}

		$callBack->status = "success";
		$callBack->data = true;
		return $callBack;
	}
	#endregion
	#region borrower_permissions: 讀者權限
	public function get_borrower_permissions($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["`bit`"]);
		#endregion

		$values = [
			"borrowernumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (count($values) == 0) {
			$this->SetError("讀取讀者資料失敗. 沒有相關參數.");
			return;
		}
		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND p.borrowernumber = :borrowernumber"
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
				uf.`bit`,
				CASE WHEN p.flags & (1 << uf.`bit`) > 0 THEN 1 ELSE 0 END AS `allow_flag`,
				uf.`flag`,
				uf.flagdesc,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"module_bit", pm.module_bit,
							"allow_code", CASE WHEN p.flags & (1 << uf.`bit`) > 0 OR up.borrowernumber IS NOT NULL THEN 1 ELSE 0 END,
							"code", pm.code,
							"description", pm.description
						))
					FROM permissions AS pm
						LEFT JOIN user_permissions AS up ON up.borrowernumber = p.borrowernumber AND up.module_bit = pm.module_bit AND up.code = pm.code
					WHERE pm.module_bit = uf.`bit`
				), '[]') AS permissions
			FROM userflags AS uf
				LEFT JOIN borrowers AS p ON true
			WHERE uf.`bit` <> 25 {$cWhere_Inner}
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
			foreach ($result['data'] as $row_id => $row) {
				$result['data'][$row_id]["bit"] = (int) $result['data'][$row_id]["bit"];
				$result['data'][$row_id]["allow_flag"] = (int) $result['data'][$row_id]["allow_flag"];
				foreach ($row as $key => $value) {
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
	public function patch_borrower_permissions($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_borrower_permissions_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_borrower_permissions_single($data) {
		$values = [
			"borrowernumber" => null,
			"userflags" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");


		$borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["borrowernumber"]);
		$userflags = $this->CheckArrayData("userflags", $values, true, false, "a"); if ($this->bErrorOn) { return; }
		unset($values["userflags"]);

		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		//$callBack->message = json_encode($permissions);
		//return $callBack;

		foreach ($userflags as $i => $userflag) {
			$bit = $this->CheckArrayData("bit", $userflag, true, false, "i"); if ($this->bErrorOn) { return; }
			$allow_flag = $this->CheckArrayData("allow_flag", $userflag, true, false, "i"); if ($this->bErrorOn) { return; }
			$allow_flag = $allow_flag == 1;
			$permissions = $this->CheckArrayData("permissions", $userflag, true, true, "a"); if ($this->bErrorOn) { return; }
			foreach ($permissions as $j => $permission) {
				$allow_code = $this->CheckArrayData("allow_code", $permission, true, false, "i"); if ($this->bErrorOn) { return; }
				$allow_code = $allow_code == 1;
				//轉型為布林後，回寫權限表
				$userflags[$i]["permissions"][$j]["allow_code"] = $allow_code;

				$code = $this->CheckArrayData("code", $permission, true, false, "c"); if ($this->bErrorOn) { return; }

				//避免權限子項目非全為 1 時，母權限被設定為 1
				$allow_flag = $allow_flag && $allow_code;
			}
			//回寫權限母項目
			$userflags[$i]["bit"] = $bit;
			$userflags[$i]["allow_flag"] = $allow_flag;
		}

		$borrower_flag = 0x0; {
			//以下為權限子項目全為 1 時，則 $borrower_flag 對應的母權限為 1
			//例如：若母權限(1)項下有3個子權限皆為 allow: 1，則 $borrower_flag 的第 3 位元 (0b0100) 為 1
			$bits = [7 => false, 8 => false, 25 => false, 31 => false];
			foreach ($userflags as $index => $userflag) {
				$bit = $userflag["bit"];
				$allow_flag = $userflag["allow_flag"];
				$bits[$bit] = $allow_flag;
			}
			foreach ($bits as $bit => $allow) {
				if ($allow != true) { continue; }
				$borrower_flag = $borrower_flag | (1 << $bit);
			}
		}
		$bFullPower = ($borrower_flag & 1) == 1;

		#region 設定子權限清單
		$cSql = <<<EOD
			UPDATE borrowers SET flags = :flags WHERE borrowernumber = :borrowernumber;
			DELETE FROM user_permissions WHERE borrowernumber = :borrowernumber_up;
EOD;
		$values = ["flags" => $borrower_flag, "borrowernumber" => $borrowernumber, "borrowernumber_up" => $borrowernumber];

		if (!$bFullPower) {
			$aInsertList = [];
			foreach ($userflags as $userflag) {
				$allow_flag = $userflag["allow_flag"];
				if ($allow_flag) { continue; } //若母權限為 1，則不需設定子權限
				$permissions = $userflag["permissions"];
				foreach ($permissions as $permission) {
					$allow_code = $permission["allow_code"];
					if (!$allow_code) { continue; }

					$iCount = count($aInsertList);
					$aInsertList[] = "(:borrowernumber_{$iCount}, :module_bit_{$iCount}, :code_{$iCount})";
					$values["borrowernumber_{$iCount}"] = $borrowernumber;
					$values["module_bit_{$iCount}"] = $permission["module_bit"];
					$values["code_{$iCount}"] = $permission["code"];
				}
			}
			if (count($aInsertList) > 0) {
				$cValues = implode(", ", $aInsertList);
				$cSql .= "\nINSERT INTO user_permissions (borrowernumber, module_bit, code) VALUES {$cValues};";
			}
		}
		#endregion
		//$callBack->message = $cSql; return $callBack;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			$callBack->status = "success";
			$callBack->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$this->SetOK();
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$callBack->cMessage = $cMessage;
			$this->SetError($cMessage);
		}

		return $callBack;
	}
	#region borrower map to user
	public function get_borrower_map2user($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["borrowernumber"]);
		#endregion

		$values = [
			"borrowernumber" => null,
			"user_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND borrowernumber = :borrowernumber",
				"user_id" => " AND user_id = :user_id"
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
				id, borrowernumber, user_id
			FROM library.borrower_map2user
		    WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS key
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
		WHERE key > :start
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_AllRow}
			) AS C
EOD;

		$stmt = $this->db->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		if (!$stmt->execute() || !$stmt_count->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		foreach ($result['data'] as $row_id => $row_value) {
			foreach ($row_value as $key => $value) {
				if (xString::IsJson($value)) {
					$result['data'][$row_id][$key] = json_decode($value, true);
				}
			}
		}
		$result['total'] = $result_count;
		return $result;
	}
	public function post_borrower_map2user($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_borrower_map2user_single($value); if ($this->bErrorOn) { 
				return $callBack;
				break; 
			}
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function post_borrower_map2user_single($data) {
		$values = [
			"borrowernumber" => null,
			"user_id" => null, 
			"api_borrowernumber" => null,
			"api_borrower_userid" => null,
			"api_borrower_password" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("user_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			INSERT INTO library.borrower_map2user (
				borrowernumber, user_id, api_borrowernumber, api_borrower_userid, api_borrower_password
			) VALUES (
				:borrowernumber, :user_id, :api_borrowernumber, :api_borrower_userid, :api_borrower_password
			)
			RETURNING id
EOD;

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			return $oInfo;
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$id = $stmt->fetchColumn(0); $stmt->closeCursor();
		return $id;
	}
	public function patch_borrower_map2user($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_borrower_map2user_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function patch_borrower_map2user_single($data) {
		$values = [
			"id" => null,
			"borrowernumber" => null,
			"user_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        $borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("user_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			UPDATE library.borrower_map2user SET
				borrowernumber = :borrowernumber, user_id = :user_id
			WHERE id = :id
EOD;

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$stmt->closeCursor();
		return true;
	}
	public function delete_borrower_map2user($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_borrower_map2user_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function delete_borrower_map2user_single($data) {
		$values = [
			"id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = "DELETE FROM library.borrower_map2user WHERE id = :id";

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$stmt->closeCursor();
		return true;
	}
	#endregion
	#endregion
	#region attribute_types
	public function get_borrower_attribute_types($params) {
		$code = null;
		if (array_key_exists("code", $params)){ $code = $params["code"]; }
		if ($code === null) { return $this->get_borrower_attribute_types_p0($params); }
		return $this->get_borrower_attribute_types_px($params);
	}
	private function get_borrower_attribute_types_p0($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["code"]);
		#endregion
		$values = [
			"keyword" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$aCondition = [
			"keyword" => " AND CONCAT(code, description) LIKE CONCAT('%', :keyword, '%')",
		];
		$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];

		$cSql = <<<EOD
			WITH x AS (
				SELECT a.code, a.description, b.branchname, a.mandatory, a.staff_searchable, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM borrower_attribute_types AS a
					LEFT JOIN borrower_attribute_types_branches AS ab ON ab.bat_code = a.code
					LEFT JOIN branches AS b ON b.branchcode = ab.b_branchcode
				WHERE TRUE {$cWhere_Inner}
			)
			SELECT
				`key`,
				code,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 0, "phrase_name", 'Numbering patterns',
						"columns", JSON_ARRAY(
							JSON_OBJECT(
								"order", 1, "title", 'Code', "value", code,
								"text", code
							),
							JSON_OBJECT(
								"order", 2, "title", 'Description', "value", '',
								"text", description
							),
							JSON_OBJECT(
								"order", 3, "title", 'Library limitation', "value", "",
								"text", IFNULL(branchname, 'No limitation')
							),
							JSON_OBJECT(
								"order", 4, "title", 'Mandatory', "value", "",
								"text", CASE WHEN mandatory = 0 THEN 'No' ELSE 'Yes' END
							),
							JSON_OBJECT(
								"order", 5, "title", 'Searching', "value", "",
								"text", CASE WHEN staff_searchable = 0 THEN 'Not searchable' ELSE 'Searchable' END
							)
						)
					)
				) AS phrases
			FROM x
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	private function get_borrower_attribute_types_px($params) {
		$code = $params["code"] ?? "";
		$cSql = <<<EOD
			WITH x AS (
				SELECT *, 1 AS `key`
				FROM (
					SELECT
						'' AS `code`,
						'' AS `description`,
						0 AS `repeatable`,			0 AS `unique_id`,				0 AS `opac_display`,		0 AS `opac_editable`,
						0 AS `staff_searchable`,	0 AS `searched_by_default`,		0 AS `mandatory`,			0 AS `display_checkout`,
						NULL AS `authorised_value_category`,
						NULL AS `branchcode`,
						NULL AS `category_code`,
						NULL AS `class`,
						NULL AS `keep_for_pseudonymization`
					UNION ALL
					SELECT
						a.`code`,
						a.`description`,
						a.`repeatable`,					a.`unique_id`,					a.`opac_display`,			a.`opac_editable`,
						a.`staff_searchable`,			a.`searched_by_default`,		a.`mandatory`,				a.`display_checkout`,
						a.`authorised_value_category`,
						ab.b_branchcode AS `branchcode`,
						a.`category_code`,
						a.`class`,
						a.`keep_for_pseudonymization`
					FROM borrower_attribute_types AS a
						LEFT JOIN borrower_attribute_types_branches AS ab ON ab.bat_code = a.code
				) AS u_s
				WHERE code = :code
			)
			SELECT
				`key`,
				code,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 1, "phrase_name", CONCAT(CASE WHEN code = '' THEN 'New' ELSE 'Edit' END, 'patron attribute type'),
						"columns", JSON_OBJECT(
							"code", code,
							"description", description,
							"repeatable", repeatable,				"unique_id", unique_id,							"opac_display", opac_display,		"opac_editable", opac_editable,
							"staff_searchable", staff_searchable,	"searched_by_default", searched_by_default,		"mandatory", mandatory,				"display_checkout", display_checkout,
							"authorised_value_category", authorised_value_category,
							"branchcode", branchcode,
							"category_code", category_code ,
							"class", `class`,
							"keep_for_pseudonymization", keep_for_pseudonymization
						),
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'authorised_value_category' AS column_name, 'Authorized value category' AS column_title, '' AS description, IFNULL((
									SELECT
										JSON_ARRAYAGG(JSON_OBJECT("value", category_name, "text", category_name, "title", ''))
									FROM authorised_value_categories
								), JSON_ARRAY()) AS options
								UNION VALUES
								('branchcode', 'Library limitation', '', IFNULL((
									SELECT
										JSON_ARRAYAGG(JSON_OBJECT("value", branchcode, "text", branchname, "title", ''))
									FROM branches
								), JSON_ARRAY())),
								('category_code', 'Category', '', IFNULL((
									SELECT
										JSON_ARRAYAGG(JSON_OBJECT("value", categorycode, "text", description, "title", ''))
									FROM categories
								), JSON_ARRAY()))
							) as u
						)
					)
				) AS phrases
			FROM x
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["code" => $code]);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	public function post_borrower_attribute_types($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_borrower_attribute_types_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_borrower_attribute_types_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_borrower_attribute_types($data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		$code = $values["code"];
		$branchcode = $values["branchcode"]; unset($values["branchcode"]);
		#endregion

		$cSql = <<<EOD
			INSERT INTO borrower_attribute_types (
				`code`,
				`description`,
				`repeatable`,					`unique_id`,				`opac_display`,			`opac_editable`,
				`staff_searchable`,				`searched_by_default`,		`mandatory`,			`display_checkout`,
				`authorised_value_category`,
				`category_code`,
				`class`,
				`keep_for_pseudonymization`
			) VALUES (
				:code,
				:description,
				:repeatable,					:unique_id,					:opac_display,			:opac_editable,
				:staff_searchable,				:searched_by_default,		:mandatory,				:display_checkout,
				:authorised_value_category,
				:category_code,
				:class,
				:keep_for_pseudonymization
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack = $this->set_borrower_attribute_branches($code, $branchcode);
		return $callBack;
	}
	public function patch_borrower_attribute_types($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_borrower_attribute_types_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_borrower_attribute_types_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_borrower_attribute_types($data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		$code = $values["code"];
		$branchcode = $values["branchcode"]; unset($values["branchcode"]);
		#endregion

		$cSql = <<<EOD
			REPLACE INTO borrower_attribute_types (
				`code`,
				`description`,
				`repeatable`,					`unique_id`,				`opac_display`,			`opac_editable`,
				`staff_searchable`,				`searched_by_default`,		`mandatory`,			`display_checkout`,
				`authorised_value_category`,
				`category_code`,
				`class`,
				`keep_for_pseudonymization`
			) VALUES (
				:code,
				:description,
				:repeatable,					:unique_id,					:opac_display,			:opac_editable,
				:staff_searchable,				:searched_by_default,		:mandatory,				:display_checkout,
				:authorised_value_category,
				:category_code,
				:class,
				:keep_for_pseudonymization
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack = $this->set_borrower_attribute_branches($code, $branchcode);
		return $callBack;
	}
	public function delete_borrower_attribute_types($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_borrower_attribute_types_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_borrower_attribute_types_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }

		$code = array_key_exists("code", $data) ? $data["code"] : "";

		$cSql = <<<EOD
			DELETE FROM borrower_attribute_types
			WHERE code = :code
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["code" => $code]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack = $this->set_borrower_attribute_branches($code, null);
		return $callBack;
	}
	public function get_borrower_attribute_types_type($params) {
		$sql = <<<EOD
			SELECT 0 AS column_order, 'code' AS column_name, 'Patron attribute type code' AS column_title, 'unique key used to identify each custom field' AS description, JSON_ARRAY() AS options
			UNION VALUES
			(1, 'description', 'Description', 'description for each custom field', JSON_ARRAY()),
			(2, 'repeatable', 'Repeatable', 'defines whether one patron/borrower can have multiple values for this custom field  (1 for yes, 0 for no)', JSON_ARRAY()),
			(3, 'unique_id', 'Unique identifier', 'defines if this value needs to be unique (1 for yes, 0 for no)', JSON_ARRAY()),
			(4, 'opac_display', 'Display in OPAC', 'defines if this field is visible to patrons on their account in the OPAC (1 for yes, 0 for no)', JSON_ARRAY()),
			(5, 'opac_editable', 'Editable in OPAC', 'defines if this field is editable by patrons on their account in the OPAC (1 for yes, 0 for no)', JSON_ARRAY()),
			(6, 'staff_searchable', 'Searchable', 'defines if this field is searchable via the patron search in the staff interface (1 for yes, 0 for no)', JSON_ARRAY()),
			(7, 'searched_by_default', 'Search by default', 'defines if this field is included in "Standard" patron searches in the staff interface (1 for yes, 0 for no)', JSON_ARRAY()),
			(8, 'mandatory', 'Mandatory', 'defines if the attribute is mandatory or not', JSON_ARRAY()),
			(9, 'display_checkout', 'Display in patron\'s brief information', 'defines if this field displays in checkout screens', JSON_ARRAY()),
			(10, 'authorised_value_category', 'Authorized value category', 'foreign key from authorised_values that links this custom field to an authorized value category', IFNULL((
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT("value", category_name, "text", category_name, "title", ''))
				FROM authorised_value_categories
			), JSON_ARRAY())),
			(11, 'branchcode', 'Library limitation', '', IFNULL((
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT("value", branchcode, "text", branchname, "title", ''))
				FROM branches
			), JSON_ARRAY())),
			(12, 'category_code', 'Category', 'defines a category for an attribute_type', IFNULL((
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT("value", categorycode, "text", description, "title", ''))
				FROM categories
			), JSON_ARRAY())),
			(13, 'class', 'Class', 'defines a class for an attribute_type', JSON_ARRAY()),
			(14, 'keep_for_pseudonymization', '???', 'defines if this field is copied to anonymized_borrower_attributes (1 for yes, 0 for no)', JSON_ARRAY())
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["options"] = json_decode($oRow["options"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;

	}
	private function set_borrower_attribute_branches($bat_code, $b_branchcode) {
		$cSql = ""; $values = []; {
			if ($b_branchcode === null || $b_branchcode === "") {
				$cSql = "DELETE FROM borrower_attribute_types_branches WHERE bat_code = :bat_code";
				$values = [ "bat_code" => $bat_code];
			} else {
				$cSql = "REPLACE INTO borrower_attribute_types_branches (bat_code, b_branchcode) VALUES (:bat_code, :b_branchcode)";
				$values = [ "bat_code" => $bat_code, "b_branchcode" => $b_branchcode];
			}
		}

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();
		$callBack = new stdClass(); $callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private $fileds_borrower_attribute_types = [
		"code" => "",
		"description" => "",
		"repeatable" => 0,			"unique_id" => 0,				"opac_display" => 0,		"opac_editable" => 0,
		"staff_searchable" => 0,	"searched_by_default" => 0,		"mandatory" => 0,			"display_checkout" => 0,
		"authorised_value_category" => null,
		"branchcode" => null,
		"category_code" => null,
		"class" => "",
		"keep_for_pseudonymization" => 0
	];
	private function checkAndConvert_borrower_attribute_types($data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數

		$aValues = $this->fileds_borrower_attribute_types;
		$code = array_key_exists("code", $data) ? $data["code"] : "";
		if (array_key_exists("phrases", $data)) {
			foreach($data["phrases"] as $phrase) {
				foreach($phrase["columns"] as $column_name => $column_value) {
					if (array_key_exists($column_name, $this->fileds_borrower_attribute_types)) {
						$aValues[$column_name] = $column_value;
					}
				}
			}
		} else {
			$aValues = xStatic::KeyMatchThenReplaceValue($aValues, true, $data, false);
			$aValues = xStatic::ValueMatchThenRemove($aValues, "");
		}
		$aValues["code"] = $code;

		foreach ($this->fileds_borrower_attribute_types as $column_name => $default_value) {
			$column_value = array_key_exists($column_name, $aValues) ? $aValues[$column_name] : null;
			if ($column_value === null && $default_value !== null) { $callBack->message = "{$column_name}不可為空值."; return $callBack; }
			if ($column_value === $default_value) { continue; }

			switch ($column_name) {
				#region key
				case "code": {
					if ($column_value === "") { $callBack->message = "{$column_name}必須不可為空值."; return $callBack; }
				} break;
				#endregion
				#region bool
				case "repeatable":			case "unique_id":			case "opac_display":	case "opac_editable":
				case "staff_searchable":	case "searched_by_default":	case "mandatory":		case "display_checkout":
				case "keep_for_pseudonymization": {
					if ($column_value !== null) {
						$allow = [0, 1];
						if (!in_array($column_value, $allow)) { $callBack->message = "{$column_name}非允許的值 [{$column_value}]."; return $callBack; }
					}
				} break;
				#endregion
			}
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aValues;
		return $callBack;
	}
	#endregion

	#region borrower_attribute (additional_attributes)
	private function post_borrower_attributes($borrowernumber, $additional_attributes) {
		return $this->patch_borrower_attributes($borrowernumber, $additional_attributes);
	}
	private function patch_borrower_attributes($borrowernumber, $additional_attributes) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		$fields = ""; {
			$aField = []; $aValues = [];
			foreach ($additional_attributes as $key => $attribute) {
				$aField[] = "(:id{$key}, :borrowernumber{$key}, :code{$key}, :attribute{$key})";
				$aValues["id{$key}"] = array_key_exists("id", $attribute) ? $attribute["id"] : 0;
				$aValues["borrowernumber{$key}"] = $borrowernumber;
				$aValues["code{$key}"] = $attribute["code"];
				$aValues["attribute{$key}"] = $attribute["attribute"];
			}
			$fields = implode(",", $aField);
			unset($aField);
		}

		$cSql = <<<EOD
			REPLACE INTO borrower_attributes (id, borrowernumber, code, `attribute`) VALUES {$fields}
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $aValues);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private function delete_borrower_attributes($deleteList) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		$fields = ""; {
			$aField = [];
			foreach ($deleteList as $key => $value) {
				$aField[] = ":{$key}";
			}
			$fields = implode(",", $aField);
			unset($aField);
		}

		$cSql = <<<EOD
			DELETE FROM borrower_attributes
			WHERE id IN ({$fields})
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $deleteList);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
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
	#region authorised_values
	public function get_authorised_values($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["category"]);
		#endregion

		$values = [
			"id" => null,
			"category" => null,
			"description" => null,
			"description_opac" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"id" => " AND id = :id",
				"category" => " AND category LIKE CONCAT('%', :category, '%')",
				"description" => " AND lib LIKE CONCAT('%', :description, '%')",
				"description_opac" => " AND lib_opac LIKE CONCAT('%', :description_opac, '%')",
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
				id, category, authorised_value, lib AS description, lib_opac AS description_opac,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"branchcode", av_b.branchcode,
							"branchname", b.branchname
						))
					FROM authorised_values_branches AS av_b
						LEFT JOIN branches AS b ON b.branchcode = av_b.branchcode
					WHERE av_id = authorised_values.id
				), '[]') AS branches,
				imageurl
			FROM authorised_values
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
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
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
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	public function post_authorised_values($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_authorised_values_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function post_authorised_values_single($data) {
		$values = [
			"category" => null,
			"authorised_value" => null,
			"lib" => null,
			"lib_opac" => null,
			"branches" => null,
			"imageurl" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

        $this->CheckArrayData("category", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("authorised_value", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("lib", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $lib_opac = $this->CheckArrayData("lib_opac", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		if ($lib_opac == null) { $values["lib_opac"] = $lib_opac; }
        $branches = $this->CheckArrayData("branches", $values, false, true, "a"); if ($this->bErrorOn) { return; }
		unset($values["branches"]);
        $imageurl = $this->CheckArrayData("imageurl", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		if ($imageurl == null) { $values["imageurl"] = $imageurl; }
		$cSql = <<<EOD
			INSERT INTO authorised_values (
				category, authorised_value, lib, lib_opac, imageurl
			) VALUES (
				:category, :authorised_value, :lib, :lib_opac, :imageurl
			)
			RETURNING id
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$id = $stmt->fetchColumn(0);
		#region 有無限定分館
		if ($branches != null && count($branches) > 0) {
			$values = []; $cSql = "";
			foreach ($branches as $key => $branch) {
				$values["av_id_{$key}"] = $id;
				$values["branchcode_{$key}"] = $branch["branchcode"];
				$cSql .= "\n INSERT INTO authorised_values_branches (av_id, branchcode) VALUES (:av_id_{$key}, :branchcode_{$key});";
			}
			$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$stmt->closeCursor();
		}
		#endregion
		return $id;
	}
	public function patch_authorised_values($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_authorised_values_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function patch_authorised_values_single($data) {
		$values = [
			"id" => null,
			"authorised_value" => null,
			"lib" => null,
			"lib_opac" => null,
			"branches" => null,
			"imageurl" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

        $id = $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		$values["id1"] = $id;

        $this->CheckArrayData("authorised_value", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("lib", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $lib_opac = $this->CheckArrayData("lib_opac", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		if ($lib_opac == null) { $values["lib_opac"] = $lib_opac; }
        $branches = $this->CheckArrayData("branches", $values, false, true, "a"); if ($this->bErrorOn) { return; }
		unset($values["branches"]);
        $imageurl = $this->CheckArrayData("imageurl", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		if ($imageurl == null) { $values["imageurl"] = $imageurl; }
		//
		$cSql = <<<EOD
			UPDATE authorised_values SET
				authorised_value = :authorised_value, lib = :lib, lib_opac = :lib_opac, imageurl = :imageurl
			WHERE id = :id;
			DELETE FROM authorised_values_branches WHERE av_id = :id1;
EOD;
		#region 有無限定分館
		if ($branches != null && count($branches) > 0) {
			foreach ($branches as $key => $branch) {
				$values["av_id_{$key}"] = $id;
				$values["branchcode_{$key}"] = $branch["branchcode"];
				$cSql .= "INSERT INTO authorised_values_branches (av_id, branchcode) VALUES (:av_id_{$key}, :branchcode_{$key});";
			}
		}
		#endregion
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();
		return true;
	}
	public function delete_authorised_values($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_authorised_values_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function delete_authorised_values_single($data) {
		$values = [
			"id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $id = $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$values["id1"] = $id;
		$cSql = <<<EOD
			DELETE FROM authorised_values_branches WHERE av_id = :id;
			DELETE FROM authorised_values WHERE id = :id1;
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			return true;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage); return;
		}
	}
	#region category
	public function get_authorised_values_category($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["category"]);
		#endregion

		$values = [
			"category" => null,
			"is_system" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"category" => " AND avc.category_name LIKE CONCAT('%', :category, '%')",
				"is_system" => " AND avc.is_system = :is_system",
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
				avc.category_name AS category, avcd.description, avc.is_system
			FROM authorised_value_categories AS avc
				LEFT JOIN (
					SELECT 'AR_CANCELLATION' AS category_name, '<p>Reasons why an article request might have been cancelled</p>' AS description
					UNION ALL VALUES
					('Asort1', '<p>Used for acquisitions statistical purposes</p>'),
					('Asort2', '<p>Used for acquisitions statistical purposes</p>'),
					('BOR_NOTES', '<p>Values for custom patron messages that appear on the circulation screen and the OPAC. The value in the description field should be the message text and is limited to 200 characters</p>'),
					('branches', '<p></p>'),
					('Bsort1', '<p>Values that can be entered to fill in the patron’s sort 1 field, that can be used for statistical purposes</p>'),
					('Bsort2', '<p>Values that can be entered to fill in the patron’s sort 2 field, that can be used for statistical purposes</p>'),
					('CAND', '<p></p>'),
					('CCODE', '<p>Collections (appear when cataloging and working with items)</p>'),
					('cn_source', '<p></p>'),
					('CONTROL_NUM_SEQUENCE', '<p></p>'),
					('COUNTRY', '<p>Used in UNIMARC 102 \$a</p>'),
					('DAMAGED', '<p>Descriptions for items marked as damaged (appears when cataloging and working with items)</p>'),
					('DEPARTMENT', '<p>Departments are required by and will be used in the Course Reserves module</p>'),
					('ERM_AGREEMENT_CLOSURE_REASON', '<p>Close reasons for agreements (e-resource management module)</p>'),
					('ERM_AGREEMENT_LICENSE_LOCATION', '<p>Locations of the licenses\' agreements (e-resource management module)</p>'),
					('ERM_AGREEMENT_LICENSE_STATUS', '<p>Statuses of the licenses\' agreements (e-resource management module)</p>'),
					('ERM_AGREEMENT_RENEWAL_PRIORITY', '<p>Renewal priorities of agreements (e-resource management module)</p>'),
					('ERM_AGREEMENT_STATUS', '<p>Statuses of agreements (e-resource management module)</p>'),
					('ERM_LICENSE_STATUS', '<p>Statuses of the licenses (e-resource management module)</p>'),
					('ERM_LICENSE_TYPE', '<p>Types of the licenses (e-resource management module)</p>'),
					('ERM_PACKAGE_CONTENT_TYPE', '<p>Content type of the packages (e-resource management module)</p>'),
					('ERM_PACKAGE_TYPE', '<p>Types of the packages (e-resource management module)</p>'),
					('ERM_TITLE_PUBLICATION_TYPE', '<p>Publication types of the titles (e-resource management module)</p>'),
					('ERM_USER_ROLES', '<p>Roles for users (e-resource management module)</p>'),
					('ETAT', '<p>Used in French UNIMARC installations in field 995 \$o to identify item status. Similar to NOT_LOAN</p>'),
					('HINGS_AS', '<p>General holdings: acquisition status designator :: This data element specifies acquisition status for the unit at the time of the holdings report.</p>'),
					('HINGS_C', '<p>General holdings: completeness designator</p>'),
					('HINGS_PF', '<p>Physical form designators</p>'),
					('HINGS_RD', '<p>General holdings: retention designator :: This data element specifies the retention policy for the unit at the time of the holdings report.</p>'),
					('HINGS_UT', '<p>General holdings: type of unit designator</p>'),
					('HOLD_CANCELLATION', '<p>Reasons why a hold might have been cancelled</p>'),
					('HSBND_FREQ', '<p>Frequencies used by the housebound module. They are displayed on the housebound tab in the patron account in staff.</p>'),
					('ILL_STATUS_ALIAS', '<p>ILL request status aliases used by the interlibrary loans module</p>'),
					('ITEMTYPECAT', '<p>Allows multiple Item types to be searched in a category. Categories can be entered into the Authorized value ITEMTYPECAT. To combine Item types to this category, enter this Search category to any Item types</p>'),
					('itemtypes', '<p></p>'),
					('LANG', '<p>ISO 639-2 standard language codes</p>'),
					('LOC', '<p>Shelving location (usually appears when adding or editing an item). LOC maps to items.location in the Koha database.</p>'),
					('LOST', '<p>Descriptions for the items marked as lost (appears when adding or editing an item)</p>'),
					('NOT_LOAN', '<p>Reasons why a title is not for loan</p>'),
					('OPAC_SUG', '<p>A list of reasons displayed in the suggestion form on the OPAC.</p>'),
					('ORDER_CANCELLATION_REASON', '<p>Reasons why an order might have been cancelled</p>'),
					('PAYMENT_TYPE', '<p>Populates a dropdown list of custom payment types when paying fines</p>'),
					('qualif', '<p>Function codes (author, editor, collaborator, etc.) used in UNIMARC 7XX $4 (French)</p>'),
					('RELTERMS', '<p>List of relator codes and terms according to <a target="_blank" href="https://www.loc.gov/marc/relators/">MARC code list for relators</a></p>'),
					('REPORT_GROUP', '<p>A way to sort and filter your reports, the default values in this category include the Koha modules (Accounts, Acquitisions, Catalog, Circulation, Patrons)</p>'),
					('REPORT_SUBGROUP', '<p>Can be used to further sort and filter your reports. This category is empty by default. Values here need to include the authorized value code from REPORT_GROUP in the Description (OPAC) field to link the subgroup to the appropriate group.</p>'),
					('RESTRICTED', '<p>Restricted status of an item</p>'),
					('ROADTYPE', '<p>Road types to be used in patron addresses</p>'),
					('SIP_MEDIA_TYPE', '<p>Used when creating or editing an item type to assign a SIP specific media type for devices like lockers and sorters.</p>'),
					('STACK', '<p>Shelving control number</p>'),
					('SUGGEST', '<p>List of patron suggestion reject or accept reasons (appears when managing suggestions)</p>'),
					('SUGGEST_FORMAT', '<p>List of Item types to display in a drop down menu on the Purchase suggestion form on the OPAC. When creating the authorized values for SUGGEST_FORMAT, enter a description into this form so it is visible on the OPAC to patrons.</p>'),
					('SUGGEST_STATUS', '<p>A list of additional custom status values for suggestions that can be used in addition to the default values.</p>'),
					('TERM', '<p>Terms to be used in Course Reserves module. Enter terms that will show in the drop down menu when setting up a Course reserve. (For example: Spring, Summer, Winter, Fall).</p>'),
					('TICKET_RESOLUTION', '<p>A list of custom resolution values for tickets that can be used in addition to the standard ""Resolved"".</p>'),
					('TICKET_STATUS', '<p>A list of custom status values for tickets that can be used in addition to the default values of ""New"" and ""Resolved"".</p>'),
					('UPLOAD', '<p>Categories to be assigned to file uploads. Without a category an upload is considered temporary and may be removed during automated cleanup.</p>'),
					('VENDOR_INTERFACE_TYPE', '<p>Values that can be entered to fill in the \'Vendor interface type\' field in the acquisitions module</p>'),
					('VENDOR_ISSUE_TYPE', '<p>Values that can be entered to fill in the \'Vendor issue type\' field in the acquisitions module</p>'),
					('VENDOR_TYPE', '<p>Values that can be entered to fill in the \'Vendor type\' field in the acquisitions module, that can be used for statistical purposes</p>'),
					('WITHDRAWN', '<p>Description of a withdrawn item (appears when adding or editing an item)</p>'),
					('YES_NO', '<p>A generic authorized value field that can be used anywhere you need a simple yes/no pull down menu.</p>')
				) AS avcd ON avcd.category_name = avc.category_name
			WHERE avc.category_name <> '' {$cWhere_Inner}
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

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
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
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	public function post_authorised_values_category($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_authorised_values_category_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function post_authorised_values_category_single($data) {
		$values = [
			"category" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $category = $this->CheckArrayData("category", $values, true, false, "c"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			INSERT INTO authorised_value_categories (
				category_name, is_system
			) VALUES (
				:category, 0
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			return true;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage); return;
		}
	}
	public function delete_authorised_values_category($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_authorised_values_category_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function delete_authorised_values_category_single($data) {
		$values = [
			"category" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $category = $this->CheckArrayData("category", $values, true, false, "c"); if ($this->bErrorOn) { return; }
		$oResponse = $this->get_authorised_values_category(["category" => $category]); if ($this->bErrorOn) { return; }
		if ($oResponse["total"] != 1) { $this->SetError("傳入的類別名稱不夠精準，未能對應到正確的資料."); return; }
		if ($oResponse["data"][0]["is_system"] == 1) { $this->SetError("系統類別不可刪除."); return; }

		$values["category1"] = $category; $values["category2"] = $category;
		$cSql = <<<EOD
			DELETE FROM authorised_values_branches WHERE av_id IN (SELECT id FROM authorised_values WHERE category = :category);
			DELETE FROM authorised_values WHERE  category = :category1;
			DELETE FROM authorised_value_categories WHERE category_name = :category2";
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			return true;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage); return;
		}
	}
	#endregion
	#endregion
	#region itemtypes
	public function get_itemtypes($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["itemtype"]);
		#endregion

		$values = [
			"itemtype" => null,
			"description" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"itemtype" => " AND itemtype LIKE CONCAT('%', :itemtype, '%')",
				"description" => " AND description LIKE CONCAT('%', :description, '%')",
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
				`itemtype`,
				`parent_type`,
				`description`,
				`rentalcharge`,
				`rentalcharge_daily`,
				`rentalcharge_daily_calendar`,
				`rentalcharge_hourly`,
				`rentalcharge_hourly_calendar`,
				`defaultreplacecost`,
				`processfee`,
				`notforloan`,
				`imageurl`,
				`summary`,
				`checkinmsg`,
				`checkinmsgtype`,
				`sip_media_type`,
				`hideinopac`,
				`searchcategory`,
				`automatic_checkin`,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"branchcode", it_b.branchcode,
							"branchname", b.branchname
						))
					FROM itemtypes_branches AS it_b
						LEFT JOIN branches AS b ON b.branchcode = it_b.branchcode
					WHERE it_b.itemtype = itemtypes.itemtype
				), '[]') AS branches
			FROM itemtypes
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
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute() || !$stmt_count->execute()) { return $this->GetDatabaseErrorMessage($stmt); }

		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		foreach ($aRows as $row_id => $row_value) {
			$aRows[$row_id]["branches"] = json_decode($row_value["branches"], true);
		}
		$result['data'] = $aRows;
		$result['total'] = $result_count;
		return $result;
	}
	public function post_itemtypes($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_itemtypes_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function post_itemtypes_single($data) {
		$values = [
			"itemtype" => null,
			"parent_type" => null,
			"description" => null,
			"rentalcharge" => null,
			"rentalcharge_daily" => null,
			"rentalcharge_daily_calendar" => null,
			"rentalcharge_hourly" => null,
			"rentalcharge_hourly_calendar" => null,
			"defaultreplacecost" => null,
			"processfee" => null,
			"notforloan" => null,
			"imageurl" => null,
			"summary" => null,
			"checkinmsg" => null,
			"checkinmsgtype" => null,
			"sip_media_type" => null,
			"hideinopac" => null,
			"searchcategory" => null,
			"automatic_checkin" => null,
			"branches" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $itemtype = $this->CheckArrayData("itemtype", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("rentalcharge_daily_calendar", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("rentalcharge_hourly_calendar", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $checkinmsgtype = $this->CheckArrayData("checkinmsgtype", $values, true, false, "c"); if ($this->bErrorOn) { return; }
		if ($checkinmsgtype != "alert" && $checkinmsgtype != "message") { $this->setError("{$checkinmsgtype}必須是 alert 或 message");}
        $this->CheckArrayData("hideinopac", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("automatic_checkin", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $branches = $this->CheckArrayData("branches", $values, false, true, "a"); if ($this->bErrorOn) { return; }
		unset($values["branches"]);
		$cSql = <<<EOD
			INSERT INTO itemtypes (
				`itemtype`,				`parent_type`,						`description`,
				`rentalcharge`,			`rentalcharge_daily`,				`rentalcharge_daily_calendar`,
				`rentalcharge_hourly`,	`rentalcharge_hourly_calendar`,		`defaultreplacecost`,
				`processfee`,			`notforloan`,						`imageurl`,
				`summary`,				`checkinmsg`,						`checkinmsgtype`,
				`sip_media_type`,		`hideinopac`,						`searchcategory`,
				`automatic_checkin`
			) VALUES (
				:itemtype,				:parent_type,						:description,
				:rentalcharge,			:rentalcharge_daily,				:rentalcharge_daily_calendar,
				:rentalcharge_hourly,	:rentalcharge_hourly_calendar,		:defaultreplacecost,
				:processfee,			:notforloan,						:imageurl,
				:summary,				:checkinmsg,						:checkinmsgtype,
				:sip_media_type,		:hideinopac,						:searchcategory,
				:automatic_checkin
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		#region 有無限定分館
		if ($branches != null && count($branches) > 0) {
			$values = []; $cSql = "";
			foreach ($branches as $key => $branch) {
				$values["itemtype_{$key}"] = $itemtype;
				$values["branchcode_{$key}"] = $branch["branchcode"];
				$cSql .= "\n INSERT INTO itemtypes_branches (itemtype, branchcode) VALUES (:itemtype_{$key}, :branchcode_{$key});";
			}
			$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$stmt->closeCursor();
		}
		#endregion
		return true;
	}
	public function patch_itemtypes($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_itemtypes_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function patch_itemtypes_single($data) {
		$values = [
			"itemtype" => null,
			"parent_type" => null,
			"description" => null,
			"rentalcharge" => null,
			"rentalcharge_daily" => null,
			"rentalcharge_daily_calendar" => null,
			"rentalcharge_hourly" => null,
			"rentalcharge_hourly_calendar" => null,
			"defaultreplacecost" => null,
			"processfee" => null,
			"notforloan" => null,
			"imageurl" => null,
			"summary" => null,
			"checkinmsg" => null,
			"checkinmsgtype" => null,
			"sip_media_type" => null,
			"hideinopac" => null,
			"searchcategory" => null,
			"automatic_checkin" => null,
			"branches" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $itemtype = $this->CheckArrayData("itemtype", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("rentalcharge_daily_calendar", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("rentalcharge_hourly_calendar", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $checkinmsgtype = $this->CheckArrayData("checkinmsgtype", $values, true, false, "c"); if ($this->bErrorOn) { return; }
		if ($checkinmsgtype != "alert" && $checkinmsgtype != "message") { $this->setError("{$checkinmsgtype}必須是 alert 或 message");}
        $this->CheckArrayData("hideinopac", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $this->CheckArrayData("automatic_checkin", $values, true, false, "b"); if ($this->bErrorOn) { return; }
        $branches = $this->CheckArrayData("branches", $values, false, true, "a"); if ($this->bErrorOn) { return; }
		unset($values["branches"]);
		$cSql = <<<EOD
			UPDATE itemtypes SET
				`parent_type` = :parent_type,
				`description` = :description,
				`rentalcharge` = :rentalcharge,
				`rentalcharge_daily` = :rentalcharge_daily,
				`rentalcharge_daily_calendar` = :rentalcharge_daily_calendar,
				`rentalcharge_hourly` = :rentalcharge_hourly,
				`rentalcharge_hourly_calendar` = :rentalcharge_hourly_calendar,
				`defaultreplacecost` = :defaultreplacecost,
				`processfee` = :processfee,
				`notforloan` = :notforloan,
				`imageurl` = :imageurl,
				`summary` = :summary,
				`checkinmsg` = :checkinmsg,
				`checkinmsgtype` = :checkinmsgtype,
				`sip_media_type` = :sip_media_type,
				`hideinopac` = :hideinopac,
				`searchcategory` = :searchcategory,
				`automatic_checkin` = :automatic_checkin
			WHERE `itemtype` = :itemtype;
			DELETE FROM itemtypes_branches WHERE `itemtype` = :itemtype;
EOD;
		#region 有無限定分館
		if ($branches != null && count($branches) > 0) {
			foreach ($branches as $key => $branch) {
				$values["itemtype_{$key}"] = $itemtype;
				$values["branchcode_{$key}"] = $branch["branchcode"];
				$cSql .= "INSERT INTO itemtypes_branches (itemtype, branchcode) VALUES (:itemtype_{$key}, :branchcode_{$key});";
			}
		}
		#endregion
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();
		return true;
	}
	public function delete_itemtypes($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_itemtypes_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function delete_itemtypes_single($data) {
		$values = [
			"itemtype" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $itemtype = $this->CheckArrayData("itemtype", $values, true, false, "c"); if ($this->bErrorOn) { return; }
		$values["itemtype1"] = $itemtype;

		$cSql = <<<EOD
			DELETE FROM itemtypes_branches WHERE itemtype = :itemtype;
			DELETE FROM itemtypes WHERE itemtype = :itemtype1;
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		return true;
	}
	#endregion
	#region checkin
	public function post_checkin($data) {
		foreach ($data as $key => $value) {
			$callBack = $this->post_checkin_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	public function post_checkin_single($data) {
		$values = [
			"borrowernumber" => null,
			"itemnumber" => null,
			"branchcode" => null,
			"exempt_fine" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

        $borrowernumber = $this->CheckArrayData("borrowernumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        $itemnumber = $this->CheckArrayData("itemnumber", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        $branchcode = $this->CheckArrayData("branchcode", $values, true, false, "c"); if ($this->bErrorOn) { return; }
        $exempt_fine = $this->CheckArrayData("exempt_fine", $values, false, true, "b"); if ($this->bErrorOn) { return; }
		if ($exempt_fine == null) { $exempt_fine = false; }
		$values["exempt_fine"] = $exempt_fine;		//免除罰金
		$values["op"] = "cud-checkin";
		$callBack = $this->callKohaApi_svc("post", "/checkin", $values);
		return $callBack;
	}

	#endregion
	#region checkout
	public function get_checkout($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["issue_date_due DESC"]);
		#endregion

		#region 處理傳入參數
		$values = [
			"borrowernumber" => null,
			"cardnumber" => null,
			"itemnumber" => null,
			"barcode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		//if (count($values) == 0) { $this->SetError("讀取借書資料失敗. 沒有相關參數."); return; }
		$date_due_start = array_key_exists("date_due_start", $values) ? $values["date_due_start"] : null;
		$date_due_end = array_key_exists("date_due_end", $values) ? $values["date_due_end"] : null;
		if ($date_due_start != null && $date_due_end != null) {
			$values["date_due_start"] = $date_due_start . " 00:00:00";
			$values["date_due_end"] = $date_due_end . " 23:59:59";
		}
		#endregion

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND c.borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(p.cardnumber) = UPPER(:cardnumber)",
				"itemnumber" => " AND c.itemnumber = :itemnumber",
				"barcode" => " AND i.barcode = :barcode",
				"date_due_start" => " AND c.date_due >= :date_due_start",
				"date_due_end" => " AND c.date_due <= date_due_end",
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion
		//下面的程式碼是為了讓自己可以查詢自己的借書資料，但又會影響上面的Where，因此要放在 Where 判斷之後
		if (!array_key_exists("borrowernumber", $values)) {
			$values["borrowernumber"] = -1;
		}
		if (!array_key_exists("cardnumber", $values) || $values["cardnumber"] == "") {
			$values["cardnumber"] = "~";
		}


		// 搜尋框搜尋
        $select_condition = "";
        $custom_filter_bind_values = [
			"bib_title" => null,
			"item_itype_description" => null,
			"issue_id" => null,
			"issue_date_due" => null,
			"item_location" => null,
			"issue_issuedate" => null,
			"issue_branchname" => null,
			"issue_charge" => null,
			"issue_accountlines_amount" => null,
        ];
        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $values = $custom_filter_return['bind_values'];


		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
            WITH h AS (
				SELECT
					r.reserve_id,
					r.reservedate AS reserve_reservedate,
					r.suspend AS reserve_suspend,
					r.biblionumber AS bib_biblionumber,
					r.itemnumber AS reserve_itemnumber,
					r.`found` AS reserve_found_id,
					CASE WHEN r.found = 't' THEN '傳輸中' WHEN r.found = 'w' THEN '已到館' ELSE '' END AS reserve_found_name,
					r.expirationdate AS reserve_expirationdate,
					r.branchcode AS reserve_branchcode, b.branchname AS reserve_branchname,
					r.priority AS reserve_priority,
					h.itemnumber AS hold_itemnumber,
					p.borrowernumber AS borrower_borrowernumber,
					UPPER(p.cardnumber) AS borrower_cardnumber,
					CASE WHEN (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber)) THEN true ELSE false END AS selfhold
				FROM reserves AS r
					INNER JOIN branches AS b ON b.branchcode = r.branchcode
					INNER JOIN borrowers AS p ON p.borrowernumber = r.borrowernumber
					LEFT JOIN hold_fill_targets AS h ON h.reserve_id = r.reserve_id
				WHERE	:borrowernumber IN (p.borrowernumber, -1)
					AND UPPER(:cardnumber) IN (UPPER(p.cardnumber), '~' )
			)
			SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
			    i.barcode AS item_barcode,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemlost AS item_itemlost,
			    i.onloan AS item_onloan,	/* 應還日 */
			    i.datelastborrowed AS item_datelastborrowed,
			    i.itemcallnumber AS item_itemcallnumber,
			    i.`location` AS item_location,
			    i.copynumber AS item_copynumber,
				IFNULL(i.replacementprice, 0) AS item_replacement_price,
				c.issue_id,
			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    CASE WHEN DATEDIFF(c.date_due, now()) < 0 THEN 0 ELSE DATEDIFF(c.date_due, now()) END AS issue_days_due,
			    CASE WHEN DATEDIFF(now(), c.date_due) < 0 THEN 0 ELSE DATEDIFF(now(), c.date_due) END AS issue_days_over,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
			    c.branchcode AS issue_branchcode,
			    branch_issues.branchname AS issue_branchname,
			    CASE
					WHEN c.borrowernumber = :borrowernumber THEN true
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN true
					ELSE false
				END AS issue_selfcheckout,
				CASE
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					WHEN c.borrowernumber = :borrowernumber THEN false
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false
					WHEN (SELECT COUNT(*) FROM h WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 有人預約就無法外借 */
					ELSE true
				END AS issue_cancheckout,
				a.accountlines_id AS issue_accountlines_id,
				IFNULL((SELECT SUM(amount) FROM account_offsets WHERE debit_id = a.accountlines_id), 0) AS issue_charge,
				IFNULL(a.amount, 0) AS issue_accountlines_amount,
				IFNULL(a.amountoutstanding, 0) AS issue_accountlines_amountoutstanding,
				a.debit_type_code AS issue_accountlines_debitType_code,
				adt.description AS issue_accountlines_debitType_description,
				c.renewals_count AS issue_renewals_count,
				c.unseen_renewals AS issue_renewals_unseen,
				(
			    	SELECT
			    		CAST(rule_value AS SIGNED)
			    	FROM circulation_rules
			    	WHERE rule_name = 'renewalsallowed'
			    		AND IFNULL(branchcode, c.branchcode) = c.branchcode
			    		AND IFNULL(categorycode, p.categorycode) = p.categorycode
			    		AND IFNULL(itemtype, i.itype) = i.itype
			    	ORDER BY CASE
			    				WHEN branchcode = c.branchcode AND categorycode = p.categorycode AND itemtype = i.itype THEN 1
			    				WHEN branchcode = c.branchcode AND categorycode = p.categorycode AND itemtype IS NULL THEN 2
			    				WHEN branchcode = c.branchcode AND categorycode IS NULL AND itemtype IS NULL THEN 3
			    				ELSE 4
			    			END
			    	limit 1
			    ) AS issue_allow_renewals_count,
			    IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"reserve_id", reserve_id,
							"reserve_reservedate", reserve_reservedate,
							"reserve_suspend", reserve_suspend,
							"reserve_found_id", reserve_found_id,
							"reserve_found_name", reserve_found_name,
							"reserve_expirationdate", reserve_expirationdate,
							"reserve_branchcode", reserve_branchcode,
							"reserve_branchname", reserve_branchname,
							"reserve_priority", reserve_priority,
							"borrower_borrowernumber", borrower_borrowernumber,
							"borrower_cardnumber", borrower_cardnumber
						))
					FROM h
					WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)
				), '[]') AS reserve,
				CASE
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN true
					ELSE false
				END AS reserve_selfhold,
				CASE
					WHEN c.issue_id IS NOT NULL THEN false	/* 此書借出中 */
					WHEN c.issue_id IS NULL THEN false	/* 尚未借出不能預約 */
					WHEN c.borrowernumber = :borrowernumber THEN false /* 自己已借出了不能預約 */
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false /* 自己已借出了不能預約 */
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 已經預約了 */
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					ELSE true
				END AS reserve_canhold,
				ic.items_count
		    FROM biblio AS b
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
		    	INNER JOIN items AS i ON i.biblionumber = b.biblionumber
				LEFT JOIN branches AS branch_item_home ON branch_item_home.branchcode = i.homebranch
				LEFT JOIN branches AS branch_item_holding ON branch_item_holding.branchcode = i.holdingbranch
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype
		    	INNER JOIN issues AS c ON c.itemnumber = i.itemnumber
		    	INNER JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
				LEFT JOIN branches AS branch_issues ON branch_issues.branchcode = c.branchcode
				LEFT JOIN accountlines AS a ON a.issue_id = c.issue_id AND (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber))
		    	LEFT JOIN account_debit_types AS adt ON adt.code = a.debit_type_code
			WHERE TRUE
					{$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dtInner
			WHERE TRUE {$select_condition}
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
	public function post_checkout($data) {
		foreach ($data as $key => $value) {
			$library = new library();
            $borrowernumber = $library->get_borrower_map2user_by_borrowernumber([$value]);
			$borrower_info = [];
			foreach($borrowernumber as $bKey => $bValue) $borrower_info = $bValue;
			$callBack = $this->post_checkout_single($value, $borrower_info["api_borrower_userid"], $borrower_info["api_borrower_password"]); 
			if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	public function post_checkout_single($data, $userid, $password) {
		$callBack = new stdClass(); $callBack->status = "success"; $callBack->message = ""; $callBack->data = "";

		$values = [
			"borrowernumber" => null,
			"itemnumber" => null,
			"due_date" => null,
			"note" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$cKey1 = "patron_id";
		$cKey2 = "borrowernumber";
		if (!array_key_exists($cKey2, $values)) { $this->SetError("必須包含 {$cKey2}"); return; }
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$cKey1 = "item_id";
		$cKey2 = "itemnumber";
		if (!array_key_exists($cKey2, $values)) { $this->SetError("必須包含 {$cKey2}"); return; }
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		// 因為指定了 due_date 會無效，所以先存下來，到時候再修改
		$temp_due_date = $values["due_date"];
		unset($values["due_date"]);
		//koha api 怪怪的，指定了 due_date 但無效，還是依流通規則，所以硬用 update 來搞
		$callBack = $this->callKohaApi("post", "/checkouts", $values, "application/json", [], true, $userid, $password);
		$values["due_date"] = $temp_due_date;
		if ($callBack->status == "failed") { return $callBack; }
		$issue_id = $callBack->data->checkout_id;
		$due_date = $this->CheckArrayData("due_date", $values, false, false, "dt"); if ($this->bErrorOn) { return; }
		if ($due_date != null) {
			$due_date = $due_date->format('Y-m-d H:i:s');
			$callBack = $this->setCheckoutDue_Date($issue_id, $due_date);
			if ($callBack->status == "failed") { return $callBack->data; }
			// $callBack->data->due_date = $due_date;
		}
		return $callBack;
	}
	public function post_checkout_renew($data) {
		foreach ($data as $key => $value) {
			$callBack = $this->post_checkout_renew_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_checkout_renew_single($data) {
		$callBack = new stdClass(); $callBack->status = "success"; $callBack->message = ""; $callBack->data = "";

		$values = [
			"issue_id" => null,
			"due_date" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$issue_id = $this->CheckArrayData("issue_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		$callBack = $this->callKohaApi("post", "/checkouts/" . $issue_id . "/renewal", []);
		if ($callBack->status == "failed") { return $callBack; }

		$due_date = $this->CheckArrayData("due_date", $values, false, false, "dt"); if ($this->bErrorOn) { return; }
		if ($due_date != null) {
			$callBack = $this->setCheckoutDue_Date($issue_id, $due_date->format('Y-m-d H:i:s'));
			if ($callBack->status == "failed") { return $callBack->data; }
		}
		return $callBack;
	}
	private function setCheckoutDue_Date($issue_id, $due_date) {
		$cSql = <<<EOD
			UPDATE issues SET
				date_due = :due_date
			WHERE issue_id = :issue_id
EOD;
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["issue_id" => $issue_id, "due_date" => $due_date]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		return $this->CallBack(true, null, true);
	}
	public function get_checkout_history($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["issue_date_due DESC"]);
		#endregion

		#region 處理傳入參數
		$values = [
			"borrowernumber" => null,
			"cardnumber" => null,
			"biblionumber" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		//if (count($values) == 0) { $this->SetError("讀取借書資料失敗. 沒有相關參數."); return; }
		#endregion

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND c.borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(p.cardnumber) = UPPER(:cardnumber)",
				"biblionumber" => " AND b.biblionumber = :biblionumber"
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion


		// 搜尋框搜尋
        $select_condition = "";
        $custom_filter_bind_values = [
            "issue_borrowernumber" => null,
			"bib_title" => null,
			"item_itype_description" => null,
			"issue_issuedate" => null,
        ];
        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $values = $custom_filter_return['bind_values'];


		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
            SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
			    i.barcode AS item_barcode,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemcallnumber AS item_itemcallnumber,
			    i.copynumber AS item_copynumber,
			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
				ic.items_count
		    FROM biblio AS b
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
		    	INNER JOIN items AS i ON i.biblionumber = b.biblionumber
				LEFT JOIN branches AS branch_item_home ON branch_item_home.branchcode = i.homebranch
				LEFT JOIN branches AS branch_item_holding ON branch_item_holding.branchcode = i.holdingbranch
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype
		    	INNER JOIN old_issues AS c ON c.itemnumber = i.itemnumber
		    	INNER JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
		    WHERE TRUE
					{$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dtInner
			WHERE TRUE {$select_condition}
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
			return $this->GetDatabaseErrorMessage($stmt);
		}
	}
	public function delete_checkout_history($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_checkout_history_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_checkout_history_single($data) {
		$borrowernumber = $this->CheckArrayData("borrowernumber", $data, true, false, "i"); if ($this->bErrorOn) { return; }
		$cSql = <<<EOD
			DELETE FROM old_issues
			WHERE borrowernumber = :borrowernumber
EOD;
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["borrowernumber" => $borrowernumber]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		return $this->CallBack(true, null, true);
	}
	public function get_checkout_renew_history($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["issue_date_due DESC"]);
		#endregion

		#region 處理傳入參數
		$values = [
			"borrowernumber" => null,
			"cardnumber" => null,
			"biblionumber" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		//if (count($values) == 0) { $this->SetError("讀取借書資料失敗. 沒有相關參數."); return; }
		#endregion

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND c.borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(p.cardnumber) = UPPER(:cardnumber)",
				"biblionumber" => " AND b.biblionumber = :biblionumber"
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
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
			    i.barcode AS item_barcode,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemcallnumber AS item_itemcallnumber,
			    i.copynumber AS item_copynumber,

			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
			    cr.checkout_renewal_count,
			    (
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"renewal_id", renewal_id,
							"renewer_id", renewer_id,
							"timestamp", `timestamp`
						))
					FROM checkout_renewals
					WHERE checkout_id = cr.checkout_id
				) AS renewals,
				ic.items_count
			FROM (
				SELECT
					checkout_id, COUNT(*) AS checkout_renewal_count
				FROM checkout_renewals
				GROUP BY checkout_id
			) AS cr
				INNER JOIN (
					SELECT issue_id, borrowernumber, itemnumber, issuedate, date_due
					FROM issues
					UNION ALL
					SELECT issue_id, borrowernumber, itemnumber, issuedate, date_due
					FROM old_issues
				) AS c ON c.issue_id = cr.checkout_id
				INNER JOIN items AS i ON i.itemnumber = c.itemnumber
				INNER JOIN biblio AS b ON b.biblionumber = i.biblionumber

				INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber

				LEFT JOIN branches AS branch_item_home ON branch_item_home.branchcode = i.homebranch
				LEFT JOIN branches AS branch_item_holding ON branch_item_holding.branchcode = i.holdingbranch
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype

				INNER JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
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
			return $this->GetDatabaseErrorMessage($stmt);
		}
	}
	public function delete_checkout_renew_history($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_checkout_renew_history_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_checkout_renew_history_single($data) {
		$borrowernumber = $this->CheckArrayData("borrowernumber", $data, true, false, "i"); if ($this->bErrorOn) { return; }
		$cSql = <<<EOD
			DELETE cr
			FROM checkout_renewals AS cr
				INNER JOIN old_issues AS c ON c.issue_id = cr.checkout_id
			WHERE c.borrowernumber = :borrowernumber
EOD;
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["borrowernumber" => $borrowernumber]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		return $this->CallBack(true, null, true);
	}

	public function get_checkout_overdue($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["issue_date_due DESC"]);
		#endregion

		#region 處理傳入參數
		$values = [
			"borrowernumber" => null,
			"cardnumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (count($values) == 0) {
			$this->SetError("讀取逾期資料失敗. 沒有相關參數.");
			return;
		}

		if (!array_key_exists("borrowernumber", $values)) {
			$values["borrowernumber"] = -1;
		}
		if (!array_key_exists("cardnumber", $values) || $values["cardnumber"] == "") {
			$values["cardnumber"] = "~";
		}
		#endregion

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND c.borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(p.cardnumber) = UPPER(:cardnumber)"
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
            WITH h AS (
				SELECT
					r.reserve_id,
					r.reservedate AS reserve_reservedate,
					r.suspend AS reserve_suspend,
					r.biblionumber AS bib_biblionumber,
					r.itemnumber AS reserve_itemnumber,
					r.`found` AS reserve_found_id,
					CASE WHEN r.found = 't' THEN '傳輸中' WHEN r.found = 'w' THEN '已到館' ELSE '' END AS reserve_found_name,
					r.expirationdate AS reserve_expirationdate,
					r.branchcode AS reserve_branchcode, b.branchname AS reserve_branchname,
					r.priority AS reserve_priority,
					h.itemnumber AS hold_itemnumber,
					p.borrowernumber AS borrower_borrowernumber,
					UPPER(p.cardnumber) AS borrower_cardnumber,
					CASE WHEN (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber)) THEN true ELSE false END AS selfhold
				FROM reserves AS r
					INNER JOIN branches AS b ON b.branchcode = r.branchcode
					INNER JOIN borrowers AS p ON p.borrowernumber = r.borrowernumber
					LEFT JOIN hold_fill_targets AS h ON h.reserve_id = r.reserve_id
				WHERE :borrowernumber IN (p.borrowernumber, -1) AND UPPER(:cardnumber) IN (UPPER(p.cardnumber), '~' )
			)
			SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
			    i.barcode AS item_barcode,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemlost AS item_itemlost,
			    i.onloan AS item_onloan,	/* 應還日 */
			    i.datelastborrowed AS item_datelastborrowed,
			    i.itemcallnumber AS item_itemcallnumber,
			    i.`location` AS item_location,
			    i.copynumber AS item_copynumber,
			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    CASE WHEN DATEDIFF(c.date_due, now()) < 0 THEN 0 ELSE DATEDIFF(c.date_due, now()) END AS issue_days_due,
			    CASE WHEN DATEDIFF(now(), c.date_due) < 0 THEN 0 ELSE DATEDIFF(now(), c.date_due) END AS issue_days_over,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
			    CASE
					WHEN c.borrowernumber = :borrowernumber THEN true
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN true
					ELSE false
				END AS issue_selfcheckout,
				CASE
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					WHEN c.borrowernumber = :borrowernumber THEN false
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false
					WHEN (SELECT COUNT(*) FROM h WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 有人預約就無法外借 */
					ELSE true
				END AS issue_cancheckout,
			    IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"reserve_id", reserve_id,
							"reserve_reservedate", reserve_reservedate,
							"reserve_suspend", reserve_suspend,
							"reserve_found_id", reserve_found_id,
							"reserve_found_name", reserve_found_name,
							"reserve_expirationdate", reserve_expirationdate,
							"reserve_branchcode", reserve_branchcode,
							"reserve_branchname", reserve_branchname,
							"reserve_priority", reserve_priority,
							"borrower_borrowernumber", borrower_borrowernumber,
							"borrower_cardnumber", borrower_cardnumber
						))
					FROM h
					WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)
				), '[]') AS reserve,
				CASE
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN true
					ELSE false
				END AS reserve_selfhold,
				CASE
					WHEN c.issue_id IS NULL THEN false	/* 尚未借出不能預約 */
					WHEN c.borrowernumber = :borrowernumber THEN false /* 自己已借出了不能預約 */
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false /* 自己已借出了不能預約 */
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 已經預約了 */
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					ELSE true
				END AS reserve_canhold,
				ic.items_count
		    FROM biblio AS b
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
		    	INNER JOIN items AS i ON i.biblionumber = b.biblionumberbiblionumber
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype
		    	INNER JOIN issues AS c ON c.itemnumber = i.itemnumber AND c.date_due < NOW()
		    	INNER JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
				WHERE TRUE
					{$cWhere_Inner}
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
	public function get_checkout_statistic_top100($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		// if (count($values) == 0) {
		// 	$this->SetError("沒有相關參數.");
		// 	return;
		// }

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = 'ORDER BY checkout_count DESC';

		$sql_default_inside = <<<EOD
            WITH dt AS (
				SELECT
					b2i.bib_record_id AS bib_id,
					checkout_total AS checkout_count
				FROM koha_view.item_record
					INNER JOIN koha_view.bib_record_item_record_link AS b2i ON b2i.item_record_id = item_record.record_id
				ORDER BY checkout_count DESC
				limit 100
			)
			SELECT
				b.id AS bib_id, b.record_num AS bib_record_num,
				bp.best_title,
				bp.best_author,
				dt.checkout_count
			FROM dt
				INNER JOIN koha_view.bib_view AS b ON b.id = dt.bib_id
				INNER JOIN koha_view.bib_record_property bp on bp.bib_record_id = b.id
EOD;

		$sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                        FROM (
                            {$sql_default_inside}
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

		$stmt = $this->db_koha->prepare($sql);
		$stmt_count = $this->db_koha->prepare($sql_count);
		if ($stmt->execute($values) && $stmt_count->execute($values_count)) {
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
			$this->SetError($oInfo[2]);
			return ["status" => "failed"];
		}
	}
	public function get_checkout_statistic_pcode3($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			"checkout_start_time" => null,
			"checkout_end_time" => null,
			"fromHistory" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		$fromHistory = false;
		if (array_key_exists("fromHistory", $values)) {
			$fromHistory = $values["fromHistory"] === "true";
			unset($values["fromHistory"]);
		}
		if (count($values) == 0) {
			$this->SetError("沒有相關參數.");
			return;
		}
		$checkoutTable = $fromHistory ? "item_circ_history" : "checkout";
		$checkoutPatronField = $fromHistory ? "patron_record_metadata_id" : "patron_record_id";

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = 'ORDER BY value DESC';

		$sql_default_inside = <<<EOD
            WITH dt AS (
				SELECT
					p.pcode3,
					COUNT(*) AS checkout_count
				FROM koha_view.{$checkoutTable} AS checkout
					INNER JOIN koha_view.patron_view AS p ON p.id = checkout.{$checkoutPatronField}
				WHERE checkout.checkout_gmt BETWEEN :checkout_start_time AND :checkout_end_time
				GROUP BY p.pcode3
			)
			SELECT
				dfpn3.name AS type,
				dt.checkout_count AS value
			FROM dt
				INNER JOIN koha_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
				INNER JOIN koha_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = dt.pcode3::varchar
				INNER JOIN koha_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4
EOD;

		$sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                        FROM (
                            {$sql_default_inside}
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

		$stmt = $this->db_koha->prepare($sql);
		$stmt_count = $this->db_koha->prepare($sql_count);
		if ($stmt->execute($values) && $stmt_count->execute($values_count)) {
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
			$this->SetError($oInfo[2]);
			return ["status" => "failed"];
		}
	}
	public function get_checkout_statistic_ptype($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			"checkout_start_time" => null,
			"checkout_end_time" => null,
			"fromHistory" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		$fromHistory = false;
		if (array_key_exists("fromHistory", $values)) {
			$fromHistory = $values["fromHistory"] === "true";
			unset($values["fromHistory"]);
		}
		if (count($values) == 0) {
			$this->SetError("沒有相關參數.");
			return;
		}
		$checkoutTable = $fromHistory ? "item_circ_history" : "checkout";
		$checkoutPatronField = $fromHistory ? "patron_record_metadata_id" : "patron_record_id";

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = 'ORDER BY value DESC';

		$sql_default_inside = <<<EOD
            WITH dt AS (
				SELECT
					p.ptype_code,
					COUNT(*) AS checkout_count
				FROM koha_view.{$checkoutTable} AS checkout
					INNER JOIN koha_view.patron_view AS p ON p.id = checkout.{$checkoutPatronField}
				WHERE checkout.checkout_gmt BETWEEN :checkout_start_time AND :checkout_end_time
				GROUP BY p.ptype_code
			)
			SELECT
				ppn.description AS type,
				dt.checkout_count as value
			FROM dt
				INNER JOIN koha_view.ptype_property AS pp ON pp.value = dt.ptype_code
				INNER JOIN koha_view.ptype_property_name AS ppn ON ppn.ptype_id = pp.id AND ppn.iii_language_id = 4
EOD;

		$sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                        FROM (
                            {$sql_default_inside}
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

		$stmt = $this->db_koha->prepare($sql);
		$stmt_count = $this->db_koha->prepare($sql_count);
		if ($stmt->execute($values) && $stmt_count->execute($values_count)) {
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
			$this->SetError($oInfo[2]);
			return ["status" => "failed"];
		}
	}
	#endregion
	#region hold
	public function get_hold($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["bib_biblionumber"]);
		#endregion

		#region 處理傳入參數
		$values = [
			"biblionumber" => null,
			"borrowernumber" => null,
			"cardnumber" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		//if (count($values) == 0) { $this->SetError("讀取預約書資料失敗. 沒有相關參數."); return; }
		//下面的程式碼是為了讓自己可以查詢自己的借書資料，但又會影響上面的Where，因此要放在 Where 判斷之後
		if (!array_key_exists("borrowernumber", $values)) {
			$values["borrowernumber"] = -1;
		}
		if (!array_key_exists("cardnumber", $values) || $values["cardnumber"] == "") {
			$values["cardnumber"] = "~";
		}
		#endregion

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND :borrowernumber IN (p.borrowernumber, -1)",
				"cardnumber" => " AND UPPER(:cardnumber) IN (UPPER(p.cardnumber), '~' )",
				"biblionumber" => " AND r.biblionumber = :biblionumber",
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
            WITH h AS (
				SELECT
					r.reserve_id,
					r.reservedate AS reserve_reservedate,
					r.suspend AS reserve_suspend,
					r.biblionumber AS bib_biblionumber,
					r.itemnumber AS reserve_itemnumber,
					r.`found` AS reserve_found_id,
					CASE WHEN r.found = 't' THEN '傳輸中' WHEN r.found = 'w' THEN '已到館' ELSE '' END AS reserve_found_name,
					r.expirationdate AS reserve_expirationdate,
					r.branchcode AS reserve_branchcode, b.branchname AS reserve_branchname,
					r.priority AS reserve_priority,
					h.itemnumber AS hold_itemnumber,
					p.borrowernumber AS borrower_borrowernumber,
					UPPER(p.cardnumber) AS borrower_cardnumber,
					CASE WHEN (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber)) THEN true ELSE false END AS selfhold
				FROM reserves AS r
					INNER JOIN branches AS b ON b.branchcode = r.branchcode
					INNER JOIN borrowers AS p ON p.borrowernumber = r.borrowernumber
					LEFT JOIN hold_fill_targets AS h ON h.reserve_id = r.reserve_id
				WHERE TRUE {$cWhere_Inner}
			)
			SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
			    i.barcode AS item_barcode,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemlost AS item_itemlost,
			    i.onloan AS item_onloan,	/* 應還日 */
			    i.datelastborrowed AS item_datelastborrowed,
			    i.itemcallnumber AS item_itemcallnumber,
			    i.`location` AS item_location,
			    i.copynumber AS item_copynumber,
			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    CASE WHEN DATEDIFF(c.date_due, now()) < 0 THEN 0 ELSE DATEDIFF(c.date_due, now()) END AS issue_days_due,
			    CASE WHEN DATEDIFF(now(), c.date_due) < 0 THEN 0 ELSE DATEDIFF(now(), c.date_due) END AS issue_days_over,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
			    CASE
					WHEN c.borrowernumber = :borrowernumber THEN true
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN true
					ELSE false
				END AS issue_selfcheckout,
				CASE
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					WHEN c.borrowernumber = :borrowernumber THEN false
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false
					WHEN (SELECT COUNT(*) FROM h WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 有人預約就無法外借 */
					ELSE true
				END AS issue_cancheckout,
			    IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"reserve_id", reserve_id,
							"reserve_reservedate", reserve_reservedate,
							"reserve_suspend", reserve_suspend,
							"reserve_found_id", reserve_found_id,
							"reserve_found_name", reserve_found_name,
							"reserve_expirationdate", reserve_expirationdate,
							"reserve_branchcode", reserve_branchcode,
							"reserve_branchname", reserve_branchname,
							"reserve_priority", reserve_priority,
							"borrower_borrowernumber", borrower_borrowernumber,
							"borrower_cardnumber", borrower_cardnumber
						))
					FROM h AS h2
					WHERE h2.reserve_id = h.reserve_id
				), '[]') AS reserve,
				CASE
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN true
					ELSE false
				END AS reserve_selfhold,
				CASE
					WHEN c.issue_id IS NULL THEN false	/* 尚未借出不能預約 */
					WHEN c.borrowernumber = :borrowernumber THEN false /* 自己已借出了不能預約 */
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false /* 自己已借出了不能預約 */
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 已經預約了 */
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					ELSE true
				END AS reserve_canhold,
				ic.items_count
		    FROM h
		    	INNER JOIN biblio AS b ON b.biblionumber = h.bib_biblionumber
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
		    	LEFT JOIN items AS i ON i.biblionumber = b.biblionumber AND i.itemnumber = h.reserve_itemnumber
				LEFT JOIN branches AS branch_item_home ON branch_item_home.branchcode = i.homebranch
				LEFT JOIN branches AS branch_item_holding ON branch_item_holding.branchcode = i.holdingbranch
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype
		    	LEFT JOIN issues AS c ON c.itemnumber = i.itemnumber
		    	LEFT JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
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
	public function get_hold_arrive($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["bib_biblionumber"]);
		#endregion

		#region 處理傳入參數
		$values = [
			"borrowernumber" => null,
			"cardnumber" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (count($values) == 0) {
			$this->SetError("讀取預約書資料失敗. 沒有相關參數.");
			return;
		}
		#endregion

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"borrowernumber" => " AND h.borrower_borrowernumber = :borrowernumber",
				"cardnumber" => " AND UPPER(h.borrower_cardnumber) = UPPER(:cardnumber)"
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
            WITH h AS (
				SELECT
					r.reserve_id,
					r.reservedate AS reserve_reservedate,
					r.suspend AS reserve_suspend,
					r.biblionumber AS bib_biblionumber,
					r.itemnumber AS reserve_itemnumber,
					r.`found` AS reserve_found_id,
					CASE WHEN r.found = 't' THEN '傳輸中' WHEN r.found = 'w' THEN '已到館' ELSE '' END AS reserve_found_name,
					r.expirationdate AS reserve_expirationdate,
					r.branchcode AS reserve_branchcode, b.branchname AS reserve_branchname,
					r.priority AS reserve_priority,
					h.itemnumber AS hold_itemnumber,
					p.borrowernumber AS borrower_borrowernumber,
					UPPER(p.cardnumber) AS borrower_cardnumber,
					CASE WHEN (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber)) THEN true ELSE false END AS selfhold
				FROM reserves AS r
					INNER JOIN branches AS b ON b.branchcode = r.branchcode
					INNER JOIN borrowers AS p ON p.borrowernumber = r.borrowernumber
					LEFT JOIN hold_fill_targets AS h ON h.reserve_id = r.reserve_id
				WHERE	:borrowernumber IN (p.borrowernumber, -1)
					AND UPPER(:cardnumber) IN (UPPER(p.cardnumber), '~' )
					AND r.`found` = 'w'
			)
			SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield[@code="c"]')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
			    i.barcode AS item_barcode,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemlost AS item_itemlost,
			    i.onloan AS item_onloan,	/* 應還日 */
			    i.datelastborrowed AS item_datelastborrowed,
			    i.itemcallnumber AS item_itemcallnumber,
			    i.`location` AS item_location,
			    i.copynumber AS item_copynumber,
			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    CASE WHEN DATEDIFF(c.date_due, now()) < 0 THEN 0 ELSE DATEDIFF(c.date_due, now()) END AS issue_days_due,
			    CASE WHEN DATEDIFF(now(), c.date_due) < 0 THEN 0 ELSE DATEDIFF(now(), c.date_due) END AS issue_days_over,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
			    CASE
					WHEN c.borrowernumber = :borrowernumber THEN true
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN true
					ELSE false
				END AS issue_selfcheckout,
				CASE
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					WHEN c.borrowernumber = :borrowernumber THEN false
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false
					WHEN (SELECT COUNT(*) FROM h WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 有人預約就無法外借 */
					ELSE true
				END AS issue_cancheckout,
			    IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"reserve_id", reserve_id,
							"reserve_reservedate", reserve_reservedate,
							"reserve_suspend", reserve_suspend,
							"reserve_found_id", reserve_found_id,
							"reserve_found_name", reserve_found_name,
							"reserve_expirationdate", reserve_expirationdate,
							"reserve_branchcode", reserve_branchcode,
							"reserve_branchname", reserve_branchname,
							"reserve_priority", reserve_priority,
							"borrower_borrowernumber", borrower_borrowernumber,
							"borrower_cardnumber", borrower_cardnumber
						))
					FROM h AS h2
					WHERE h2.reserve_id = h.reserve_id
				), '[]') AS reserve,
				CASE
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN true
					ELSE false
				END AS reserve_selfhold,
				CASE
					WHEN c.issue_id IS NULL THEN false	/* 尚未借出不能預約 */
					WHEN c.borrowernumber = :borrowernumber THEN false /* 自己已借出了不能預約 */
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false /* 自己已借出了不能預約 */
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 已經預約了 */
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					ELSE true
				END AS reserve_canhold,
				ic.items_count
		    FROM h
		    	INNER JOIN biblio AS b ON b.biblionumber = h.bib_biblionumber
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
		    	INNER JOIN items AS i ON i.biblionumber = b.biblionumber AND i.itemnumber = h.reserve_itemnumber
				LEFT JOIN branches AS branch_item_home ON branch_item_home.branchcode = i.homebranch
				LEFT JOIN branches AS branch_item_holding ON branch_item_holding.branchcode = i.holdingbranch
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype
		    	LEFT JOIN issues AS c ON c.itemnumber = i.itemnumber
		    	LEFT JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
		    WHERE TRUE
					{$cWhere_Inner}
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
	public function post_hold($data) {
		foreach ($data as $key => $value) {
			$callBack = $this->post_hold_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_hold_single($data) {
		#region 處理傳入參數
		$values = [
			"borrowernumber" => null,
			"itemnumber" => null,
			"pickupLibrary" => null,
			"expiration_date" => null,
			"notes" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		#endregion

		$cKey1 = "patron_id";
		$cKey2 = "borrowernumber";
		if (!array_key_exists($cKey2, $values)) {
			$this->SetError("必須包含 {$cKey2}");
			return;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$cKey1 = "item_id";
		$cKey2 = "itemnumber";
		if (!array_key_exists($cKey2, $values)) {
			$this->SetError("必須包含 {$cKey2}");
			return;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$cKey1 = "pickup_library_id";
		$cKey2 = "pickupLibrary";
		if (!array_key_exists($cKey2, $values)) {
			$this->SetError("必須包含 {$cKey2}");
			return;
		}
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$callBack = $this->callKohaApi("post", "/holds", $values);
		return $callBack;
	}
	public function patch_hold($data) {
		foreach ($data as $key => $value) {
			$callBack = $this->patch_hold_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_hold_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed"; }
		#region 處理傳入參數
		$values = [
			"reserve_id" => null,
			"pickupLibrary" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		#endregion

		$hold_id = $this->CheckArrayData("reserve_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["reserve_id"]);

		$cKey1 = "pickup_library_id";
		$cKey2 = "pickupLibrary";
		if (!array_key_exists($cKey2, $values)) { $this->SetError("必須包含 {$cKey2}"); return; }
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$callBack = $this->callKohaApi("patch", "/holds/" . $hold_id, $values);
		return $callBack;
	}
	public function delete_hold($data) {
		foreach ($data as $key => $value) {
			$callBack = $this->delete_hold_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_hold_single($data) {
		#region 處理傳入參數
		$values = [
			"reserve_id" => null,
			"pickupLibrary" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		#endregion

		$cKey2 = "reserve_id";
		if (!array_key_exists($cKey2, $values)) {
			$this->SetError("必須包含 {$cKey2}");
			return;
		}
		$hold_id = $values[$cKey2];
		unset($values[$cKey2]);

		$callBack = $this->callKohaApi("delete", "/holds/" . $hold_id, $values);
		return $callBack;
	}
	public function patch_hold_priority($data) {
		foreach ($data as $key => $value) {
			$callBack = $this->patch_hold_priority_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_hold_priority_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed"; }
		#region 處理傳入參數
		$values = [
			"reserve_id" => null,
			"priority" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		#endregion

		$hold_id = $this->CheckArrayData("reserve_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["reserve_id"]);

		$priority = $this->CheckArrayData("priority", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["priority"]);

		$callBack = $this->callKohaApi("put", "/holds/" . $hold_id . "/priority", $priority);
		return $callBack;
	}
	#endregion
	#region fine
	public function get_fine($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			"patron_record_num" => null,
			"patron_barcode" => null,
			"rfid" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$condition = ""; {
			$condition_values = [
				"patron_record_num" => " AND patron_record_num = :patron_record_num",
				"patron_barcode" => " AND patron_barcode = UPPER(:patron_barcode)",
				"rfid" => " AND patron_id = (SELECT record_id FROM koha_view.varfield_view WHERE record_type_code = 'p' AND varfield_type_code = 'b' AND UPPER(field_content) = :rfid)",
			];
			$condition = xStatic::KeyMatchThenJoinValue($values, false, $condition_values, true);
		}
		#endregion

		$select_condition = "";

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = ''; {
			$default_order = " checkout_date_due";
			if (array_key_exists('order', $params)) {
				$order = 'ORDER BY ';
				foreach ($params['order'] as $key => $column_data) {
					if (xString::IsJson($column_data)) {
						$column_data = json_decode($column_data, true);
					} else {
						$order = '';
						$this->SetError("order 解析失敗.");
						return;
					}
					$sort_type = 'ASC';
					if ($column_data['type'] != 'ascend') {
						$sort_type = 'DESC';
					}
				}
				$order = rtrim($order, ',');
			}
			$order = $order == '' ? 'ORDER BY' . $default_order : $order .= ', ' . $default_order;
		}


		$sql_default_inside = <<<EOD
            SELECT
				p.id AS patron_id,
				p.record_num AS patron_record_num,
				pname.last_name AS patron_name,
				UPPER(p.barcode) AS patron_barcode,
				(
					SELECT json_agg(t.field_content)
					FROM (
						SELECT UPPER(field_content) AS field_content
						FROM koha_view.varfield_view
						WHERE record_id = p.id AND UPPER(field_content) <> UPPER(p.barcode)
							AND record_type_code = 'p' AND varfield_type_code = 'b'
							AND LENGTH(field_content) = 8
					) AS t
				) AS rfid,
				b.id AS bib_id, b.record_num AS bib_record_num,
				COALESCE(bp.best_title, f.title, f.description) AS best_title,
				bp.best_author,
				(
					SELECT
						string_agg(content, ' ' ORDER BY display_order) AS content
					FROM koha_view.subfield_view
					WHERE record_num = b.record_num AND marc_tag= '092'
				) AS s092,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							occ_num,
							CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END AS tag,
							string_agg(content, ' ' ORDER BY display_order) AS content
						FROM koha_view.subfield_view
						where record_num = b.record_num AND marc_tag= '020'
						GROUP BY occ_num, CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END
					) AS t
				) AS s020,
				i.id AS item_id, i.record_num AS item_record_num,
				UPPER(i.barcode) AS item_barcode,
				checkout_gmt::timestamp without time zone AS checkout_issuedate,
				(to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_date_due,
				(to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_days_over,
				returned_gmt::timestamp without time zone AS checkin_datetime,
				f.paid_gmt::timestamp without time zone AS paid_datetime,
				f.paid_amt * 100 AS paid_amt,
				(f.item_charge_amt + f.processing_fee_amt + f.billing_fee_amt) * 100 AS fine_amt
			FROM koha_view.fine AS f
				INNER JOIN koha_view.patron_view p on p.id = f.patron_record_id
				LEFT JOIN koha_view.patron_record_fullname pname on pname.patron_record_id = p.id

				LEFT JOIN koha_view.bib_record_item_record_link b2i ON b2i.item_record_id = f.item_record_metadata_id
				LEFT JOIN koha_view.bib_view AS b ON b.id = b2i.bib_record_id
				LEFT JOIN koha_view.bib_record_property bp on bp.bib_record_id = b.id

				LEFT JOIN koha_view.item_view AS i ON b2i.item_record_id = i.id
EOD;

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

		$stmt = $this->db_koha->prepare($sql);
		$stmt_count = $this->db_koha->prepare($sql_count);
		if ($stmt->execute($values) && $stmt_count->execute($values_count)) {
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
			return $result;
		} else {
			var_dump($stmt->errorInfo());
			return ["status" => "failed"];
		}
	}
	public function get_fine_outstanding($params)
	{
		$values = [
			"patron_record_num" => null,
			"patron_barcode" => null,
			"removeByNotValidRfid" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		$removeByNotValidRfid = false;
		if (array_key_exists("removeByNotValidRfid", $values)) {
			$removeByNotValidRfid = $values["removeByNotValidRfid"] === "true";
			unset($values["removeByNotValidRfid"]);
		}

		#region where condition
		$condition = ""; {
			$condition_values = [
				"patron_record_num" => " AND patron_record_num = :patron_record_num",
				"patron_barcode" => " AND patron_barcode = UPPER(:patron_barcode)",
			];
			$condition = xStatic::KeyMatchThenJoinValue($values, false, $condition_values, true);
		}
		#endregion

		$sql = <<<EOD
            WITH dt (patron_id, patron_record_num, patron_name, patron_barcode, pcode3, pcode3_name, mblock_code, mblock_name) AS (
				SELECT DISTINCT
					p.id AS patron_id,
					p.record_num AS patron_record_num,
					na.last_name AS patron_name,
					UPPER(p.barcode) AS patron_barcode,
					p.pcode3, dfpn3.name AS pcode3_name,
					p.mblock_code AS mblock_code,
					CASE WHEN p.mblock_code = '-' THEN '' ELSE mbn.name END AS mblock_name,
					vf_m.field_content AS memo
				FROM koha_view.fine AS f
					INNER JOIN koha_view.patron_view p on p.id = f.patron_record_id
					INNER JOIN koha_view.patron_record_fullname AS na on na.patron_record_id = p.id
					LEFT JOIN koha_view.varfield_view AS vf_m ON vf_m.record_id = p.id AND vf_m.record_type_code = 'p' AND vf_m.varfield_type_code = 'm'

					INNER JOIN koha_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
					INNER JOIN koha_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = p.pcode3::varchar
					INNER JOIN koha_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4

					INNER JOIN koha_view.mblock_property AS mb ON mb.code = p.mblock_code
					INNER JOIN koha_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4
				WHERE (f.paid_amt - f.item_charge_amt - f.processing_fee_amt - f.billing_fee_amt) < 0
			)
			SELECT
				dt.*,
				(
					SELECT json_agg(t.field_content)
					FROM (
						SELECT UPPER(field_content) AS field_content
						FROM koha_view.varfield_view
						WHERE record_id = dt.patron_id AND UPPER(field_content) <> UPPER(dt.patron_barcode)
							AND record_type_code = 'p' AND varfield_type_code = 'b'
							AND LENGTH(field_content) = 8
					) AS t
				) AS rfid,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							b.id AS bib_id, b.record_num AS bib_record_num,
							COALESCE(bp.best_title, f.title, f.description) AS best_title,
							bp.best_author,

							i.id AS item_id, i.record_num AS item_record_num,
							i.barcode AS item_barcode,
							i.item_status_code,
							i_status_name.name AS item_status_name,

							f.checkout_gmt::timestamp without time zone AS checkout_issuedate,
							(to_char(f.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_date_due,
							(to_char(f.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_days_over,
							f.returned_gmt::timestamp without time zone AS checkin_datetime,
							f.paid_gmt::timestamp without time zone AS paid_datetime,
							f.paid_amt * 100 AS paid_amt,
							(f.item_charge_amt + f.processing_fee_amt + f.billing_fee_amt) * 100 AS fine_amt
						FROM koha_view.fine AS f
							LEFT JOIN koha_view.bib_record_item_record_link brir ON brir.item_record_id = f.item_record_metadata_id
							LEFT JOIN koha_view.bib_view b ON brir.bib_record_id = b.id
							LEFT JOIN koha_view.bib_record_property bp on bp.bib_record_id = brir.bib_record_id

							LEFT JOIN koha_view.item_view i ON i.id = brir.item_record_id
							LEFT JOIN koha_view.item_status_property AS i_status ON i_status.code = i.item_status_code
							LEFT JOIN koha_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4
						WHERE f.patron_record_id = dt.patron_id AND (f.paid_amt - f.item_charge_amt - f.processing_fee_amt - f.billing_fee_amt) < 0
					) AS t
				) AS items
			FROM dt
			WHERE TRUE {$condition}
EOD;

		$stmt = $this->db_koha->prepare($sql);
		if (!$stmt->execute($values)) {
			$oErrorInfo = $stmt->errorInfo();
			var_dump($oErrorInfo);
			$this->SetError($oErrorInfo[2]);
			return;
		}
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($removeByNotValidRfid) {
			$aRows = $this->removeByNotValidRfid($aRows);
		}
		return $aRows;
	}
	#endregion
	#region bib
	//public function get_bib($params)
	//{
	//	#region page control
	//	$values = $this->initialize_search();
	//	$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
	//	$_page = $values['cur_page']; $_per_page = $values['size'];
	//	#endregion
	//	$_order_by = []; {
	//		if (array_key_exists('order', $params)) {
	//			foreach ($params['order'] as $column_data) {
	//				if (!xString::IsJson($column_data)) { $this->SetError("order 解析失敗."); return; }
	//				$column_data = json_decode($column_data, true);
	//				$_order_by[] = trim($column_data['column']);
	//			}
	//		}
	//	}

	//	$values = [
	//		"field" => null, "keyword" => null
	//	];
	//	$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
	//	$values = xStatic::ValueMatchThenRemove($values, "");
	//	$cField = $values["field"];
	//	$cKeyword = str_replace(["'", '"'], "", $values["keyword"]);
	//	$values = [];
	//	$bIsKeyword = $cField == "keyword";
	//	$bIsTitle = $bIsKeyword || $cField == "title";
	//	$bIsAuthor = $bIsKeyword || $cField == "author";
	//	$bIsIsbn = $bIsKeyword || $cField == "isbn";

	//	$query = http_build_query(
	//		["_page" => $_page, "_per_page" => $_per_page, "_match" => "contains", "_order_by" => $_order_by]
	//	);

	//	if ($bIsTitle) {
	//		$values["title"] = [ "-like" => "%" . $cKeyword . "%" ];
	//		$callBack = $this->callKohaApi("get", "/biblios?$query", $values);
	//	}
	//	if ($bIsAuthor) { $values["author"] = [ "-like" => "%" . $cKeyword . "%" ]; }
	//	if ($bIsIsbn) { $values["isbn"] = [ "-like" => "%" . "9786267206666" . "%" ]; }
	//	$callBack = $this->callKohaApi("get", "/biblios?$query", $values);
	//	//$callBack = $this->callKohaApi("get", "/patrons", $values);

	//	return $callBack;
	//}
	private function get_bib_fulltext($field, $keyword) {
		$callBack = new stdClass(); { $callBack->status = "success"; $callBack->data = []; }
		if ($keyword == "") { return $callBack; }
		if (!in_array($field, ["title", "author", "isbn"])) { $field = "keyword"; }
		$cSql = <<<EOD
			SELECT biblionumber, ts_rank({$field}, websearch_to_tsquery('jiebacfg', :keyword)) AS rank
			FROM library.biblio_search_ts
			WHERE {$field} @@ websearch_to_tsquery('jiebacfg', :keyword)
EOD;
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, ["keyword"=> $keyword]);
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$callBack->data = $aRows;
		return $callBack;
	}
	public function get_bib($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["`rank` DESC", "bib_biblionumber"]);
		#endregion
		$values = [
			"field" => null, "keyword" => null,
			"field2" => null, "keyword2" => null, "operator2" => null,
			"field3" => null, "keyword3" => null, "operator3" => null,
			"biblionumber" => null,
			"itemtype" => null,
			"datecreated_start" => null,
			"datecreated_end" => null,
			"marcdata" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		if (array_key_exists("marcdata", $values)) {
			$cDataType = $values["marcdata"];
			return $this->get_bib_marcdata($values, $cDataType);
		}


		$cWhere_Inner = ""; {
			#region rank
			$bFindByRank = false;
			$aRank = []; {
				if (array_key_exists("biblionumber", $values)) {
					$bFindByRank = true;
					$aRank[] = ["biblionumber" => $values["biblionumber"], "rank" => 99];
					unset($values["biblionumber"]);
					unset($values["field"]);	unset($values["keyword"]);
					unset($values["field2"]);	unset($values["keyword2"]);	unset($values["operator2"]);
					unset($values["field3"]);	unset($values["keyword3"]);	unset($values["operator3"]);
				}
				#region keyword
				if (array_key_exists("keyword", $values)) {
					$bFindByRank = true;
					$keyword = $values["keyword"]; unset($values["keyword"]);
					$field = array_key_exists("field", $values) ? $values["field"] : ""; unset($values["field"]);
					$callBack = $this->get_bib_fulltext($field, $keyword); if ($callBack->status == "failed") { return $callBack; }
					$aRank = $callBack->data; unset($callBack->data);
				}
				#endregion
				#region keyword2
				if (array_key_exists("keyword2", $values)) {
					$bFindByRank = true;
					$aRank2 = []; {
						$keyword = $values["keyword2"]; unset($values["keyword2"]);
						$field = array_key_exists("field2", $values) ? $values["field2"] : ""; unset($values["field2"]);
						$callBack = $this->get_bib_fulltext($field, $keyword); if ($callBack->status == "failed") { return $callBack; }
						$aRank2 = $callBack->data; unset($callBack->data);
						if (count($aRank2) > 0) {
							foreach($aRank as $key => $value) { $aRank[$key]["match"] = false; }
							foreach($aRank2 as $key2 => $value2) {
								$biblionumber = $value2["biblionumber"];
								$aRank2[$key2]["match"] = false;
								foreach($aRank as $key => $value) {
									if ($value["match"]) { continue; }
									if ($value["biblionumber"] != $biblionumber) { continue; }
									$aRank[$key]["rank"] += $value["rank"];
									$aRank[$key]["match"] = true;
									$aRank2[$key2]["match"] = true;
									break;
								}
							}
							$operator = array_key_exists("operator2", $values) ? $values["operator2"] : ""; unset($values["operator2"]);
							$isAnd = strtolower($operator) == "and";
							if ($isAnd) {
								//取交集
								foreach ($aRank as $key => $value) { if (!$value["match"]) { unset($aRank[$key]); } }
							} else {
								//合併
								foreach($aRank2 as $key2 => $value2) { if (!$value2["match"]) { $aRank[] = $value2; } }
							}
						}
					} unset($aRank2);
				}
				#endregion
				#region keyword3
				if (array_key_exists("keyword3", $values)) {
					$bFindByRank = true;
					$aRank3 = []; {
						$keyword = $values["keyword3"]; unset($values["keyword3"]);
						$field = array_key_exists("field3", $values) ? $values["field3"] : ""; unset($values["field3"]);
						$callBack = $this->get_bib_fulltext($field, $keyword); if ($callBack->status == "failed") { return $callBack; }
						$aRank3 = $callBack->data; unset($callBack->data);
						if (count($aRank3) > 0) {
							foreach($aRank as $key => $value) { $aRank[$key]["match"] = false; }
							foreach($aRank3 as $key3 => $value3) {
								$biblionumber = $value3["biblionumber"];
								$aRank3[$key3]["match"] = false;
								foreach($aRank as $key => $value) {
									if ($value["match"]) { continue; }
									if ($value["biblionumber"] != $biblionumber) { continue; }
									$aRank[$key]["rank"] += $value["rank"];
									$aRank[$key]["match"] = true;
									$aRank3[$key3]["match"] = true;
									break;
								}
							}
							$operator = array_key_exists("operator3", $values) ? $values["operator3"] : ""; unset($values["operator3"]);
							$isAnd = strtolower($operator) == "and";
							if ($isAnd) {
								//取交集
								foreach ($aRank as $key => $value) { if (!$value["match"]) { unset($aRank[$key]); } }
							} else {
								//合併
								foreach($aRank3 as $key3 => $value3) { if (!$value3["match"]) { $aRank[] = $value3; } }
							}
						}
					} unset($aRank3);
				}
				#endregion
				foreach ($aRank as $key => $value) { unset($aRank[$key]["match"]); }
			}
			$values["json_ts"] = json_encode($aRank, JSON_UNESCAPED_UNICODE);
			if ($bFindByRank || count($aRank) > 0) {
				$cWhere_Inner .= " AND ts.`rank` > 0";
			}
			unset($aRank);
			#endregion

			$bHasDateCreated_Start = array_key_exists("datecreated_start", $values);
			if ($bHasDateCreated_Start) {
				$cWhere_Inner .= " AND b.datecreated >= :datecreated_start";
			}
			$bHasDateCreated_End = array_key_exists("datecreated_end", $values);
			if ($bHasDateCreated_End) {
				$cWhere_Inner .= " AND b.datecreated <= :datecreated_end";
			}
			$bHasItemType = array_key_exists("itemtype", $values);
			if ($bHasItemType) {
				$cWhere_Inner .= " AND bi.itemtype = :itemtype";
			}
		}
		if ($cWhere_Inner == "") { $this->SetError("輸入條件不足"); return; }

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
			SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
				'[]' AS bib_cover_images,
				ic.items_count,
				ibc.items_borrowed_count,
				bm.metadata,
				ts.`rank`
		    FROM biblio AS b
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
				LEFT JOIN (
					SELECT items.biblionumber, COUNT(*) AS items_borrowed_count
					FROM issues
						INNER JOIN items ON items.itemnumber = issues.itemnumber
					GROUP BY items.biblionumber
				) AS ibc ON ibc.biblionumber = b.biblionumber
				LEFT JOIN JSON_TABLE(
					:json_ts,
					'$[*]' COLUMNS(biblionumber INT PATH '$.biblionumber',  `rank` FLOAT PATH '$.rank')
				) AS ts ON ts.biblionumber = b.biblionumber
			WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dt
            {$order}
EOD;

		$sql = <<<EOD
		SELECT *
        FROM (
			{$cSql_AllRow}
			LIMIT :length
		) dt
		WHERE `key` > :start
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_AllRow}
			) AS c
EOD;

		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		if (!$stmt->execute() || !$stmt_count->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		$aBibNumbers = [];
		foreach ($aRows as $row_id => $row) {
			$aBibNumbers[] = $row["bib_biblionumber"];

			$field = "metadata";
			$marc21Json = $this->marcXml2Json($row[$field]);
			unset($aRows[$row_id][$field]);

			$this->setField_LeaderField($row, $marc21Json);
			$this->setField_ControlField($row, $marc21Json);
			$this->setField_Title($row, $marc21Json);
			$this->setField_OtherTitle($row, $marc21Json);
			$this->setField_Author($row, $marc21Json);
			$this->setField_Translator($row, $marc21Json);
			$this->setField_Language($row, $marc21Json);
			$this->setField_Publisher($row, $marc21Json);

			$aRows[$row_id] = $row;
		}
		$aBibNumbers = array_unique($aBibNumbers);
		$aRows = $this->fillBibCoverImageFileID($aRows, $aBibNumbers);
		$result['data'] = $aRows;
		$result['total'] = $result_count;
		$this->SetOK();
		return $result;
	}
	private function setField_LeaderField(&$row, &$marc21Json) {
		$leader = $marc21Json["leader"];
		$c6 = substr($leader, 6, 1);
		$BibliographicLevel = substr($leader, 7, 1);
		$TypeOfRecord = "Book"; $format = "print";
		switch ($c6) {
			case "a":
			case "t": {
				$levels = ["a" => "Article", "b" => "Article", "i" => "Serial", "s" => "Serial"];
                if (array_key_exists($BibliographicLevel, $levels)) { $TypeOfRecord = $levels[$BibliographicLevel]; }
			} break;
			case "c":
			case "d": {
                $TypeOfRecord = "Score";
			} break;
			case "e":
			case "f": {
                $TypeOfRecord = "Map";
			} break;
			case "g":
			case "k":
			case "r": {
                $TypeOfRecord = "Visual material";
			} break;
			case "i": {
                $TypeOfRecord = "Sound";
			} break;
			case "j": {
                $TypeOfRecord = "Music";
			} break;
			case "m": {
                $TypeOfRecord = "Computer file"; $format = "electronic";
			} break;
			case "o": {
                $TypeOfRecord = "Kit";
			} break;
			case "p": {
                $TypeOfRecord = "Mixed materials";
			} break;
		}
		$row["bib_materialType"] = $TypeOfRecord;
		$row["bib_format"] = $format;
	}
	private function setField_ControlField(&$row, &$marc21Json) {
		$Text008 = "";
		foreach ($marc21Json["controlfield"] as $key => $oTag) {
			if ($oTag["tag"] == "008") { $Text008 = $oTag["text"]; break; }
		}
		if ($Text008 == "") { return; }
		$c22 = substr($Text008, 22, 1);
		$a22 = [
			" " => "Unknown or not specified", "a" => "Preschool", "b" => "Primary", "c" => "Pre-adolescent", "d" => "Adolescent",
			"e" => "Adult", "f" => "Specialized", "g" => "General", "j" => "Juvenile", "|" => "No attempt to code",
		];
		$row["bib_targetAudience"] = $a22[$c22];

		$c33 = substr($Text008, 33, 1);
		$a33 = [
			" " => "Unknown or not specified",
			"0" => "Not fiction (not further specified)", "1" => "Fiction (not further specified)", "d" => "Dramas",
			"e" => "Essays", "f" => "Novels", "h" => "Humor, satires, etc.", "i" => "Letters",
			"j" => "Short stories", "m" => "Mixed forms", "p" => "Poetry",
			"s" => "Speeches", "u" => "Unknown", "|" => "No attempt to code"
		];
		$row["bib_literaryForm"] = $a33[$c33];
	}
	private function setField_Title(&$row, &$marc21Json) { $this->setField_ByTagCodeField($row, $marc21Json, "245", [], "bib_title"); }
	private function setField_OtherTitle(&$row, &$marc21Json) { $this->setField_ByTagCodeField($row, $marc21Json, "246", [], "bib_othertitle"); }
	private function setField_Author(&$row, &$marc21Json) { $this->setField_ByTagCodeField($row, $marc21Json, "100", [], "bib_author"); }
	private function setField_ISBN(&$row, &$marc21Json, $subCodes = []) { $this->setField_ByTagCodeField($row, $marc21Json, "020", $subCodes, "bib_isbn"); }
	private function setField_Translator(&$row, &$marc21Json) { $this->setField_ByTagCodeField($row, $marc21Json, "700", [], "bib_translator"); }
	private function setField_Language(&$row, &$marc21Json) { $this->setField_ByTagCodeField($row, $marc21Json, "041", [], "bib_language"); }
	private function setField_Publisher(&$row, &$marc21Json) { $this->setField_ByTagCodeField($row, $marc21Json, "260", [], "bib_publisher"); }
	private function setField_ByTagCodeField(&$row, &$marc21Json, $tagCode, $subCodes, $field) {
		$singleRoweTags = ["041", "245", "100", "260"];
		$languages = ["chi" => "Chinese", "eng" => "English", "jpn" => "Japan"];
		$bIsSingle = in_array($tagCode, $singleRoweTags);

		$aContent = [];
		$tags = [];
		foreach ($marc21Json["datafield"] as $key => $oTag) { if ($oTag["tag"] == $tagCode) { $tags[$key] = $oTag; } }
		foreach ($tags as $key => $oTag) {
			$tag = $oTag["tag"];
			$ind1 = $oTag["ind1"]; $ind2 = $oTag["ind2"];
			$isLimitSubCode = count($subCodes) > 0;
			$content = [];
			foreach ($oTag["subfield"] as $subfield) {
				$code = $subfield["code"]; if ($isLimitSubCode && !(in_array($code, $subCodes))) { continue; }
				$text = $subfield["text"];
				switch ($tag) {
					case "100":
					case "700": {
						if ($code == "e") { $text = "[{$text}]"; }
					} break;
					case "041": {
						$text = key_exists($text, $languages) ? $languages[$text] : "--";
					} break;
				}
				switch ($tag) {
					case "041": {
						switch ($code) {
							case "a": $content["Language"][] = $text; break;
							case "h": $content["Original Language"][] = $text; break;
							default: $content[$code][] = $text; break;
						}
					} break;
					default: {
						$content[] = $text;
					} break;
				}
			}
			switch ($tag) {
				case "041": {
					$aContent[] = $content;
				} break;
				default: {
					$aContent[] = implode("", $content);
				} break;
			}

			//unset($marc21Json["datafield"][$key]);
		}

		$row[$field] = $bIsSingle && count($aContent) > 0 ? $aContent[0] : $aContent;
	}
	public function get_bib_marcdata($params, $cDataType) {
		$values = [
			"biblionumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$cWhere_Inner = ""; {
			$aCondition = [
				"biblionumber" => " AND biblionumber = :biblionumber",
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		if ($cWhere_Inner == "") { $this->SetError("無輸入條件(biblionumber)"); return; }

		$cSql = <<<EOD
			SELECT
				metadata, 1 AS `key`
			FROM biblio_metadata
			WHERE format = 'marcxml' {$cWhere_Inner}
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$iCount = count($aRows);
			if ($iCount > 0 && $cDataType == "json") {
				$metadata = $aRows[0]["metadata"];
				$aRows[0]["metadata"] = $this->marcXml2Json($metadata);
			}
			$oRow = $aRows[0];
			$result['data'] = $aRows;
			$result['total'] = $iCount;
			$this->SetOK();
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			var_dump($oInfo);
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	public function get_bib_z3950($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$iSize = $values['size'];
		$length = $values['cur_page'] * $iSize;
		$iStart = $length - $iSize;
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["bib_biblionumber"]);
		#endregion
		$values = [
			"search" => null,
			"marcdata" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (!array_key_exists("search", $values) || !is_array($values)) { $this->SetError("條件未包含 search"); return; }
		$search = $values["search"];
		#region 產生查詢字串
		$aQuery = [];
		foreach ($search as $v) {
			if (is_string($v)) {
				if (!xString::IsJson($v)) { $this->SetError("search 條件解析失敗."); return; }
				$v = json_decode($v, true);
			}
			if (!array_key_exists("field", $v) || !array_key_exists("keyword", $v)) { $this->SetError("search 條件格式有誤."); return; }
			$cField = $v["field"]; $cKeyword = $v["keyword"];

			$aSubQuery = [];
			$bIsKeyword = $cField == "keyword";
			$bIsTitle = $bIsKeyword || $cField == "title";
			$bIsAuthor = $bIsKeyword || $cField == "author";
			$bIsIsbn = $bIsKeyword || $cField == "isbn";

			if ($bIsTitle) { $aSubQuery[] = "@attr 1=4 {$cKeyword}"; }
			if ($bIsAuthor) { $aSubQuery[] = "@attr 1=1003 {$cKeyword}"; }
			if ($bIsIsbn) { $aSubQuery[] = "@attr 1=7 {$cKeyword}"; }
			$iCount = count($aSubQuery); if ($iCount == 0) { $this->SetError("field 參數不正確."); return; }
			$cSubQuery = ""; {
				for($i = 1; $i < $iCount; $i++) { $cSubQuery .= "@or "; }
				$cSubQuery .= implode(" ", $aSubQuery);
			}
			$aQuery[] = $cSubQuery;
		}
		$cQuery = ""; {
			for($i = 1; $i < count($aQuery); $i++) { $cQuery .= "@and "; }
			$cQuery .= implode(" ", $aQuery);
		}
		#endregion
		$bMarcXmlType = array_key_exists("marcdata", $values) && $values["marcdata"] == "xml";
		$bMarcJsonType = !$bMarcXmlType;
		//return "";
		//$id = yaz_connect('cylis.lib.cycu.edu.tw:210/INNOPAC');	//
		//$id = yaz_connect('tulips.ntu.edu.tw:210/innopac');	//
		//$id = yaz_connect('innopac.lib.fcu.edu.tw:210/INNOPAC');	//逢甲 cmarc
		$id = yaz_connect('las.sinica.edu.tw:210/INNOPAC');	//中研院 usmarc
		//$id = yaz_connect('nbinet3.ncl.edu.tw:210/INNOPAC');
		//$id = yaz_connect('metadata.ncl.edu.tw:210/bg');
		$this->testYazError($id); if ($this->bErrorOn) { return; }
		yaz_range($id, $iStart + 1, $iSize);
		yaz_syntax($id, "usmarc");
		yaz_search($id, 'rpn', $cQuery);
		try {
			yaz_wait();
			$this->testYazError($id); if ($this->bErrorOn) { return; }
		} catch (Exception $e) {
			$this->SetError($e->getMessage()); return;
		}

		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		$aRows = [];
		$hits = yaz_hits($id); if ($hits == '0') { return $aRows; }

		for ($p = $iStart; $p < $length; $p++) {
			$oRow = ["key" => $p];
			$bibMarcString = yaz_record($id, $p + 1, "string; charset=utf-8"); if (empty($bibMarcString)) { continue; }
			$oRow["marcstring"] = $bibMarcString;

			$bibMarcXml = yaz_record($id, $p + 1, "xml; charset=utf-8"); if (empty($bibMarcXml)) { continue; }

			$bibMarcJson = $this->marcXml2Json($bibMarcXml);
			if ($bMarcXmlType) {
				$oRow["marcxml"] = $bibMarcXml;
			} else {
				$oRow["marcjson"] = $bibMarcJson;
			}

			$Text008 = "";
			foreach ($bibMarcJson["controlfield"] as $oTag) {
				if ($oTag["tag"] == "008") { $Text008 = $oTag["text"]; break; }
			}
			$oRow["year"] = $Text008 == "" ? "" : substr($Text008, 7, 4);

			$aWantTags = ["245" => "title", "100" => "author", "260" => "publisher", "020" => "isbn", "022" => "issn", "250" => "edition"];
			foreach ($bibMarcJson["datafield"] as $oDataField) {
				$tag = $oDataField["tag"]; if (!array_key_exists($tag, $aWantTags)) { continue; }
				$text = "";
				$bIsTag020 = $tag == "020";
				foreach ($oDataField["subfield"] as $oSubfield) {
					if ($bIsTag020 && $oSubfield["code"] != "a") { continue; }
					$text .= $oSubfield["text"];
				}
				$oRow[$aWantTags[$tag]] = $text;
				unset($aWantTags[$tag]);
				if (count($aWantTags) == 0) { break; }
			}

			$aRows[] = $oRow;
		}
		return ["data" => $aRows, "total" => $hits];
	}
	public function post_bib_marcFile2Json($params) {
		if ($params == null || !is_array($params) || count($params) == 0) { $this->SetError("沒有傳入檔案."); return; }
		$aBibs = [];
		foreach ($params as $value) {
			$cExtension = strtolower(pathinfo($value["name"], PATHINFO_EXTENSION));
			$cTempFile = $value["tmp_name"];
			switch ($cExtension) {
				case "iso":
				case "mrc":
				case "out":
					$aBib = $this->marc21FileToArray($cTempFile); if ($this->bErrorOn) { return; }
					$aBibs = array_merge($aBibs, $aBib);
					break;
				default:
					$this->SetError("未支援的附檔名格式. (" . $cExtension . ")");
					return;
			}
		}
		return ["data" => $aBibs, "total" => count($aBibs)];
	}
	private function marc21FileToArray($cTempFile) {
		$cMarc21 = null; {
			try {
				$fp = fopen($cTempFile, "r");
				$cMarc21 = fread($fp, filesize($cTempFile));
				fclose($fp);
				if ($cMarc21 === null) { $this->SetError("檔案為空."); return; }
				$aBytes = unpack('C*', $cMarc21);
				//$cText = implode(array_map("chr", $aBytes));
				$bIsUTF8 = xFile::IsUTF8Bytes($aBytes);
				if (!$bIsUTF8) {
					$cMarc21 = utf8_encode($cMarc21);
				}
			} catch (Exception $e) {
				$this->SetError($e->getMessage()); return null;
			}
		}
		$aBib = $this->marc21TextToArray($cMarc21);
		return $aBib;
	}
	private function marc21TextToArray($cText) {
		$aCut_Bib = explode(chr(0x1d), $cText); $cText = "";
		array_pop($aCut_Bib);	//最後一筆會是空的

		$aBib = [];
		foreach ($aCut_Bib as $cBib) {
			if (strlen($cBib) < 25) { continue; }
			$metadata = [];
			$leader = substr($cBib, 0, 24); $cBib = substr($cBib, 24);
			$metadata["leader"] = $leader;

			$aLines = explode(chr(0x1e), $cBib); $cBib = ""; if (count($aLines) < 5) { return; }
			array_pop($aLines); //最後一筆會是空的

			$cFirstLine = array_shift($aLines);
			$aTags = str_split($cFirstLine, 12); if (count($aTags) < count($aLines)) { return; }

			$controlfield = []; $datafield = [];
			for ($i = 0; $i < count($aTags); $i++) {
				$cTag = substr($aTags[$i], 0, 3);
				$cLine = $aLines[$i];
				$bIsControlField = substr($cTag, 0, 2) == "00";

				if ($bIsControlField) { $controlfield[] = ["tag" => $cTag, "text" => $cLine]; continue; }

				$aSubFields = explode(chr(0x1f), $cLine);
				$cInd12 = array_shift($aSubFields);
				$data = ["tag" => $cTag, "ind1" => substr($cInd12, 0, 1), "ind2" => substr($cInd12, 1, 1), "subfield" => [] ];
				foreach($aSubFields as $cSubField) {
					$data["subfield"][] = ["code" => substr($cSubField, 0, 1), "text" => substr($cSubField, 1)];
				}
				$datafield[] = $data;
			}
			$metadata["controlfield"] = $controlfield; $metadata["datafield"] = $datafield;

			$row = ["metadata" => $metadata];
			$this->setField_Title($row, $metadata);
			$this->setField_OtherTitle($row, $metadata);
			$this->setField_Author($row, $metadata);
			$this->setField_ISBN($row, $metadata, ["a"]);
			$isExist = false; {
				if (array_key_exists("bib_isbn", $row)) {
					$aISBN = $row["bib_isbn"];
					if (count($aISBN) > 0) {
						//若是多個 isbn，則欄位裡放的是以 | 號串起來的資料
						$cSql = "SELECT COUNT(*) FROM biblioitems WHERE isbn = :isbn";
						$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["isbn"=> implode(" | ", $aISBN)]);
						if ($stmt->execute()) {
							$iCount = $stmt->fetchColumn(0);
							$isExist = $iCount > 0;
						}
					}
				}
			} $row["bib_isbn_exist"] = $isExist;

			$aBib[] = $row;
		}
		foreach ($aBib as $key => $oBib) { $aBib[$key]["key"] = $key + 1; }

		return $aBib;
	}
	private function testYazError($id)
	{
		$error = yaz_error($id);
		if (empty($error)) {
			$this->SetOK();
		} else {
			$this->SetError("{$error}");
		}
	}
	public function post_bib($data) {
		if ($this->isNullOrEmptyArray($data)) { $this->SetError("isNullOrEmptyArray"); return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_bib_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		if (!$this->bErrorOn)  { $this->patch_refresh_biblio_search_ts([]); }
		return $data;
	}
	private function post_bib_single($data) {
		$values = [
			"bib_title" => null,
			"metadata" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$marcXml = $this->marcJson2Xml($values["metadata"]);
		$metadata = $marcXml->asXML();

		$callBack = $this->callKohaApi("post", "/biblios/", $metadata, "application/marcxml+xml");
		if ($callBack->status == "failed" && strpos($callBack->message, "Duplicate biblio") == 0) {
			$callBack->message = "重複的書目：" . $data["bib_title"];
		}
		return $callBack;
	}
	public function patch_bib($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_bib_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		if (!$this->bErrorOn)  { $this->patch_refresh_biblio_search_ts([]); }
		return $data;
	}
	private function patch_bib_single($data)
	{
		$values = [
			"biblionumber" => null,
			"metadata" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;

		$cKey1 = "biblionumber";
		if (!array_key_exists($cKey1, $values)) { $callBack->message = "必須包含 {$cKey1}"; return $callBack; }
		$biblionumber = $values[$cKey1];
		unset($values[$cKey1]);

		$marcXml = $this->marcJson2Xml($values["metadata"]);
		$metadata = $marcXml->asXML();

		$callBack = $this->callKohaApi("put", "/biblios/{$biblionumber}/", $metadata, "application/marcxml+xml");
		return $callBack;
	}
	public function delete_bib($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_bib_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_bib_single($data)
	{
		$values = [
			"biblionumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		$cKey1 = "biblionumber";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 {$cKey1}";
			return $callBack;
		}
		$biblionumber = $values[$cKey1];
		unset($values[$cKey1]);

		$callBack = $this->callKohaApi("delete", "/biblios/{$biblionumber}/", $values);
		return $callBack;
	}
	#endregion
	#region item
	public function get_item($params) {

		// [Export] 為匯出用程式碼
		// [Export] 先判斷是否是匯出使用
        if (array_key_exists('excel', $params)) {
            unset($params['excel']);
            $excel_check = true;
        }


		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["bib_biblionumber"]);
		#endregion

		$values = [
			"biblionumber" => null,
			"title" => null,
			"author" => null,
			"itemnumber" => null,
			"barcode" => null,
			"borrowernumber" => null,
			"cardnumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$borrowernumber = -1;
		if (array_key_exists("borrowernumber", $values)) {
			$borrowernumber = $values["borrowernumber"];
		}
		$values["borrowernumber"] = ["oValue" => $borrowernumber, "iType" => PDO::PARAM_INT];

		if (!array_key_exists("cardnumber", $values) || $values["cardnumber"] == "") {
			$values["cardnumber"] = "~";
		}

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"biblionumber" => " AND b.biblionumber = :biblionumber",
				"title" => " AND extractvalue(bm.metadata, '//datafield[@tag=\"505\"]/subfield[@code=\"t\"] | //datafield[@tag=\"245\" OR @tag=\"246\" OR @tag=\"240\" OR @tag=\"490\"]/subfield[@code=\"a\"]') LIKE CONCAT('%', :title, '%')",
				"author" => " AND extractvalue(bm.metadata, '//datafield[@tag=\"245\"]/subfield[@code=\"c\"] | //datafield[@tag=\"100\" OR @tag=\"700\" OR @tag=\"110\" OR @tag=\"710\" OR @tag=\"111\" OR @tag=\"130\"]/subfield[@code=\"a\"]') LIKE CONCAT('%', :author, '%')",
				"itemnumber" => " AND i.itemnumber = :itemnumber",
				"barcode" => " AND i.barcode = UPPER(:barcode)",
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion


		// 搜尋框搜尋
        $select_condition = "";
        $custom_filter_bind_values = [
            "bib_title" => null,
			"item_itype_description" => null,
			"item_itemnumber" => null,
			"item_barcode" => null,
			"item_branchname_home" => null,
			"item_branchname_holding" => null,
			"item_itemcallnumber" => null,
			"item_copynumber" => null,
        ];
        $custom_filter_return = $this->custom_filter_function($params, $select_condition, $values, $custom_filter_bind_values);
        $select_condition = $custom_filter_return['select_condition'];
        $values = $custom_filter_return['bind_values'];
		$condition_values = [
            "itemnumber_id_arr" => "",
        ];
        foreach ($condition_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                // [Export] 匯出勾選的內容 - 資料處理
                if ($key == "itemnumber_id_arr" && count($params[$key]) != 0) {
                    $cWhere_Inner .= " AND i.itemnumber IN (";
                    foreach ($params[$key] as $itemnumber_id_arr_index => $itemnumber_id_arr_value) {
                        $cWhere_Inner .= " $itemnumber_id_arr_value,";
                    }
                    $cWhere_Inner = rtrim($cWhere_Inner, ',');
                    $cWhere_Inner .= ")";
                    unset($values[$key]);
                } else {
                    $cWhere_Inner .= $value;
                }
            } else {
                unset($values[$key]);
            }
        }


		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
            WITH h AS (
				SELECT
					r.reserve_id,
					r.reservedate AS reserve_reservedate,
					r.suspend AS reserve_suspend,
					r.biblionumber AS bib_biblionumber,
					r.itemnumber AS reserve_itemnumber,
					r.`found` AS reserve_found_id,
					CASE WHEN r.found = 't' THEN '傳輸中' WHEN r.found = 'w' THEN '已到館' ELSE '' END AS reserve_found_name,
					r.expirationdate AS reserve_expirationdate,
					r.branchcode AS reserve_branchcode, b.branchname AS reserve_branchname,
					r.priority AS reserve_priority,
					h.itemnumber AS hold_itemnumber,
					p.borrowernumber AS borrower_borrowernumber,
					UPPER(p.cardnumber) AS borrower_cardnumber,
					CASE WHEN (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber)) THEN true ELSE false END AS selfhold
				FROM reserves AS r
					INNER JOIN branches AS b ON b.branchcode = r.branchcode
					INNER JOIN borrowers AS p ON p.borrowernumber = r.borrowernumber
					LEFT JOIN hold_fill_targets AS h ON h.reserve_id = r.reserve_id
				WHERE	:borrowernumber IN (p.borrowernumber, -1)
					AND UPPER(:cardnumber) IN (UPPER(p.cardnumber), '~' )
			)
			SELECT
			    b.biblionumber AS bib_biblionumber,
			    b.author AS bib_author,
			    b.title AS bib_title,
			    b.subtitle AS bib_subtitle,
			    b.notes AS bib_notes,
			    b.copyrightdate AS bib_copyrightdate,
			    b.datecreated AS bib_datecreated,
				b.seriestitle AS bib_seriestitle,
				(
					extractvalue(bm.metadata, '//datafield[@tag="260"]/subfield')
				) AS bib_publisher,
			    bi.biblioitemnumber AS bibitem_biblioitemnumber,
			    bi.volume AS bibitem_volume,
			    bi.itemtype AS bibitem_itemtype, it.description AS bibitem_itemtype_description,
			    bi.isbn AS bibitem_isbn,
			    bi.publishercode AS bibitem_publishercode,
			    bi.editionstatement AS bibitem_editionstatement,
			    bi.illus AS bibitem_illus,
			    bi.pages AS bibitem_pages,
			    bi.notes AS bibitem_notes,
			    bi.`size` AS bibitem_size,
			    bi.place AS bibitem_place,
				'[]' AS bib_cover_images,
			    i.itemnumber AS item_itemnumber,
				i.itype AS item_itype, iti.description AS item_itype_description,
				i.holdingbranch AS item_branchcode_holding, branch_item_holding.branchname AS item_branchname_holding,
				i.homebranch AS item_branchcode_home, branch_item_home.branchname AS item_branchname_home,
				i.itemcallnumber AS item_itemcallnumber,
				i.datelastseen AS item_lastseen,
				i.dateaccessioned AS item_dateaccessioned,
				i.datelastborrowed AS item_datelastborrowed,
			    i.barcode AS item_barcode,
				i.cn_source AS item_call_number_source_code,
			    CASE WHEN i.cn_source = 'ddc' THEN 'Dewey Decimal Classification' ELSE 'Library of Congress Classification' END AS item_call_number_source_name,
			    i.bookable AS item_bookable,
			    i.notforloan AS item_notforloan,
			    i.damaged AS item_damaged,
			    i.itemlost AS item_itemlost,
			    i.onloan AS item_onloan,	/* 應還日 */
				i.issues AS item_issue_count,
				i.renewals AS item_renewal_count,
			    i.`location` AS item_location,
			    i.copynumber AS item_copynumber,
				i.replacementpricedate AS item_replacement_price_date,

				i.withdrawn AS item_withdrawn,
				i.materials AS item_materials_notes,
				i.restricted AS item_restricted_status,
				i.ccode AS item_collection_code,
				i.booksellerid AS item_acquisition_source,
				i.coded_location_qualifier AS item_coded_location_qualifier,
				i.price AS item_purchase_price,
				i.enumchron AS item_serial_issue_number,
				i.stocknumber AS item_inventory_number,
				i.stack AS item_shelving_control_number,
				i.uri AS item_uri,
				i.replacementprice AS item_replacement_price,
				i.itemnotes_nonpublic AS item_internal_notes,
				i.itemnotes AS item_public_notes,

				'[]' AS item_cover_images,

				c.issue_id,
			    c.issuedate AS issue_issuedate,
			    c.date_due AS issue_date_due,
			    CASE WHEN DATEDIFF(c.date_due, now()) < 0 THEN 0 ELSE DATEDIFF(c.date_due, now()) END AS issue_days_due,
			    CASE WHEN DATEDIFF(now(), c.date_due) < 0 THEN 0 ELSE DATEDIFF(now(), c.date_due) END AS issue_days_over,
			    c.borrowernumber AS issue_borrowernumber,
			    UPPER(p.cardnumber) AS issue_borrower_cardnumber,
				c.branchcode AS issue_branchcode,
			    branch_issues.branchname AS issue_branchname,
			    CASE
					WHEN c.borrowernumber = :borrowernumber THEN true
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN true
					ELSE false
				END AS issue_selfcheckout,
				CASE
					WHEN c.issue_id IS NOT NULL THEN false	/* 此書借出中 */
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					WHEN c.borrowernumber = :borrowernumber THEN false
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false
					WHEN (SELECT COUNT(*) FROM h WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 有人預約就無法外借 */
					ELSE true
				END AS issue_cancheckout,
				a.accountlines_id AS issue_accountlines_id,
				a.amount AS issue_accountlines_amount,
				a.amountoutstanding AS issue_accountlines_amountoutstanding,
				a.debit_type_code AS issue_accountlines_debitType_code,
				adt.description AS issue_accountlines_debitType_description,
			    IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"reserve_id", reserve_id,
							"reserve_reservedate", reserve_reservedate,
							"reserve_suspend", reserve_suspend,
							"reserve_found_id", reserve_found_id,
							"reserve_found_name", reserve_found_name,
							"reserve_expirationdate", reserve_expirationdate,
							"reserve_branchcode", reserve_branchcode,
							"reserve_branchname", reserve_branchname,
							"reserve_priority", reserve_priority,
							"borrower_borrowernumber", borrower_borrowernumber,
							"borrower_cardnumber", borrower_cardnumber
						))
					FROM h
					WHERE h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)
				), '[]') AS reserve,
				CASE
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN true
					ELSE false
				END AS reserve_selfhold,
				CASE
					WHEN c.issue_id IS NULL THEN false	/* 尚未借出不能預約 */
					WHEN c.borrowernumber = :borrowernumber THEN false /* 自己已借出了不能預約 */
					WHEN UPPER(p.cardnumber) = UPPER(:cardnumber) THEN false /* 自己已借出了不能預約 */
					WHEN (SELECT COUNT(*) FROM h WHERE selfhold AND h.bib_biblionumber = b.biblionumber AND (h.reserve_itemnumber IS NULL OR h.reserve_itemnumber = i.itemnumber)) > 0 THEN false	/* 已經預約了 */
					WHEN i.exclude_from_local_holds_priority = 1 THEN false	/* 限館內 */
					WHEN i.notforloan = 1 THEN false
					WHEN i.damaged = 1 THEN false
					WHEN i.itemlost = 1 THEN false
					ELSE true
				END AS reserve_canhold,
				ic.items_count
		    FROM biblio AS b
		    	INNER JOIN biblioitems AS bi ON bi.biblionumber = b.biblionumber
				INNER JOIN biblio_metadata AS bm ON bm.biblionumber = b.biblionumber
				LEFT JOIN itemtypes AS it ON it.itemtype = bi.itemtype
				LEFT JOIN (
					SELECT biblionumber, COUNT(*) AS items_count
					FROM items
					WHERE notforloan = 0 AND damaged = 0 AND itemlost = 0 AND withdrawn = 0
					GROUP BY biblionumber
				) AS ic ON ic.biblionumber = b.biblionumber
		    	INNER JOIN items AS i ON i.biblionumber = b.biblionumber
				LEFT JOIN branches AS branch_item_home ON branch_item_home.branchcode = i.homebranch
				LEFT JOIN branches AS branch_item_holding ON branch_item_holding.branchcode = i.holdingbranch
				LEFT JOIN itemtypes AS iti ON iti.itemtype = i.itype
		    	LEFT JOIN issues AS c ON c.itemnumber = i.itemnumber
		    	LEFT JOIN borrowers AS p ON p.borrowernumber = c.borrowernumber
				LEFT JOIN branches AS branch_issues ON branch_issues.branchcode = c.branchcode
		    	LEFT JOIN accountlines AS a ON a.issue_id = c.issue_id AND (p.borrowernumber = :borrowernumber OR UPPER(p.cardnumber) = UPPER(:cardnumber))
		    	LEFT JOIN account_debit_types AS adt ON adt.code = a.debit_type_code
		    WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dtInner
			WHERE TRUE {$select_condition}
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


		// [Export] 匯出獨立判斷不走原本公版
        if ($excel_check) {
		// if (false) {
            // [Export] 對應你SELECT出來column，我稱為解碼器
            $define_columns = [
				"bib_author" => "書目作者", 
				"bib_biblionumber" => "書目編號", 
				"bib_copyrightdate" => "版權日期", 
				"bib_cover_images" => "封面圖片", 
				"bib_datecreated" => "書目創建日期", 
				"bib_notes" => "書目備註", 
				"bib_publisher" => "出版社", 
				"bib_seriestitle" => "叢書名稱", 
				"bib_subtitle" => "副標題", 
				"bib_title" => "書目標題", 
				"bibitem_biblioitemnumber" => "書目項目編號", 
				"bibitem_editionstatement" => "版本說明", 
				"bibitem_illus" => "插圖資訊", 
				"bibitem_isbn" => "ISBN 編號", 
				"bibitem_itemtype" => "項目類型", 
				"bibitem_itemtype_description" => "項目類型描述", 
				"bibitem_notes" => "項目備註", 
				"bibitem_pages" => "頁數", 
				"bibitem_place" => "出版地", 
				"bibitem_publishercode" => "出版社代碼", 
				"bibitem_size" => "尺寸", 
				"bibitem_volume" => "卷次", 
				"issue_accountlines_amount" => "借閱帳戶金額", 
				"issue_accountlines_amountoutstanding" => "尚未支付金額", 
				"issue_accountlines_debitType_code" => "借方類型代碼", 
				"issue_accountlines_debitType_description" => "借方類型描述", 
				"issue_accountlines_id" => "借閱帳戶 ID", 
				"issue_borrower_cardnumber" => "借閱者卡號", 
				"issue_borrowernumber" => "借閱者編號", 
				"issue_branchcode" => "借閱分館代碼", 
				"issue_branchname" => "借閱分館名稱", 
				"issue_cancheckout" => "是否可借出", 
				"issue_date_due" => "借閱到期日期", 
				"issue_days_due" => "借閱剩餘天數", 
				"issue_days_over" => "借閱逾期天數", 
				"issue_id" => "借閱 ID", 
				"issue_issuedate" => "借出日期", 
				"issue_selfcheckout" => "是否為自助借閱", 
				"item_acquisition_source" => "購入來源", 
				"item_barcode" => "條碼", 
				"item_bookable" => "是否可預約", 
				"item_branchcode_holding" => "館藏所在分館代碼", 
				"item_branchcode_home" => "所屬分館代碼", 
				"item_branchname_holding" => "館藏所在分館名稱", 
				"item_branchname_home" => "所屬分館名稱", 
				"item_call_number_source_code" => "索書號來源代碼", 
				"item_call_number_source_name" => "索書號來源名稱", 
				"item_coded_location_qualifier" => "編碼位置限定符", 
				"item_collection_code" => "收藏類別代碼", 
				"item_copynumber" => "副本編號", 
				"item_cover_images" => "館藏封面圖片", 
				"item_damaged" => "是否損壞", 
				"item_dateaccessioned" => "館藏入館日期", 
				"item_datelastborrowed" => "最近借閱日期", 
				"item_internal_notes" => "內部備註", 
				"item_inventory_number" => "盤點編號", 
				"item_issue_count" => "借閱次數", 
				"item_itemcallnumber" => "索書號", 
				"item_itemlost" => "是否遺失", 
				"item_itemnumber" => "館藏項目編號", 
				"item_itype" => "館藏類型", 
				"item_itype_description" => "館藏類型描述", 
				"item_lastseen" => "最近檢視日期", 
				"item_location" => "館藏位置", 
				"item_materials_notes" => "資料備註", 
				"item_notforloan" => "是否不可外借", 
				"item_onloan" => "是否外借中", 
				"item_public_notes" => "公開備註", 
				"item_purchase_price" => "購買價格", 
				"item_renewal_count" => "續借次數", 
				"item_replacement_price" => "替代價格", 
				"item_replacement_price_date" => "替代價格設定日期", 
				"item_restricted_status" => "限制狀態", 
				"item_serial_issue_number" => "期刊號", 
				"item_shelving_control_number" => "排架控制編號", 
				"item_uri" => "資源 URI", 
				"item_withdrawn" => "是否已下架", 
				"items_count" => "館藏數量", 
				"reserve" => "是否預約", 
				"reserve_canhold" => "是否可保持預約", 
				"reserve_selfhold" => "是否為自助預約", 
            ];
            $excel_column = "";
            foreach ($define_columns as $key => $value) {
                $label = $define_columns[$key];
				$excel_column .= " COALESCE(CAST({$key} AS CHAR), '-') AS \"{$label}\","; // [Export] 強制轉型所有欄位為TEXT，以方便後續使用
            }
            $excel_column = rtrim($excel_column, ',');
            $sql_excel = <<<EOD
				SELECT {$excel_column}
				FROM(
					{$cSql_Inner}
				) AS db
EOD;
			
			$stmt = $this->db_koha->prepare($sql_excel); xStatic::BindValue($stmt, $values);
			if (!$stmt->execute()) {
				$oInfo = $stmt->errorInfo();
				$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
				$this->SetError($cMessage);
				return ["status" => "failed", "message" => $stmt->errorInfo()];
			}
			$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
			$aBibNumbers = []; $aItemNumbers = [];
			foreach ($aRows as $row_id => $row_value) {
				if ($row_value["bib_biblionumber"] != null) { $aBibNumbers[] = $row_value["bib_biblionumber"]; }
				if ($row_value["item_itemnumber"] != null) { $aItemNumbers[] = $row_value["item_itemnumber"]; }
				foreach ($row_value as $key => $value) {
					if (xString::IsJson($value)) {
						$aRows[$row_id][$key] = json_decode($value, true);
					}
				}
			}
			$aBibNumbers = array_unique($aBibNumbers); $aItemNumbers = array_unique($aItemNumbers);
			$aRows = $this->fillBibCoverImageFileID($aRows, $aBibNumbers);
			$aRows = $this->fillItemCoverImageFileID($aRows, $aItemNumbers);
			$result = $aRows;
			return $result;
        }


		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		if (!$stmt->execute() || !$stmt_count->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		$aBibNumbers = []; $aItemNumbers = [];
		foreach ($aRows as $row_id => $row_value) {
			if ($row_value["bib_biblionumber"] != null) { $aBibNumbers[] = $row_value["bib_biblionumber"]; }
			if ($row_value["item_itemnumber"] != null) { $aItemNumbers[] = $row_value["item_itemnumber"]; }
			foreach ($row_value as $key => $value) {
				if (xString::IsJson($value)) {
					$aRows[$row_id][$key] = json_decode($value, true);
				}
			}
		}
		$aBibNumbers = array_unique($aBibNumbers); $aItemNumbers = array_unique($aItemNumbers);
		$aRows = $this->fillBibCoverImageFileID($aRows, $aBibNumbers);
		$aRows = $this->fillItemCoverImageFileID($aRows, $aItemNumbers);
		$result['data'] = $aRows;
		$result['total'] = $result_count;
		return $result;
	}
	function fillBibCoverImageFileID($aRows, $aBibNumbers) {
		if (count($aBibNumbers) == 0) { return $aRows; }
		$cList = implode(",", $aBibNumbers);
		$cSql = <<<EOD
			SELECT biblionumber, json_agg(file_id) AS cover_images
			FROM library.cover_images_map2file
			WHERE biblionumber IN ({$cList})
			GROUP BY biblionumber;
EOD;
		$stmt = $this->db->prepare($cSql);
		if (!$stmt->execute()) {
			//$oInfo = $stmt->errorInfo();
			//$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			//$this->SetError($cMessage);
			return $aRows;
		}
		$aMap = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		foreach ($aMap as $drMap) {
			$biblionumber = $drMap["biblionumber"];
			$cover_images = $drMap["cover_images"];
			foreach ($aRows as $iRowIndex => $oRow) {
				if ($oRow["bib_biblionumber"] == $biblionumber) {
					$aRows[$iRowIndex]["bib_cover_images"] = $cover_images;
				}
			}
		}
		return $aRows;
	}
	function fillItemCoverImageFileID($aRows, $aItemNumbers) {
		if (count($aItemNumbers) == 0) { return $aRows; }
		$cList = implode(",", $aItemNumbers);
		$cSql = <<<EOD
			SELECT itemnumber, json_agg(file_id) AS cover_images
			FROM library.cover_images_map2file
			WHERE itemnumber IN ({$cList})
			GROUP BY itemnumber;
EOD;
		$stmt = $this->db->prepare($cSql);
		if (!$stmt->execute()) {
			//$oInfo = $stmt->errorInfo();
			//$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			//$this->SetError($cMessage);
			return $aRows;
		}
		$aMap = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		foreach ($aMap as $drMap) {
			$itemnumber = $drMap["itemnumber"];
			$cover_images = $drMap["cover_images"];
			foreach ($aRows as $iRowIndex => $oRow) {
				if ($oRow["item_itemnumber"] == $itemnumber) {
					$aRows[$iRowIndex]["item_cover_images"] = $cover_images;
				}
			}
		}
		return $aRows;
	}
	public function get_item_api($params) {
		$values = [
			"itemnumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (count($values) == 0) { $this->SetError("必須包含 itemnumber"); return; }
		$itemnumber = $values["itemnumber"];

		if ($itemnumber > 0) {
			$sql = <<<EOD
				SELECT
					itemnumber AS item_id,
					biblionumber AS biblio_id,
					withdrawn,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'WITHDRAWN'
					), '[]') AS withdrawn_options,
					withdrawn_on AS withdrawn_date,
					cn_source AS call_number_source, /* ddc or lcc */
					(
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM (
							SELECT 'ddc' AS authorised_value, 'Dewey Decimal Classification' AS lib, 'Dewey Decimal Classification' AS lib_opac
							UNION
							SELECT 'lcc' AS authorised_value, 'Library of Congress Classification' AS lib, 'Library of Congress Classification' AS lib_opac
						) AS U
					) AS call_number_source_options,
					materials AS materials_notes,
					damaged AS damaged_status,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'DAMAGED'
					), '[]') AS damaged_options,
					damaged_on AS damaged_date,
					restricted AS restricted_status,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'RESTRICTED'
					), '[]') AS restricted_options,
					notforloan AS not_for_loan_status,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'NOT_LOAN'
					), '[]') AS notforloan_options,
					ccode AS collection_code,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'CCODE'
					), '[]') AS collection_options,
					homebranch AS home_library_id,
					holdingbranch AS holding_library_id,
					(
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", branchcode, "lib", branchname, "lib_opac", branchname))
						FROM branches
					) AS library_options,
					`location`,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'LOC'
					), '[]') AS location_options,
					dateaccessioned AS acquisition_date,
					booksellerid AS acquisition_source,
					coded_location_qualifier,
					price AS purchase_price,
					enumchron AS serial_issue_number,
					stocknumber AS inventory_number,
					stack AS shelving_control_number,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
						FROM authorised_values
						WHERE category = 'STACK'
					), '[]') AS shelving_control_number_options,
					itemcallnumber AS callnumber,
					barcode AS external_id,
					copynumber AS copy_number,
					uri,	/* Uniform resource identifier */
					replacementprice AS replacement_price,
					replacementpricedate AS replacement_price_date,
					itemnotes_nonpublic AS internal_notes,
					itype AS item_type_id,
					IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", itemtype, "lib", description, "lib_opac", description))
						FROM itemtypes
					), '[]') AS item_type_options,
					itemnotes AS public_notes
				FROM items
				WHERE itemnumber = :itemnumber
EOD;
		} else {
			$sql = <<<EOD
			SELECT
				0 AS item_id,
				null AS biblio_id,
				null AS withdrawn,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'WITHDRAWN'
				), '[]') AS withdrawn_options,
				null AS withdrawn_date,
				'ddc' AS call_number_source, /* ddc or lcc */
				(
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM (
						SELECT 'ddc' AS authorised_value, 'Dewey Decimal Classification' AS lib, 'Dewey Decimal Classification' AS lib_opac
						UNION
						SELECT 'lcc' AS authorised_value, 'Library of Congress Classification' AS lib, 'Library of Congress Classification' AS lib_opac
					) AS U
				) AS call_number_source_options,
				'' AS materials_notes,
				0 AS damaged_status,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'DAMAGED'
				), '[]') AS damaged_options,
				null AS damaged_date,
				0 AS restricted_status,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'RESTRICTED'
				), '[]') AS restricted_options,
				0 AS not_for_loan_status,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'NOT_LOAN'
				), '[]') AS notforloan_options,
				null AS collection_code,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'CCODE'
				), '[]') AS collection_options,
				(
					SELECT branchcode FROM branches limit 1
				) AS home_library_id,
				(
					SELECT branchcode FROM branches limit 1
				) AS holding_library_id,
				(
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", branchcode, "lib", branchname, "lib_opac", branchname))
					FROM branches
				) AS library_options,
				null AS `location`,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'LOC'
				), '[]') AS location_options,
				null AS acquisition_date,
				'' AS acquisition_source,
				'' AS coded_location_qualifier,
				null AS purchase_price,
				'' AS serial_issue_number,
				'' AS inventory_number,
				null AS shelving_control_number,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", authorised_value, "lib", lib, "lib_opac", lib_opac))
					FROM authorised_values
					WHERE category = 'STACK'
				), '[]') AS shelving_control_number_options,
				'' AS callnumber,
				'' AS external_id,
				'' AS copy_number,
				'' AS uri,	/* Uniform resource identifier */
				null AS replacement_price,
				null AS replacement_price_date,
				'' AS internal_notes,
				(
					SELECT itemtype FROM itemtypes limit 1
				) AS item_type_id,
				IFNULL((
					SELECT JSON_ARRAYAGG(JSON_OBJECT("authorised_value", itemtype, "lib", description, "lib_opac", description))
					FROM itemtypes
				), '[]') AS item_type_options,
				'' AS public_notes
EOD;
		}
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($aRows as $row_id => $row_value) {
				//$aRows[$row_id]["biblio_id"] = (int) $row_value["biblio_id"];
				//$aRows[$row_id]["item_id"] = (int) $row_value["item_id"];
				//$aRows[$row_id]["damaged_status"] = (int) $row_value["damaged_status"];
				//$aRows[$row_id]["restricted_status"] = (int) $row_value["restricted_status"];
				//$aRows[$row_id]["not_for_loan_status"] = (int) $row_value["not_for_loan_status"];
				foreach ($row_value as $key => $value) {
					if (xString::IsJson($value)) {
						$aRows[$row_id][$key] = json_decode($value, true);
					}
				}
			}
			$result['data'] = $aRows;
			$result['total'] = count($aRows);
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	public function post_item($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_item_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_item_single($data) {
		$values = [
			"biblio_id" => null,
			"withdrawn" => null,
			"withdrawn_date" => null,
			"call_number_source" => null,
			"materials_notes" => null,
			"damaged_status" => null,
			"damaged_date" => null,
			"restricted_status" => null,
			"not_for_loan_status" => null,
			"collection_code" => null,
			"home_library_id" => null,
			"holding_library_id" => null,
			"location" => null,
			"acquisition_date" => null,
			"acquisition_source" => null,
			"coded_location_qualifier" => null,
			"purchase_price" => null,
			"serial_issue_number" => null,
			"inventory_number" => null,
			"shelving_control_number" => null,
			"callnumber" => null,
			"external_id" => null,
			"copy_number" => null,
			"uri" => null,
			"replacement_price" => null,
			"replacement_price_date" => null,
			"internal_notes" => null,
			"item_type_id" => null,
			"public_notes" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		//$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		//$callBack->message = json_encode($values);
		//return $callBack;

		$biblio_id = $this->CheckArrayData("biblio_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["biblio_id"]);

		#region 預先轉出特別欄位
		$aSpecialField = [];
		$field = "shelving_control_number"; if (array_key_exists($field, $values)) { $aSpecialField[$field] = $values[$field]; unset($values[$field]); }
		#endregion

		$callBack = $this->callKohaApi("post", "/biblios/{$biblio_id}/items", $values);
		$aSpecialField["item_id"] = $callBack->data->item_id;

		$this->patch_item_specialField("post", $aSpecialField);

		return $callBack;
	}
	public function patch_item($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_item_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_item_single($data) {
		$values = [
			"biblio_id" => null,
            "item_id" => null,
            "withdrawn" => null,
            "withdrawn_date" => null,
            "call_number_source" => null,
            "materials_notes" => null,
            "damaged_status" => null,
            "damaged_date" => null,
            "restricted_status" => null,
            "not_for_loan_status" => null,
            "collection_code" => null,
            "home_library_id" => null,
            "holding_library_id" => null,
            "location" => null,
            "acquisition_date" => null,
            "acquisition_source" => null,
            "coded_location_qualifier" => null,
            "purchase_price" => null,
            "serial_issue_number" => null,
            "inventory_number" => null,
            "shelving_control_number" => null,
            "callnumber" => null,
            "external_id" => null,
            "copy_number" => null,
            "uri" => null,
            "replacement_price" => null,
            "replacement_price_date" => null,
            "internal_notes" => null,
            "item_type_id" => null,
            "public_notes" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$biblio_id = $this->CheckArrayData("biblio_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["biblio_id"]);
		$item_id = $this->CheckArrayData("item_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
		unset($values["item_id"]);

		$acquisition_date = $this->CheckArrayData("acquisition_date", $values, false, true, "d"); if ($this->bErrorOn) { return; }
		if (isset($acquisition_date)) { $values["acquisition_date"] = $acquisition_date->format('Y-m-d'); }
		$replacement_price_date = $this->CheckArrayData("replacement_price_date", $values, false, true, "d"); if ($this->bErrorOn) { return; }
		if (isset($replacement_price_date)) { $values["replacement_price_date"] = $replacement_price_date->format('Y-m-d'); }

		#region 預先轉出特別欄位
		$aSpecialField = ["item_id" => $item_id];
		$field = "shelving_control_number"; if (array_key_exists($field, $values)) { $aSpecialField[$field] = $values[$field]; unset($values[$field]); }
		#endregion

		$callBack = $this->callKohaApi("put", "/biblios/{$biblio_id}/items/{$item_id}", $values);
		$this->patch_item_specialField("put", $aSpecialField);
		return $callBack;
	}
	//有些欄位的異動沒有包含在 api 裡，這裡額外直接操作資料庫
	private function patch_item_specialField($method, $aSpecialField) {
		$item_id = $aSpecialField["item_id"];
		$field = "shelving_control_number";
		if (array_key_exists($field, $aSpecialField)) {
			$shelving_control_number = $aSpecialField[$field];
			if ($method == "put" || ($method == "post" && $shelving_control_number != null)) {
				$sql = "UPDATE items SET stack = :shelving_control_number WHERE itemnumber = :item_id";
				$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, ["shelving_control_number"=> $shelving_control_number, "item_id" => $item_id]);
				if (!$stmt->execute()) {
					$oInfo = $stmt->errorInfo();
					$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
					$this->SetError($cMessage);
					return ["status" => "failed"];
				}
			}
		}
	}
	public function delete_item($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_item_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_item_single($data) {
		$values = [
			"itemnumber" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		$cKey2 = "itemnumber";
		if (!array_key_exists($cKey2, $values)) {
			$callBack->message = "必須包含 {$cKey2}";
			return $callBack;
		}
		$itemnumber = $values["itemnumber"];

		$callBack = $this->callKohaApi("delete", "/items/{$itemnumber}", $values);
		return $callBack;
	}

	public function post_item_label($aRows) {
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		#region init pdf
		$pdf->SetTitle('Item barcode pdf');
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);
		$fontname = TCPDF_FONTS::addTTFfont(__DIR__ . DIRECTORY_SEPARATOR . '/fonts/droidsansfallback.ttf', 'TrueTypeUnicode', '', 96);
		$pdf->SetFont($fontname, '', 12, '', false);
		#endregion

		$fPadding_X = 3;
		$fPadding_Y = 6;
		$iMaxRows = 10;
		$iMaxColumns = 3;
		$iRow = 0;
		$iColumn = 0;
		$fX = 0;
		$fY = 0;
		$fLabelWidth = 0;
		$fLabelHeight = 0;
		foreach ($aRows as $oRow) {
			if ($fLabelWidth == 0) {
				$pdf->AddPage();
				$fLabelWidth = ($pdf->getPageWidth() - $fPadding_X * 2) / $iMaxColumns;
				$fLabelHeight = ($pdf->getPageHeight() - $fPadding_Y * 2 - 20) / $iMaxRows;
				$iRow = 1;
				$iColumn = 1;
				$fX = $fPadding_X;
				$fY = $fPadding_Y;
			}
			if ($iColumn > $iMaxColumns) {
				$iColumn = 1;
				$fX = $fPadding_X; // /r
				$iRow += 1; // /n
				if ($iRow <= $iMaxRows) {
					$fY += $fLabelHeight;
				} else {
					$pdf->AddPage();
					$iRow = 1;
					$fY = $fPadding_Y;
				}
			}
			$style = array(
				'align' => 'C',
				'stretch' => true,
				'fitwidth' => false,
				'border' => true,
				'text' => true,
				'hpadding' => 3,
				'vpadding' => 3,
				'font' => 'helvetica',
				'fontsize' => 8,
				'stretchtext' => 4
			);

			$pdf->setXY($fX, $fY + 3);
			$pdf->Cell(0, 0, $oRow["text"], 0, 1);
			$fTextHeight = $fLabelHeight / 3;
			//text, codetype, x, y, w, h, xres, style, align
			$pdf->write1DBarcode($oRow["barcode"], 'C39', $fX, $fY + $fTextHeight, $fLabelWidth, $fTextHeight * 2, 0.4, $style, 'M');
			$pdf->Ln();

			$iColumn += 1;
			$fX += $fLabelWidth;
		}
		$pdf->Output('barcode.pdf', "D");
		//$result = $pdf->Output('complaint.pdf', 'I');  /* export file */
		//return $pdf->get;
	}
	#endregion

	#region message
	public function get_message_type($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["message_attribute_id"]);
		#endregion

		$values = [
			"borrowernumber" => null,
			"categorycode" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (count($values) == 0) { $this->SetError("傳入參數不正確. borrowernumber 或 categorycode 至少一個"); return; }

		$categorycode = $this->CheckArrayData("categorycode", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		$bIs4NewPatronType = $categorycode == "0";

		#region where condition
		if ($bIs4NewPatronType) {
			unset($values["categorycode"]);
			$cWhere_Inner = "";
		} else {
			$aCondition = [
				"borrowernumber" => " AND mp.borrowernumber = :borrowernumber",
				"categorycode" => " AND mp.categorycode = :categorycode",
			];
			$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		}
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		if ($bIs4NewPatronType) {
			$cSql_Inner = <<<EOD
				SELECT
					0 AS borrower_message_preference_id,
					m.message_attribute_id,
					m.message_name,
					m.takes_days,
					0 AS days_in_advance,
					IFNULL(mt1.is_digest, mt0.is_digest) AS canset_digest,
					CASE WHEN mt1.is_digest = 1 THEN 0 ELSE NULL END AS wants_digest,
					0 AS email
				FROM message_attributes AS m
					INNER JOIN message_transports AS mt0 ON mt0.message_attribute_id = m.message_attribute_id AND mt0.message_transport_type = 'email' AND mt0.is_digest = 0
					LEFT JOIN message_transports AS mt1 ON mt1.message_attribute_id = m.message_attribute_id AND mt1.message_transport_type = 'email' AND mt1.is_digest = 1
				WHERE m.message_attribute_id IN (1, 2, 4, 5, 6, 10)
EOD;
		} else {
			$cSql_Inner = <<<EOD
				SELECT
					mp.borrower_message_preference_id,
					m.message_attribute_id,
					m.message_name,
					m.takes_days,
					mp.days_in_advance,
					IFNULL(mt1.is_digest, mt0.is_digest) AS canset_digest,
					mp.wants_digest,
					CASE WHEN mtp.borrower_message_preference_id IS NULL THEN 0 ELSE 1 END AS email
				FROM message_attributes AS m
					INNER JOIN message_transports AS mt0 ON mt0.message_attribute_id = m.message_attribute_id AND mt0.message_transport_type = 'email' AND mt0.is_digest = 0
					LEFT JOIN message_transports AS mt1 ON mt1.message_attribute_id = m.message_attribute_id AND mt1.message_transport_type = 'email' AND mt1.is_digest = 1
					LEFT JOIN borrower_message_preferences AS mp ON mp.message_attribute_id = m.message_attribute_id
					LEFT JOIN borrower_message_transport_preferences AS mtp ON mtp.borrower_message_preference_id = mp.borrower_message_preference_id AND mtp.message_transport_type = 'email'
				WHERE m.message_attribute_id IN (1, 2, 4, 5, 6, 10) {$cWhere_Inner}
EOD;
		}
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
		if ($stmt->execute() && $stmt_count->execute()) {
			$aChangeToInt = ["key", "borrower_message_preference_id", "message_attribute_id", "takes_days", "days_in_advance", "email", "canset_digest" , "wants_digest"];
			$result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$result_count = $stmt_count->fetchColumn(0);
			foreach ($result['data'] as $row_id => $row_value) {
				foreach ($aChangeToInt as $field) {
					$result['data'][$row_id][$field] = (int) $result['data'][$row_id][$field];
				}
			}
			$result['total'] = $result_count;
			$this->SetOK();
			return $result;
		} else {
			$oInfo = $stmt->errorInfo();
			var_dump($oInfo);
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
	}
	private function post_message_type($borrowernumber, $categorycode, $message) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		foreach ($message as $value) {
			$cSql = <<<EOD
	\n			INSERT INTO borrower_message_preferences (
	\n				borrowernumber, categorycode, message_attribute_id, days_in_advance, wants_digest
	\n			) VALUES (
	\n				:borrowernumber, :categorycode, :message_attribute_id, :days_in_advance, :wants_digest
	\n			);
EOD;
			$messageValues = [];
			$messageValues["borrowernumber"] = $borrowernumber;
			$messageValues["categorycode"] = $categorycode;
			$messageValues["message_attribute_id"] = ["oValue" => $value["message_attribute_id"], "iType" => PDO::PARAM_INT];
			$messageValues["days_in_advance"] = ["oValue" => $value["days_in_advance"], "iType" => PDO::PARAM_INT];
			$messageValues["wants_digest"] = ["oValue" => $value["wants_digest"], "iType" => PDO::PARAM_INT];

			$email = $value["email"];
			$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $messageValues);
			if ($stmt->execute()) {
				$borrower_message_preference_id = $this->db_koha->lastInsertId();
				if ($email == 1) {
					$cSql = <<<EOD
			\n			INSERT INTO borrower_message_transport_preferences (
			\n				borrower_message_preference_id, message_transport_type
			\n			) VALUES (
			\n				{$borrower_message_preference_id}, 'email'
			\n			);
EOD;
					$stmt = $this->db_koha->prepare($cSql);
					if (!$stmt->execute()) {
						$oInfo = $stmt->errorInfo(); $cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
						$callBack->cMessage = $cMessage;
						return $callBack;
					}
				}
			} else {
				$oInfo = $stmt->errorInfo(); $cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
				$callBack->cMessage = $cMessage;
				return $callBack;
			}
		}
		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private function patch_message_type($message_old, $message_new) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->data = null;
		foreach ($message_new as $value_new) {
			$message_attribute_id = $value_new["message_attribute_id"];
			$days_in_advance_new = $value_new["days_in_advance"];
			$wants_digest_new = $value_new["wants_digest"];
			$email_new = $value_new["email"];

			$borrower_message_preference_id_old = null;
			$days_in_advance_old = null;
			$wants_digest_old = null;
			$email_old = null;
			foreach ($message_old as $value_old) {
				if ($value_old["message_attribute_id"] == $message_attribute_id) {
					$borrower_message_preference_id_old = $value_old["borrower_message_preference_id"];
					$days_in_advance_old = $value_old["days_in_advance"];
					$wants_digest_old = $value_old["wants_digest"];
					$email_old = $value_old["email"];
					break;
				}
			}

			$cSql = "";
			$messageValues = [];
			if ($days_in_advance_old != $days_in_advance_new || $wants_digest_old != $wants_digest_new) {
				$cSql = <<<EOD
					UPDATE borrower_message_preferences SET
						days_in_advance = :days_in_advance,
						wants_digest = :wants_digest
					WHERE borrower_message_preference_id = :borrower_message_preference_id_mp;
EOD;
				$messageValues["borrower_message_preference_id_mp"] = ["oValue" => $borrower_message_preference_id_old, "iType" => PDO::PARAM_INT];
				$messageValues["days_in_advance"] = ["oValue" => $days_in_advance_new, "iType" => PDO::PARAM_INT];
				$messageValues["wants_digest"] = ["oValue" => $wants_digest_new, "iType" => PDO::PARAM_INT];
			}
			if ($email_new != $email_old) {
				if ($email_new == 0) {
					$cSql .= <<<EOD
						DELETE FROM borrower_message_transport_preferences WHERE borrower_message_preference_id = :borrower_message_preference_id_mtp;
EOD;
				} else {
					$cSql .= <<<EOD
						INSERT INTO borrower_message_transport_preferences (
							borrower_message_preference_id, message_transport_type
						) VALUES (
							:borrower_message_preference_id_mtp, 'email'
						);
EOD;
				}
				$messageValues["borrower_message_preference_id_mtp"] = ["oValue" => $borrower_message_preference_id_old, "iType" => PDO::PARAM_INT];
			}
			if (count($messageValues) > 0) {
				$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $messageValues);
				if (!$stmt->execute()) {
					$oInfo = $stmt->errorInfo(); $cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
					$callBack->cMessage = $cMessage;
					return $callBack;
				}
			}
		}
		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	#endregion
	#region authority
	public function get_authority($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["summary", "`authid`"]);
		#endregion
		$values = [
			"authid" => null,
			"scope" => null,
			"keyword" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$cWhere_Inner = ""; {
			$aCondition = [];
			if (array_key_exists("authid", $values)) { $aCondition[] = "h.`authid` = :authid"; }

			$scope = $this->CheckArrayData("scope", $values, false, true, "c"); if ($this->bErrorOn) { return; }
			unset($values["scope"]);
			$keyword = $this->CheckArrayData("keyword", $values, false, true, "c"); if ($this->bErrorOn) { return; }
			$keyword = trim($keyword ?? "");
			if ($keyword != "") {
				$bIsMainHeadingA = $scope == "mainHeadingA";
				$bIsMainHeading = $scope == "mainHeading";
				$bIsAllHeading = $scope == "allHeading";
				if (!$bIsMainHeadingA && !$bIsMainHeading && !$bIsAllHeading) {
					$this->SetError("scope [{$scope}]非預期的值.");
					return;
				}
				$tags = $bIsAllHeading ? "" : "[@tag=\"100\" OR @tag=\"150\"]";
				$code = $bIsMainHeadingA ? "[@code=\"a\"]" : "";
				//100:author, 150:topical
				$aCondition[] = "extractvalue(marcxml, '//datafield{$tags}/subfield{$code}') LIKE CONCAT('%', :keyword, '%')";
			}
			if (count($aCondition) > 0) {
				$cWhere_Inner = " AND (" . implode(" OR ", $aCondition) . ")";
			}
		}
		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
			SELECT
				h.`authid`,
				h.authtypecode,
				h.datecreated, h.modification_time,
				h.origincode, h.authtrees,
				h.marc, h.marcxml,
				h.linkid,
				t.auth_tag_to_report,
				t.authtypetext, t.summary,
				CASE
					WHEN h.authtypecode = 'PERSO_NAME' THEN extractvalue(marcxml, '//datafield[@tag=\"100\"]/subfield[@code=\"a\"]')
					WHEN h.authtypecode = 'TOPIC_TERM' THEN extractvalue(marcxml, '//datafield[@tag=\"150\"]/subfield[@code=\"a\"]')
				END AS code_value,
				(
					SELECT COUNT(*)
					FROM biblio_metadata
					WHERE extractvalue(metadata, '//datafield/subfield[@code=\"9\"]') = h.`authid`
				) AS linked_count
			FROM auth_header AS h
				INNER JOIN auth_types AS t ON t.authtypecode = h.authtypecode
			WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dt
            {$order}
EOD;

		$sql = <<<EOD
		SELECT *
        FROM (
			{$cSql_AllRow}
			LIMIT :length
		) dt
		WHERE `key` > :start
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_AllRow}
			) AS c
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute() || !$stmt_count->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();
		foreach ($aRows as $row_id => $row) {
			$field = "marcxml";
			$marc21Json = $this->marcXml2Json($row[$field]);
			unset($row[$field]);
			$row["metadata"] = $marc21Json;

			$this->setField_Author($row, $marc21Json);

			$aRows[$row_id] = $row;
		}
		$result['data'] = $aRows;
		$result['total'] = $result_count;
		$this->SetOK();
		return $result;
	}
	public function post_authority($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_authority_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_authority_single($data) {
		$authtypecode = $this->CheckArrayData("authtypecode", $data, true, false, "c"); if ($this->bErrorOn) { return; }
		$metadata = $this->CheckArrayData("metadata", $data, true, false, "a"); if ($this->bErrorOn) { return; }
		$marcXml = $this->marcJson2Xml($metadata);
		$metadata = $marcXml->asXML();

		$header = array('accept:application/marcxml+xml', "x-authority-type:${authtypecode}");
		$callBack = $this->callKohaApi("post", "/authorities", $metadata, "application/marcxml+xml", $header);

		return $callBack;
	}
	public function patch_authority($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_authority_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_authority_single($data) {
		$authid = $this->CheckArrayData("authid", $data, true, false, "i"); if ($this->bErrorOn) { return; }
		$metadata = $this->CheckArrayData("metadata", $data, true, false, "a"); if ($this->bErrorOn) { return; }
		$marcXml = $this->marcJson2Xml($metadata);
		$metadata = $marcXml->asXML();

		$callBack = $this->callKohaApi("put", "/authorities/" . $authid, $metadata, "application/marcxml+xml");
		if ($callBack->status == "success") {

		}
		return $callBack;
	}
	public function delete_authority($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_authority_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_authority_single($data) {
		$authid = $this->CheckArrayData("authid", $data, true, false, "i"); if ($this->bErrorOn) { return; }
		$callBack = $this->callKohaApi("delete", "/authorities/" . $authid, [], "application/json");

		return $callBack;
	}
	public function get_authority_type($data) {
		$cSql = <<<EOD
			SELECT authtypecode, authtypetext, auth_tag_to_report, summary, ROW_NUMBER() OVER (ORDER BY authtypecode) AS `key`
			FROM auth_types
EOD;
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	#endregion

	#region circulation_rules
	public function get_circulation_rules($params) {
		#region order by
		$order = $this->getOrderby($params, ["p", "branchcode", "categorycode", "itemtype"]);
		#endregion
		$values = [
			"branchcode" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		if (!array_key_exists("branchcode", $values)) { $values["branchcode"] = null; }

		$cSql_Inner = <<<EOD
			WITH bc AS (
				SELECT
					`id`, branchcode, categorycode, itemtype, rule_name, rule_value,
					CASE
						WHEN rule_name IN (
							'note', 'maxissueqty', 'maxonsiteissueqty',
							'issuelength', 'daysmode', 'lengthunit', 'hardduedatecompare', 'hardduedate',
							'decreaseloanholds', 'fine', 'chargeperiod', 'chargeperiod_charge_at', 'firstremind',
							'overduefinescap', 'cap_fine_to_replacement_price', 'finedays', 'maxsuspensiondays', 'suspension_chargeperiod' ,
							'renewalsallowed', 'renewalperiod', 'norenewalbefore', 'noautorenewalbefore', 'auto_renew' ,
							'no_auto_renewal_after', 'no_auto_renewal_after_hard_limit', 'reservesallowed', 'holds_per_day', 'holds_per_record',
							'onshelfholds', 'opacitemholds', 'holds_pickup_period', 'rentaldiscount',
							'unseen_renewals_allowed', 'article_requests'
						) THEN 'P1'
						WHEN categorycode IS NULL AND itemtype IS NULL AND rule_name IN (
							'patron_maxissueqty', 'patron_maxonsiteissueqty', 'max_holds', 'holdallowed', 'hold_fulfillment_policy', 'returnbranch'
						) THEN 'P2'
						WHEN categorycode IS NOT NULL AND itemtype IS NULL AND rule_name IN (
							'patron_maxissueqty', 'patron_maxonsiteissueqty', 'max_holds'
						) THEN 'P3'
						WHEN rule_name = 'waiting_hold_cancellation' THEN 'P4'
						WHEN rule_name IN ('lostreturn', 'processingreturn') THEN 'P5'
						WHEN categorycode IS NULL AND itemtype IS NOT NULL AND rule_name IN (
							'holdallowed', 'hold_fulfillment_policy', 'returnbranch'
						) THEN 'P6'
						ELSE ''
					END AS p
				FROM circulation_rules
				WHERE IFNULL(branchcode, '') = IFNULL(:branchcode, '')
			)
			SELECT
				p, branchcode, categorycode, itemtype,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT("id", `id`, "rule_name", rule_name, "rule_value", rule_value))
					FROM bc
					WHERE p = ps.p AND IFNULL(branchcode, '') = IFNULL(ps.branchcode, '') AND IFNULL(categorycode, '') = IFNULL(ps.categorycode, '') AND IFNULL(itemtype, '') = IFNULL(ps.itemtype, '')
				), JSON_ARRAY()) AS rules
			FROM (
				SELECT DISTINCT p, branchcode, categorycode, itemtype FROM bc
			) AS ps
EOD;

		$sql = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dt
            {$order}
EOD;

		//$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		//$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["rules"] = json_decode($oRow["rules"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	#region fields
	private $fields_P1 = [
		'note',					'maxissueqty',				'maxonsiteissueqty',		'issuelength',				'daysmode',
		'lengthunit',			'hardduedatecompare',		'hardduedate',				'decreaseloanholds',		'fine',
		'chargeperiod',			'chargeperiod_charge_at',	'firstremind',				'overduefinescap',			'cap_fine_to_replacement_price',
		'finedays',				'maxsuspensiondays',		'suspension_chargeperiod',	'renewalsallowed',			'renewalperiod',
		'norenewalbefore',		'noautorenewalbefore',		'auto_renew',				'no_auto_renewal_after',	'no_auto_renewal_after_hard_limit',
		'reservesallowed',		'holds_per_day',			'holds_per_record',			'onshelfholds',				'opacitemholds',
		'holds_pickup_period',	'rentaldiscount',			'unseen_renewals_allowed',	'article_requests',
	];
    private $fields_P2 = [
		'patron_maxissueqty', 'patron_maxonsiteissueqty', 'max_holds', 'holdallowed', 'hold_fulfillment_policy', 'returnbranch'
	];
    private $fields_P2_emptyThenRemove = [
		'holdallowed', 'hold_fulfillment_policy', 'returnbranch'
	];
    private $fields_P3 = [
		'patron_maxissueqty', 'patron_maxonsiteissueqty', 'max_holds'
	];
    private $fields_P4 = [
		'waiting_hold_cancellation'
	];
    private $fields_P5 = [
		'lostreturn', 'processingreturn'
	];
    private $fields_P5_emptyThenRemove = [
		'lostreturn', 'processingreturn'
	];
    private $fields_P6 = [
		'holdallowed', 'hold_fulfillment_policy', 'returnbranch'
	];
	#endregion
	public function post_circulation_rules($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_circulation_rules_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_circulation_rules_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		$p = $this->CheckArrayData("p", $data, true, false, "c"); if ($this->bErrorOn) { return; }
		unset($data["p"]);

		#region 處理傳入參數
		$values = [
			'branchcode' => null,
			'categorycode' => null,
			'itemtype' => null,
			'rules' => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$branchcode = $this->CheckArrayData("branchcode", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		$categorycode = $this->CheckArrayData("categorycode", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		$itemtype = $this->CheckArrayData("itemtype", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		if (!array_key_exists("rules", $values)) { $this->SetError("rules 未傳入."); return; }
		$rules = $this->rules2Class($values["rules"]); if ($this->bErrorOn) { return; }

		switch ($p) {
			case "P1": $callBack = $this->checkAndConvert_circulation_rules_P1(false, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P2": $callBack = $this->checkAndConvert_circulation_rules_P2(false, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P3": $callBack = $this->checkAndConvert_circulation_rules_P3(false, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P4": $callBack = $this->checkAndConvert_circulation_rules_P4(false, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P5": $callBack = $this->checkAndConvert_circulation_rules_P5(false, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P6": $callBack = $this->checkAndConvert_circulation_rules_P6(false, $branchcode, $categorycode, $itemtype, $rules); break;
			default: { $callBack->message = "p [{$p}]非預期的值."; return; }
		} if ($callBack->status === "failed") { return $callBack; }
		$values = $callBack->data;

		if ($p == "P2" || $p == "P5") {
			$fields_emptyThenRemove = $p == "P2" ? $this->fields_P2_emptyThenRemove : $this->fields_P5_emptyThenRemove;
			$emptyValue = $p == "P2" ? "" : "*";
			foreach ($fields_emptyThenRemove as $filed) {
				foreach ($values as $key => $value) {
					if ($value["rule_name"] === $filed && $value["rule_value"] === $emptyValue) {
						unset($values[$key]);
						break;
					}
				}
			}
		}
		#endregion

		$cSql = "INSERT INTO circulation_rules (branchcode, categorycode, itemtype, rule_name, rule_value) VALUES ";
		$aSql_Values = [];
		foreach ($values as $key => $value) {
			$values["branchcode_{$key}"] = $value["branchcode"];
			$values["categorycode_{$key}"] = $value["categorycode"];
			$values["itemtype_{$key}"] = $value["itemtype"];
			$values["rule_name_{$key}"] = $value["rule_name"];
			$values["rule_value_{$key}"] = $value["rule_value"];
			$aSql_Values[] = "(:branchcode_{$key}, :categorycode_{$key}, :itemtype_{$key}, :rule_name_{$key}, :rule_value_{$key})";
			unset($values[$key]);
		}
		$cSql .= implode(",", $aSql_Values); unset($aSql_Values);

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function patch_circulation_rules($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_circulation_rules_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_circulation_rules_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		$p = $this->CheckArrayData("p", $data, true, false, "c"); if ($this->bErrorOn) { return; }
		unset($data["p"]);

		#region 處理傳入參數
		$values = [
			'branchcode' => null,
			'categorycode' => null,
			'itemtype' => null,
			'rules' => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$branchcode = $this->CheckArrayData("branchcode", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		$categorycode = $this->CheckArrayData("categorycode", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		$itemtype = $this->CheckArrayData("itemtype", $values, false, true, "c"); if ($this->bErrorOn) { return; }
		if (!array_key_exists("rules", $values)) { $this->SetError("rules 未傳入."); return; }
		$rules = $this->rules2Class($values["rules"]); if ($this->bErrorOn) { return; }

		switch ($p) {
			case "P1": $callBack = $this->checkAndConvert_circulation_rules_P1(true, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P2": $callBack = $this->checkAndConvert_circulation_rules_P2(true, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P3": $callBack = $this->checkAndConvert_circulation_rules_P3(true, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P4": $callBack = $this->checkAndConvert_circulation_rules_P4(true, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P5": $callBack = $this->checkAndConvert_circulation_rules_P5(true, $branchcode, $categorycode, $itemtype, $rules); break;
			case "P6": $callBack = $this->checkAndConvert_circulation_rules_P6(true, $branchcode, $categorycode, $itemtype, $rules); break;
			default: { $callBack->message = "p [{$p}]非預期的值."; return; }
		} if ($callBack->status === "failed") { return $callBack; }
		$values = $callBack->data;

		$values_no_id = []; $values_has_id = [];
		foreach ($values as $key => $value) {
			if ($value["id"] == null) {
				$values_no_id[] = $value;
			} else {
				$values_has_id[] = $value;
			}
		} $values = [];

		$values_has_id_has_rule_value = [];	//update
		$values_has_id_no_rule_value = [];	//delete
		$values_no_id_has_rule_value = [];	//post
		if ($p == "P2" || $p == "P5") {
			$fields_emptyThenRemove = $p == "P2" ? $this->fields_P2_emptyThenRemove : $this->fields_P5_emptyThenRemove;
			$emptyValue = $p == "P2" ? "" : "*";
			foreach ($values_has_id as $key => $value) {
				$rule_name = $value["rule_name"];
				$isEmptyThenRemove = in_array($rule_name, $fields_emptyThenRemove);
				if ($isEmptyThenRemove) {
					$rule_value = $value["rule_value"];
					$isEmptyValue = $rule_value == $emptyValue;
					if ($isEmptyValue) {
						$values_has_id_no_rule_value[] = $value;	//delete: 特殊欄位，需刪除的預設值
					} else {
						$values_has_id_has_rule_value[] = $value;	//update: 特殊欄位，不是需刪除的值
					}
				} else {
					$values_has_id_has_rule_value[] = $value;	//update: 非特殊欄位
				}
			}
			foreach ($values_no_id as $key => $value) {
				$rule_name = $value["rule_name"];
				$isEmptyThenRemove = in_array($rule_name, $fields_emptyThenRemove);
				if ($isEmptyThenRemove) {
					$rule_value = $value["rule_value"];
					$isEmptyValue = $rule_value == $emptyValue;
					if (!$isEmptyValue) {
						$values_no_id_has_rule_value[] = $value;	//post: 特殊欄位，從需刪除的值(未存在於貢料庫)，改為不是需刪除的值
					}
				}
			}
		} else {
			$values_has_id_has_rule_value = $values_has_id;
		} $values_no_id = []; $values_has_id = [];
		#endregion
		#region update
		if (count($values_has_id_has_rule_value) > 0) {
			$cSql = "REPLACE INTO circulation_rules (id, branchcode, categorycode, itemtype, rule_name, rule_value) VALUES ";
			$aSql_Values = [];
			foreach ($values_has_id_has_rule_value as $key => $value) {
				$values_has_id_has_rule_value["id_{$key}"] = $value["id"];
				$values_has_id_has_rule_value["branchcode_{$key}"] = $value["branchcode"];
				$values_has_id_has_rule_value["categorycode_{$key}"] = $value["categorycode"];
				$values_has_id_has_rule_value["itemtype_{$key}"] = $value["itemtype"];
				$values_has_id_has_rule_value["rule_name_{$key}"] = $value["rule_name"];
				$values_has_id_has_rule_value["rule_value_{$key}"] = $value["rule_value"];
				$aSql_Values[] = "(:id_{$key}, :branchcode_{$key}, :categorycode_{$key}, :itemtype_{$key}, :rule_name_{$key}, :rule_value_{$key})";
				unset($values_has_id_has_rule_value[$key]);
			}
			$cSql .= implode(",", $aSql_Values);
			unset($aSql_Values);

			$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values_has_id_has_rule_value);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$stmt->closeCursor();
		}
		#endregion
		#region delete
		if (count($values_has_id_no_rule_value) > 0) {
			$aID = [];
			foreach ($values_has_id_no_rule_value as $value) {
				$aID[] = $value["id"];
			}
			$cID = implode(",", $aID);
			$cSql = "DELETE FROM circulation_rules WHERE id IN ({$cID}) ";

			$stmt = $this->db_koha->prepare($cSql);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$stmt->closeCursor();
		}
		#endregion
		#region post
		if (count($values_no_id_has_rule_value) > 0) {
			$cSql = "INSERT INTO circulation_rules (branchcode, categorycode, itemtype, rule_name, rule_value) VALUES ";
			$aSql_Values = [];
			foreach ($values_no_id_has_rule_value as $key => $value) {
				$values_no_id_has_rule_value["branchcode_{$key}"] = $value["branchcode"];
				$values_no_id_has_rule_value["categorycode_{$key}"] = $value["categorycode"];
				$values_no_id_has_rule_value["itemtype_{$key}"] = $value["itemtype"];
				$values_no_id_has_rule_value["rule_name_{$key}"] = $value["rule_name"];
				$values_no_id_has_rule_value["rule_value_{$key}"] = $value["rule_value"];
				$aSql_Values[] = "(:branchcode_{$key}, :categorycode_{$key}, :itemtype_{$key}, :rule_name_{$key}, :rule_value_{$key})";
				unset($values_no_id_has_rule_value[$key]);
			}
			$cSql .= implode(",", $aSql_Values); unset($aSql_Values);

			$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values_no_id_has_rule_value);
			if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
			$stmt->closeCursor();
		}
		#endregion
		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function delete_circulation_rules($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_circulation_rules_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_circulation_rules_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }

		$p = $this->CheckArrayData("p", $data, true, false, "c"); if ($this->bErrorOn) { return; }
		unset($data["p"]);

		$fileds = [];
		switch ($p) {
			case "P1": $fileds = $this->fields_P1; break;
			case "P2": $fileds = $this->fields_P2; break;
			case "P3": $fileds = $this->fields_P3; break;
			case "P4": $fileds = $this->fields_P4; break;
			case "P5": $fileds = $this->fields_P5; break;
			case "P6": $fileds = $this->fields_P6; break;
			default: { $callBack->message = "p [{$p}]非預期的值."; return $callBack; } break;
		}
		#region 處理傳入參數
		$values = [
			'branchcode' => null,
			'categorycode' => null,
			'itemtype' => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		if (!array_key_exists("branchcode", $values)) { $values["branchcode"] = null; }
		if (!array_key_exists("categorycode", $values)) { $values["categorycode"] = null; }
		if (!array_key_exists("itemtype", $values)) { $values["itemtype"] = null; }

		if ($p == "P2" || $p == "P5" || $p == "P6") { $values["categorycode"] = null; }
		if ($p == "P2" || $p == "P5" || $p == "P3") { $values["itemtype"] = null; }

		$rule_name_list = [];
		foreach ($fileds as $field) {
			$rule_name_list[] = "'" . $field ."'";
		}
		$rule_name_list = implode(",", $rule_name_list);
		#endregion

		$cSql = <<<EOD
			DELETE FROM circulation_rules
			WHERE IFNULL(branchcode, '') = IFNULL(:branchcode, '')
				AND IFNULL(categorycode, '') = IFNULL(:categorycode, '')
				AND IFNULL(itemtype, '') = IFNULL(:itemtype, '')
				AND rule_name IN ({$rule_name_list})
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private function rules2Class($rules) {
		if (is_array($rules)) {
			foreach ($rules as $key => $rule) {
				$temp = new stdClass();
				$temp->id = array_key_exists("id", $rule) ? $rule["id"] : null;
				$temp->rule_name = $rule["rule_name"];
				$temp->rule_value = $rule["rule_value"];
				$rules[$key] = $temp;
			}
		} else {
			$rules = json_decode($rules); if ($rules === false || json_last_error() !== JSON_ERROR_NONE){ $this->SetError("rules 格式不正確."); return; }
		}
		return $rules;
	}
	private function checkAndConvert_circulation_rules_P1($bIncludeID, $branchcode, $categorycode, $itemtype, $rules) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數

		$aRows = [];
		foreach ($this->fields_P1 as $field) {
			$rule_id = null;
			$rule_value = null;
			foreach ($rules as $rule) {
				if ($rule->rule_name === $field) {
					$rule_id = $rule->id ?? null;
					$rule_value = $rule->rule_value ?? "";
					break;
				}
			}

			switch ($field) {
				#region string
				case 'note':
				case "unseen_renewals_allowed":
				case "article_requests": {
				} break;
				#endregion
				#region int
				case 'maxissueqty':
				case 'maxonsiteissueqty':
				case 'issuelength':
				case 'decreaseloanholds':
				case 'chargeperiod':
				case 'firstremind':
				case 'finedays':
				case 'maxsuspensiondays':
				case 'suspension_chargeperiod':
				case 'renewalsallowed':
				case 'renewalperiod':
				case 'norenewalbefore':
				case 'noautorenewalbefore':
				case 'no_auto_renewal_after':
				case 'reservesallowed':
				case 'holds_per_day':
				case 'holds_per_record':
				case 'holds_pickup_period':
				case "rentaldiscount": {
					if ($rule_value !== "") {
						if (!xInt::To($rule_value)) { $callBack->message = "{$field}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
				case 'daysmode': {
					if ($rule_value !== "") {
						$allow = ["Calendar", "Datedue", "Days", "Dayweek"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				case 'lengthunit': {
					if ($rule_value !== "") {
						$allow = ["days", "hours"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				case 'hardduedatecompare': {
					if ($rule_value !== "") {
						$allow = ["-1", "0", "1"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				#region date
				case 'hardduedate':
				case 'no_auto_renewal_after_hard_limit': {
					if ($rule_value !== "") {
						if (!xDateTime::ToDate($rule_value)) { $callBack->message = "{$field}必須為日期."; return $callBack; }
						$rule_value = $rule_value->format("Y-m-d");
					}
				} break;
				#endregion
				#region float
				case "fine":
				case 'overduefinescap': {
					if ($rule_value !== "") {
						if (!xFloat::To($rule_value)) { $callBack->message = "{$field}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
				#region bool
				case 'chargeperiod_charge_at':
				case 'cap_fine_to_replacement_price':
				case 'auto_renew': {
					if ($rule_value !== "") {
						$allow = ["0", "1"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				#endregion
				case 'onshelfholds': {
					if ($rule_value !== "") {
						$allow = ["0", "1", "2"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				case 'opacitemholds': {
					if ($rule_value !== "") {
						$allow = ["N", "Y", "F"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
			}
			$oRow = [
				"branchcode" => $branchcode,
				"categorycode" => $categorycode,
				"itemtype" => $itemtype,
				"rule_name" => $field,
				"rule_value" => $rule_value,
			];
			if ($bIncludeID) { $oRow["id"] = $rule_id; }
			$aRows[] = $oRow;
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aRows;
		return $callBack;
	}
	private function checkAndConvert_circulation_rules_P2($bIncludeID, $branchcode, $categorycode, $itemtype, $rules) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數
		if ($categorycode != null) { $callBack->message = "categorycode 必須為 null"; return $callBack; }
		if ($itemtype != null) { $callBack->message = "itemtype 必須為 null"; return $callBack; }

		$aRows = [];
		foreach ($this->fields_P2 as $field) {
			$rule_id = null;
			$rule_value = "";
			foreach ($rules as $rule) {
				if ($rule->rule_name === $field) {
					$rule_id = $rule->id ?? null;
					$rule_value = $rule->rule_value ?? "";
					break;
				}
			}
			switch ($field) {
				#region int
				case 'patron_maxissueqty':
				case 'patron_maxonsiteissueqty':
				case "max_holds": {
					if ($rule_value !== "") {
						if (!xInt::To($rule_value)) { $callBack->message = "{$field}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
				case 'holdallowed': {
					if ($rule_value !== "") {
						$allow = ["from_any_library", "from_local_hold_group", "from_home_library", "not_allowed"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				case 'hold_fulfillment_policy': {
					if ($rule_value !== "") {
						$allow = ["any", "holdgroup", "patrongroup", "homebranch", "holdingbranch"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				case 'returnbranch': {
					if ($rule_value !== "") {
						$allow = ["homebranch", "holdingbranch", "noreturn", "returnbylibrarygroup"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
			}
			$oRow = [
				"branchcode" => $branchcode,
				"categorycode" => $categorycode,
				"itemtype" => $itemtype,
				"rule_name" => $field,
				"rule_value" => $rule_value,
			];
			if ($bIncludeID) { $oRow["id"] = $rule_id; }
			$aRows[] = $oRow;
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aRows;
		return $callBack;
	}
	private function checkAndConvert_circulation_rules_P3($bIncludeID, $branchcode, $categorycode, $itemtype, $rules) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數
		if ($categorycode == null || $categorycode == "") { $callBack->message = "categorycode 不可為空值"; return $callBack; }
		if ($itemtype != null) { $callBack->message = "itemtype 必須為 null"; return $callBack; }

		$aRows = [];
		foreach ($this->fields_P3 as $field) {
			$rule_id = null;
			$rule_value = "";
			foreach ($rules as $rule) {
				if ($rule->rule_name === $field) {
					$rule_id = $rule->id ?? null;
					$rule_value = $rule->rule_value ?? "";
					break;
				}
			}
			switch ($field) {
				#region int
				case 'patron_maxissueqty':
				case 'patron_maxonsiteissueqty':
				case "max_holds": {
					if ($rule_value !== "") {
						if (!xInt::To($rule_value)) { $callBack->message = "{$field}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
			}
			$oRow = [
				"branchcode" => $branchcode,
				"categorycode" => $categorycode,
				"itemtype" => $itemtype,
				"rule_name" => $field,
				"rule_value" => $rule_value,
			];
			if ($bIncludeID) { $oRow["id"] = $rule_id; }
			$aRows[] = $oRow;
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aRows;
		return $callBack;
	}
	private function checkAndConvert_circulation_rules_P4($bIncludeID, $branchcode, $categorycode, $itemtype, $rules) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數

		$aRows = [];
		foreach ($this->fields_P4 as $field) {
			$rule_id = null;
			$rule_value = "*";
			foreach ($rules as $rule) {
				if ($rule->rule_name === $field) {
					$rule_id = $rule->id ?? null;
					$rule_value = $rule->rule_value ?? "*";
					break;
				}
			}

			switch ($field) {
				case 'waiting_hold_cancellation': {
					if ($rule_value !== "*") {
						$allow = ["0", "1"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
			}
			$oRow = [
				"branchcode" => $branchcode,
				"categorycode" => $categorycode,
				"itemtype" => $itemtype,
				"rule_name" => $field,
				"rule_value" => $rule_value,
			];
			if ($bIncludeID) { $oRow["id"] = $rule_id; }
			$aRows[] = $oRow;
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aRows;
		return $callBack;
	}
	private function checkAndConvert_circulation_rules_P5($bIncludeID, $branchcode, $categorycode, $itemtype, $rules) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數
		if ($categorycode != null) { $callBack->message = "categorycode 必須為 null"; return $callBack; }
		if ($itemtype != null) { $callBack->message = "itemtype 必須為 null"; return $callBack; }

		$aRows = [];
		foreach ($this->fields_P5 as $field) {
			$rule_id = null;
			$rule_value = "*";
			foreach ($rules as $rule) {
				if ($rule->rule_name === $field) {
					$rule_id = $rule->id ?? null;
					$rule_value = $rule->rule_value ?? "*";
					break;
				}
			}
			switch ($field) {
				case 'lostreturn': {
					if ($rule_value !== "*") {
						$allow = ["refund", "refund_unpaid", "charge", "restore", "0"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
				case 'processingreturn': {
					if ($rule_value !== "*") {
						$allow = ["refund", "refund_unpaid", "0"];
						if (!in_array($rule_value, $allow)) { $callBack->message = "{$field}非允許的值 [{$rule_value}]."; return $callBack; }
					}
				} break;
			}
			$oRow = [
				"branchcode" => $branchcode,
				"categorycode" => $categorycode,
				"itemtype" => $itemtype,
				"rule_name" => $field,
				"rule_value" => $rule_value,
			];
			if ($bIncludeID) { $oRow["id"] = $rule_id; }
			$aRows[] = $oRow;
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aRows;
		return $callBack;
	}
	private function checkAndConvert_circulation_rules_P6($bIncludeID, $branchcode, $categorycode, $itemtype, $rules) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數
		if ($categorycode != null) { $callBack->message = "categorycode 必須為 null"; return $callBack; }
		if ($itemtype == null || $itemtype == "") { $callBack->message = "itemtype 不可為空值"; return $callBack; }

		$aRows = [];
		foreach ($this->fields_P6 as $field) {
			$rule_id = null;
			$rule_value = "";
			foreach ($rules as $rule) {
				if ($rule->rule_name === $field) {
					$rule_id = $rule->id ?? null;
					$rule_value = $rule->rule_value ?? "";
					break;
				}
			}

			switch ($field) {
				case 'holdallowed': {
					if ($rule_value !== "") {
						$allow = ["from_any_library", "from_local_hold_group", "from_home_library", "not_allowed"];
						if (!in_array($rule_value, $allow)) {
							$callBack->message = "{$field}非允許的值 [{$rule_value}].";
							return $callBack;
						}
					}
				}
					break;
				case 'hold_fulfillment_policy': {
					if ($rule_value !== "") {
						$allow = ["any", "holdgroup", "patrongroup", "homebranch", "holdingbranch"];
						if (!in_array($rule_value, $allow)) {
							$callBack->message = "{$field}非允許的值 [{$rule_value}].";
							return $callBack;
						}
					}
				}
					break;
				case 'returnbranch': {
					if ($rule_value !== "") {
						$allow = ["homebranch", "holdingbranch", "noreturn", "returnbylibrarygroup"];
						if (!in_array($rule_value, $allow)) {
							$callBack->message = "{$field}非允許的值 [{$rule_value}].";
							return $callBack;
						}
					}
				}
					break;
			}
			$oRow = [
				"branchcode" => $branchcode,
				"categorycode" => $categorycode,
				"itemtype" => $itemtype,
				"rule_name" => $field,
				"rule_value" => $rule_value,
			];
			if ($bIncludeID) { $oRow["id"] = $rule_id; }
			$aRows[] = $oRow;
		}
		#endregion

		$callBack->status = "success"; $callBack->data = $aRows;
		return $callBack;
	}
	public function get_circulation_rules_type($params) {
		// P0 { branchcode }
		// P1 { categorycode Allow NULL, itemtype Allow NULL } + 32
		// P2 { branchcode, categorycode = NULL, itemtype = NULL } + 6 (如果選項是 Not Set，則是刪除或不新增)
		// P3 { branchcode, categorycode, itemtype = NULL } + 3【Allow ADD】
		// P4 { branchcode, categorycode Allow NULL, itemtype Allow NULL } + 1【Allow ADD】
		// P5 { branchcode, categorycode = NULL, itemtype = NULL }, 如果選項是 Use Default，則是刪除或不新增
		// P6 { branchcode, categorycode = NULL, itemtype } + 3【Allow ADD】
		// *** P2 6個欄位的前3個與 P3 一樣，只是 itemtype 強制為 NULL
		// *** P2 6個欄位的後3個與 P6 一樣，只是 categorycode 強制為 NULL
		$sql = <<<EOD
			SELECT 1 AS `key`, 'P0' AS p, (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'branchcode' AS rule_name, '' AS rule_title,
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
							FROM (
								SELECT '*' AS `value`, 'All' AS text, '' AS title
								UNION ALL
								SELECT branchcode, branchname, '' FROM branches
							) AS u
						) AS options
				) AS u
			) AS rules
			UNION ALL
			SELECT 2, 'P1', (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'categorycode' AS rule_name, 'Patron category' AS rule_title,
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
							FROM (
								SELECT '*' AS `value`, 'All' AS text, '' AS title
								UNION ALL
								SELECT categorycode, description, '' FROM categories
							) AS u
						) AS options
					UNION ALL
					SELECT
						2, 'itemtype', 'Item type',
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
							FROM (
								SELECT '*' AS `value`, 'All' AS text, '' AS title
								UNION ALL
								SELECT itemtype, description, '' FROM itemtypes
							) AS u
						) AS options
					UNION ALL
					SELECT 3, 'note', 'Note', '[]'
					UNION ALL
					SELECT 4, 'maxissueqty', 'Current checkouts allowed', '[]'
					UNION ALL
					SELECT 5, 'maxonsiteissueqty', 'Current on-site checkouts allowed', '[]'
					UNION ALL
					SELECT 6, 'issuelength', 'Loan period', '[]'
					UNION ALL
					SELECT 7, 'daysmode', 'Days mode',
						'[
							{ "text": "Default", "value": "", "title": "Use the system preference \'useDaysMode\' as a default value" },
							{ "text": "Skip closed days", "value": "Calendar", "title": "Use the calendar to skip days the library is closed" },
							{ "text": "Next open day", "value": "Datedue", "title": "Use the calendar to push the due date to the next open day" },
							{ "text": "Ignore the calendar", "value": "Days", "title": "Ignore the calendar" },
							{ "text": "Same week day", "value": "Dayweek", "title": "Use the calendar to push the due date to the next open matching weekday for weekly loan periods, or the next open day otherwise" }
						]'
					UNION ALL
					SELECT 8, 'lengthunit', 'Unit',
						'[ { "text": "Days", "value": "days", "title": "" }, { "text": "Hours", "value": "hours", "title": "" } ]'
					UNION ALL
					SELECT 9, 'hardduedatecompare', 'Hard due date',
						'[
							{ "text": "Before", "value": "-1", "title": "" },
							{ "text": "Exactly on", "value": "0", "title": "" },
							{ "text": "After", "value": "1", "title": "" }
						]'
					UNION ALL
					SELECT 10, 'hardduedate', 'Hard due date', '[]'
					UNION ALL
					SELECT 11, 'decreaseloanholds', 'Decreased loan period for high holds (day)', '[]'
					UNION ALL
					SELECT 12, 'fine', 'Fine amount', '[]'
					UNION ALL
					SELECT 13, 'chargeperiod', 'Fine charging interval', '[]'
					UNION ALL
					SELECT 14, 'chargeperiod_charge_at', 'When to charge',
						'[{ "text": "End of interval", "value": "0", "title": "" },{ "text": "Start of interval", "value": "1", "title": "" }]'
					UNION ALL
					SELECT 15, 'firstremind', 'Fine/suspension grace period', '[]'
					UNION ALL
					SELECT 16, 'overduefinescap', 'Overdue fines cap (amount)	', '[]'
					UNION ALL
					SELECT 17, 'cap_fine_to_replacement_price', 'Cap fine at replacement price', '[]'
					UNION ALL
					SELECT 18, 'finedays', 'Suspension in days (day)', '[]'
					UNION ALL
					SELECT 19, 'maxsuspensiondays', 'Max. suspension duration (day)', '[]'
					UNION ALL
					SELECT 20, 'suspension_chargeperiod', 'Suspension charging interval', '[]'
					UNION ALL
					SELECT 21, 'renewalsallowed', 'Renewals allowed (count)', '[]'
					UNION ALL
					SELECT 22, 'renewalperiod', 'Renewal period', '[]'
					UNION ALL
					SELECT 23, 'norenewalbefore', 'No renewal before', '[]'
					UNION ALL
					SELECT 24, 'noautorenewalbefore', 'No automatic renewal before', '[]'
					UNION ALL
					SELECT 25, 'auto_renew', 'Automatic renewal', '[{ "text": "No", "value": "0", "title": "" },{ "text": "Yes", "value": "1", "title": "" }]'
					UNION ALL
					SELECT 26, 'no_auto_renewal_after', 'No automatic renewal after', '[]'
					UNION ALL
					SELECT 27, 'no_auto_renewal_after_hard_limit', 'No automatic renewal after (hard limit)', '[]'
					UNION ALL
					SELECT 28, 'reservesallowed', 'Holds allowed (total)', '[]'
					UNION ALL
					SELECT 29, 'holds_per_day', 'Holds allowed (daily)', '[]'
					UNION ALL
					SELECT 30, 'holds_per_record', 'Holds per record (count)', '[]'
					UNION ALL
					SELECT 31, 'onshelfholds', 'On shelf holds allowed',
						'[
							{ "text": "Yes", "value": "1", "title": "" },
							{ "text": "If any unavailable", "value": "0", "title": "" },
							{ "text": "If all unavailable", "value": "2", "title": "" }
						]'
					UNION ALL
					SELECT 32, 'opacitemholds', 'OPAC item level holds',
						'[
							{ "text": "Don\'t allow", "value": "N", "title": "" },
							{ "text": "Allow", "value": "Y", "title": "" },
							{ "text": "Force", "value": "F", "title": "" }
						]'
					UNION ALL
					SELECT 33, 'holds_pickup_period', 'Holds pickup period (day)', '[]'
					UNION ALL
					SELECT 34, 'rentaldiscount', 'Rental discount (%)', '[]'
					UNION ALL
					SELECT 35 AS column_order, 'unseen_renewals_allowed' AS rule_name, '不知道用途' AS rule_title, '[]' AS options
					UNION ALL
					SELECT 36 AS column_order, 'article_requests' AS rule_name, '不知道用途' AS rule_title, '[]' AS options
				) AS u
			) AS rules
			UNION ALL
			SELECT 3, 'P2', (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'patron_maxissueqty' AS rule_name, 'Total current checkouts allowed' AS rule_title, '[]' AS options
					UNION ALL
					SELECT
						2, 'patron_maxonsiteissueqty', 'Total current on-site checkouts allowed', '[]'
					UNION ALL
					SELECT
						3, 'max_holds', 'Maximum total holds allowed (count)', '[]'
					UNION ALL
					SELECT
						4, 'holdallowed', 'Hold policy',
						'[
							{ "text": "Not set", "value": "", "title": "" },
							{ "text": "From any library", "value": "from_any_library", "title": "" },
							{ "text": "From local hold group", "value": "from_local_hold_group", "title": "" },
							{ "text": "From home library", "value": "from_home_library", "title": "" },
							{ "text": "No holds allowed", "value": "not_allowed", "title": "" }
						]'
					UNION ALL
					SELECT
						5, 'hold_fulfillment_policy', 'Hold pickup library match',
						'[
							{ "text": "Not set", "value": "", "title": "" },
							{ "text": "any library", "value": "any", "title": "" },
							{ "text": "item\'s hold group", "value": "holdgroup", "title": "" },
							{ "text": "patron\'s hold group", "value": "patrongroup", "title": "" },
							{ "text": "item\'s home library", "value": "homebranch", "title": "" },
							{ "text": "item\'s holding library", "value": "holdingbranch", "title": "" }
						]'
					UNION ALL
					SELECT
						6, 'returnbranch', 'Return policy',
						'[
							{ "text": "Not set", "value": "", "title": "" },
							{ "text": "Item returns home", "value": "homebranch", "title": "" },
							{ "text": "Item returns to issuing library", "value": "holdingbranch", "title": "" },
							{ "text": "Item floats", "value": "noreturn", "title": "" },
							{ "text": "Item floats by library group", "value": "returnbylibrarygroup", "title": "" },

						]'
				) AS u
			) AS rules
			UNION ALL
			SELECT 4, 'P3', (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'categorycode' AS rule_name, 'Patron category' AS rule_title,
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("value", categorycode, "text", description, "title", ''))
							FROM categories
						) AS options
					UNION ALL
					SELECT
						2, 'patron_maxissueqty', 'Total current checkouts allowed', '[]'
					UNION ALL
					SELECT
						3, 'patron_maxonsiteissueqty', 'Total current on-site checkouts allowed', '[]'
					UNION ALL
					SELECT
						4, 'max_holds', 'Total holds allowed', '[]'
				) AS u
			) AS rules
			UNION ALL
			SELECT 5, 'P4', (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'categorycode' AS rule_name, 'Patron category' AS rule_title,
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
							FROM (
								SELECT '*' AS `value`, 'All' AS text, '' AS title
								UNION ALL
								SELECT categorycode, description, '' FROM categories
							) AS u
						) AS options
					UNION ALL
					SELECT
						2, 'itemtype', 'Item type',
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
							FROM (
								SELECT '*' AS `value`, 'All' AS text, '' AS title
								UNION ALL
								SELECT itemtype, description, '' FROM itemtypes
							) AS u
						) AS options
					UNION ALL
					SELECT
						3, 'waiting_hold_cancellation', 'Cancellation allowed', '[{ "text": "No", "value": "0", "title": "" },{ "text": "Yes", "value": "1", "title": "" }]'

				) AS u
			) AS rules
			UNION ALL
			SELECT 6, 'P5', (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'lostreturn' AS rule_name, 'Refund lost item replacement fee' AS rule_title,
						'[
							{ "text": "Use default (Refund lost item charge)", "value": "*", "title": "" },
							{ "text": "Item returns home", "value": "refund", "title": "" },
							{ "text": "Item returns to issuing library", "value": "refund_unpaid", "title": "" },
							{ "text": "Item floats", "value": "charge", "title": "" },
							{ "text": "Item floats by library group", "value": "restore", "title": "" },
							{ "text": "Leave lost item charge", "value": "0", "title": "" }
						]' AS options
					UNION ALL
					SELECT
						2 AS column_order, 'processingreturn' AS rule_name, 'Refund lost item processing fee' AS rule_title,
						'[
							{ "text": "Use default (Refund lost item processing charge)", "value": "*", "title": "" },
							{ "text": "Refund lost item processing charge", "value": "refund", "title": "" },
							{ "text": "Refund lost item processing charge (only if unpaid)", "value": "refund_unpaid", "title": "" },
							{ "text": "Leave lost item processing charge", "value": "0", "title": "" }
						]' AS options
				) AS u
			) AS rules
			UNION ALL
			SELECT 7, 'P6', (
				SELECT
					JSON_ARRAYAGG(JSON_OBJECT(
						"column_order", column_order, "rule_name", rule_name, "rule_title", rule_title, "options", options
					))
				FROM (
					SELECT
						1 AS column_order, 'itemtype' AS rule_name, 'Item type' AS rule_title,
						(
							SELECT
								JSON_ARRAYAGG(JSON_OBJECT("rule_name", itemtype, "text", description, "title", ''))
							FROM itemtypes
						) AS options
					UNION ALL
					SELECT
						2 AS column_order, 'holdallowed', 'Hold policy',
						'[
							{ "text": "From any library", "value": "from_any_library", "title": "" },
							{ "text": "From local hold group", "value": "from_local_hold_group", "title": "" },
							{ "text": "From home library", "value": "from_home_library", "title": "" },
							{ "text": "No holds allowed", "value": "not_allowed", "title": "" }
						]' AS options
					UNION ALL
					SELECT
						3 AS column_order, 'hold_fulfillment_policy', 'Hold pickup library match',
						'[
							{ "text": "any library", "value": "any", "title": "" },
							{ "text": "item\'s hold group", "value": "holdgroup", "title": "" },
							{ "text": "patron\'s hold group", "value": "patrongroup", "title": "" },
							{ "text": "item\'s home library", "value": "homebranch", "title": "" },
							{ "text": "item\'s holding library", "value": "holdingbranch", "title": "" }
						]' AS options
					UNION ALL
					SELECT
						4 AS column_order, 'returnbranch', 'Return policy',
						'[
							{ "text": "Item returns home", "value": "homebranch", "title": "" },
							{ "text": "Item returns to issuing library", "value": "holdingbranch", "title": "" },
							{ "text": "Item floats", "value": "noreturn", "title": "" },
							{ "text": "Item floats by library group", "value": "returnbylibrarygroup", "title": "" }

						]' AS options
				) AS u
			) rules

EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	#endregion
	#region vendor
	public function get_vendor_type($params) {
		$sql = <<<EOD
			SELECT 1000 AS column_order, 'id' AS column_name, '' AS field_title, 'primary key and unique identifier assigned by Koha' AS description, '[]' AS options
			UNION VALUES
				(1001, 'name', 'Name', 'vendor name', '[]'),
				(1002, 'postal', 'Postal address', 'vendor postal address (all lines)', '[]'),
				(1003, 'address1', 'Physical address', 'first line of vendor physical address', '[]'),
				(1004, 'phone', 'Phone', 'vendor phone number', '[]'),
				(1005, 'fax', 'Fax', 'vendor fax number', '[]'),
				(1006, 'url', 'Website', 'vendor web address', '[]'),
				(1007, 'accountnumber', 'Account number', 'vendor account number', '[]'),
				(1008, 'type', 'Vendor type', '', '[]'),

				(1100, 'alias_id', '', 'primary key and unique identifier assigned by Koha', '[]'),
				(1101, 'vendor_id', '', 'linke 2 vendor', '[]'),
				(1102, 'alias', '', 'the alias', '[]'),

				(2000, 'id', '', 'primary key and unique number assigned by Koha', '[]'),
				(2001, 'name', 'Contact name', 'name of contact at vendor', '[]'),
 				(2002, 'position', 'Position', 'contact person\'s position', '[]'),
				(2003, 'phone', 'Phone', 'contact\'s phone number', '[]'),
				(2004, 'altphone', 'Alternative phone', 'contact\'s alternate phone number', '[]'),
				(2005, 'fax', 'Fax', 'contact\'s fax number', '[]'),
				(2006, 'email', 'Email', 'contact\'s email address', '[]'),
				(2007, 'notes', 'Notes', 'notes related to the contact', '[]'),
				(2008, 'acqprimary', 'Primary acquisitions contact', 'is this the primary contact for acquisitions messages', '[]'),
				(2009, 'orderacquisition', 'Contact when ordering', 'should this contact receive acquisition orders', '[]'),
				(2010, 'claimacquisition', 'Contact about late orders', 'should this contact receive acquisitions claims', '[]'),
				(2011, 'serialsprimary', 'Primary serials contact', 'is this the primary contact for serials messages', '[]'),
				(2012, 'claimissues', 'Contact about late issues', 'should this contact receive serial claims', '[]'),
				(2013, 'booksellerid', '', 'link 2 vendor id', '[]'),

				(3000, 'interface_id', '', 'primary key and unique identifier assigned by Koha', '[]'),
				(3001, 'vendor_id', '', 'link to the vendor', '[]'),
				(3002, 'name', 'Name', 'name of the interface', '[]'),
 				(3003, 'type', 'Type', 'type of the interface, authorised value VENDOR_INTERFACE_TYPE', IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT("value", authorised_value, "text", lib, "title", ''))
					FROM authorised_values
					WHERE category = 'VENDOR_INTERFACE_TYPE'
				), '[]')),
				(3004, 'uri', 'URI', 'uri of the interface', '[]'),
				(3005, 'login', 'Login', 'contact\'s fax number', '[]'),
				(3006, 'password', 'Password', 'hashed password', '[]'),
				(3007, 'account_email', 'Account Email', 'account email', '[]'),
				(3008, 'notes', 'Notes', 'notes', '[]'),
				(4001, 'active', 'Vendor is', 'is this vendor active (1 for yes, 0 for no)',
    				'[
						{ "text": "Active", "value": "1", "title": "" },
						{ "text": "Inactive", "value": "0", "title": "" }
				]'),
				(4002, 'listprice', 'InactiveList prices are', 'currency code for list prices', (
						SELECT
							JSON_ARRAYAGG(JSON_OBJECT("value", currency, "text", currency, "title", ''))
						FROM currency
						WHERE active = 1
					)
				),
				(4003, 'invoiceprice', 'Invoice prices are', 'currency code for invoice prices', (
						SELECT
							JSON_ARRAYAGG(JSON_OBJECT("value", currency, "text", currency, "title", ''))
						FROM currency
						WHERE active = 1
					)
				),
				(4004, 'gstreg', 'Tax number registered', 'is your library charged tax (1 for yes, 0 for no)',
    				'[
						{ "text": "Yes", "value": "1", "title": "" },
						{ "text": "No", "value": "0", "title": "" }
					]'),
				(4005, 'listincgst', 'List prices', 'is tax included in list prices (1 for yes, 0 for no)',
    				'[
						{ "text": "Include tax", "value": "1", "title": "" },
						{ "text": "Don\'t include tax", "value": "0", "title": "" }
					]'),
				(4006, 'invoiceincgst', 'Invoice prices', 'is tax included in invoice prices (1 for yes, 0 for no)',
    				'[
						{ "text": "Include tax", "value": "1", "title": "" },
						{ "text": "Don\'t include tax", "value": "0", "title": "" }
					]'),
				(4007, 'tax_rate', 'Tax rate', 'the tax rate the library is charged', '[]'),
				(4008, 'discount', 'Discount', 'discount offered on all items ordered from this vendor', '[]'),
				(4009, 'deliverytime', 'Delivery time', 'vendor delivery time', '[]'),
				(4010, 'notes', 'Order Notes', 'order notes', '[]'),
				(4011, 'address2', '???', 'second line of vendor physical address', '[]'),
				(4012, 'address3', '???', 'third line of vendor physical address', '[]'),
				(4013, 'address4', '???', 'fourth line of vendor physical address', '[]'),
				(4014, 'external_id', '???', 'external id of the vendor', '[]')
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();
		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	#endregion
	#region serial
	private $fileds_serial = [
		"biblionumber" => 0, "subscriptionid" => 0, "librarian" => "", "startdate" => null, "aqbooksellerid" => 0,
		"cost" => 0, "aqbudgetid" => 0, "weeklength" => 0, "monthlength" => 0, "numberlength" => 0,
		"periodicity" => null, "countissuesperunit" => 1, "notes" => null, "status" => "1", "lastvalue1" => null,
		"innerloop1" => 0, "lastvalue2" => null, "innerloop2" => 0, "lastvalue3" => null, "innerloop3" => 0,
		"firstacquidate" => null, "manualhistory" => 0, "irregularity" => null, "skip_serialseq" => 0, "letter" => null,
		"numberpattern" => null, "locale" => null, "distributedto" => null, "internalnotes" => null, "callnumber" => null,
		"location" => "", "branchcode" => "", "lastbranch" => null, "serialsadditems" => 0, "staffdisplaycount" => null,
		"opacdisplaycount" => null, "graceperiod" => 0, "enddate" => null, "closed" => 0, "reneweddate" => null,
		"itemtype" => null, "previousitemtype" => null, "mana_id" => null, "ccode" => null, "published_on_template" => null
	];
	public function get_serial($params) {
		$isNewType = false;
		if (array_key_exists("isNewType", $params)) {
			$isNewType = $params["isNewType"] == "1" ? true : false;
			unset($params["isNewType"]);
		}
		if (!$isNewType) { return $this->get_serial_all($params); }
		$subscriptionid = "";
		if (array_key_exists("subscriptionid", $params)){ $subscriptionid = $params["subscriptionid"] ?? ""; }
		if ($subscriptionid == ""){ return $this->get_serial_p0($params); }
		return $this->get_serial_px($params);
	}
	private function get_serial_all($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["subscriptionid"]);
		#endregion
		$values = [
			"subscriptionid" => null,
			"issn" => null,
			"title" => null,
			"callnumber" => null,
			"publisher" => null,
			"vendor" => null,
			"branchcode" => null,
			"location" => null,
			"enddate" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		if (array_key_exists("branchcode", $values) && $values["branchcode"] === ""){ unset($values["branchcode"]); }
		if (array_key_exists("location", $values) && $values["location"] === ""){ unset($values["location"]); }

		#region where condition
		$aCondition = [
			"subscriptionid" => " AND subscriptionid = :subscriptionid",
			"title" => " AND biblionumber IN (SELECT biblionumber FROM biblio_metadata WHERE extractvalue(metadata, '//datafield[@tag=\"505\"]/subfield[@code=\"t\"] | //datafield[@tag=\"245\" OR @tag=\"246\" OR @tag=\"240\" OR @tag=\"490\"]/subfield[@code=\"a\" OR @code=\"n\"]') LIKE CONCAT('%', :title, '%'))",
			"callnumber" => " AND callnumber LIKE CONCAT('%', :callnumber, '%')",
			"publisher" => " AND biblionumber IN (SELECT biblionumber FROM biblio_metadata WHERE extractvalue(metadata, '//datafield[@tag=\"260\"]/subfield') LIKE CONCAT('%', :publisher, '%'))",
			"vendor" => " AND aqbooksellerid IN (SELECT id FROM aqbooksellers WHERE name LIKE CONCAT('%', :vendor, '%'))",
			"branchcode" => " AND branchcode = :branchcode",
			"location" => " AND location = :location",
			"enddate" => " AND enddate < :enddate",
		];
		$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		if ($cWhere_Inner == "") { $cWhere_Inner = " AND subscriptionid > 0"; }
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];

		$cSql = <<<EOD
			WITH s AS (
				SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM (
					SELECT
						0 AS `biblionumber`,			0 AS `subscriptionid`,			'' AS `librarian`,			NULL AS `startdate`,			0 AS `aqbooksellerid`,
						0 AS `cost`,					0 AS `aqbudgetid`,				0 AS `weeklength`,			0 AS `monthlength`,				0 AS `numberlength`,
						NULL AS `periodicity`,			1 AS `countissuesperunit`,		NULL AS `notes`,			'1' AS `status`,				NULL AS `lastvalue1`,
						0 AS `innerloop1`,				NULL AS `lastvalue2`,			0 AS `innerloop2`,			NULL AS `lastvalue3`,			0 AS `innerloop3`,
						NULL AS `firstacquidate`,		0 AS `manualhistory`,			NULL AS `irregularity`,		0 AS `skip_serialseq`,			NULL AS `letter`,
						NULL AS `numberpattern`,		NULL AS `locale`,				NULL AS `distributedto`,	NULL AS `internalnotes`,		NULL AS `callnumber`,
						'' AS `location`,				'' AS `branchcode`,				NULL AS `lastbranch`,		0 AS `serialsadditems`,			NULL AS `staffdisplaycount`,
						NULL AS `opacdisplaycount`,		0 AS `graceperiod`,				NULL AS `enddate`,			0 AS `closed`,					NULL AS `reneweddate`,
						NULL AS `itemtype`,				NULL AS `previousitemtype`,		NULL AS `mana_id`,			NULL AS `ccode`,				NULL AS `published_on_template`
					UNION ALL
					SELECT * FROM subscription
				) AS u_s
				WHERE TRUE {$cWhere_Inner}
			),
			p AS (
				SELECT *
				FROM (
					SELECT
						0 AS `id`,
						'' AS `label`,
						NULL AS `displayorder`,
						NULL AS `description`,
						'' AS `numberingmethod`,
						NULL AS `label1`,	NULL AS `add1`,	NULL AS `every1`,	NULL AS `whenmorethan1`,	NULL AS `setto1`,	NULL AS `numbering1`,
						NULL AS `label2`,	NULL AS `add2`,	NULL AS `every2`,	NULL AS `whenmorethan2`,	NULL AS `setto2`,	NULL AS `numbering2`,
						NULL AS `label3`,	NULL AS `add3`,	NULL AS `every3`,	NULL AS `whenmorethan3`,	NULL AS `setto3`,	NULL AS `numbering3`
					UNION ALL
					SELECT * FROM subscription_numberpatterns
				) AS u_p
			)
			SELECT
				`key`,
				subscriptionid,
				extractvalue(mt.metadata, '//datafield[@tag=\"023\"]') AS issn,
				extractvalue(metadata, '//datafield[@tag=\"505\"]/subfield[@code=\"t\"] | //datafield[@tag=\"245\" OR @tag=\"246\" OR @tag=\"240\" OR @tag=\"490\"]/subfield[@code=\"a\" OR @code=\"n\"]') AS title,
				s.notes, s.internalnotes,
				s.branchcode, br.branchname,
				location, l.lib AS locationname,
				s.callnumber,
				s.enddate,
				aqbooksellerid,
				s.biblionumber,
				serialsadditems,
				skip_serialseq,
				manualhistory,
				callnumber,
				letter,
				ccode,
				itemtype,
				graceperiod,
				staffdisplaycount,
				opacdisplaycount,
				/* P2 */
				firstacquidate,
				periodicity,
				numberlength,
				weeklength, monthlength,
				startdate, enddate,
				numberpattern,
				published_on_template,
				locale,
				lastvalue1, innerloop1,
				lastvalue2,	innerloop2,
				lastvalue3,	innerloop3,
				/* P3 */
				p.label,
				p.numberingmethod,
				p.label1, s.lastvalue1, p.add1, p.every1, p.setto1, p.whenmorethan1, s.innerloop1, p.numbering1,
				p.label2, s.lastvalue2, p.add2, p.every2, p.setto2, p.whenmorethan2, s.innerloop2, p.numbering2,
				p.label3, s.lastvalue3, p.add3, p.every3, p.setto3, p.whenmorethan3, s.innerloop3, p.numbering3,
				/* P4 */
				librarian,
				cost,
				aqbudgetid,
				countissuesperunit,
				status,
				irregularity,
				distributedto,
				lastbranch,
				closed,
				reneweddate,
				previousitemtype,
				mana_id,
				(SELECT COUNT(*) AS total FROM s) AS total
			FROM s
				INNER JOIN p ON p.id = IFNULL(s.numberpattern, 0)
				LEFT JOIN biblio_metadata AS mt ON mt.biblionumber = s.biblionumber
				LEFT JOIN branches AS br ON br.branchcode = s.branchcode
				LEFT JOIN authorised_values AS l ON l.category = 'LOC' AND l.authorised_value = s.location
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$total = 0;
		if (count($aRows) > 0) {
			$total = $aRows[0]["total"];
			foreach ($aRows as $iIndex => $oRow) {
				unset($aRows[$iIndex]["total"]);
			}
		}

		$result['data'] = $aRows;
		$result['total'] = $total;
		$this->SetOK();
		return $result;
	}
	private function get_serial_p0($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["subscriptionid"]);
		#endregion
		$values = [
			"issn" => null,
			"title" => null,
			"callnumber" => null,
			"publisher" => null,
			"vendor" => null,
			"branchcode" => null,
			"location" => null,
			"enddate" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		if (array_key_exists("branchcode", $values) && $values["branchcode"] === ""){ unset($values["branchcode"]); }
		if (array_key_exists("location", $values) && $values["location"] === ""){ unset($values["location"]); }

		#region where condition
		$aCondition = [
			"title" => " AND biblionumber IN (SELECT biblionumber FROM biblio_metadata WHERE extractvalue(metadata, '//datafield[@tag=\"505\"]/subfield[@code=\"t\"] | //datafield[@tag=\"245\" OR @tag=\"246\" OR @tag=\"240\" OR @tag=\"490\"]/subfield[@code=\"a\" OR @code=\"n\"]') LIKE CONCAT('%', :title, '%'))",
			"callnumber" => " AND callnumber LIKE CONCAT('%', :callnumber, '%')",
			"publisher" => " AND biblionumber IN (SELECT biblionumber FROM biblio_metadata WHERE extractvalue(metadata, '//datafield[@tag=\"260\"]/subfield') LIKE CONCAT('%', :publisher, '%'))",
			"vendor" => " AND aqbooksellerid IN (SELECT id FROM aqbooksellers WHERE name LIKE CONCAT('%', :vendor, '%'))",
			"branchcode" => " AND branchcode = :branchcode",
			"location" => " AND location = :location",
			"enddate" => " AND enddate < :enddate",
		];
		$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];

		$cSql = <<<EOD
			WITH s AS (
				SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM subscription
				WHERE TRUE {$cWhere_Inner}
			)
			SELECT
				`key`,
				subscriptionid,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 0, "phrase_name", 'Serials subscriptions',
						"columns", JSON_ARRAY(
							JSON_OBJECT(
								"order", 1, "title", "ISSN", "value", "",
								"text", extractvalue(mt.metadata, '//datafield[@tag=\"023\"]')
							),
							JSON_OBJECT(
								"order", 2, "title", "Title", "value", s.subscriptionid,
								"text", extractvalue(metadata, '//datafield[@tag=\"505\"]/subfield[@code=\"t\"] | //datafield[@tag=\"245\" OR @tag=\"246\" OR @tag=\"240\" OR @tag=\"490\"]/subfield[@code=\"a\" OR @code=\"n\"]')
							),
							JSON_OBJECT(
								"order", 3, "title", "Notes", "value", "",
								"text", CONCAT(IFNULL(s.notes, ''), CASE WHEN s.internalnotes IS NULL THEN '' ELSE CONCAT(' (', s.internalnotes,')') END)
							),
							JSON_OBJECT(
								"order", 4, "title", "Library", "value", "",
								"text", IFNULL(br.branchname, '')
							),
							JSON_OBJECT(
								"order", 5, "title", "Location", "value", "",
								"text", IFNULL(l.lib, '')
							),
							JSON_OBJECT(
								"order", 6, "title", "Call number", "value", "",
								"text", IFNULL(s.callnumber, '')
							),
							JSON_OBJECT(
								"order", 7, "title", "Expiration date", "value", "",
								"text", IFNULL(s.enddate, '')
							)
						)
					)
				) AS phrases
			FROM s
				INNER JOIN biblio_metadata AS mt ON mt.biblionumber = s.biblionumber
				LEFT JOIN branches AS br ON br.branchcode = s.branchcode
				LEFT JOIN authorised_values AS l ON l.category = 'LOC' AND l.authorised_value = s.location
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	private function get_serial_px($params) {
		$subscriptionid = $params["subscriptionid"] ?? 0;
		$cSql = <<<EOD
			WITH s AS (
				SELECT *, 1 AS `key`
				FROM (
					SELECT
						0 AS `biblionumber`,			0 AS `subscriptionid`,			'' AS `librarian`,			NULL AS `startdate`,			0 AS `aqbooksellerid`,
						0 AS `cost`,					0 AS `aqbudgetid`,				0 AS `weeklength`,			0 AS `monthlength`,				0 AS `numberlength`,
						NULL AS `periodicity`,			1 AS `countissuesperunit`,		NULL AS `notes`,			'1' AS `status`,				NULL AS `lastvalue1`,
						0 AS `innerloop1`,				NULL AS `lastvalue2`,			0 AS `innerloop2`,			NULL AS `lastvalue3`,			0 AS `innerloop3`,
						NULL AS `firstacquidate`,		0 AS `manualhistory`,			NULL AS `irregularity`,		0 AS `skip_serialseq`,			NULL AS `letter`,
						NULL AS `numberpattern`,		NULL AS `locale`,				NULL AS `distributedto`,	NULL AS `internalnotes`,		NULL AS `callnumber`,
						'' AS `location`,				'' AS `branchcode`,				NULL AS `lastbranch`,		0 AS `serialsadditems`,			NULL AS `staffdisplaycount`,
						NULL AS `opacdisplaycount`,		0 AS `graceperiod`,				NULL AS `enddate`,			0 AS `closed`,					NULL AS `reneweddate`,
						NULL AS `itemtype`,				NULL AS `previousitemtype`,		NULL AS `mana_id`,			NULL AS `ccode`,				NULL AS `published_on_template`
					UNION ALL
					SELECT * FROM subscription
				) AS u_s
				WHERE subscriptionid = :subscriptionid
			),
			p AS (
				SELECT
					0 AS `id`,
					'' AS `label`,
					NULL AS `displayorder`,
					NULL AS `description`,
					'' AS `numberingmethod`,
					NULL AS `label1`,	NULL AS `add1`,	NULL AS `every1`,	NULL AS `whenmorethan1`,	NULL AS `setto1`,	NULL AS `numbering1`,
					NULL AS `label2`,	NULL AS `add2`,	NULL AS `every2`,	NULL AS `whenmorethan2`,	NULL AS `setto2`,	NULL AS `numbering2`,
					NULL AS `label3`,	NULL AS `add3`,	NULL AS `every3`,	NULL AS `whenmorethan3`,	NULL AS `setto3`,	NULL AS `numbering3`
				UNION ALL
				SELECT p.*
				FROM subscription_numberpatterns AS p
					INNER JOIN s ON s.numberpattern = p.id
			)
			SELECT
				`key`,
				subscriptionid,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 1, "phrase_name", 'Subscription details',
						"columns", JSON_OBJECT(
							"aqbooksellerid", aqbooksellerid,	"biblionumber", biblionumber,
							"serialsadditems", serialsadditems,	"skip_serialseq", skip_serialseq,
							"manualhistory", manualhistory,
							"callnumber", callnumber,
							"branchcode", branchcode,
							"notes", notes,
							"letter", letter,
							"location", location,
							"ccode", ccode,						"itemtype", itemtype,
							"graceperiod", graceperiod,
							"staffdisplaycount", staffdisplaycount,
							"opacdisplaycount", opacdisplaycount
						),
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'aqbooksellerid' AS column_name, 'Vendor' AS column_title, '' AS description, IFNULL((
									SELECT
										JSON_ARRAYAGG(JSON_OBJECT("value", id, "text", name, "title", ''))
									FROM aqbooksellers
								), JSON_ARRAY()) AS options
								UNION VALUES
								('biblionumber', 'Record', '', IFNULL((
									SELECT
										JSON_ARRAYAGG(JSON_OBJECT("value", b.biblionumber, "text", CONCAT(b.title, IFNULL(b.subtitle, '')), "title", ''))
									FROM biblio AS b
										INNER JOIN s ON s.biblionumber = b.biblionumber
								), JSON_ARRAY())),
								('serialsadditems', 'When receiving this serial', '', JSON_ARRAY(
									JSON_OBJECT("value", 1, "text", 'Create an item record', "title", ''),
									JSON_OBJECT("value", 0, "text", 'Do not create an item record', "title", '')
								)),
								('skip_serialseq', 'recordWhen there is an irregular issue', '', JSON_ARRAY(
									JSON_OBJECT("value", 1, "text", 'Skip issue number', "title", ''),
									JSON_OBJECT("value", 0, "text", 'Keep issue number', "title", '')
								)),
								('branchcode', 'Library', '', IFNULL((
									SELECT
										JSON_ARRAYAGG(JSON_OBJECT("value", branchcode, "text", branchname, "title", ''))
									FROM branches
								), JSON_ARRAY())),
								('letter', 'Patron notification', '', JSON_ARRAY(
									JSON_OBJECT("value", '', "text", 'None', "title", ''),
									JSON_OBJECT("value", 'SERIAL_ALERT', "text", 'New serial issue', "title", '')
								)),
								('location', 'Location', '', IFNULL((
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
									FROM (
										SELECT '' AS `value`, 'None' AS text, '' AS title
										UNION
										SELECT authorised_value, lib, '' FROM authorised_values WHERE category = 'LOC'
									) AS u12
								), JSON_ARRAY())),
								('ccode', 'Collection', '', IFNULL((
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
									FROM (
										SELECT '' AS `value`, 'None' AS text, '' AS title
										UNION
										SELECT authorised_value, lib, '' FROM authorised_values WHERE category = 'CCODE'
									) AS u13
								), JSON_ARRAY())),
								('itemtype', 'Item type', '', IFNULL((
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
									FROM (
										SELECT '' AS `value`, 'Not set' AS text, '' AS title
										UNION
										SELECT itemtype, description, '' FROM itemtypes
									) AS u14
								), JSON_ARRAY())),
								('staffdisplaycount', 'Number of issues to display to staff', (
									SELECT CONCAT('Default:', `value`, ' (StaffSerialIssueDisplayCount system preference)') FROM systempreferences WHERE `variable` = 'StaffSerialIssueDisplayCount'
								), JSON_ARRAY()),
								('opacdisplaycount', 'Number of issues to display to the public', (
									SELECT CONCAT('Default:', `value`, ' (OPACSerialIssueDisplayCount system preference)') FROM systempreferences WHERE `variable` = 'OPACSerialIssueDisplayCount'
								), JSON_ARRAY())
							) AS o
						)
					),
					JSON_OBJECT(
						"phrase", 2, "phrase_name", 'Serials planning',
						"columns", JSON_OBJECT(
							"firstacquidate", firstacquidate,
							"periodicity", periodicity,
							"numberlength", numberlength,	"weeklength", weeklength,	"monthlength", monthlength,
							"startdate", startdate,			"enddate", enddate,
							"numberpattern", numberpattern,
							"published_on_template", published_on_template,
							"locale", locale,
							"location", location,
							"lastvalue1", lastvalue1,	"innerloop1", innerloop1,
							"lastvalue2", lastvalue2,	"innerloop2", innerloop2,
							"lastvalue3", lastvalue3,	"innerloop3", innerloop3
						),
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'periodicity' AS column_name, 'Frequency' AS column_title, '' AS description, IFNULL((
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
									FROM (
										SELECT '' AS `value`, '-- please choose --' AS text, '' AS title, 0 AS displayorder
										UNION ALL
										SELECT id, description, '', displayorder
										FROM subscription_frequencies
										ORDER BY displayorder
									) AS u2
								), JSON_ARRAY()) AS options
								UNION VALUES
								('numberpattern', 'Numbering pattern', '', IFNULL((
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
									FROM (
										SELECT '' AS `value`, '-- please choose --' AS text, '' AS title, 0 AS displayorder
										UNION ALL
										SELECT id, label, '', displayorder
										FROM subscription_numberpatterns
										ORDER BY displayorder
									) AS u6
								), JSON_ARRAY())),
								('locale', 'Locale', '', (
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
									FROM (
										SELECT '' AS `value`, '-- please choose --' AS text, '' AS title
										UNION ALL
										SELECT r2i.iso639_2_code, CASE WHEN d.description IS NULL THEN r.description ELSE CONCAT(d.description, ' (', r.description, ')') END AS text, ''
										FROM language_subtag_registry AS r
											INNER JOIN language_rfc4646_to_iso639 AS r2i ON r2i.rfc4646_subtag = r.subtag
											LEFT JOIN language_descriptions AS d ON d.subtag = r2i.rfc4646_subtag AND d.`type` = r.`type` AND d.lang = d.subtag
										WHERE r.`type` = 'language'
										ORDER BY `value`
									) AS u8
								))
							) AS o
						)
					),
					JSON_OBJECT(
						"phrase", 3, "phrase_name", 'advanced pattern',
						"columns", JSON_OBJECT(
							"id", p.id,
							"label", p.label,
							"numberingmethod", p.numberingmethod,

							"label1", p.label1,
							"lastvalue1", s.lastvalue1,
							"add1", p.add1, "every1", p.every1, "setto1", p.setto1, "whenmorethan1", p.whenmorethan1,
							"innerloop1", s.innerloop1,
							"numbering1", p.numbering1,

							"label2", p.label2,
							"lastvalue2", s.lastvalue2,
							"add2", p.add2, "every2", p.every2, "setto2", p.setto2, "whenmorethan2", p.whenmorethan2,
							"innerloop2", s.innerloop2,
							"numbering2", p.numbering2,

							"label3", p.label3,
							"lastvalue3", s.lastvalue3,
							"add3", p.add3, "every3", p.every3, "setto3", p.setto3, "whenmorethan3", p.whenmorethan3,
							"innerloop3", s.innerloop3,
							"numbering3", p.numbering3
						),
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'numbering1' AS column_name, 'Formatting' AS column_title, '' AS description, JSON_ARRAY(
									JSON_OBJECT("value", '', "text", '', "title", ''),
									JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
									JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
									JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
									JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
									JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
								) AS options
							) AS o
						)
					),
					JSON_OBJECT(
						"phrase", 4, "phrase_name", 'other',
						"columns", JSON_OBJECT(
							"librarian", librarian,
							"cost", cost,
							"aqbudgetid", aqbudgetid,
							"countissuesperunit", countissuesperunit,
							"status", status,
							"irregularity", irregularity,
							"distributedto", distributedto,
							"lastbranch", lastbranch,
							"closed", closed,
							"reneweddate", reneweddate,
							"previousitemtype", previousitemtype,
							"mana_id", mana_id
						)
					)
				) AS phrases
			FROM s
				LEFT JOIN p ON p.id = s.numberpattern
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["subscriptionid" => $subscriptionid]);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	public function post_serial($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_serial_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_serial_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_serial(false, $data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		#endregion

		$cSql = <<<EOD
			INSERT INTO subscription (
				`biblionumber`,									`librarian`,		`startdate`,			`aqbooksellerid`,
				`cost`,					`aqbudgetid`,			`weeklength`,		`monthlength`,			`numberlength`,
				`periodicity`,			`countissuesperunit`,	`notes`,			`status`,				`lastvalue1`,
				`innerloop1`,			`lastvalue2`,			`innerloop2`,		`lastvalue3`,			`innerloop3`,
				`firstacquidate`,		`manualhistory`,		`irregularity`,		`skip_serialseq`,		`letter`,
				`numberpattern`,		`locale`,				`distributedto`,	`internalnotes`,		`callnumber`,
				`location`,				`branchcode`,			`lastbranch`,		`serialsadditems`,		`staffdisplaycount`,
				`opacdisplaycount`,		`graceperiod`,			`enddate`,			`closed`,				`reneweddate`,
				`itemtype`,				`previousitemtype`,		`mana_id`,			`ccode`,				`published_on_template`
			) VALUES (
				:biblionumber,									:librarian,			:startdate,				:aqbooksellerid,
				:cost,					:aqbudgetid,			:weeklength,		:monthlength,			:numberlength,
				:periodicity,			:countissuesperunit,	:notes,				:status,				:lastvalue1,
				:innerloop1,			:lastvalue2,			:innerloop2,		:lastvalue3,			:innerloop3,
				:firstacquidate,		:manualhistory,			:irregularity,		:skip_serialseq,		:letter,
				:numberpattern,			:locale,				:distributedto,		:internalnotes,			:callnumber,
				:location,				:branchcode,			:lastbranch,		:serialsadditems,		:staffdisplaycount,
				:opacdisplaycount,		:graceperiod,			:enddate,			:closed,				:reneweddate,
				:itemtype,				:previousitemtype,		:mana_id,			:ccode,					:published_on_template
			)
EOD;

		//$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		//if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		//$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function patch_serial($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_serial_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_serial_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_serial(true, $data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		#endregion

		$cSql = <<<EOD
			REPLACE INTO subscription (
				`biblionumber`,			`subscriptionid`,		`librarian`,		`startdate`,			`aqbooksellerid`,
				`cost`,					`aqbudgetid`,			`weeklength`,		`monthlength`,			`numberlength`,
				`periodicity`,			`countissuesperunit`,	`notes`,			`status`,				`lastvalue1`,
				`innerloop1`,			`lastvalue2`,			`innerloop2`,		`lastvalue3`,			`innerloop3`,
				`firstacquidate`,		`manualhistory`,		`irregularity`,		`skip_serialseq`,		`letter`,
				`numberpattern`,		`locale`,				`distributedto`,	`internalnotes`,		`callnumber`,
				`location`,				`branchcode`,			`lastbranch`,		`serialsadditems`,		`staffdisplaycount`,
				`opacdisplaycount`,		`graceperiod`,			`enddate`,			`closed`,				`reneweddate`,
				`itemtype`,				`previousitemtype`,		`mana_id`,			`ccode`,				`published_on_template`
			) VALUES (
				:biblionumber,			:subscriptionid,		:librarian,			:startdate,				:aqbooksellerid,
				:cost,					:aqbudgetid,			:weeklength,		:monthlength,			:numberlength,
				:periodicity,			:countissuesperunit,	:notes,				:status,				:lastvalue1,
				:innerloop1,			:lastvalue2,			:innerloop2,		:lastvalue3,			:innerloop3,
				:firstacquidate,		:manualhistory,			:irregularity,		:skip_serialseq,		:letter,
				:numberpattern,			:locale,				:distributedto,		:internalnotes,			:callnumber,
				:location,				:branchcode,			:lastbranch,		:serialsadditems,		:staffdisplaycount,
				:opacdisplaycount,		:graceperiod,			:enddate,			:closed,				:reneweddate,
				:itemtype,				:previousitemtype,		:mana_id,			:ccode,					:published_on_template
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function delete_serial($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_serial_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_serial_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }

		$subscriptionid = array_key_exists("subscriptionid", $data) ? $data["subscriptionid"] : 0;

		$cSql = <<<EOD
			DELETE FROM subscription
			WHERE subscriptionid = :subscriptionid
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["subscriptionid" => $subscriptionid]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private function checkAndConvert_serial($bIncludeID, $data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數

		$aValues = $this->fileds_serial;
		$subscriptionid = array_key_exists("subscriptionid", $data) ? $data["subscriptionid"] : 0;
		if (array_key_exists("phrases", $data)) {
			foreach($data["phrases"] as $key => $phrase) {
				if ($phrase["phrase"] == 3) { continue; }
				foreach($phrase["columns"] as $column_name => $column_value) {
					if (array_key_exists($column_name, $this->fileds_serial)) {
						$aValues[$column_name] = $column_value;
					}
				}
			}
		} else {
			$aValues = xStatic::KeyMatchThenReplaceValue($aValues, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		}
		$aValues["subscriptionid"] = $subscriptionid;

		foreach ($this->fileds_serial as $column_name => $default_value) {
			$column_value = array_key_exists($column_name, $aValues) ? $aValues[$column_name] : null;
			if ($column_value === null && $default_value !== null) { $callBack->message = "{$column_name}不可為空值."; return $callBack; }
			if ($column_value == $default_value) { continue; }

			switch ($column_name) {
				#region int
				case 'biblionumber':
				case 'aqbooksellerid':
				case 'cost':
				case 'aqbudgetid':
				case 'weeklength': case 'monthlength': case 'numberlength':
				case 'periodicity':
				case 'countissuesperunit':
				case 'lastvalue1': case 'innerloop1':
				case 'lastvalue2': case 'innerloop2':
				case 'lastvalue3': case 'innerloop3':
				case 'staffdisplaycount':
				case 'opacdisplaycount':
				case 'graceperiod':
				case 'closed': {
					if ($column_value != null) {
						if (!xInt::To($column_value)) { $callBack->message = "{$column_name}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
				#region date
				case 'startdate': case 'enddate':
				case 'firstacquidate': case 'reneweddate': {
					if ($column_value != null) {
						if (!xDateTime::ToDate($column_value)) { $callBack->message = "{$column_name}必須為日期."; return $callBack; }
						$aValues[$column_name] = $column_value->format("Y-m-d");
					}
				} break;
				#endregion
				#region float
				/*
				case '---': {
					if ($rule_value !== "") {
						if (!xFloat::To($rule_value)) { $callBack->message = "{$field}必須為數字."; return $callBack; }
					}
				} break;
				*/
				#endregion
				#region bool
				case 'manualhistory':
				case 'serialsadditems':
				case 'skip_serialseq':
				case 'serialsadditems': {
					if ($column_value != null) {
						$allow = [0, 1];
						if (!in_array($column_value, $allow)) { $callBack->message = "{$column_name}必須為 0 / 1."; return $callBack; }
					}
				} break;
				#endregion
				case 'letter': {
					if ($column_value != null && $column_value != '') {
						$allow = ["SERIAL_ALERT"];
						if (!in_array($column_value, $allow)) { $callBack->message = "{$column_name}非允許的值 [{$column_value}]."; return $callBack; }
					}
				} break;
			}
		}
		if (!$bIncludeID) { unset($aValues["subscriptionid"]); }
		#endregion

		$callBack->status = "success"; $callBack->data = $aValues;
		return $callBack;
	}
	public function get_serial_type($params) {
		$sql = <<<EOD
					SELECT 1 AS column_order, 'id' AS column_name, 'Subscription number' AS column_title, 'unique key for this subscription' AS description, JSON_ARRAY() AS options
					UNION VALUES
					(2, 'aqbooksellerid', 'Vendor', 'foreign key for aqbooksellers.id to link to the vendor', IFNULL((
						SELECT
							JSON_ARRAYAGG(JSON_OBJECT("value", id, "text", name, "title", ''))
						FROM aqbooksellers
					), JSON_ARRAY())),
					(3, 'biblionumber', 'Record', 'foreign key for biblio.biblionumber that this subscription is attached to', JSON_ARRAY()),
					(4, 'serialsadditems', 'When receiving this serial', 'does receiving this serial create an item record', JSON_ARRAY(
						JSON_OBJECT("value", 1, "text", 'Create an item record', "title", ''),
						JSON_OBJECT("value", 0, "text", 'Do not create an item record', "title", '')
					)),
					(5, 'skip_serialseq', 'recordWhen there is an irregular issue', '', JSON_ARRAY(
						JSON_OBJECT("value", 1, "text", 'Skip issue number', "title", ''),
						JSON_OBJECT("value", 0, "text", 'Keep issue number', "title", '')
					)),
					(6, 'manualhistory', 'Manual history', 'yes or no to managing the history manually', JSON_ARRAY()),
					(7, 'callnumber', 'Call number', 'default call number', JSON_ARRAY()),
					(8, 'branchcode', 'Library', 'default branches (items.homebranch)', IFNULL((
						SELECT
							JSON_ARRAYAGG(JSON_OBJECT("value", branchcode, "text", branchname, "title", ''))
						FROM branches
					), JSON_ARRAY())),
					(9, 'notes', 'Public note', '', JSON_ARRAY()),
					(10, 'internalnotes', 'Nonpublic note', '', JSON_ARRAY()),
					(11, 'letter', 'Patron notification', 'Selecting a notice will allow patrons to subscribe to notifications when a new issue is received.', JSON_ARRAY(
						JSON_OBJECT("value", '', "text", 'None', "title", ''),
						JSON_OBJECT("value", 'SERIAL_ALERT', "text", 'New serial issue', "title", '')
					)),
					(12, 'location', 'Location', 'default shelving location (items.location)', IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
						FROM (
							SELECT '' AS `value`, 'None' AS text, '' AS title
							UNION
							SELECT authorised_value, lib, '' FROM authorised_values WHERE category = 'LOC'
						) AS u12
					), JSON_ARRAY())),
					(13, 'ccode', 'Collection', 'collection code to assign to serial items', IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
						FROM (
							SELECT '' AS `value`, 'None' AS text, '' AS title
							UNION
							SELECT authorised_value, lib, '' FROM authorised_values WHERE category = 'CCODE'
						) AS u13
					), JSON_ARRAY())),
					(14, 'itemtype', 'Item type', 'collection code to assign to serial items', IFNULL((
						SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", title))
						FROM (
							SELECT '' AS `value`, 'Not set' AS text, '' AS title
							UNION
							SELECT itemtype, description, '' FROM itemtypes
						) AS u14
					), JSON_ARRAY())),
					(15, 'graceperiod', 'Grace period', 'grace period in days', JSON_ARRAY()),
					(16, 'staffdisplaycount', 'Number of issues to display to staff', (
						SELECT CONCAT('Default:', `value`, ' (StaffSerialIssueDisplayCount system preference)') FROM systempreferences WHERE `variable` = 'StaffSerialIssueDisplayCount'
					), JSON_ARRAY()),
					(17, 'opacdisplaycount', 'Number of issues to display to the public', (
						SELECT CONCAT('Default:', `value`, ' (OPACSerialIssueDisplayCount system preference)') FROM systempreferences WHERE `variable` = 'OPACSerialIssueDisplayCount'
					), JSON_ARRAY())
				) AS u
			) AS fields
			UNION VALUES
				(2, 'Serials planning', (
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT("column_order", column_order, "column_name", column_name, "column_title", column_title, "description", description, "options", options))
					FROM (
						SELECT 1 AS column_order, 'firstacquidate' AS column_name, 'First issue publication date' AS column_title, 'first issue received date' AS description, JSON_ARRAY() AS options
						UNION VALUES
						(2, 'periodicity', 'Frequency', 'frequency type links to subscription_frequencies.id', IFNULL((
							SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
							FROM (
								SELECT '' AS `value`, '-- please choose --' AS text, '' AS title, 0 AS displayorder
								UNION ALL
								SELECT
									id, description, '', displayorder
								FROM subscription_frequencies
								ORDER BY displayorder
							) AS u2
						), JSON_ARRAY())),
						(3, '*', 'Subscription length', '以下3選一後依textbox值，寫入對應的欄位', JSON_ARRAY(
							JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
							JSON_OBJECT("value", 'weeklength', "text", 'weeks', "title", ''),
							JSON_OBJECT("value", 'monthlength', "text", 'months', "title", '')
						)),
						(4, 'startdate', 'Subscription start date', 'start date for this subscription', JSON_ARRAY()),
						(5, 'enddate', 'Subscription end date', 'subscription end date', JSON_ARRAY()),
						(6, 'numberpattern', 'Numbering pattern', 'the numbering pattern used links to subscription_numberpatterns.id', IFNULL((
							SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
							FROM (
								SELECT '' AS `value`, '-- please choose --' AS text, '' AS title, 0 AS displayorder
								UNION ALL
								SELECT
									id, label, '', displayorder
								FROM subscription_numberpatterns
								ORDER BY displayorder
							) AS u6
						), JSON_ARRAY())),
						(7, 'published_on_template', 'Publication date template', 'Template Toolkit syntax to generate the default "Published on (text)" field when receiving an issue this serial', JSON_ARRAY()),
						(8, 'locale', 'Locale', 'for foreign language subscriptions to display months, seasons, etc correctly', IFNULL((
							SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
							FROM (
								SELECT '' AS `value`, '-- please choose --' AS text, '' AS title
								UNION ALL
								SELECT r2i.iso639_2_code, CASE WHEN d.description IS NULL THEN r.description ELSE CONCAT(d.description, ' (', r.description, ')') END AS text, ''
								FROM language_subtag_registry AS r
									INNER JOIN language_rfc4646_to_iso639 AS r2i ON r2i.rfc4646_subtag = r.subtag
									LEFT JOIN language_descriptions AS d ON d.subtag = r2i.rfc4646_subtag AND d.`type` = r.`type` AND d.lang = d.subtag
								WHERE r.`type` = 'language'
								ORDER BY `value`
							) AS u8
						), JSON_ARRAY())),
						(9, 'lastvalue1', 'Begins with 1', '["Number", "Volume", "Season"]', JSON_ARRAY()),
						(10, 'innerloop1', 'Inner counter 1', '["Number", "Volume", "Season"]', JSON_ARRAY()),
						(11, 'lastvalue2', 'Begins with 2', '["Number", "Year"]', JSON_ARRAY()),
						(12, 'innerloop2', 'Inner counter 2', '["Number", "Year"]', JSON_ARRAY()),
						(13, 'lastvalue3', 'Begins with 3', '["Issue"]', JSON_ARRAY()),
						(14, 'innerloop3', 'Inner counter 3', '["Issue"]', JSON_ARRAY())
					) AS u
				)),
				(3, 'advanced pattern', (
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT("column_order", column_order, "column_name", column_name, "column_title", column_title, "description", description, "options", options))
					FROM (
						SELECT 1 AS column_order, 'id' AS column_name, '' AS column_title, '資料取決於第2部份的 numberpattern 參數' AS description, JSON_ARRAY() AS options
						UNION VALUES
						(2, 'label', 'Pattern name', '', IFNULL((
							SELECT JSON_ARRAYAGG(JSON_OBJECT("value", id, "text", label, "title", ''))
							FROM subscription_numberpatterns
							ORDER BY displayorder
						), JSON_ARRAY())),
						(3, 'numberingmethod', 'Numbering formula', '', JSON_ARRAY()),

						(4, 'label1', 'Label', 'X', JSON_ARRAY()),
						(5, 'lastvalue1', 'Begins with', 'X', JSON_ARRAY()),
						(6, 'add1', 'Add', 'X', JSON_ARRAY()),
						(7, 'every1', 'Every', 'X', JSON_ARRAY()),
						(8, 'setto1', 'Set back to', 'X', JSON_ARRAY()),
						(9, 'whenmorethan1', 'When more than', 'X', JSON_ARRAY()),
						(10, 'innerloop1', 'Inner counter', 'X', JSON_ARRAY()),
						(11, 'numbering1', 'Formatting', 'X', JSON_ARRAY(
							JSON_OBJECT("value", '', "text", '', "title", ''),
							JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
							JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
							JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
							JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
							JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
							JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
							JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
						))
					) AS u
				)),
				(4, 'other', (
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT("column_order", column_order, "column_name", column_name, "column_title", column_title, "description", description, "options", options))
					FROM (
						SELECT 1 AS column_order, 'librarian' AS column_name, '' AS column_title, 'the librarian''s username from borrowers.userid' AS description, JSON_ARRAY() AS options
						UNION VALUES
						(2, 'cost', '', '', JSON_ARRAY()),
						(3, 'aqbudgetid', '', '', JSON_ARRAY()),
						(4, 'countissuesperunit', '', '', JSON_ARRAY()),
						(5, 'status', '', 'status of this subscription', JSON_ARRAY()),
						(6, 'irregularity', '', 'any irregularities in the subscription', JSON_ARRAY()),
						(7, 'distributedto', '', '', JSON_ARRAY()),
						(8, 'lastbranch', '', '', JSON_ARRAY()),
						(9, 'closed', '', 'yes / no if the subscription is closed', JSON_ARRAY()),
						(10, 'reneweddate', '', 'date of last renewal for the subscription', JSON_ARRAY()),
						(11, 'previousitemtype', '', '', JSON_ARRAY()),
						(12, 'mana_id', '', '', JSON_ARRAY())
					) AS u
				))
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["fields"] = json_decode($oRow["fields"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	#region periodicity
	public function get_serial_periodicity($params) {
		$isNewType = false;
		if (array_key_exists("isNewType", $params)) {
			$isNewType = $params["isNewType"] == "1" ? true : false;
			unset($params["isNewType"]);
		}
		if (!$isNewType) { return $this->get_serial_periodicity_all($params); }
		$id = "";
		if (array_key_exists("id", $params)){ $id = $params["id"] ?? ""; }
		if ($id == ""){ return $this->get_serial_periodicity_p0($params); }
		return $this->get_serial_periodicity_px($params);
	}
	private function get_serial_periodicity_all($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["displayorder"]);
		#endregion
		$values = [
			"id" => null,
			"keyword" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$aCondition = [
			"id" => " AND id = :id",
			"keyword" => " AND CONCAT(description, unit) LIKE CONCAT('%', :keyword, '%')",
		];
		$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		if ($cWhere_Inner == "") { $cWhere_Inner = " AND id > 0"; }
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];

		$cSql = <<<EOD
			WITH sf AS (
				SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM (
					SELECT
						0 AS `id`,					'' AS `description`,		NULL AS `unit`,
						1 AS `issuesperunit`,		1 AS `unitsperissue`,		99 AS `displayorder`
					UNION ALL
					SELECT id, description, unit, issuesperunit, unitsperissue, displayorder FROM subscription_frequencies
				) AS u_s
				WHERE TRUE {$cWhere_Inner}
			)
			SELECT
				`key`,
				id,					description,		unit,
				issuesperunit,		unitsperissue,		displayorder,
				(SELECT COUNT(*) AS total FROM sf) AS total
			FROM sf
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$total = 0;
		if (count($aRows) > 0) {
			$total = $aRows[0]["total"];
			foreach ($aRows as $iIndex => $oRow) {
				unset($aRows[$iIndex]["total"]);
			}
		}

		$result['data'] = $aRows;
		$result['total'] = $total;
		$this->SetOK();
		return $result;
	}

	private function get_serial_periodicity_p0($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["displayorder"]);
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		unset($values["cur_page"]);
		unset($values["size"]);

		$cSql = <<<EOD
			WITH f AS (
				SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM subscription_frequencies
			)
			SELECT
				`key`,
				id,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 0, "phrase_name", 'Frequencies',
						"columns", JSON_ARRAY(
							JSON_OBJECT(
								"order", 1, "title", 'Description', "value", '',
								"text", description
							),
							JSON_OBJECT(
								"order", 2, "title", 'Unit', "value", '',
								"text", unit
							),
							JSON_OBJECT(
								"order", 3, "title", 'Issues per unit', "value", "",
								"text", issuesperunit
							),
							JSON_OBJECT(
								"order", 4, "title", 'Units per issue', "value", "",
								"text", unitsperissue
							),
							JSON_OBJECT(
								"order", 5, "title", 'Display order', "value", "",
								"text", displayorder
							)
						)
					)
				) AS phrases
			FROM f
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	private function get_serial_periodicity_px($params) {
		$id = $params["id"] ?? 0;
		$cSql = <<<EOD
			WITH f AS (
				SELECT *, 1 AS `key`
				FROM (
					SELECT
						0 AS `id`,					'' AS `description`,		NULL AS `unit`,
						1 AS `issuesperunit`,		1 AS `unitsperissue`,		99 AS `displayorder`
					UNION ALL
					SELECT id, description, unit, issuesperunit, unitsperissue, displayorder FROM subscription_frequencies
				) AS u_s
				WHERE id = :id
			)
			SELECT
				`key`,
				id,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 1, "phrase_name", 'New / Edit frequency',
						"columns", JSON_OBJECT(
							"description", description,
							"unit", unit,
							"issuesperunit", issuesperunit,
							"unitsperissue", unitsperissue,
							"displayorder", displayorder
						),
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'unit' AS column_name, 'Unit' AS column_title, '' AS description, JSON_ARRAY(
									JSON_OBJECT("value", '', "text", 'None', "title", ''),
									JSON_OBJECT("value", 'day', "text", 'day', "title", ''),
									JSON_OBJECT("value", 'week', "text", 'week', "title", ''),
									JSON_OBJECT("value", 'month', "text", 'month', "title", ''),
									JSON_OBJECT("value", 'year', "text", 'year', "title", '')
								) AS options
							) as u
						)
					)
				) AS phrases
			FROM f
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["id" => $id]);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	public function post_serial_periodicity($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_serial_periodicity_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_serial_periodicity_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_serial_periodicity(false, $data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		#endregion

		$cSql = <<<EOD
			INSERT INTO subscription_frequencies (
										`description`,			`unit`,
				`issuesperunit`,		`unitsperissue`,		`displayorder`
			) VALUES (
										:description,			:unit,
				:issuesperunit,			:unitsperissue,			:displayorder
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function patch_serial_periodicity($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_serial_periodicity_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_serial_periodicity_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_serial_periodicity(true, $data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		#endregion

		$cSql = <<<EOD
			REPLACE INTO subscription_frequencies (
				`id`,					`description`,			`unit`,
				`issuesperunit`,		`unitsperissue`,		`displayorder`
			) VALUES (
				:id,					:description,			:unit,
				:issuesperunit,			:unitsperissue,			:displayorder
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function delete_serial_periodicity($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_serial_periodicity_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_serial_periodicity_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }

		$id = array_key_exists("id", $data) ? $data["id"] : 0;

		$cSql = <<<EOD
			DELETE FROM subscription_frequencies
			WHERE id = :id
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["id" => $id]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function get_serial_periodicity_type($params) {
		$sql = <<<EOD
			SELECT 0 AS column_order, 'id' AS column_name, '' AS column_title, '' AS description, JSON_ARRAY() AS options
			UNION VALUES
			(1, 'description', 'Description', '', JSON_ARRAY()),
			(2, 'unit', 'Unit', '', JSON_ARRAY(
										JSON_OBJECT("value", '', "text", 'None', "title", ''),
										JSON_OBJECT("value", 'day', "text", 'day', "title", ''),
										JSON_OBJECT("value", 'week', "text", 'week', "title", ''),
										JSON_OBJECT("value", 'month', "text", 'month', "title", ''),
										JSON_OBJECT("value", 'year', "text", 'year', "title", '')
									)),
			(3, 'issuesperunit', 'Issues per unit', '', JSON_ARRAY()),
			(4, 'unitsperissue', 'Units per issue', '', JSON_ARRAY()),
			(5, 'displayorder', 'Display order', '', JSON_ARRAY())
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["options"] = json_decode($oRow["options"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;

	}
	private $fileds_serial_periodicity = [
		"id" => 0,
		"description" => "",
		"unit" => null,
		"issuesperunit" => 1,
		"unitsperissue" => 1,
		"displayorder" => 99
	];
	private function checkAndConvert_serial_periodicity($bIncludeID, $data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數

		$aValues = $this->fileds_serial_periodicity;
		$id = array_key_exists("id", $data) ? $data["id"] : 0;
		if (array_key_exists("phrases", $data)) {
			foreach($data["phrases"] as $key => $phrase) {
				foreach($phrase["columns"] as $column_name => $column_value) {
					if (array_key_exists($column_name, $this->fileds_serial_periodicity)) {
						$aValues[$column_name] = $column_value;
					}
				}
			}
		} else {
			$aValues = xStatic::KeyMatchThenReplaceValue($aValues, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
			$aValues = xStatic::ValueMatchThenRemove($aValues, "");
		}
		$aValues["id"] = $id;

		foreach ($this->fileds_serial_periodicity as $column_name => $default_value) {
			$column_value = array_key_exists($column_name, $aValues) ? $aValues[$column_name] : null;
			if ($column_value === null && $default_value !== null) { $callBack->message = "{$column_name}不可為空值."; return $callBack; }
			if ($column_value == $default_value) { continue; }

			switch ($column_name) {
				#region int
				case 'issuesperunit':
				case 'unitsperissue':
				case 'displayorder': {
					if ($column_value != null) {
						if (!xInt::To($column_value)) { $callBack->message = "{$column_name}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
				case 'unit': {
					if ($column_value != null && $column_value != '') {
						$allow = ["day", "week", "month", "year"];
						if (!in_array($column_value, $allow)) { $callBack->message = "{$column_name}非允許的值 [{$column_value}]."; return $callBack; }
					}
				} break;
			}
		}
		if (!$bIncludeID) { unset($aValues["id"]); }
		#endregion

		$callBack->status = "success"; $callBack->data = $aValues;
		return $callBack;
	}
	#endregion
	#region numberingpattern
	public function get_serial_numberingpattern($params) {
		$isNewType = false;
		if (array_key_exists("isNewType", $params)) {
			$isNewType = $params["isNewType"] == "1" ? true : false;
			unset($params["isNewType"]);
		}
		if (!$isNewType) { return $this->get_serial_numberingpattern_all($params); }
		$id = "";
		if (array_key_exists("id", $params)){ $id = $params["id"] ?? ""; }
		if ($id == ""){ return $this->get_serial_numberingpattern_p0($params); }
		return $this->get_serial_numberingpattern_px($params);
	}
	private function get_serial_numberingpattern_all($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["displayorder"]);
		#endregion
		$values = [
			"id" => null,
			"keyword" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$aCondition = [
			"id" => " AND id = :id",
			"keyword" => " AND CONCAT(label, description) LIKE CONCAT('%', :keyword, '%')",
		];
		$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		if ($cWhere_Inner == "") { $cWhere_Inner = " AND id > 0"; }
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];

		$cSql = <<<EOD
			WITH np AS (
				SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM (
					SELECT
						0 AS `id`,
						'' AS `label`,					'' AS `description`,	'' AS `numberingmethod`,
						99 AS `displayorder`,
						NULL AS `label1`,				NULL AS `add1`,			NULL AS `every1`,
						NULL AS `whenmorethan1`,		NULL AS `setto1`,		NULL AS `numbering1`,
						NULL AS `label2`,				NULL AS `add2`,			NULL AS `every2`,
						NULL AS `whenmorethan2`,		NULL AS `setto2`,		NULL AS `numbering2`,
						NULL AS `label3`,				NULL AS `add3`,			NULL AS `every3`,
						NULL AS `whenmorethan3`,		NULL AS `setto3`,		NULL AS `numbering3`
					UNION ALL
					SELECT
						id,
						label, description, numberingmethod,
						displayorder,
						label1, add1, every1, whenmorethan1, setto1, numbering1,
						label2, add2, every2, whenmorethan2, setto2, numbering2,
						label3, add3, every3, whenmorethan3, setto3, numbering3
					FROM subscription_numberpatterns
				) AS u_np
				WHERE TRUE {$cWhere_Inner}
			)
			SELECT
				`key`,
				id,
				label, description, numberingmethod,
				displayorder,
				label1, add1, every1, whenmorethan1, setto1, numbering1,
				label2, add2, every2, whenmorethan2, setto2, numbering2,
				label3, add3, every3, whenmorethan3, setto3, numbering3,
				(SELECT COUNT(*) AS total FROM np) AS total
			FROM np
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$total = 0;
		if (count($aRows) > 0) {
			$total = $aRows[0]["total"];
			foreach ($aRows as $iIndex => $oRow) {
				unset($aRows[$iIndex]["total"]);
			}
		}

		$result['data'] = $aRows;
		$result['total'] = $total;
		$this->SetOK();
		return $result;
	}
	private function get_serial_numberingpattern_p0($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["displayorder"]);
		#endregion
		$values = [
			"keyword" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$aCondition = [
			"keyword" => " AND CONCAT(label, description) LIKE CONCAT('%', :keyword, '%')",
		];
		$cWhere_Inner = xStatic::KeyMatchThenJoinValue($values, false, $aCondition, true);
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];

		$cSql = <<<EOD
			WITH np AS (
				SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
				FROM subscription_numberpatterns
				WHERE TRUE {$cWhere_Inner}
			)
			SELECT
				`key`,
				id,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 0, "phrase_name", 'Numbering patterns',
						"columns", JSON_ARRAY(
							JSON_OBJECT(
								"order", 1, "title", 'Name', "value", '',
								"text", label
							),
							JSON_OBJECT(
								"order", 2, "title", 'Description', "value", '',
								"text", description
							),
							JSON_OBJECT(
								"order", 3, "title", 'Numbering formula', "value", "",
								"text", numberingmethod
							),
							JSON_OBJECT(
								"order", 4, "title", 'Display order', "value", "",
								"text", displayorder
							)
						)
					)
				) AS phrases
			FROM np
			WHERE `key` > :start {$order} LIMIT :length
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	private function get_serial_numberingpattern_px($params) {
		$id = $params["id"] ?? 0;
		$cSql = <<<EOD
			WITH np AS (
				SELECT *, 1 AS `key`
				FROM (
					SELECT
						0 AS `id`,
						'' AS `label`,					'' AS `description`,	'' AS `numberingmethod`,
						99 AS `displayorder`,
						NULL AS `label1`,				NULL AS `add1`,			NULL AS `every1`,
						NULL AS `whenmorethan1`,		NULL AS `setto1`,		NULL AS `numbering1`,
						NULL AS `label2`,				NULL AS `add2`,			NULL AS `every2`,
						NULL AS `whenmorethan2`,		NULL AS `setto2`,		NULL AS `numbering2`,
						NULL AS `label3`,				NULL AS `add3`,			NULL AS `every3`,
						NULL AS `whenmorethan3`,		NULL AS `setto3`,		NULL AS `numbering3`
					UNION ALL
					SELECT
						id,
						label, description, numberingmethod,
						displayorder,
						label1, add1, every1, whenmorethan1, setto1, numbering1,
						label2, add2, every2, whenmorethan2, setto2, numbering2,
						label3, add3, every3, whenmorethan3, setto3, numbering3
					FROM subscription_numberpatterns
				) AS u_s
				WHERE id = :id
			)
			SELECT
				`key`,
				id,
				JSON_ARRAY(
					JSON_OBJECT(
						"phrase", 1, "phrase_name", 'New / Edit pattern',
						"columns", JSON_OBJECT(
							"label", label,
							"description", description,
							"numberingmethod", numberingmethod,
							"displayorder", displayorder,
							"label1", label1, "add1", add1, "every1", every1, "setto1", setto1, "whenmorethan1", whenmorethan1, "numbering1", numbering1,
							"label2", label2, "add2", add2, "every2", every2, "setto2", setto2, "whenmorethan2", whenmorethan2, "numbering2", numbering2,
							"label3", label3, "add3", add3, "every3", every3, "setto3", setto3, "whenmorethan3", whenmorethan3, "numbering3", numbering3
						),
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'numbering1' AS column_name, 'Formatting' AS column_title, 'X' AS description, JSON_ARRAY(
									JSON_OBJECT("value", '', "text", '', "title", ''),
									JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
									JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
									JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
									JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
									JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
								) AS options
								UNION VALUES
								('numbering2', 'Formatting', 'Y', JSON_ARRAY(
									JSON_OBJECT("value", '', "text", '', "title", ''),
									JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
									JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
									JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
									JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
									JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
								)),
								('numbering3', 'Formatting', 'Z', JSON_ARRAY(
									JSON_OBJECT("value", '', "text", '', "title", ''),
									JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
									JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
									JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
									JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
									JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
									JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
								))
							) as u
						)
					),
					JSON_OBJECT(
						"phrase", 2, "phrase_name", 'Test prediction pattern',
						"columns", '{}',
						"options", (
							SELECT JSON_ARRAYAGG(JSON_OBJECT("column_name", column_name, "column_title", column_title, "description", description,	"options", options))
							FROM (
								SELECT 'periodicity' AS column_name, 'Frequency' AS column_title, '' AS description, IFNULL((
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", id, "text", description, "title", ''))
									FROM subscription_frequencies
									ORDER BY displayorder
								), JSON_ARRAY()) AS options
								UNION VALUES
								('*', 'Subscription length', '以下3選一後依textbox值，寫入對應的欄位', JSON_ARRAY(
									JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
									JSON_OBJECT("value", 'weeklength', "text", 'weeks', "title", ''),
									JSON_OBJECT("value", 'monthlength', "text", 'months', "title", '')
								)),
								('locale', 'Locale', '', (
									SELECT JSON_ARRAYAGG(JSON_OBJECT("value", `value`, "text", text, "title", ''))
									FROM (
										SELECT '' AS `value`, '-- please choose --' AS text, '' AS title
										UNION ALL
										SELECT r2i.iso639_2_code, CASE WHEN d.description IS NULL THEN r.description ELSE CONCAT(d.description, ' (', r.description, ')') END AS text, ''
										FROM language_subtag_registry AS r
											INNER JOIN language_rfc4646_to_iso639 AS r2i ON r2i.rfc4646_subtag = r.subtag
											LEFT JOIN language_descriptions AS d ON d.subtag = r2i.rfc4646_subtag AND d.`type` = r.`type` AND d.lang = d.subtag
										WHERE r.`type` = 'language'
										ORDER BY `value`
									) AS u8
								))
							) AS o
						)
					)
				) AS phrases
			FROM np
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["id" => $id]);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["phrases"] = json_decode($oRow["phrases"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;
	}
	public function post_serial_numberingpattern($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_serial_numberingpattern_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function post_serial_numberingpattern_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_serial_numberingpattern(false, $data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		#endregion

		$cSql = <<<EOD
			INSERT INTO subscription_numberpatterns (

				`label`, `description`, `numberingmethod`,
				`displayorder`,
				`label1`, `add1`, `every1`, `whenmorethan1`, `setto1`, `numbering1`,
				`label2`, `add2`, `every2`, `whenmorethan2`, `setto2`, `numbering2`,
				`label3`, `add3`, `every3`, `whenmorethan3`, `setto3`, `numbering3`
			) VALUES (

				:label, :description, :numberingmethod,
				:displayorder,
				:label1, :add1, :every1, :whenmorethan1, :setto1, :numbering1,
				:label2, :add2, :every2, :whenmorethan2, :setto2, :numbering2,
				:label3, :add3, :every3, :whenmorethan3, :setto3, :numbering3
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function patch_serial_numberingpattern($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_serial_numberingpattern_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function patch_serial_numberingpattern_single($data) {
		$callBack = new stdClass(); $callBack->status = "failed"; $callBack->message = "";

		#region 處理傳入參數
		$callBack = $this->checkAndConvert_serial_numberingpattern(true, $data); if ($callBack->status == "failed") { return $callBack; }
		$values = $callBack->data;
		#endregion

		$cSql = <<<EOD
			REPLACE INTO subscription_numberpatterns (
				`id`,
				`label`, `description`, `numberingmethod`,
				`displayorder`,
				`label1`, `add1`, `every1`, `whenmorethan1`, `setto1`, `numbering1`,
				`label2`, `add2`, `every2`, `whenmorethan2`, `setto2`, `numbering2`,
				`label3`, `add3`, `every3`, `whenmorethan3`, `setto3`, `numbering3`
			) VALUES (
				:id,
				:label, :description, :numberingmethod,
				:displayorder,
				:label1, :add1, :every1, :whenmorethan1, :setto1, :numbering1,
				:label2, :add2, :every2, :whenmorethan2, :setto2, :numbering2,
				:label3, :add3, :every3, :whenmorethan3, :setto3, :numbering3
			)
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function delete_serial_numberingpattern($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_serial_numberingpattern_single($value); if ($this->bErrorOn) { break; }
			if ($callBack->status == "failed") { $this->SetError($callBack->message); break; }
			$data[$key]["result"] = $callBack->data;
		}
		return $data;
	}
	private function delete_serial_numberingpattern_single($data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }

		$id = array_key_exists("id", $data) ? $data["id"] : 0;

		$cSql = <<<EOD
			DELETE FROM subscription_numberpatterns
			WHERE id = :id
EOD;

		$stmt = $this->db_koha->prepare($cSql); xStatic::BindValue($stmt, ["id" => $id]);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$stmt->closeCursor();

		$callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	public function get_serial_numberingpattern_type($params) {
		$sql = <<<EOD
			SELECT 0 AS column_order, 'id' AS column_name, '' AS column_title, '' AS description, JSON_ARRAY() AS options
			UNION VALUES
			(1, 'label', 'Name', '', JSON_ARRAY()),
			(2, 'description', 'Description', '', JSON_ARRAY()),
			(3, 'displayorder', 'Display order', '', JSON_ARRAY()),
			(4, 'numberingmethod', 'Numbering formula', '', JSON_ARRAY()),

			(11, 'label1', 'Label', 'X', JSON_ARRAY()),
			(12, 'add1', 'Add', 'X', JSON_ARRAY()),
			(13, 'every1', 'Every', 'X', JSON_ARRAY()),
			(14, 'setto1', 'Set back to', 'X', JSON_ARRAY()),
			(15, 'whenmorethan1', 'When more than', 'X', JSON_ARRAY()),
			(16, 'numbering1', 'Formatting', 'X', JSON_ARRAY(
				JSON_OBJECT("value", '', "text", '', "title", ''),
				JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
				JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
				JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
				JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
				JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
				JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
				JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
			)),
			(21, 'label2', 'Label', 'Y', JSON_ARRAY()),
			(22, 'add2', 'Add', 'Y', JSON_ARRAY()),
			(23, 'every2', 'Every', 'Y', JSON_ARRAY()),
			(24, 'setto2', 'Set back to', 'Y', JSON_ARRAY()),
			(25, 'whenmorethan2', 'When more than', 'Y', JSON_ARRAY()),
			(26, 'numbering2', 'Formatting', 'Y', JSON_ARRAY(
				JSON_OBJECT("value", '', "text", '', "title", ''),
				JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
				JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
				JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
				JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
				JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
				JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
				JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
			)),
			(31, 'label3', 'Label', 'Z', JSON_ARRAY()),
			(32, 'add3', 'Add', 'Z', JSON_ARRAY()),
			(33, 'every3', 'Every', 'Z', JSON_ARRAY()),
			(34, 'setto3', 'Set back to', 'Z', JSON_ARRAY()),
			(35, 'whenmorethan3', 'When more than', 'Z', JSON_ARRAY()),
			(36, 'numbering3', 'Formatting', 'Z', JSON_ARRAY(
				JSON_OBJECT("value", '', "text", '', "title", ''),
				JSON_OBJECT("value", 'numberlength', "text", 'issues', "title", ''),
				JSON_OBJECT("value", 'dayname', "text", 'Name of day', "title", ''),
				JSON_OBJECT("value", 'dayabrv', "text", 'Name of day (abbreviated)', "title", ''),
				JSON_OBJECT("value", 'monthname', "text", 'Name of month', "title", ''),
				JSON_OBJECT("value", 'monthabrv', "text", 'Name of month (abbreviated)', "title", ''),
				JSON_OBJECT("value", 'season', "text", 'Name of season', "title", ''),
				JSON_OBJECT("value", 'seasonabrv', "text", 'Name of season (abbreviated)', "title", '')
			))
EOD;

		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$stmt = $this->db_koha->prepare($sql);
		$this->db_koha->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		foreach ($aRows as $iIndex => $oRow) {
			$aRows[$iIndex]["options"] = json_decode($oRow["options"], true);
		}

		$result['data'] = $aRows;
		$result['total'] = count($aRows);
		$this->SetOK();
		return $result;

	}
	private $fileds_serial_numberingpattern = [
		"id" => 0,
		"label" => "", "description" => "", "numberingmethod" => "",
		"displayorder" => 99,
		"label1" => NULL, "add1" => NULL, "every1" => NULL, "whenmorethan1" => NULL, "setto1" => NULL, "numbering1" => NULL,
		"label2" => NULL, "add2" => NULL, "every2" => NULL, "whenmorethan2" => NULL, "setto2" => NULL, "numbering2" => NULL,
		"label3" => NULL, "add3" => NULL, "every3" => NULL, "whenmorethan3" => NULL, "setto3" => NULL, "numbering3" => NULL
	];
	private function checkAndConvert_serial_numberingpattern($bIncludeID, $data) {
		$callBack = new stdClass(); { $callBack->status = "failed";  $callBack->message = ""; }
		#region 處理傳入參數

		$aValues = $this->fileds_serial_numberingpattern;
		$id = array_key_exists("id", $data) ? $data["id"] : 0;
		if (array_key_exists("phrases", $data)) {
			foreach($data["phrases"] as $key => $phrase) {
				foreach($phrase["columns"] as $column_name => $column_value) {
					if (array_key_exists($column_name, $this->fileds_serial_numberingpattern)) {
						$aValues[$column_name] = $column_value;
					}
				}
			}
		} else {
			$aValues = xStatic::KeyMatchThenReplaceValue($aValues, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
			$aValues = xStatic::ValueMatchThenRemove($aValues, "");
		}
		$aValues["id"] = $id;

		foreach ($this->fileds_serial_numberingpattern as $column_name => $default_value) {
			$column_value = array_key_exists($column_name, $aValues) ? $aValues[$column_name] : null;
			if ($column_value === null && $default_value !== null) { $callBack->message = "{$column_name}不可為空值."; return $callBack; }
			if ($column_value == $default_value) { continue; }

			switch ($column_name) {
				#region int
				case "add1": case "every1": case "whenmorethan1": case "setto1":
				case "add2": case "every2": case "whenmorethan2": case "setto2":
				case "add3": case "every3": case "whenmorethan3": case "setto3":
				case "displayorder": {
					if ($column_value != null) {
						if (!xInt::To($column_value)) { $callBack->message = "{$column_name}必須為數字."; return $callBack; }
					}
				} break;
				#endregion
				case "numbering1":
				case "numbering2":
				case "numbering3": {
					if ($column_value != null && $column_value != '') {
						$allow = ["dayname", "dayabrv", "monthname", "monthabrv", "season", "seasonabrv"];
						if (!in_array($column_value, $allow)) { $callBack->message = "{$column_name}非允許的值 [{$column_value}]."; return $callBack; }
					}
				} break;
			}
		}
		if (!$bIncludeID) { unset($aValues["id"]); }
		#endregion

		$callBack->status = "success"; $callBack->data = $aValues;
		return $callBack;
	}
	#endregion
	#endregion
	public function get_marc_tagStructure($data) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($data, ["tagfield"]);
		#endregion
		$values = [
			"frameworkcode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [];

			$frameworkcode = $this->CheckArrayData("frameworkcode", $values, false, true, "c"); if ($this->bErrorOn) { return; }
			$values["frameworkcode"] = $frameworkcode ?? "";
			$aCondition[] = "frameworkcode = :frameworkcode";

			if (count($aCondition) > 0) {
				$cWhere_Inner = "AND " . implode(" AND ", $aCondition);
			}
		}
		#endregion

		$values["start"] = ["oValue" => $start, "iType" => PDO::PARAM_INT];
		$values["length"] = ["oValue" => $length, "iType" => PDO::PARAM_INT];
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		$cSql_Inner = <<<EOD
            SELECT
				t.tagfield,
				t.liblibrarian,
				t.libopac,
				t.repeatable,
				t.mandatory,
				t.important,
				t.authorised_value,
				t.ind1_defaultvalue,
				t.ind2_defaultvalue,
				t.frameworkcode,
				IFNULL((
					SELECT
						JSON_ARRAYAGG(JSON_OBJECT(
							"tagsubfield", tagsubfield,
							"liblibrarian", liblibrarian,
							"libopac", libopac,
							"repeatable", repeatable,
							"mandatory", mandatory,
							"important", important,
							"kohafield", kohafield,
							"tab", tab,
							"authorised_value", authorised_value,
							"authtypecode", authtypecode,
							"value_builder", value_builder,
							"isurl", isurl,
							"hidden", hidden,
							"frameworkcode", frameworkcode,
							"seealso", seealso,
							"link", link,
							"defaultvalue", defaultvalue,
							"maxlength", maxlength,
							"display_order", display_order
						))
					FROM marc_subfield_structure
					WHERE tagfield = t.tagfield AND frameworkcode = t.frameworkcode
					ORDER BY tagsubfield
			), '[]') AS subfield
			FROM marc_tag_structure AS t
			WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_Limit = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS `key`
            FROM (
                {$cSql_Inner}
            ) dtInner
			ORDER BY `key`
			LIMIT :length
EOD;

		$sql = <<<EOD
		SELECT *
        FROM (
			{$cSql_Limit}
		) L
		WHERE `key` > :start
        ORDER BY `key`
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_Inner}
			) AS C
EOD;

		$stmt = $this->db_koha->prepare($sql); xStatic::BindValue($stmt, $values);
		if (!$stmt->execute()) { return $this->GetDatabaseErrorMessage($stmt); }
		$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

		$stmt_count = $this->db_koha->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		if (!$stmt_count->execute()) { return $this->GetDatabaseErrorMessage($stmt_count); }
		$result_count = $stmt_count->fetchColumn(0); $stmt_count->closeCursor();

		foreach ($aRows as $row_id => $oRow) {
			$aRows[$row_id]["subfield"] = json_decode($oRow["subfield"], true);
		}
		$result['data'] = $aRows;
		$result['total'] = $result_count;
		$this->SetOK();
		return $result;
	}

	//更新全文索引
	public function patch_refresh_biblio_search_ts($data) {
		$stmt_koha = $this->db_koha->prepare("CALL refresh_biblio_search;");
		if (!$stmt_koha->execute()) { return $this->GetDatabaseErrorMessage($stmt_koha); }
		$stmt_koha->closeCursor();

		$stmt_pg = $this->db->prepare("CALL library.refresh_biblio_search_ts();");
		if (!$stmt_pg->execute()) { return $this->GetDatabaseErrorMessage($stmt_pg); }
		$stmt_pg->closeCursor();

		$callBack = new stdClass(); $callBack->status = "success"; $callBack->data = true;
		return $callBack;
	}
	private function checkPatron($patron)
	{
		if ($patron["mblock_code"] != "-") {
			$this->SetError("讀者狀態為[" . $patron["mblock_name"] . "].");
			return;
		}
		if ($patron["expiration_date"] < date("Y-m-d H:i:s")) {
			$this->SetError("讀者帳號已過期.");
			return;
		}
		$this->SetOK();
	}
	#region token
	private function get_token() {
		$cUrl = $this->container->koha["api_url"] . "/oauth/token";
		$postFields = [
			"grant_type" => "client_credentials",
			"client_id" => $this->container->koha["client_id"],
			"client_secret" => $this->container->koha["client_secret"]
		];
		$postFields = http_build_query($postFields);

		$callBack = new stdClass();
		try {
			$oRequest = curl_init();
			curl_setopt($oRequest, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($oRequest, CURLOPT_URL, $cUrl);
			curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oRequest, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($oRequest, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
			curl_setopt($oRequest, CURLOPT_RETURNTRANSFER, true);
			$cJson = curl_exec($oRequest);
			if ($cJson === false) {
				throw new Exception(curl_error($oRequest), curl_errno($oRequest));
			}
			$data = json_decode($cJson);
			if (isset($data->errors)) {
				$oMessage = $data->errors[0];
				$message = "path: $oMessage->path, message: $oMessage->message";
				throw new Exception($message);
			}
			$callBack->status = "success";
			$callBack->message = null;
			$callBack->data = $data->access_token;
		} catch (Exception $e) {
			$callBack->status = "failed";
			$callBack->message = $e->getMessage();
			$callBack->data = null;
		} finally {
			if (is_resource($oRequest)) {
				curl_close($oRequest);
			}
		}
		return $callBack;
	}
	private function get_token_svc() {
		$cUrl = $this->container->koha["svc_url"] . "/authentication";
		$postFields = [
			"userid" => $this->container->koha["userid"],
			"password" => $this->container->koha["password"]
		];
		$postFields = http_build_query($postFields);

		$oRequest = curl_init();
		curl_setopt($oRequest, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($oRequest, CURLOPT_URL, $cUrl);
		curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($oRequest, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($oRequest, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
		curl_setopt($oRequest, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($oRequest, CURLOPT_HEADER, true);
		$callBack = new stdClass();
		try {
			$cContent = curl_exec($oRequest);
			$iHeaderSize = curl_getinfo($oRequest, CURLINFO_HEADER_SIZE);
			$cHeader = substr($cContent, 0, $iHeaderSize);
			$cBody = substr($cContent, $iHeaderSize);

			// 检查 HTTP 响应码
			$httpCode = curl_getinfo($oRequest, CURLINFO_HTTP_CODE);
			if ($httpCode !== 200) {
				throw new Exception("HTTP request failed with status code $httpCode");
			}

			// 检查返回内容是否为有效的 XML
			libxml_use_internal_errors(true);

			// var_dump($cBody);
			$oXml = simplexml_load_string($cBody);

			if ($oXml === false) {
				$errors = libxml_get_errors();
				$errorMessages = "";
				foreach ($errors as $error) {
					$errorMessages .= "XML Error: {$error->message}\n";
				}
				throw new Exception("Invalid XML format received from Koha API. Details: $errorMessages");
			}
			//throw new Exception($cBody);
			$status = $oXml->status; if ($status != "ok") { throw new Exception($status); }
			$aHeader = explode("\n", $cHeader);
			foreach($aHeader as $cLine) {
				if (strpos($cLine, "Set-Cookie") === 0) {
					$iCut = strpos($cLine, "CGISESSID=");
					$cLine = substr($cLine, $iCut + strlen("CGISESSID="));
					$iCut = strpos($cLine, ";");
					$cToken = substr($cLine, 0, $iCut);
					break;
				}
			}
			$callBack->status = "success"; $callBack->message = null; $callBack->data = $cToken;
		} catch (Exception $e) {
			$callBack->status = "failed"; $callBack->message = $e->getMessage(); $callBack->data = null;
		} finally {
			if (is_resource($oRequest)) { curl_close($oRequest); }
		}
		return $callBack;
	}
	#endregion
	#region coverImages_map2file
	public function get_cover_images_map2file($params) {
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion
		#region order by
		$order = $this->getOrderby($params, ["biblionumber", "itemnumber"]);
		#endregion

		$values = [
			"biblionumber" => null,
			"itemnumber" => null,
			"file_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$cWhere_Inner = ""; {
			$aCondition = [
				"biblionumber" => " AND biblionumber = :biblionumber",
				"itemnumber" => " AND itemnumber = :itemnumber",
				"file_id" => " AND file_id = :file_id"
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
				id, biblionumber, itemnumber, file_id
			FROM library.cover_images_map2file
		    WHERE TRUE {$cWhere_Inner}
EOD;

		$cSql_AllRow = <<<EOD
			SELECT *, ROW_NUMBER() OVER ({$order}) AS key
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
		WHERE key > :start
EOD;

		$sql_count = <<<EOD
			SELECT COUNT(*)
            FROM(
                {$cSql_AllRow}
			) AS C
EOD;

		$stmt = $this->db->prepare($sql); xStatic::BindValue($stmt, $values);
		$stmt_count = $this->db->prepare($sql_count); xStatic::BindValue($stmt_count, $values_count);
		if (!$stmt->execute() || !$stmt_count->execute()) {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage);
			return ["status" => "failed"];
		}
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
		return $result;
	}
	public function post_cover_images_map2file($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->post_cover_images_map2file_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function post_cover_images_map2file_single($data) {
		$values = [
			"biblionumber" => null,
			"itemnumber" => null,
			"file_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $biblionumber = $this->CheckArrayData("biblionumber", $values, false, true, "i"); if ($this->bErrorOn) { return; }
        $itemnumber = $this->CheckArrayData("itemnumber", $values, false, true, "i"); if ($this->bErrorOn) { return; }
		if ($biblionumber == null && $itemnumber == null) { $this->SetError("biblionumber 與 itemnumber 必須至少一個"); return; }
        $this->CheckArrayData("file_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			INSERT INTO library.cover_images_map2file (
				biblionumber, itemnumber, file_id
			) VALUES (
				:biblionumber, :itemnumber, :file_id
			)
			RETURNING id
EOD;

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			return $stmt->fetchColumn(0);
		} else {
			$oInfo = $stmt->errorInfo(); var_dump($oInfo);
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage); return;
		}
	}
	public function patch_cover_images_map2file($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->patch_cover_images_map2file_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function patch_cover_images_map2file_single($data) {
		$values = [
			"id" => null,
			"biblionumber" => null,
			"itemnumber" => null,
			"file_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }
        $biblionumber = $this->CheckArrayData("biblionumber", $values, false, true, "i"); if ($this->bErrorOn) { return; }
        $itemnumber = $this->CheckArrayData("itemnumber", $values, false, true, "i"); if ($this->bErrorOn) { return; }
		if ($biblionumber == null && $itemnumber == null) { $this->SetError("biblionumber 與 itemnumber 必須至少一個"); return; }
        $this->CheckArrayData("file_id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = <<<EOD
			UPDATE library.cover_images_map2file SET
				biblionumber = :biblionumber, itemnumber = :itemnumber, file_id = :file_id
			WHERE id = :id
EOD;

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			return true;
		} else {
			$oInfo = $stmt->errorInfo();
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage); return;
		}
	}
	public function delete_cover_images_map2file($data) {
		if ($this->isNullOrEmptyArray($data)) { return; }
		foreach ($data as $key => $value) {
			$callBack = $this->delete_cover_images_map2file_single($value); if ($this->bErrorOn) { break; }
			$data[$key]["result"] = $callBack;
		}
		return $data;
	}
	private function delete_cover_images_map2file_single($data) {
		$values = [
			"id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		//$values = xStatic::ValueMatchThenRemove($values, "");

        $this->CheckArrayData("id", $values, true, false, "i"); if ($this->bErrorOn) { return; }

		$cSql = "DELETE FROM library.cover_images_map2file WHERE id = :id";

		$stmt = $this->db->prepare($cSql); xStatic::BindValue($stmt, $values);
		if ($stmt->execute()) {
			return true;
		} else {
			$oInfo = $stmt->errorInfo(); var_dump($oInfo);
			$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
			$this->SetError($cMessage); return;
		}
	}
	#endregion
	private function callKohaApi($method, $url, $postFields, $contentType = "application/json", $header = [], $circulation = false, $pass_userid = 0, $pass_password = "") {
		$method = strtoupper($method);
		$callBack = new stdClass();
		/*
			$callBack = $this->get_token(); if ($callBack->status == "") { return $callBack; }
			$cToken = $callBack->data;
			$cBearer = "Authorization: Bearer {$cToken}";
		*/
		//$cBearer = "Authorization: Basic YXBpa2V5OjFxYXpAV1NYM2VkYw==";
		$url = $this->container->koha["api_url"] . $url;
		if ($method == "GET") {
			$url = $url . '?' . http_build_query($postFields);
		}
		$userid = $this->container->koha["userid"];
		$password = $this->container->koha["password"];
		if($circulation) {
			$userid = $pass_userid;
			$password = $pass_password;
		}
		if ($contentType == "application/json") {
			$postFields = json_encode($postFields, JSON_UNESCAPED_UNICODE);
		}
		if (count($header) == 0) {
			$header[] = "accept: {$contentType}";
		}
		$header[] = "content-type: {$contentType}";
		//$header[] = $cBearer;
		try {

			$oRequest = curl_init();
			curl_setopt($oRequest, CURLOPT_USERPWD, $userid . ":" . $password);
			curl_setopt($oRequest, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($oRequest, CURLOPT_URL, $url);
			//curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oRequest, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($oRequest, CURLOPT_HTTPHEADER, $header);
			curl_setopt($oRequest, CURLOPT_TIMEOUT, 15000);
			curl_setopt($oRequest, CURLOPT_CONNECTTIMEOUT, 0);
			//curl_setopt($oRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
			curl_setopt($oRequest, CURLOPT_RETURNTRANSFER, true);
			$cJson = curl_exec($oRequest);
			if ($cJson === false) { throw new Exception(curl_error($oRequest), curl_errno($oRequest)); }
			$data = json_decode($cJson);
			if (isset($data->error)) { $cMessage = $data->error; throw new Exception($cMessage); }
			if (isset($data->errors)) { throw new Exception($data->errors[0]->message); }
			if (isset($data->description)) { throw new Exception($data->description); }
			$callBack->status = "success"; $callBack->message = null; $callBack->data = $data;
		} catch (Exception $e) {
			$callBack->status = "failed"; $callBack->message = $e->getMessage(); $callBack->data = null;
		} finally {
			if (is_resource($oRequest)) {
				curl_close($oRequest);
			}
		}
		return $callBack;
	}
	private function callKohaApi_svc($method, $url, $postFields, $contentType = "application/x-www-form-urlencoded") {
		$callBack = $this->get_token_svc(); if ($callBack->status == "failed") { return $callBack; }
		$cToken = $callBack->data; if ($cToken == "") { $callBack->status = "failed"; $callBack->message = "讀取 token 失敗"; return $callBack;}

		$url = $this->container->koha["svc_url"] . $url;
		if ($contentType == "application/json") {
			$postFields = json_encode($postFields, JSON_UNESCAPED_UNICODE);
		} else if ($contentType == "text/xml") {
			$postFields = "xml=" . $postFields->asXML();
		} else {
			$postFields = http_build_query($postFields);
		}
		try {
			$oRequest = curl_init();
			curl_setopt($oRequest, CURLOPT_CUSTOMREQUEST, strtoupper($method));
			curl_setopt($oRequest, CURLOPT_URL, $url);
			//curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oRequest, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($oRequest, CURLOPT_COOKIE, "CGISESSID={$cToken}");
			curl_setopt($oRequest, CURLOPT_HTTPHEADER, array("content-type:{$contentType}", "charset=utf-8"));
			curl_setopt($oRequest, CURLOPT_RETURNTRANSFER, true);
			$cJson = curl_exec($oRequest); if ($cJson === false) { throw new Exception(curl_error($oRequest), curl_errno($oRequest)); }
			$data = json_decode($cJson);
			$status = $data->returned == "1" ? "success" : "failed";
			$callBack->status = $status; $callBack->message = ""; $callBack->data = $data;
		} catch (Exception $e) {
			$callBack->status = "failed"; $callBack->message = $e->getMessage(); $callBack->data = null;
		} finally {
			if (is_resource($oRequest)) {
				curl_close($oRequest);
			}
		}
		return $callBack;
	}

	private function get_permissions() {
		$cSql = <<<EOD
            SELECT
				pm.`bit`, pm.`flag`, pm.flagdesc,
				pc.code, pc.description
			FROM userflags AS pm
				LEFT JOIN permissions AS pc ON pc.module_bit = pm.`bit`
			WHERE pm.`bit` <> 25
EOD;

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;
		$stmt = $this->db_koha->prepare($cSql);
		if ($stmt->execute()) {
			$callBack->status = "success";
			$callBack->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$this->SetOK();
		} else {
			$oInfo = $stmt->errorInfo();
			var_dump($oInfo);
			$cMessage = $oInfo[2];
			if ($cMessage == null) {
				$cMessage = "error";
			}
			$callBack->status = "failed";
			$callBack->cMessage = $cMessage;
			$this->SetError($cMessage);
		}
		return $callBack;
	}

	public function initialize_search() {
		$default_value = ["cur_page" => 1, "size" => 999999];
		return $default_value;
	}
	private function getOrderby($aRequest, $aOrderBy) {
		$order = ""; {
			if (array_key_exists("order", $aRequest)) {
				$aOrderBy = [];
				foreach ($aRequest["order"] as $sort_data) {
					if (is_string($sort_data)) {
						if (!xString::IsJson($sort_data)) {
							$this->SetError("order 解析失敗.");
							return;
						}
						$sort_data = json_decode($sort_data, true);
					}
					$sort_column = trim($sort_data["column"]);
					$sort_type = strtoupper(trim($sort_data['type']));
					$sort_type = $sort_type == "DESC" ? " DESC" : "";
					$sort = $sort_column . $sort_type;
					if (!in_array($sort, $aOrderBy)) {
						$aOrderBy[] = $sort;
					}
				}
			}
			if (count($aOrderBy) > 0) {
				$order = "ORDER BY " . implode(", ", $aOrderBy);
			}
		}
		return $order;
	}
	private function marcXml2Json($cXml) {
		$aData = [];
		$oXml = simplexml_load_string($cXml);
		if ($oXml === false) {
			return $aData;
		}
		$aData["leader"] = (string) $oXml->leader;

		$aData["controlfield"] = [];
		foreach ($oXml->controlfield as $controlfield) {
			$aAttributes = $controlfield->attributes();
			$oControlField = ["tag" => (string) $aAttributes["tag"], "text" => (string) $controlfield];
			$aData["controlfield"][] = $oControlField;
		}

		$aData["datafield"] = [];
		foreach ($oXml->datafield as $datafield) {
			$aAttributes = $datafield->attributes();
			$oDataField = [
				"tag" => (string) $aAttributes["tag"],
				"ind1" => (string) $aAttributes["ind1"],
				"ind2" => (string) $aAttributes["ind2"]
			];

			$oDataField["subfield"] = [];
			foreach ($datafield->subfield as $subfield) {
				$aAttributes = $subfield->attributes();
				$oSubField = ["code" => (string) $aAttributes["code"], "text" => (string) $subfield];
				$oDataField["subfield"][] = $oSubField;
			}

			$aData["datafield"][] = $oDataField;
		}

		return $aData;
	}
	private function marcJson2Xml($oJson) {
		$cXml = '<?xml version="1.0" encoding="UTF-8"?><record xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd" xmlns="http://www.loc.gov/MARC21/slim"></record>';
		$oXml = simplexml_load_string($cXml);
		$oXml->addChild("leader", $oJson["leader"]);

		foreach ($oJson["controlfield"] as $controlfield) {
			$child = $oXml->addChild("controlfield", $controlfield["text"]);
			$child->addAttribute("tag", $controlfield["tag"]);
		}
		foreach ($oJson["datafield"] as $datafield) {
			$child = $oXml->addChild("datafield");
			$child->addAttribute("tag", $datafield["tag"]);
			$child->addAttribute("ind1", $datafield["ind1"]);
			$child->addAttribute("ind2", $datafield["ind2"]);

			foreach ($datafield["subfield"] as $subfield) {
				$text = str_replace(["&"], "&amp;", $subfield["text"]);
				$text = str_replace(["<"], "&lt;", $text);
				$subChild = $child->addChild("subfield", $text);
				$subChild->addAttribute("code", $subfield["code"]);
				//$a = $subChild->attributes();
			}
		}

		return $oXml;
	}

	private function isNullOrEmptyArray($data) {
		if ($data == null) { $this->SetError("無傳入任何參數."); return true; }
		if (!is_array($data)) { $this->SetError("傳入參數非陣列."); return true; }
		if (count($data) == 0) { $this->SetError("傳入之陣列為空值."); return true; }
		return false;
	}

	// 模糊搜尋用的函式




	public function isJson($string)
    {
        json_decode($string);
        return json_decode($string) !== false && json_last_error() === JSON_ERROR_NONE;
    }
	public function custom_filter_function($data, $select_condition, $default_arr, $custom_filter_arr)
    {
        if (array_key_exists('custom_filter_key', $data) && array_key_exists('custom_filter_value', $data) && count($data['custom_filter_key']) != 0) {
            $select_condition .= " AND (";
            foreach ($data['custom_filter_key'] as $custom_filter_key) {
                if (array_key_exists($custom_filter_key, $custom_filter_arr)) {
                    $select_condition .= " CAST({$custom_filter_key} AS CHAR) LIKE CONCAT('%', :{$custom_filter_key}, '%') OR";
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
	// 讀取 excel 並轉回二維陣列
    public function getExcel($data, $break = false)
    {
        //獲得最大欄位 Thead用
        function getMaxColumns($data)
        {
            $columns = [];
            foreach ($data as $row) {
                $columns = array_merge($columns, array_keys($row));
            }
            return array_unique($columns);
        }
        //補充欄位
        function normalizeData($data, $columns)
        {
            $normalizedData = [];
            foreach ($data as $row) {
                $normalizedRow = [];
                foreach ($columns as $column) {
                    $normalizedRow[$column] = isset($row[$column]) ? $row[$column] : "";
                }
                $normalizedData[] = $normalizedRow;
            }
            return $normalizedData;
        }
        $response = $data['response'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowArray = [];
        $row_count = 1;

        $columns = getMaxColumns($data['data']);
        $normalizedData = normalizeData($data['data'], $columns);
        foreach ($normalizedData as $index => $row) {
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

        // 自動調整列寬
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', "attachment; filename={$data['name']}報表.xlsx");
        return $response;
    }
	public function get_manual($params)
    {
        $values = $this->initialize_search();
        foreach ($values as $key => $value) {
            array_key_exists($key, $params) && $values[$key] = $params[$key];
        }
        $length = $values['cur_page'] * $values['size'];
        $start = $length - $values['size'];

        $bind_values = [
            "name" => null,
        ];
        $custom_filter_bind_values = [
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
            "name" => " AND \"name\" = :name",
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
                $sort_mission_task_group = 'ASC';
                if ($column_data['mission_task_group'] != 'ascend') {
                    $sort_mission_task_group = 'DESC';
                }

                switch ($column_data['column']) {
                        //時間只篩到日期 所以額外分開
                    case 'employment_time_start':
                        $order .= " to_char(employment_time_start, 'yyyy-MM-dd') {$sort_mission_task_group},";
                        break;
                    case 'employment_time_end':
                        $order .= " to_char(employment_time_end, 'yyyy-MM-dd') {$sort_mission_task_group},";
                        break;
                    default:
                        $order .= " {$column_data['column']} {$sort_mission_task_group},";
                }
            }
            $order = rtrim($order, ',');
        }

        $sql_default = "SELECT *, ROW_NUMBER() OVER ($order) \"key\"
                FROM(
                    SELECT classify_structure.\"file\".file_name, classify_structure.\"file\".file_client_name
                        {$customize_select}
                    FROM (
                        SELECT classify_structure.classify_structure.classify_structure_id
                        FROM classify_structure.classify_structure
                        WHERE TRUE {$condition} {$select_condition}  
                    )classify_structure
                    LEFT JOIN classify_structure.classify_structure_type ON classify_structure.classify_structure_id = classify_structure.classify_structure_type.classify_structure_id
                    LEFT JOIN classify_structure.classify_structure_type_file ON  classify_structure.classify_structure_type.classify_structure_type_id = classify_structure.classify_structure_type_file.classify_structure_type_id
                    LEFT JOIN classify_structure.\"file\" ON classify_structure.classify_structure_type_file.file_id = classify_structure.\"file\".\"id\"
                    {$customize_table}
                )dt
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
            return ["status" => "failed", "errorInfo" => $stmt->errorInfo()];
        }
    }
	// 讀取 Excel
    public function read_excel($uploadedFile)
    {
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
                for ($col = 1; $col < $highestColumnIndex + 1; ++$col) {
                    $tmp[trim(strval($worksheet->getCellByColumnAndRow($col, 1)))] = trim(strval($worksheet->getCellByColumnAndRow($col, $row))) == '-' ? '' : trim(strval($worksheet->getCellByColumnAndRow($col, $row)));
                }
                if (count($tmp) != 0) {
                    $data[] = $tmp;
                }
            }
        }
        return $data;
    }
	// 解碼器
    function decodeStaffData($inputData, $encoder, $item)
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
            $user_role_department_data = [];
            foreach ($item['department_role_data'] as $departmentRole) {
                $headerValue = $departmentRole['department_language_data'][0]['language_culture_value'];
                if (isset($inputData[$row][$headerValue . '代號'])) {
                    $user_languages_column = $inputData[$row][$headerValue . '代號'];
                    $user_role_department_data[] = [
                        'department_id' => $user_languages_column,
                    ];
                }
            }
            $user_languages[] = [
                "user_role_department_data" => $user_role_department_data,
                'role_id' => $item['role_id']
            ];
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
                $result = array_merge($result, [
                    trim($keyValue[0]) => trim($keyValue[1])
                ]);
            }
        }
        return $result;
    }
}