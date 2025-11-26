<?php

use PhpOffice\PhpSpreadsheet\Helper\Size;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Content;
use \Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;
use function PHPSTORM_META\map;
use nknu\base\xBaseWithDbop;
use nknu\utility\xStatic;
use nknu\extend\xString;

class Sierra extends xBaseWithDbop
{
	protected $container;
	protected $db_sierra;
	protected $db_sqlsrv_sierra;

	// constructor receives container instance
	public function __construct()
	{
		parent::__construct();
		global $container;
		$this->container = $container;
		$this->db_sierra = $container->db_sierra;
		$this->db_sqlsrv_sierra = $container->db_sqlsrv_sierra;
	}

	#region patron
	public function get_patron($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			"record_num" => null,
			"name" => null,
			"barcode" => null,
			"b_column" => null,
			"rfid" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (count($values) == 0) {
			$this->SetError("讀取讀者資料失敗. 沒有相關參數.");
			return;
		}
		#region where condition
		$condition = ""; {
			$condition_values = [
				"record_num" => " AND patron_record_num = :record_num",
				"name" => " AND patron_name LIKE '%' || :name || '%'",
				"barcode" => " AND patron_barcode = UPPER(:barcode)",
				"rfid" => " AND patron_id = (SELECT record_id FROM sierra_view.varfield_view WHERE record_type_code = 'p' AND varfield_type_code = 'b' AND UPPER(field_content) = :rfid)"
			];
			$condition = xStatic::KeyMatchThenJoinValue($values, false, $condition_values, true);
		}
		#endregion

		$condition_b_column = array_key_exists("b_column", $values) ? "INNER JOIN sierra_view.varfield vf_b ON vf_b.record_id = pr.id AND vf_b.varfield_type_code = 'b' AND UPPER(vf_b.field_content) = UPPER(:b_column)" : "";
		/*
        $searchable_columns = ['grade_name', 'serial_name', 'name', 'name_english', 'phone', 'email', 'school', 'study_time_start', 'study_time_end', 'address', 'note'];
        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
		$select_condition = " AND (";
		foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
		if (in_array($select_filter_arr_data, $searchable_columns)) {
		$select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
		}
		}
		$select_condition = rtrim($select_condition, 'OR');
		$select_condition .= ")";
        }
		 */

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = ''; {
			$default_order = " CASE WHEN mblock_code = '-' THEN 1 ELSE 2 END, expiration_date DESC";
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
					/*
					switch ($column_data['column']) {
					//時間只篩到日期 所以額外分開
					case 'study_time_start':
					$order .= " to_char(study_time_start::timestamp, 'yyyy-MM-dd') {$sort_type},";
					break;
					case 'study_time_end':
					$order .= " to_char(study_time_end::timestamp, 'yyyy-MM-dd') {$sort_type},";
					break;
					default:
					$order .= " {$column_data['column']} {$sort_type},";
					}
					 */
				}
				$order = rtrim($order, ',');
			}
			$order = $order == '' ? 'ORDER BY' . $default_order : $order .= ', ' . $default_order;
		}


		$sql_default_inside = <<<EOD
            SELECT
			    pr.id AS patron_id,
				md.record_num AS patron_record_num,
			    UPPER(pe.index_entry) AS patron_barcode,
			    pname.last_name AS patron_name,
			    pr.expiration_date AS expiration_date,
				vf_email.field_content AS email,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							tphone.code AS type,
							json_agg(
								json_build_object(
									'order', phone.display_order,
									'number', phone.phone_number
								)
								ORDER BY phone.display_order
							) AS numbers
						FROM sierra_view.patron_record_phone AS phone
							LEFT JOIN sierra_view.patron_record_phone_type AS tphone ON tphone.id = phone.patron_record_phone_type_id
						WHERE phone.patron_record_id = pr.id
						GROUP BY tphone.code
					) AS t
				) AS phones,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							taddress.code AS type,
							json_agg(
								json_build_object(
									'order', address.display_order,
									'address', address.addr1
								)
								ORDER BY address.display_order
							) AS address
						FROM sierra_view.patron_record_address AS address
							LEFT JOIN sierra_view.patron_record_address_type AS taddress ON taddress.id = address.patron_record_address_type_id
						WHERE address.patron_record_id = pr.id
						GROUP BY taddress.code
					) AS t
				) AS addresses,

			    pr.mblock AS mblock_code,
                CASE WHEN pr.mblock = '-' THEN '' ELSE mbn.name END AS mblock_name,
			    pr.ptype AS ptype_code,
				ppn.description AS ptype_name,
			    pr.pcode1, dfpn1.name AS pcode1_name,
			    pr.pcode3, dfpn3.name AS pcode3_name,
			    (
					SELECT json_agg(t.field_content)
					FROM (
						SELECT UPPER(field_content) AS field_content
						FROM sierra_view.varfield_view
						WHERE record_id = pr.id AND UPPER(field_content) <> UPPER(pe.index_entry)
							AND record_type_code = 'p' AND varfield_type_code = 'b'
							AND LENGTH(field_content) = 8
					) AS t
				) AS rfid,
			    COALESCE(checkout.checkout_count, 0) AS checkout_count,
				COALESCE(checkout.checkout_overdue_count, 0) AS checkout_overdue_count,
				COALESCE(hold.hold_count, 0) AS hold_count,
				COALESCE(hold.hold_arrive_count, 0) AS hold_arrive_count,
			    pblock.max_hold_num,
				(
					SELECT SUM(otm_amt) otm_amt
					FROM(
						SELECT 
							loan_rule_code_num,
							CASE 
							WHEN SUM(EXTRACT(day FROM (returned_date_gmt::timestamp - due_date_gmt::timestamp))) = 0 THEN 0
							ELSE (SUM(item_charge_amt) / SUM(EXTRACT(day FROM (returned_date_gmt::timestamp - due_date_gmt::timestamp))) * 100)::int
							END AS otm_amt
						FROM sierra_view.fines_paid
						WHERE fines_paid.patron_record_metadata_id = pr.id
						GROUP BY loan_rule_code_num
						limit 1
						) AS money
				) AS owed_amt,
				md.record_last_updated at time zone 'Asia/Taipei' AS record_last_updated
		    FROM iiirecord.record_metadata md
     			INNER JOIN iiirecord.patron_record pr ON pr.id = md.id AND md.record_type = 'p'::bpchar AND char_length(md.campus_code::text) = 0
				LEFT JOIN iiirecord.phrase_entry pe ON pe.record_id = pr.id AND pe.field_group_tag::text = 'b'::text AND pe.occurrence = 0
			    INNER JOIN sierra_view.patron_record_fullname AS pname ON pname.patron_record_id = pr.id
				{$condition_b_column}

				INNER JOIN sierra_view.ptype_property AS pp ON pp.value = pr.ptype
				INNER JOIN sierra_view.ptype_property_name AS ppn ON ppn.ptype_id = pp.id AND ppn.iii_language_id = 4

				INNER JOIN sierra_view.user_defined_category AS dfc1 ON dfc1.code = 'pcode1'
				INNER JOIN sierra_view.user_defined_property AS dfp1 ON dfp1.user_defined_category_id = dfc1.id AND dfp1.code = pr.pcode1::varchar
				INNER JOIN sierra_view.user_defined_property_name AS dfpn1 ON dfpn1.user_defined_property_id = dfp1.id AND dfpn1.iii_language_id = 4

				INNER JOIN sierra_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
				INNER JOIN sierra_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = pr.pcode3::varchar
				INNER JOIN sierra_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4

                INNER JOIN sierra_view.mblock_property AS mb ON mb.code = pr.mblock
	            INNER JOIN sierra_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4

                LEFT JOIN sierra_view.pblock ON pblock.ptype_code_num = pr.ptype
				LEFT JOIN sierra_view.varfield vf_email ON vf_email.record_id = pr.id AND vf_email.varfield_type_code = 'z'
				LEFT JOIN (
					SELECT
						patron_record_id AS patron_id,
						COUNT(*) AS checkout_count,
						SUM(CASE WHEN due_gmt < CURRENT_DATE THEN 1 ELSE 0 END) AS checkout_overdue_count
					FROM sierra_view.checkout
					GROUP BY patron_record_id
				) AS checkout ON checkout.patron_id = pr.id
				LEFT JOIN (
					SELECT
						patron_record_id AS patron_id,
						COUNT(*) AS hold_count,
						SUM(CASE WHEN status = 'i' THEN 1 ELSE 0 END) AS hold_arrive_count
					FROM sierra_view.hold
					GROUP BY hold.patron_record_id
				) AS hold ON hold.patron_id = pr.id
EOD;

		$sql_default = "SELECT *, ROW_NUMBER() OVER ({$order}) \"key\"
                        FROM (
                            {$sql_default_inside}
                        )dt
                        WHERE TRUE {$condition}
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
	public function post_patron($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->post_patron_single($value);
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
	private function post_patron_single($data)
	{
		$values = [
			"name" => null,
			"barcode" => null,
			"rfid" => null,
			"password" => null,
			"email" => null,
			"phones" => null,
			"address" => null,
			"birthDate" => null,

			"patronType" => null,
			"pcode1" => null, "pcode2" => null, "pcode3" => null,
			"blockInfo" => null, "expirationDate" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		//將右側的鍵值換成左邊的鍵值，順便把 value 變成 array(value)
		$cKey1 = "names";
		$cKey2 = "name";
		if (array_key_exists($cKey2, $values)) {
			$values[$cKey1] = array($values[$cKey2]);
			unset($values[$cKey2]);
		}
		$cKey1 = "barcodes";
		$cKey2 = "barcode";
		if (array_key_exists($cKey2, $values)) {
			$values[$cKey1] = array($values[$cKey2]);
			unset($values[$cKey2]);
		}
		$cKey1 = "emails";
		$cKey2 = "email";
		if (array_key_exists($cKey2, $values)) {
			$values[$cKey1] = array($values[$cKey2]);
			unset($values[$cKey2]);
		}

		//將右側的鍵值換成左邊的鍵值，不換 value
		$cKey1 = "pin";
		$cKey2 = "password";
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$addresses = [];
		$cKey1 = "address";
		if (array_key_exists($cKey1, $values)) {
			$addresses[] = ["type" => "a", "lines" => array($values[$cKey1])];
			unset($values[$cKey1]);
		}
		if (count($addresses) > 0) {
			$values["addresses"] = $addresses;
		}

		$cKey1 = "blockInfo";
		if (array_key_exists($cKey1, $values)) {
			$values[$cKey1] = ["code" => $values[$cKey1]];
		}

		$values["pMessage"] = "-";

		$patronCodes = [];
		$cKey1 = "pcode1";
		if (array_key_exists($cKey1, $values)) {
			$patronCodes[$cKey1] = $values[$cKey1];
			unset($values[$cKey1]);
		}
		$cKey1 = "pcode2";
		if (array_key_exists($cKey1, $values)) {
			$patronCodes[$cKey1] = $values[$cKey1];
			unset($values[$cKey1]);
		}
		$cKey1 = "pcode3";
		if (array_key_exists($cKey1, $values)) {
			$patronCodes[$cKey1] = $values[$cKey1];
			unset($values[$cKey1]);
		}
		if (count($patronCodes) > 0) {
			$values["patronCodes"] = $patronCodes;
		}

		$varFields = [];
		$cKey1 = "rfid";
		if (array_key_exists($cKey1, $values)) {
			$varFields[] = ["fieldTag" => "b", "content" => $values[$cKey1]];
			unset($values[$cKey1]);
		}
		if (count($varFields) > 0) {
			$values["varFields"] = $varFields;
		}

		$callBack = $this->callSierraApi("post", "/patrons/", $values);
		return $callBack;
	}
	public function patch_patron($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->patch_patron_single($value);
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
	private function patch_patron_single($data)
	{
		$values = [
			"record_num" => null,
			"name" => null,
			"barcode" => null,
			"rfid" => null,
			"password" => null,
			"email" => null,
			"phones" => null,
			"address" => null,
			"birthDate" => null,

			"patronType" => null,
			"pcode1" => null, "pcode2" => null, "pcode3" => null,
			"blockInfo" => null, "expirationDate" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;
		$cKey1 = "record_num";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 record_num";
			return $callBack;
		}
		$record_num = $values[$cKey1];
		unset($values[$cKey1]);

		//將右側的鍵值換成左邊的鍵值，順便把 value 變成 array(value)
		$cKey1 = "names";
		$cKey2 = "name";
		if (array_key_exists($cKey2, $values)) {
			$values[$cKey1] = array($values[$cKey2]);
			unset($values[$cKey2]);
		}
		$cKey1 = "barcodes";
		$cKey2 = "barcode";
		if (array_key_exists($cKey2, $values)) {
			$values[$cKey1] = array($values[$cKey2]);
			unset($values[$cKey2]);
		}
		$cKey1 = "emails";
		$cKey2 = "email";
		if (array_key_exists($cKey2, $values)) {
			$values[$cKey1] = array($values[$cKey2]);
			unset($values[$cKey2]);
		}

		//將右側的鍵值換成左邊的鍵值，不換 value
		$cKey1 = "pin";
		$cKey2 = "password";
		$values = xStatic::KeyExistThenReplaceValue($values, $cKey1, $bNoPatchThenRemove = false, $values, $cKey2, $bReplaceThenRemove = true);

		$addresses = [];
		$cKey1 = "address";
		if (array_key_exists($cKey1, $values)) {
			$addresses[] = ["type" => "a", "lines" => array($values[$cKey1])];
			unset($values[$cKey1]);
		}
		if (count($addresses) > 0) {
			$values["addresses"] = $addresses;
		}

		$cKey1 = "blockInfo";
		if (array_key_exists($cKey1, $values)) {
			$values[$cKey1] = ["code" => $values[$cKey1]];
		}

		$patronCodes = [];
		$cKey1 = "pcode1";
		if (array_key_exists($cKey1, $values)) {
			$patronCodes[$cKey1] = $values[$cKey1];
			unset($values[$cKey1]);
		}
		$cKey1 = "pcode2";
		if (array_key_exists($cKey1, $values)) {
			$patronCodes[$cKey1] = $values[$cKey1];
			unset($values[$cKey1]);
		}
		$cKey1 = "pcode3";
		if (array_key_exists($cKey1, $values)) {
			$patronCodes[$cKey1] = $values[$cKey1];
			unset($values[$cKey1]);
		}
		if (count($patronCodes) > 0) {
			$values["patronCodes"] = $patronCodes;
		}

		$varFields = [];
		$cKey1 = "rfid";
		if (array_key_exists($cKey1, $values)) {
			$varFields[] = ["fieldTag" => "b", "content" => $values[$cKey1]];
			unset($values[$cKey1]);
		}
		if (count($varFields) > 0) {
			$values["varFields"] = $varFields;
		}

		$callBack = $this->callSierraApi("put", "/patrons/" . $record_num, $values);
		return $callBack;
	}
	public function delete_patron($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->delete_patron_single($value);
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
	private function delete_patron_single($data)
	{
		$values = [
			"record_num" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;
		$cKey1 = "record_num";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 record_num";
			return $callBack;
		}
		$record_num = $values[$cKey1];
		unset($values[$cKey1]);

		$callBack = $this->callSierraApi("delete", "/patrons/" . $record_num, $values);
		return $callBack;
	}
	public function get_patron_validate($data)
	{
		$values = [
			"barcode" => null,
			"password" => null,
			"patronDataWhenValidated" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (!array_key_exists("barcode", $values) || !array_key_exists("password", $values)) {
			$this->SetError("傳入參數不正確.");
			return;
		}
		$barcode = $values["barcode"];
		$pin = $values["password"];

		$patronDataWhenValidated = false;
		if (array_key_exists("patronDataWhenValidated", $values)) {
			$patronDataWhenValidated = $values["patronDataWhenValidated"] == "true";
			unset($values["patronDataWhenValidated"]);
		}

		$values = ["barcode" => $barcode];
		$sql = <<<EOD
            SELECT
				p.record_num AS patron_record_num,
				vf_pin.field_content AS pin_hash
			FROM sierra_view.patron_view AS p
				INNER JOIN sierra_view.varfield vf_pin ON vf_pin.record_id = p.id AND vf_pin.varfield_type_code = '='
			WHERE p.barcode = :barcode
EOD;
		$stmt = $this->db_sierra->prepare($sql);
		if ($stmt->execute($values)) {
			$aRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (count($aRows) == 0) {
				$this->SetError("帳號或不存在.");
				return;
			}
			$oRow = $aRows[0];

			$pin_hash = $oRow["pin_hash"];
			if (!password_verify($pin, $pin_hash)) {
				$this->SetError("帳號或密碼錯誤.");
				return;
			}
			if ($patronDataWhenValidated) {
				$values = ["record_num" => $oRow["patron_record_num"]];
			}
		} else {
			$oInfo = $stmt->errorInfo();
			var_dump($oInfo);
			$this->SetError($oInfo[2]);
			return;
		}
		if (!$patronDataWhenValidated) {
			return "OK";
		}

		return $this->get_patron($values);
	}
	#endregion
	#region hold
	public function get_hold($params)
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
			"rfid" => null,
			"item_record_num" => null,
			"item_barcode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$condition = ""; {
			$condition_values = [
				"patron_record_num" => " AND patron_record_num = :patron_record_num",
				"patron_barcode" => " AND patron_barcode = UPPER(:patron_barcode)",
				"rfid" => " AND patron_id = (SELECT record_id FROM sierra_view.varfield_view WHERE record_type_code = 'p' AND varfield_type_code = 'b' AND UPPER(field_content) = :rfid)",
				"item_record_num" => " AND item_record_num = :item_record_num",
				"item_barcode" => " AND item_barcode = UPPER(:item_barcode)"
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
			$default_order = " hold_id";
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
					na.last_name AS patron_name,
					UPPER(p.barcode) AS patron_barcode,
					(
						SELECT json_agg(t.field_content)
						FROM (
							SELECT UPPER(field_content) AS field_content
							FROM sierra_view.varfield_view
							WHERE record_id = p.id AND UPPER(field_content) <> UPPER(p.barcode)
								AND record_type_code = 'p' AND varfield_type_code = 'b'
								AND LENGTH(field_content) = 8
						) AS t
					) AS rfid,

					p.mblock_code AS mblock_code,
					CASE WHEN p.mblock_code = '-' THEN '' ELSE mbn.name END AS mblock_name,

					b.id AS bib_id, b.record_num AS bib_record_num,
					bp.best_title,
					bp.best_author,
					(
						SELECT
							string_agg(content, ' ' ORDER BY display_order) AS content
						FROM sierra_view.subfield_view
						WHERE record_num = b.record_num AND marc_tag= '092'
					) AS s092,
					(
						SELECT json_agg(t)
						FROM (
							SELECT
								occ_num,
								CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END AS tag,
								string_agg(content, ' ' ORDER BY display_order) AS content
							FROM sierra_view.subfield_view
							WHERE record_num = b.record_num AND marc_tag= '020'
							GROUP BY occ_num, CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END
						) AS t
					) AS s020,

					i.id AS item_id, i.record_num AS item_record_num,
					i.barcode AS item_barcode,
					i.item_status_code,
					i_status_name.name AS item_status_name,

					hold.id AS hold_id,
					hold.placed_gmt::timestamp without time zone AS hold_datetime,
					hold.pickup_location_code AS hold_pickup_location_code,
					ln.name AS hold_pickup_location_name,
					hold.status AS hold_status_code

				FROM sierra_view.hold
					INNER JOIN sierra_view.location AS l ON l.code = hold.pickup_location_code
					INNER JOIN sierra_view.location_name ln on ln.location_id = l.id AND ln.iii_language_id=4
					INNER JOIN sierra_view.patron_view p on p.id = hold.patron_record_id
					INNER JOIN sierra_view.patron_record_fullname na on na.patron_record_id = p.id

					INNER JOIN sierra_view.mblock_property AS mb ON mb.code = p.mblock_code
					INNER JOIN sierra_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4

					LEFT JOIN sierra_view.bib_record_item_record_link brir ON brir.item_record_id = hold.record_id
					LEFT JOIN sierra_view.bib_view b ON brir.bib_record_id = b.id
					LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = brir.bib_record_id

					LEFT JOIN sierra_view.item_view i ON i.id = hold.record_id
					LEFT JOIN sierra_view.item_status_property AS i_status ON i_status.code = i.item_status_code
					LEFT JOIN sierra_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4
				WHERE hold.is_frozen = false
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
	public function get_hold_arrive($params)
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
				CASE WHEN p.mblock_code = '-' THEN '' ELSE mbn.name END AS mblock_name
			FROM sierra_view.hold
				INNER JOIN sierra_view.patron_view p on p.id = hold.patron_record_id
				INNER JOIN sierra_view.patron_record_fullname na on na.patron_record_id = p.id

				INNER JOIN sierra_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
				INNER JOIN sierra_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = p.pcode3::varchar
				INNER JOIN sierra_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4

				INNER JOIN sierra_view.mblock_property AS mb ON mb.code = p.mblock_code
				INNER JOIN sierra_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4
			WHERE hold.status = 'i'
		)
		SELECT
			dt.*,
			(
				SELECT json_agg(t.field_content)
				FROM (
					SELECT UPPER(field_content) AS field_content
					FROM sierra_view.varfield_view
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
						bp.best_title,
						bp.best_author,

						i.id AS item_id, i.record_num AS item_record_num,
						i.barcode AS item_barcode,
						i.item_status_code,
						i_status_name.name AS item_status_name,

						hold.id AS hold_id,
						hold.placed_gmt::timestamp without time zone AS hold_datetime,
						hold.pickup_location_code AS hold_pickup_location_code,
						ln.name AS hold_pickup_location_name
					FROM sierra_view.hold
						INNER JOIN sierra_view.location AS l ON l.code = hold.pickup_location_code
						INNER JOIN sierra_view.location_name ln on ln.location_id = l.id AND ln.iii_language_id=4
						LEFT JOIN sierra_view.bib_record_item_record_link brir ON brir.item_record_id = hold.record_id
						LEFT JOIN sierra_view.bib_view b ON brir.bib_record_id = b.id
						LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = brir.bib_record_id

						LEFT JOIN sierra_view.item_view i ON i.id = hold.record_id
						LEFT JOIN sierra_view.item_status_property AS i_status ON i_status.code = i.item_status_code
						LEFT JOIN sierra_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4
					WHERE hold.patron_record_id = dt.patron_id AND hold.status = 'i'
				) AS t
			) AS items
		FROM dt
		WHERE TRUE {$condition}
EOD;

		$stmt = $this->db_sierra->prepare($sql);
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
	public function post_hold($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->post_hold_single($value);
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
	private function post_hold_single($data)
	{
		$values = [
			"patron_record_num" => null, "patron_barcode" => null,		//二擇一
			"item_record_num" => null, "item_barcode" => null,			//二擇一
			"pickupLocation" => null,									//*
			"expires_date" => null,
			"note" => null												//*
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
        if (!array_key_exists("note", $values)) {
            $values["note"] = "";
        }

        $callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;

		#region 讀者資料
		$data4Patron = [];
		if (array_key_exists("patron_record_num", $values)) {
			$data4Patron["record_num"] = $values["patron_record_num"];
		}
		if (array_key_exists("patron_barcode", $values)) {
			$data4Patron["barcode"] = $values["patron_barcode"];
		}
		$result = $this->get_patron($data4Patron);
		if ($this->bErrorOn) {
			return;
		}
		if ($result["total"] == 0) {
			$this->SetError("找不到讀者資料.");
			return;
		}
		$patron = $result["data"][0];
		$this->checkPatron($patron);
		if ($this->bErrorOn) {
			return;
		}
		#endregion

		#region 書籍資料
		$data4Item = [];
		if (array_key_exists("item_record_num", $values)) {
			$data4Item["item_record_num"] = $values["item_record_num"];
		}
		if (array_key_exists("item_barcode", $values)) {
			$data4Item["item_barcode"] = $values["item_barcode"];
		}
		$result = $this->get_item($data4Item);
		if ($this->bErrorOn) {
			return;
		}
		if ($result["total"] == 0) {
			$this->SetError("找不到書籍資料.");
			return;
		}
		$item = $result["data"][0];
		if ($item["checkout_patron_record_num"] != null) {
			if ($item["checkout_patron_record_num"] == $patron["patron_record_num"]) {
				$this->SetError("讀者已借閱此書.");
				return;
			}
		}
		if ($item["hold"] != null) {
			foreach ($item["hold"] as $key => $hold) {
				if ($hold["patron_record_num"] == $patron["patron_record_num"]) {
					$this->SetError("讀者已預約此書.");
					return;
				}
			}
		}
		#endregion

		$post = [
			"recordType" => "i",
			"recordNumber" => $item["item_record_num"],
			"pickupLocation" => $this->formatLocationCode($values["pickupLocation"]),
			"note" => $values["note"]
		];
		if ($values["expires_date"] != null) {
			$post["neededBy"] = $values["expires_date"];
		}
		$result = $this->callSierraApi("post", "/patrons/" . $patron["patron_record_num"] . "/holds/requests", $post);
		return $result;
	}
	public function patch_hold($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->patch_hold_single($value);
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
	private function patch_hold_single($data)
	{
		$values = [
			"hold_id" => null,
			"pickupLocation" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;
		$cKey1 = "hold_id";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 hold_id";
			return $callBack;
		}
		$hold_id = $values[$cKey1];
		unset($values[$cKey1]);

		$cKey1 = "pickupLocation";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 pickupLocation";
			return $callBack;
		}
		$values[$cKey1] = $this->formatLocationCode($values[$cKey1]);

		$callBack = $this->callSierraApi("put", "/patrons/holds/" . $hold_id, $values);
		return $callBack;
	}
	public function delete_hold($data)
	{
		foreach ($data as $key => $value) {
			$callBack = $this->delete_hold_single($value);
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
	private function delete_hold_single($data)
	{
		$values = [
			"hold_id" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$callBack = new stdClass();
		$callBack->status = "failed";
		$callBack->data = null;
		$cKey1 = "hold_id";
		if (!array_key_exists($cKey1, $values)) {
			$callBack->message = "必須包含 hold_id";
			return $callBack;
		}
		$hold_id = $values[$cKey1];
		unset($values[$cKey1]);

		$callBack = $this->callSierraApi("delete", "/patrons/holds/" . $hold_id, $values);
		return $callBack;
	}

	//地點代碼要補足5個字元
	private function formatLocationCode($location)
	{
		return substr($location . "     ", 0, 5);
	}
	#endregion
	#region checkout
	public function get_checkout($params)
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
			"rfid" => null,
			"item_record_num" => null,
			"item_barcode" => null,
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		#region where condition
		$condition = ""; {
			$condition_values = [
				"patron_record_num" => " AND patron_record_num = :patron_record_num",
				"patron_barcode" => " AND patron_barcode = UPPER(:patron_barcode)",
				"rfid" => " AND patron_id = (SELECT record_id FROM sierra_view.varfield_view WHERE record_type_code = 'p' AND varfield_type_code = 'b' AND UPPER(field_content) = :rfid)",
				"item_record_num" => " AND item_record_num = :item_record_num",
				"item_barcode" => " AND item_barcode = UPPER(:item_barcode)"
			];
			$condition = xStatic::KeyMatchThenJoinValue($values, false, $condition_values, true);
		}
		#endregion

		if (!array_key_exists("patron_record_num", $values)) {
			$values["patron_record_num"] = -1;
		}
		if (!array_key_exists("patron_barcode", $values) || $values["patron_barcode"] == "") {
			$values["patron_barcode"] = "~";
		}

		$values_external = [
            "checkout_due_datetime_start" => null,
            "checkout_due_datetime_end" => null,
        ];

		foreach ($values_external as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'checkout_due_datetime_start' || $key == 'checkout_due_datetime_end') {
                    if ($params[$key] == '') {
                        unset($values_external[$key]);
                    } else {
                        $values[$key] = $params[$key];
                    }
                } else {
                    $values[$key] = $params[$key];
                }
            } else {
                unset($values_external[$key]);
            }
        }

        $condition_values_external = [
            "checkout_due_datetime_start" => " AND (EXTRACT(DAY FROM to_char(checkout_due_datetime::timestamp, 'yyyy-MM-dd')::timestamp - :checkout_due_datetime_start::timestamp) >= 0 AND checkout_due_datetime::timestamp IS NOT NULL)",
            "checkout_due_datetime_end" => " AND (EXTRACT(DAY FROM to_char(checkout_due_datetime::timestamp, 'yyyy-MM-dd')::timestamp - :checkout_due_datetime_end::timestamp) >= 0 AND checkout_due_datetime::timestamp IS NOT NULL)",
        ];

        foreach ($condition_values_external as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key == 'checkout_due_datetime_start' || $key == 'checkout_due_datetime_end') {
                    if ($params[$key] == '') {
                        unset($condition_values[$key]);
                    } else {
                        $condition .= $value;
                    }
                } else {
                    $condition .= $value;
                }
            } else {
                unset($condition_values_external[$key]);
            }
        }

		$select_condition = "";

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = ''; {
			$default_order = " checkout_due_datetime";
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
            WITH h AS (
				SELECT
					h.id AS hold_id,
					h.placed_gmt::timestamp without time zone AS hold_datetime,
					h.status AS hold_status_code,
					h.record_id AS item_id,
					p.id AS patron_id,
					p.record_num AS patron_record_num,
					UPPER(p.barcode) AS patron_barcode,
					CASE WHEN (p.record_num = :patron_record_num OR UPPER(p.barcode) = UPPER(:patron_barcode)) THEN true ELSE false END AS selfhold
				FROM sierra_view.hold AS h
					INNER JOIN sierra_view.patron_view AS p ON p.id = h.patron_record_id
				WHERE h.is_frozen = false
			)
			SELECT
				p.id AS patron_id,
				p.record_num AS patron_record_num,
				na.last_name AS patron_name,
				UPPER(p.barcode) AS patron_barcode,
				(
					SELECT json_agg(t.field_content)
					FROM (
						SELECT UPPER(field_content) AS field_content
						FROM sierra_view.varfield_view
						WHERE record_id = p.id AND UPPER(field_content) <> UPPER(p.barcode)
							AND record_type_code = 'p' AND varfield_type_code = 'b'
							AND LENGTH(field_content) = 8
					) AS t
				) AS rfid,

				p.mblock_code AS mblock_code,
				CASE WHEN p.mblock_code = '-' THEN '' ELSE mbn.name END AS mblock_name,

				b.id AS bib_id, b.record_num AS bib_record_num,
				bp.best_title,
				bp.best_author,
				(
					SELECT
						string_agg(content, ' ' ORDER BY display_order) AS content
					FROM sierra_view.subfield_view
					WHERE record_num = b.record_num AND marc_tag= '092'
				) AS s092,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							occ_num,
							CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END AS tag,
							string_agg(content, ' ' ORDER BY display_order) AS content
						FROM sierra_view.subfield_view
						where record_num = b.record_num AND marc_tag= '020'
						GROUP BY occ_num, CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END
					) AS t
				) AS s020,

				i.id AS item_id, i.record_num AS item_record_num,
				i.barcode AS item_barcode,
				i.item_status_code,
				i_status_name.name AS item_status_name,

				checkout.id AS checkout_id,
				checkout.checkout_gmt::timestamp without time zone AS checkout_checkout_datetime,
				(to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_due_datetime,
				EXTRACT(DAY FROM (to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd'))::timestamp - CURRENT_DATE) AS checkout_due_days,
				(to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_over_datetime,
				CASE
					WHEN p.record_num = :patron_record_num OR UPPER(p.barcode) = UPPER(:patron_barcode) THEN 'x'
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id AND selfhold) > 0 THEN 'x'
					WHEN i.location_code LIKE 'maol%' THEN 'x' 	--在密集區不能預約
					ELSE 'h'
				END AS ui_status_id,
				CASE
					WHEN p.record_num = :patron_record_num OR UPPER(p.barcode) = UPPER(:patron_barcode) THEN '您已借出'
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id AND selfhold) > 0 THEN '您已預約'
					WHEN i.location_code LIKE 'maol%' THEN '在密集區不能預約'
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id) = 0 THEN '可預約 ' || to_char(checkout.due_gmt::timestamp without time zone, 'yyyy-mm-dd') || ' 到期'
					ELSE '可預約 +' || (SELECT COUNT(*) FROM h WHERE item_id = i.id) || ' 預約'	--1:已借出，1:有人預約
				END AS ui_status_name
			FROM sierra_view.checkout
				INNER JOIN sierra_view.patron_view p on p.id = checkout.patron_record_id
				INNER JOIN sierra_view.patron_record_fullname na on na.patron_record_id = p.id

				INNER JOIN sierra_view.mblock_property AS mb ON mb.code = p.mblock_code
				INNER JOIN sierra_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4

				INNER JOIN sierra_view.bib_record_item_record_link AS bri ON bri.item_record_id = checkout.item_record_id
				LEFT JOIN sierra_view.bib_view b ON b.id = bri.bib_record_id
				LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = b.id

				LEFT JOIN sierra_view.item_view i ON i.id = checkout.item_record_id
				LEFT JOIN sierra_view.item_status_property AS i_status ON i_status.code = i.item_status_code
				LEFT JOIN sierra_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
	public function post_checkout($params)
	{
		$values = [
			"patron_record_num" => null, "patron_barcode" => null,		//二擇一
			"item_record_num" => null, "item_barcode" => null			//二擇一
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);

		#region 讀者資料
		$result = $this->get_patron(["record_num" => $values["patron_record_num"], "barcode" => $values["patron_barcode"]]);
		if ($this->bErrorOn) {
			return;
		}
		if ($result["total"] == 0) {
			$this->SetError("找不到讀者資料.");
			return;
		}
		$patron = $result["data"][0];
		$this->checkPatron($patron);
		if ($this->bErrorOn) {
			return;
		}
		#endregion

		#region 書籍資料
		$result = $this->get_item(["item_record_num" => $values["item_record_num"], "item_barcode" => $values["item_barcode"]]);
		if ($this->bErrorOn) {
			return;
		}
		if ($result["total"] == 0) {
			$this->SetError("找不到書籍資料.");
			return;
		}
		$item = $result["data"][0];
		if ($item["checkout_patron_record_num"] != null) {
			if ($item["checkout_patron_record_num"] == $patron["patron_record_num"]) {
				$this->SetError("讀者已借閱此書.");
				return;
			}
			$this->SetError("此書已被借出.");
			return;
		}
		#endregion

		$post = [
			"patronBarcode" => $patron["patron_barcode"],
			"itemBarcode" => $item["item_barcode"]
		];
		$result = $this->callSierraApi("post", "/patrons/checkout", $post);
		return $result;
	}
	public function get_checkout_history($params)
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
		if (count($values) == 0) {
			$this->SetError("讀取讀者歴史還書資料失敗. 沒有相關參數.");
			return;
		}
		#region where condition
		$condition = ""; {
			$condition_values = [
				"patron_record_num" => " AND patron_record_num = :patron_record_num",
				"patron_barcode" => " AND patron_barcode = UPPER(:patron_barcode)",
				"rfid" => " AND patron_id = (SELECT record_id FROM sierra_view.varfield_view WHERE record_type_code = 'p' AND varfield_type_code = 'b' AND UPPER(field_content) = :rfid)"
			];
			$condition = xStatic::KeyMatchThenJoinValue($values, false, $condition_values, true);
		}
		#endregion

		$select_condition = "";
		/*
        $searchable_columns = ['grade_name', 'serial_name', 'name', 'name_english', 'phone', 'email', 'school', 'study_time_start', 'study_time_end', 'address', 'note'];
        if (array_key_exists('custom_filter_key', $params) && array_key_exists('custom_filter_value', $params) && count($params['custom_filter_key']) != 0) {
		$select_condition = " AND (";
		foreach ($params['custom_filter_key'] as $select_filter_arr_data) {
		if (in_array($select_filter_arr_data, $searchable_columns)) {
		$select_condition .= " {$select_filter_arr_data} LIKE '%{$params['custom_filter_value']}%' OR";
		}
		}
		$select_condition = rtrim($select_condition, 'OR');
		$select_condition .= ")";
        }
		 */

		$values["start"] = $start;
		$values["length"] = $length;
		$values_count = $values;
		unset($values_count['start']);
		unset($values_count['length']);

		//預設排序
		$order = ''; {
			$default_order = " checkout_datetime DESC";
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
					UPPER(p.barcode) AS patron_barcode,
					(
						SELECT json_agg(t.field_content)
						FROM (
							SELECT UPPER(field_content) AS field_content
							FROM sierra_view.varfield_view
							WHERE record_id = p.id AND UPPER(field_content) <> UPPER(p.barcode)
								AND record_type_code = 'p' AND varfield_type_code = 'b'
								AND LENGTH(field_content) = 8
						) AS t
					) AS rfid,
					b.id AS bib_id, b.record_num AS bib_record_num,
					bp.best_title,
					bp.best_author,
					(
						SELECT
							string_agg(content, ' ' ORDER BY display_order) AS content
						FROM sierra_view.subfield_view
						WHERE record_num = b.record_num AND marc_tag= '092'
					) AS s092,
					(
						SELECT json_agg(t)
						FROM (
							SELECT
								occ_num,
								CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END AS tag,
								string_agg(content, ' ' ORDER BY display_order) AS content
							FROM sierra_view.subfield_view
							where record_num = b.record_num AND marc_tag= '020'
							GROUP BY occ_num, CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END
						) AS t
					) AS s020,

					i.id AS item_id, i.record_num AS item_record_num,
					UPPER(i.barcode) AS item_barcode,
					h.checkout_gmt::timestamp without time zone AS checkout_datetime,
					h.checkin_gmt::timestamp without time zone AS checkin_datetime
				FROM sierra_view.item_circ_history AS h
					LEFT JOIN sierra_view.patron_view AS p ON p.id = h.patron_record_metadata_id
					LEFT JOIN sierra_view.item_view AS i ON i.id = h.item_record_metadata_id
					LEFT JOIN sierra_view.bib_record_item_record_link AS b2i ON b2i.item_record_id = i.id
					LEFT JOIN sierra_view.bib_view AS b ON b.id = b2i.bib_record_id
					LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = b.id
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
	public function get_checkout_overdue($params)
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
					FROM sierra_view.checkout
						INNER JOIN sierra_view.patron_view p on p.id = checkout.patron_record_id
						INNER JOIN sierra_view.patron_record_fullname na on na.patron_record_id = p.id
						LEFT JOIN sierra_view.varfield_view AS vf_m ON vf_m.record_id = p.id AND vf_m.record_type_code = 'p' AND vf_m.varfield_type_code = 'm'

						INNER JOIN sierra_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
						INNER JOIN sierra_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = p.pcode3::varchar
						INNER JOIN sierra_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4

						INNER JOIN sierra_view.mblock_property AS mb ON mb.code = p.mblock_code
						INNER JOIN sierra_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4
					WHERE (to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone < NOW()
				)
				SELECT
					dt.*,
					(
						SELECT json_agg(t.field_content)
						FROM (
							SELECT UPPER(field_content) AS field_content
							FROM sierra_view.varfield_view
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
								bp.best_title,
								bp.best_author,

								i.id AS item_id, i.record_num AS item_record_num,
								i.barcode AS item_barcode,
								i.item_status_code,
								i_status_name.name AS item_status_name,

								checkout.id AS checkout_id,
								checkout.checkout_gmt::timestamp without time zone AS checkout_checkout_datetime,
								(to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_due_datetime,
								(to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_over_datetime
							FROM sierra_view.checkout
								INNER JOIN sierra_view.bib_record_item_record_link AS bri ON bri.item_record_id = checkout.item_record_id
								LEFT JOIN sierra_view.bib_view b ON b.id = bri.bib_record_id
								LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = b.id

								LEFT JOIN sierra_view.item_view i ON i.id = checkout.item_record_id
								LEFT JOIN sierra_view.item_status_property AS i_status ON i_status.code = i.item_status_code
								LEFT JOIN sierra_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4
							WHERE checkout.patron_record_id = dt.patron_id
								AND (to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone < NOW()
						) AS t
					) AS items
				FROM dt
				WHERE TRUE {$condition}
EOD;

		$stmt = $this->db_sierra->prepare($sql);
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
				FROM sierra_view.item_record
					INNER JOIN sierra_view.bib_record_item_record_link AS b2i ON b2i.item_record_id = item_record.record_id
				ORDER BY checkout_count DESC
				limit 100
			)
			SELECT
				b.id AS bib_id, b.record_num AS bib_record_num,
				bp.best_title,
				bp.best_author,
				dt.checkout_count
			FROM dt
				INNER JOIN sierra_view.bib_view AS b ON b.id = dt.bib_id
				INNER JOIN sierra_view.bib_record_property bp on bp.bib_record_id = b.id
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
				FROM sierra_view.{$checkoutTable} AS checkout
					INNER JOIN sierra_view.patron_view AS p ON p.id = checkout.{$checkoutPatronField}
				WHERE checkout.checkout_gmt BETWEEN :checkout_start_time AND :checkout_end_time
				GROUP BY p.pcode3
			)
			SELECT
				dfpn3.name AS type,
				dt.checkout_count AS value
			FROM dt
				INNER JOIN sierra_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
				INNER JOIN sierra_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = dt.pcode3::varchar
				INNER JOIN sierra_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
				FROM sierra_view.{$checkoutTable} AS checkout
					INNER JOIN sierra_view.patron_view AS p ON p.id = checkout.{$checkoutPatronField}
				WHERE checkout.checkout_gmt BETWEEN :checkout_start_time AND :checkout_end_time
				GROUP BY p.ptype_code
			)
			SELECT
				ppn.description AS type,
				dt.checkout_count as value
			FROM dt
				INNER JOIN sierra_view.ptype_property AS pp ON pp.value = dt.ptype_code
				INNER JOIN sierra_view.ptype_property_name AS ppn ON ppn.ptype_id = pp.id AND ppn.iii_language_id = 4
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
				"rfid" => " AND patron_id = (SELECT record_id FROM sierra_view.varfield_view WHERE record_type_code = 'p' AND varfield_type_code = 'b' AND UPPER(field_content) = :rfid)",
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
			$default_order = " checkout_due_datetime";
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
						FROM sierra_view.varfield_view
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
					FROM sierra_view.subfield_view
					WHERE record_num = b.record_num AND marc_tag= '092'
				) AS s092,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							occ_num,
							CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END AS tag,
							string_agg(content, ' ' ORDER BY display_order) AS content
						FROM sierra_view.subfield_view
						where record_num = b.record_num AND marc_tag= '020'
						GROUP BY occ_num, CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END
					) AS t
				) AS s020,
				i.id AS item_id, i.record_num AS item_record_num,
				UPPER(i.barcode) AS item_barcode,
				checkout_gmt::timestamp without time zone AS checkout_datetime,
				(to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_due_datetime,
				(to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_over_datetime,
				returned_gmt::timestamp without time zone AS checkin_datetime,
				f.paid_gmt::timestamp without time zone AS paid_datetime,
				f.paid_amt * 100 AS paid_amt,
				(f.item_charge_amt + f.processing_fee_amt + f.billing_fee_amt) * 100 AS fine_amt
			FROM sierra_view.fine AS f
				INNER JOIN sierra_view.patron_view p on p.id = f.patron_record_id
				LEFT JOIN sierra_view.patron_record_fullname pname on pname.patron_record_id = p.id

				LEFT JOIN sierra_view.bib_record_item_record_link b2i ON b2i.item_record_id = f.item_record_metadata_id
				LEFT JOIN sierra_view.bib_view AS b ON b.id = b2i.bib_record_id
				LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = b.id

				LEFT JOIN sierra_view.item_view AS i ON b2i.item_record_id = i.id
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
				FROM sierra_view.fine AS f
					INNER JOIN sierra_view.patron_view p on p.id = f.patron_record_id
					INNER JOIN sierra_view.patron_record_fullname AS na on na.patron_record_id = p.id
					LEFT JOIN sierra_view.varfield_view AS vf_m ON vf_m.record_id = p.id AND vf_m.record_type_code = 'p' AND vf_m.varfield_type_code = 'm'

					INNER JOIN sierra_view.user_defined_category AS dfc3 ON dfc3.code = 'pcode3'
					INNER JOIN sierra_view.user_defined_property AS dfp3 ON dfp3.user_defined_category_id = dfc3.id AND dfp3.code = p.pcode3::varchar
					INNER JOIN sierra_view.user_defined_property_name AS dfpn3 ON dfpn3.user_defined_property_id = dfp3.id AND dfpn3.iii_language_id = 4

					INNER JOIN sierra_view.mblock_property AS mb ON mb.code = p.mblock_code
					INNER JOIN sierra_view.mblock_property_name AS mbn ON mbn.mblock_property_id = mb.id AND mbn.iii_language_id = 4
				WHERE (f.paid_amt - f.item_charge_amt - f.processing_fee_amt - f.billing_fee_amt) < 0
			)
			SELECT
				dt.*,
				(
					SELECT json_agg(t.field_content)
					FROM (
						SELECT UPPER(field_content) AS field_content
						FROM sierra_view.varfield_view
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

							f.checkout_gmt::timestamp without time zone AS checkout_datetime,
							(to_char(f.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_due_datetime,
							(to_char(f.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_over_datetime,
							f.returned_gmt::timestamp without time zone AS checkin_datetime,
							f.paid_gmt::timestamp without time zone AS paid_datetime,
							f.paid_amt * 100 AS paid_amt,
							(f.item_charge_amt + f.processing_fee_amt + f.billing_fee_amt) * 100 AS fine_amt
						FROM sierra_view.fine AS f
							LEFT JOIN sierra_view.bib_record_item_record_link brir ON brir.item_record_id = f.item_record_metadata_id
							LEFT JOIN sierra_view.bib_view b ON brir.bib_record_id = b.id
							LEFT JOIN sierra_view.bib_record_property bp on bp.bib_record_id = brir.bib_record_id

							LEFT JOIN sierra_view.item_view i ON i.id = brir.item_record_id
							LEFT JOIN sierra_view.item_status_property AS i_status ON i_status.code = i.item_status_code
							LEFT JOIN sierra_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4
						WHERE f.patron_record_id = dt.patron_id AND (f.paid_amt - f.item_charge_amt - f.processing_fee_amt - f.billing_fee_amt) < 0
					) AS t
				) AS items
			FROM dt
			WHERE TRUE {$condition}
EOD;

		$stmt = $this->db_sierra->prepare($sql);
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
	public function get_bib($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			"field" => null, "keyword" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");

		$values["field_1"] = $values["field_2"] = $values["field_3_1"] = $values["field_3_2"] = $values["field_3_3"] = $values["field"]; unset($values["field"]);
		$values["keyword_1"] = $values["keyword_2"] = $values["keyword_3"] = $values["keyword"]; unset($values["keyword"]);
		$values["rank_limit_1"] = $values["rank_limit_2"] = $values["rank_limit_3"] = 10;
		$values_count = $values;
		$values["start"] = $start;
		//$values["length"] = $length;
		//unset($values_count['start']);
		//unset($values_count['length']);

		//預設排序
		$order = '';
		{
			$default_order = " [rank] DESC";
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
				b.id AS bib_id, md.record_num AS bib_record_num,
				(
					SELECT
						string_agg(content, ' ') WITHIN GROUP (ORDER BY display_order)
					FROM subfield
					WHERE record_id = bp.bib_record_id AND marc_tag= '245'
				) AS bib_subject,
				(
					SELECT string_agg(t.content,' ')
					FROM (
						SELECT marc_tag, occ_num, content = string_agg(content, ' ') WITHIN GROUP (ORDER BY display_order)
						FROM subfield
						WHERE record_id = bp.bib_record_id AND field_type_code = 't' AND tag = 'a' AND marc_tag IN ('240', '246')
						GROUP BY marc_tag, occ_num
						UNION ALL
						SELECT 'best_title', 0, bp.best_title
					) AS t
				) AS bib_title,
				(
					SELECT *
					FROM (
						SELECT marc_tag, occ_num, content = string_agg(content, ' ') WITHIN GROUP (ORDER BY display_order)
						FROM subfield
						WHERE record_id = bp.bib_record_id AND field_type_code = 't' AND tag = 'a' AND marc_tag IN ('240', '246')
						GROUP BY marc_tag, occ_num
						UNION ALL
						SELECT 'best_title', 0, bp.best_title
					) AS t
					FOR JSON PATH
				) AS bib_titles,
				bp.best_author AS bib_author,
				(
					SELECT *
					FROM (
						SELECT marc_tag, occ_num, content = string_agg(content, ' ') WITHIN GROUP (ORDER BY display_order)
						FROM subfield
						WHERE record_id = bp.bib_record_id AND field_type_code = 'a' AND tag = 'a' AND marc_tag IN ('100', '110', '111', '700', '710', '711')
						GROUP BY marc_tag, occ_num
					) AS t
					FOR JSON PATH
				) AS bib_authors,
				bp.publish_year,
				(
					SELECT
						string_agg(content, ' ') WITHIN GROUP (ORDER BY display_order)
					FROM subfield
					WHERE record_id = bp.bib_record_id AND marc_tag= '260'
				) AS publisher,
				(
					SELECT
						string_agg(content, ',') WITHIN GROUP (ORDER BY display_order)
					FROM subfield
					WHERE record_id = bp.bib_record_id AND marc_tag= '020' AND tag = 'a'
				) AS isbn,
				b2l.location_code AS bib_location_code, ln.name AS bib_location_name,
				b.bcode2 AS bib_bcode2, udp_n.name AS bib_bcode2_name,
				s856.content AS bib_url,
				x.[rank]
			FROM (
				SELECT bib_id = U.bib_id, [rank] = SUM(U.[rank])
				FROM (
					SELECT bib_id = b.bib_record_id, [rank] = R.[RANK]
					FROM bib_record_property AS b
						INNER JOIN FREETEXTTABLE(pccu.dbo.bib_record_property, (best_title), :keyword_1) AS R ON b.bib_record_id = R.[KEY]
					WHERE :field_1 IN ('keyword', 'title') AND R.[RANK] > :rank_limit_1
					UNION
					SELECT bib_id = b.bib_record_id, [rank] = R.[RANK]
					FROM bib_record_property AS b
						INNER JOIN FREETEXTTABLE(pccu.dbo.bib_record_property, (best_author), :keyword_2) AS R ON b.bib_record_id = R.[KEY]
					WHERE :field_2 IN ('keyword', 'author') AND R.[RANK] > :rank_limit_2
					UNION
					SELECT id = sf.record_id, [rank] = R.[RANK]
					FROM subfield AS sf
						INNER JOIN FREETEXTTABLE(pccu.dbo.subfield, (content), :keyword_3) AS R ON sf.id = R.[KEY]
					WHERE R.[RANK] > :rank_limit_3 AND (
							(:field_3_1 IN ('keyword', 'title') AND (
								(sf.tag = 'b' AND sf.marc_tag = '245') OR (sf.tag = 'a' AND sf.marc_tag IN ('240', '246'))
							))
							OR
							(:field_3_2 IN ('keyword', 'author') AND
								sf.tag = 'a' AND sf.marc_tag IN ('100', '110', '111', '700', '710', '711')
							)
							OR
							(:field_3_3 IN ('keyword', 'isbn') AND
								sf.marc_tag = '020'
							)
						)
				) AS U
				GROUP BY U.bib_id
			) AS x
				INNER JOIN bib_record_property AS bp ON bp.bib_record_id = x.bib_id
				INNER JOIN bib_record AS b ON b.id = bp.bib_record_id
				INNER JOIN record_metadata md ON md.id = b.id

				INNER JOIN user_defined_property AS udp ON udp.code = b.bcode2 AND udp.user_defined_category_id = 13
				INNER JOIN user_defined_property_name AS udp_n ON udp_n.user_defined_property_id = udp.id

				LEFT JOIN bib_record_location AS b2l ON b2l.bib_record_id = b.id
				LEFT JOIN [location] l ON l.code = b2l.location_code
				LEFT JOIN location_name ln ON ln.location_id = l.id

				LEFT JOIN subfield AS s856 ON s856.record_id = bp.bib_record_id AND s856.field_type_code = 'y' AND s856.marc_tag = '856' AND s856.tag = 'u'
EOD;

		$sql_default = "SELECT *, [key] = ROW_NUMBER() OVER ({$order}) FROM ({$sql_default_inside}) dt";
		$sql = "SELECT TOP {$length} * FROM ({$sql_default}) dt WHERE [key] > :start ORDER BY [key]";
		$sql_count = "SELECT COUNT(*) FROM ({$sql_default}) dt";

		$stmt = $this->db_sqlsrv_sierra->prepare($sql);
		$stmt_count = $this->db_sqlsrv_sierra->prepare($sql_count);
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
    #endregion
	#region item
	public function get_item($params)
	{
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $params, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			"bib_id" => null,
			"bib_record_num" => null,
			"title" => null,
			"author" => null,
			"item_id" => null,
			"item_record_num" => null,
			"item_barcode" => null,
			"patron_record_num" => null,
			"patron_barcode" => null
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $params, $bReplaceThenRemove = false);
		$values = xStatic::ValueMatchThenRemove($values, "");
		if (!array_key_exists("patron_record_num", $values)) {
			$values["patron_record_num"] = -1;
		}
		if (!array_key_exists("patron_barcode", $values) || $values["patron_barcode"] == "") {
			$values["patron_barcode"] = "~";
		}

		#region where condition
		$condition = ""; {
			$condition_values = [
				"bib_id" => " AND bib_id = :bib_id",
				"bib_record_num" => " AND bib_record_num = :bib_record_num",
				"title" => " AND best_title LIKE '%' || :title || '%'",
				"author" => " AND best_author LIKE '%' || :author || '%'",
				"item_id" => " AND item_id = :item_id",
				"item_record_num" => " AND item_record_num = :item_record_num",
				"item_barcode" => " AND item_barcode = UPPER(:item_barcode)",
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
			$default_order = " item_id";
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
            WITH h AS (
				SELECT
					h.id AS hold_id,
					h.placed_gmt::timestamp without time zone AS hold_datetime,
					h.status AS hold_status_code,
					h.record_id AS item_id,
					p.id AS patron_id,
					p.record_num AS patron_record_num,
					UPPER(p.barcode) AS patron_barcode,
					CASE WHEN (p.record_num = :patron_record_num OR UPPER(p.barcode) = UPPER(:patron_barcode)) THEN true ELSE false END AS selfhold
				FROM sierra_view.hold AS h
					INNER JOIN sierra_view.patron_view AS p ON p.id = h.patron_record_id
				WHERE h.is_frozen = false
			)
			SELECT
				b.id AS bib_id, b.record_num AS bib_record_num,
				bp.best_title,
				bp.best_author,

				(
					SELECT
						string_agg(content, ' ' ORDER BY display_order) AS content
					FROM sierra_view.subfield_view
					WHERE record_num = b.record_num AND marc_tag= '092'
				) AS s092,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							occ_num,
							CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END AS tag,
							string_agg(content, ' ' ORDER BY display_order) AS content
						FROM sierra_view.subfield_view
						where record_num = b.record_num AND marc_tag= '020'
						GROUP BY occ_num, CASE WHEN tag = 'a' THEN 'isbn' ELSE 'other' END
					) AS t
				) AS s020,

				i.id AS item_id, i.record_num AS item_record_num,
				UPPER(i.barcode) AS item_barcode,

				sf_i_v.content AS item_version_copy,

				i.location_code AS item_location_code, ln.name AS item_location_name,
				i.item_status_code, i_status_name.name AS item_status_name,

				checkout.checkout_gmt::timestamp without time zone AS checkout_datetime,
				(to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd 23:59:59'))::timestamp without time zone AS checkout_due_datetime,
				EXTRACT(DAY FROM (to_char(checkout.due_gmt::timestamp, 'yyyy-mm-dd'))::timestamp - CURRENT_DATE) AS checkout_due_days,
				(to_char(checkout.due_gmt::timestamp + '1 day', 'yyyy-mm-dd'))::timestamp without time zone AS checkout_over_datetime,

				checkout_patron.id AS checkout_patron_id,
				checkout_patron.record_num AS checkout_patron_record_num,
				UPPER(checkout_patron.barcode) AS checkout_patron_barcode,
				CASE
					WHEN checkout_patron.record_num = :patron_record_num THEN true
					WHEN UPPER(checkout_patron.barcode) = UPPER(:patron_barcode) THEN true
					ELSE false
				END AS checkout_selfcheckout,
				CASE
					WHEN i.item_status_code = 'o' THEN false	--限館內
					WHEN checkout_patron.record_num = :patron_record_num THEN false
					WHEN UPPER(checkout_patron.barcode) = UPPER(:patron_barcode) THEN false
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id) > 0 THEN false	--有人預約就無法外借
					ELSE true
				END AS checkout_cancheckout,
				(
					SELECT json_agg(t)
					FROM (
						SELECT
							hold_id, hold_datetime, hold_status_code,
							patron_id, patron_record_num, patron_barcode
						FROM h
						WHERE h.item_id = i.id
					) AS t
				) AS hold,
				CASE
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id AND selfhold) > 0 THEN true
					ELSE false
				END AS hold_selfhold,
				CASE
					WHEN checkout_patron.id IS NULL THEN false	--尚未借出不能預約
					WHEN checkout_patron.record_num = :patron_record_num THEN false --自己已借出了不能預約
					WHEN UPPER(checkout_patron.barcode) = UPPER(:patron_barcode) THEN false --自己已借出了不能預約
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id AND selfhold) > 0 THEN false	--已經預約了
					WHEN i.location_code LIKE 'maol%' THEN false	--在密集區不能預約
					WHEN i.item_status_code IN ('g', 't', 's', '-', 'p', '!') THEN true
					ELSE false
				END AS hold_canhold,
				CASE
					WHEN checkout_patron.record_num = :patron_record_num OR UPPER(checkout_patron.barcode) = UPPER(:patron_barcode) THEN 'x'
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id AND selfhold) > 0 THEN 'x'
					WHEN checkout.id > 0 THEN (	--已被借出，判斷預約
						CASE
							WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id) = 0 THEN ( --1:已借出，0:沒有人預約
								CASE
									WHEN i.location_code LIKE 'maol%' THEN 'x'	--在密集區不能預約
									ELSE 'h'
								END
							)
							ELSE 'h'	--1:已借出，1:有人預約
						END
					)
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id) > 0 THEN 'h'	--0:沒有借出, 1:有人預約
					ELSE (	--0:沒有借出, 0:沒有人預約
						CASE WHEN i.item_status_code = 'o' THEN 'x' ELSE 'c' END
					)
				END AS ui_status_id,
				CASE
					WHEN checkout_patron.record_num = :patron_record_num OR UPPER(checkout_patron.barcode) = UPPER(:patron_barcode) THEN '您已借出'
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id AND selfhold) > 0 THEN '您已預約'
					WHEN checkout.id > 0 THEN (	--已被借出，判斷預約
						CASE
							WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id) = 0 THEN ( --1:已借出，0:沒有人預約
								CASE
									WHEN i.location_code LIKE 'maol%' THEN '在密集區不能預約'
									ELSE '可預約 ' || to_char(checkout.due_gmt::timestamp without time zone, 'yyyy-mm-dd') || ' 到期'
								END
							)
							ELSE '可預約 +' || (SELECT COUNT(*) FROM h WHERE item_id = i.id) || ' 預約'	--1:已借出，1:有人預約
						END
					)
					WHEN (SELECT COUNT(*) FROM h WHERE item_id = i.id) > 0 THEN '可預約 +' || (SELECT COUNT(*) FROM h WHERE item_id = i.id) || ' 預約'	--0:沒有借出, 1:有人預約
					ELSE i_status_name.name	--0:沒有借出, 0:沒有人預約
				END AS ui_status_name

			FROM sierra_view.bib_view AS b
				INNER JOIN sierra_view.bib_record_property bp on bp.bib_record_id = b.id

				LEFT JOIN sierra_view.bib_record_item_record_link AS b2i ON b2i.bib_record_id = b.id
				LEFT JOIN sierra_view.item_view AS i ON b2i.item_record_id = i.id
				LEFT JOIN sierra_view.item_status_property AS i_status ON i_status.code = i.item_status_code
				LEFT JOIN sierra_view.item_status_property_name AS i_status_name ON i_status_name.item_status_property_id = i_status.id AND i_status_name.iii_language_id = 4

				LEFT JOIN sierra_view.subfield AS sf_i_v ON sf_i_v.record_id = i.id AND sf_i_v.field_type_code = 'v'

				LEFT JOIN sierra_view.location l ON l.code = i.location_code
				LEFT JOIN sierra_view.location_name ln ON ln.location_id = l.id AND ln.iii_language_id = 4

				LEFT JOIN sierra_view.checkout ON checkout.item_record_id = i.id
				LEFT JOIN sierra_view.patron_view AS checkout_patron ON checkout_patron.id = checkout.patron_record_id
			WHERE b.bcode3 = '-' --d:待删除記錄, n:隱藏, c:隱藏訂購記錄
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

		$stmt = $this->db_sierra->prepare($sql);
		$stmt_count = $this->db_sierra->prepare($sql_count);
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
	#endregion
	#region token
	/* {
		"access_token": "MH8kRhNXqxKNudvJclvtgwhvVThMQ_GS8W0MDmF9V--ZXc8BSVN4DZz3xRKhdqeDKleSJvONrmE-R_4nfEVMa__jpleOLx51XgsrNVUX15Ooz24YU-MEKq9niS8cMAJH",
		"token_type": "bearer",
		"expires_in": 3600
	} */
	public function get_token() {
		$cUrl = $this->container->sierra["api_url"] . "/token";
		$cKey = "Authorization: Basic " . $this->container->sierra["api_key"];
		$callBack = new stdClass();
		try {
			$oRequest = curl_init();
			curl_setopt($oRequest, CURLOPT_POST, true);
			curl_setopt($oRequest, CURLOPT_URL, $cUrl);
			curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oRequest, CURLOPT_HTTPHEADER, array($cKey));
			curl_setopt($oRequest, CURLOPT_RETURNTRANSFER, true);
			$cJson = curl_exec($oRequest);
			if ($cJson === false) {
				throw new Exception(curl_error($oRequest), curl_errno($oRequest));
			}
			$data = json_decode($cJson);
			if (isset($data->description)) {
				throw new Exception($data->description);
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
	#endregion
	public function initialize_search()
	{
		$default_value = ["cur_page" => 1, "size" => 10];
		return $default_value;
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
	private function callSierraApi($method, $url, $postFields)
	{
		$callBack = $this->get_token();
		if ($callBack->status == "") {
			return $callBack;
		}
		$cBearer = "Authorization: Bearer " . $callBack->data;

		$url = $this->container->sierra["api_url"] . $url;
		$postFields = json_encode($postFields);
		try {
			$oRequest = curl_init();
			curl_setopt($oRequest, CURLOPT_POST, true);
			curl_setopt($oRequest, CURLOPT_CUSTOMREQUEST, strtoupper($method));
			curl_setopt($oRequest, CURLOPT_URL, $url);
			curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oRequest, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($oRequest, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $cBearer));
			curl_setopt($oRequest, CURLOPT_RETURNTRANSFER, true);
			$cJson = curl_exec($oRequest);
			if ($cJson === false) {
				throw new Exception(curl_error($oRequest), curl_errno($oRequest));
			}
			$data = json_decode($cJson);
			if (isset($data->description)) {
				throw new Exception($data->description);
			}
			$callBack->status = "success";
			$callBack->message = null;
			$callBack->data = $data;
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

	private function removeByNotValidRfid($aRows)
	{
		foreach ($aRows as $key => $oRow) {
			$bIsvalid = false;
			if (isset($oRow["rfid"])) {
				$aRfid = json_decode($oRow["rfid"]);
				foreach ($aRfid as $rfid) {
					$callBack = $this->check_card_reader_data(["sCardSN" => $rfid]);
					if (array_key_exists("total", $callBack) && $callBack["total"] > 0) {
						$bIsvalid = true;
						break;
					}
				}
			}
			if (!$bIsvalid) {
				unset($aRows[$key]);
			}
		}
		return $aRows;
	}

	public function check_card_reader_data($params)
	{
		$opts = array(
			'http' => array(
				'user_agent' => 'PHPSoapClient'
			)
		);
		$context = stream_context_create($opts);
		$pccu_url = 'https://ap2.pccu.edu.tw:8888/libSupportService/LibChkCardNoService/LibChkCardNoService.asmx?WSDL';
		// Create a SOAP client instance
		$client = new SoapClient($pccu_url, array(
			'stream_context' => $context,
			'cache_wsdl' => WSDL_CACHE_NONE
		));

		$data = array(
			'sToken' => 'inm%xfwu9s',
			'sCardSN' => $params['sCardSN']
		);

		$temp = $client->__soapCall("Lib_GetPersonInfoJson", array($data));
		$temp = (array)$temp;
		$temp = json_decode($temp['Lib_GetPersonInfoJsonResult'], true);

		if ($temp['psn_message'] != "success") {
			$result = [
				'status' => 'fail',
				'message' => '驗證失敗'
			];
			return $result;
		}

		$result['data'][0] = $temp;
		$result['total'] = 1;
		return $result;
	}

	public function get_mail($data)
	{
		$home = new Home($this->container);
		#region page control
		$values = $this->initialize_search();
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = false, $data, $bReplaceThenRemove = true);
		$length = $values['cur_page'] * $values['size'];
		$start = $length - $values['size'];
		#endregion

		$values = [
			'date' => date('Ymd'),
			'address' => false
		];
		$values = xStatic::KeyMatchThenReplaceValue($values, $bNoPatchThenRemove = true, $data, $bReplaceThenRemove = true);

		$mailbox_server = $this->container->mailbox_server;
		$mailbox_account = "ot639430@gmail.com";
		$mailbox_password = "hcdu bcrh gilq muix";
		$imapConnection = imap_open($mailbox_server, $mailbox_account, $mailbox_password, OP_HALFOPEN); // or die("can't connect: " . imap_last_error());
		if (!$imapConnection) {
			$cMessage = imap_last_error();
			$this->SetError($cMessage);
			return;
		}
		$aBox = imap_getmailboxes($imapConnection, $mailbox_server, "%INBOX");
		imap_close($imapConnection);
		if (!is_array($aBox) && count($aBox) == 0) {
			$this->SetError("找不到收件匣.");
			return;
		}
		$mailbox = new PhpImap\Mailbox(mb_convert_encoding($aBox[0]->name, 'UTF-8', 'UTF7-IMAP'), $mailbox_account, $mailbox_password);
		$mailbox->setServerEncoding('US-ASCII');
		try {
			$since = date('d-M-Y', strtotime($values['date']));
			$before = date('d-M-Y', strtotime($values['date'] . '+2 days'));
			$mail_ids = $mailbox->searchMailbox('SINCE "' . $since . '" BEFORE "' . $before . '"');
		} catch (Exception $ex) {
			exit('An error occured: ' . $ex->getMessage());
		}

		for ($i = count($mail_ids) - 1; $i >= 0; $i--) {
			$mail_id = $mail_ids[$i];
			$mail_id = $mailbox->getMail($mail_id, false);
			// var_dump(
			// 	[
			// 		"subject" => (string) $mail_id->subject,
			// 		"uid" => (int) $mail_id->id,
			// 		"text" => $mail_id->textPlain,
			// 		"html" => $mail_id->textHtml,
			// 		"date" => (string) $mail_id->date,
			// 		"fromAddress" => $mail_id->fromAddress
			// 	]
			// );
			if ($mail_id->fromAddress == "cubg@dep.pccu.edu.tw") {
				$sql = "INSERT INTO pccu.mail(mail_uid, mail_data)
						VALUES (?, ?)
						ON CONFLICT(mail_uid)
						DO NOTHING
						RETURNING mail_id;
					";
				$stmt = $this->container->db->prepare($sql);
				$stmt->execute([
					(int) $mail_id->id,
					json_encode([
						"subject" => (string) $mail_id->subject,
						"uid" => (int) $mail_id->id,
						"text" => $mail_id->textPlain,
						"html" => $mail_id->textHtml,
						"date" => (string) $mail_id->date,
						"fromAddress" => $mail_id->fromAddress
					])
				]);
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if (count($result) === 0) break;
				$result = array();
				preg_match_all('/\((.*?)\)/', $mail_id->textHtml, $result);
				$home = new Home($this->container->db);
				foreach ($result as $item_key => $item_value) {
					if ($item_key == 1) {
						foreach ($item_value as $item_value_key => $item_value_value) {
							$user_data_return = $home->getUserByUid(["uid" => $item_value_value]);
							$user_data_return = [
								[
									'user_id' => 215,
									'module_id' => 61
								]
							];
							foreach ($user_data_return as $user_data_return_index => $user_data_return_data) {
								$user_data_return_data['external_token_type_id'] = 1;
								$all_user_token_type_result = $home->get_all_user_token_type($user_data_return_data);

							}
						}
					}
				}
			}
		}
		return true;
	}
}
