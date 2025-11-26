<?php

use nknu\base\xBaseWithDbop;
use nknu\extend\xInt as xInt;
use nknu\extend\xString as xString;
use nknu\utility\xStatic;
use \Psr\Container\ContainerInterface;

class System extends xBaseWithDbop
{
    protected $container;
    protected $db;


    // constructor receives container instance
    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->container = $container;
        $this->db = $container->db;
    }

    public function postModulesAllPermissions($data){
        $sql = " DELETE FROM  setting.module_permission
        WHERE module_id = :module_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':module_id', $data['module_id']);
        $stmt->execute();

        $tmpStr = '';
        foreach ($data['permission'] as $key => $value) {
            // $tmpval = intval($value);
            $tmpStr .= " ({$data['module_id']},'{$value}'),";
        }
        $tmpStr = substr_replace($tmpStr, "", -1);
        $sql="INSERT INTO setting.module_permission (module_id, permission_id)
        VALUES {$tmpStr}
        RETURNING module_id, permission_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        // var_dump($sql);
        return;

        return  $stmt->fetchAll(PDO::FETCH_ASSOC);;
    }

    public function getModulesAllPermissions($data){
        $sql="SELECT  permission.permission_group_id,permission_group_name,
        JSON_AGG(JSON_BUILD_OBJECT('permission_id',permission.permission_id,'permission_name',permission.permission_name,'check',CASE WHEN module_id=:module_id THEN True ELSE False END)) AS permission
        FROM  system.permission
        LEFT JOIN system.permission_group ON  permission.permission_group_id = permission_group.permission_group_id
        LEFT JOIN (SELECT * FROM setting.module_permission WHERE module_id=:module_id) AS module_permission ON module_permission.permission_id = permission.permission_id
        GROUP BY permission.permission_group_id,permission_group_name
        ORDER BY  permission.permission_group_id NULLS FIRST";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':module_id', $data['module_id']);
        $stmt->execute($data);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key => $row) {
            foreach ($row as $row_key => $value) {
                if($this->isJson($value)){
                    $result[$key][$row_key] = json_decode($value,true);
                }
            }
        }
        return $result;
    }

    public function postUserPermission($data){
        $sql = " DELETE FROM  system.user_permission
        WHERE user_id = :user_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->execute();

        $tmpStr = '';
        foreach ($data['permission'] as $key => $value) {
            $tmpStr .= " ({$data['user_id']},'{$value}'),";
        }
        $tmpStr = substr_replace($tmpStr, "", -1);
        $sql="INSERT INTO system.user_permission (user_id, permission_id)
        VALUES {$tmpStr}";
        $stmt = $this->db->prepare($sql);

        if( $stmt->execute()){
            return [
                'status' => 'success'
            ];
        } else {
            return [
                'status' => 'failed'
            ];
        }
    }

    public function getUserPermission($data){
        $sql = " SELECT user_id, permission_id
        FROM system.user_permission
        WHERE user_id = :user_id;";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->execute($data);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) == 0){
            // var_dump('in');
            $sql = " INSERT INTO system.user_permission (user_id, permission_id)
            SELECT :user_id , module_permission.permission_id
            FROM system.user_modal
            LEFT JOIN setting.module_permission ON module_permission.module_id = user_modal.module_id
            WHERE user_modal.uid = :user_id AND module_permission.permission_id IS NOt NULL
            GROUP BY module_permission.permission_id
            RETURNING user_id, permission_id

            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $data['user_id']);

            if (!$stmt->execute()) {

                return ["status" => "failed",];
            };
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        }
        // $sql = "SELECT  permission.permission_group_id,permission_group_name,
        // JSON_AGG(JSON_BUILD_OBJECT('permission_id',permission.permission_id,'permission_name',permission.permission_name,'check',CASE WHEN user_id = :user_id THEN True ELSE False END))
        // FROM system.permission
        // LEFT JOIN system.permission_group ON  permission.permission_group_id = permission_group.permission_group_id
        // LEFT JOIN (SELECT * FROM system.user_permission WHERE user_id=:user_id)AS user_permission ON user_permission.permission_id = permission.permission_id

        // GROUP BY permission.permission_group_id,permission_group_name
        // ORDER BY permission.permission_group_id
        // ";
        $sql="WITH permissions AS (
            SELECT DISTINCT permission.permission_id,
                permission.permission_name, permission.permission_url, permission.permission_icon,
                permission.permission_group_id, permission.permission_index,
                permission_group.permission_group_name
            FROM setting.module_permission
            LEFT JOIN setting.module ON module.id = module_permission.module_id
            LEFT JOIN system.user_modal ON user_modal.module_id = module.id
            LEFT JOIN system.user ON \"user\".id = user_modal.uid
            LEFT JOIN system.permission ON module_permission.permission_id = permission.permission_id
            LEFT JOIN system.permission_group ON permission_group.permission_group_id = permission.permission_group_id
            WHERE \"user\".id = :user_id
            ORDER BY permission.permission_index
        )
        SELECT permissions.permission_group_id,permissions.permission_group_name,MIN(permissions.permission_index) permissions_index,
                JSON_AGG(JSON_BUILD_OBJECT('permission_id',permissions.permission_id,
                'permission_name',permissions.permission_name,'permission_url', permissions.permission_url,
                'permission_icon',permissions.permission_icon, 'permission_index', permissions.permission_index,
                'check',CASE WHEN user_id = :user_id THEN True ELSE False END)) permissions
        FROM permissions
        LEFT JOIN (
					SELECT user_permission.*
					FROM system.user_permission
					LEFT JOIN system.permission ON permission.permission_id = user_permission.permission_id
					WHERE  user_id = :user_id
				) AS user_permission ON user_permission.permission_id = permissions.permission_id

        GROUP BY permissions.permission_group_id,permissions.permission_group_name
        ORDER BY permissions_index";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->execute($data);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key => $row) {
            foreach ($row as $row_key => $value) {
                if($this->isJson($value)){
                    $result[$key][$row_key] = json_decode($value,true);
                }
            }
        }
        return $result;


    }

    public function getPermissions($data){
        $sql = "SELECT permission.permission_id, permission.permission_name, permission.permission_url, permission.permission_icon,
                permission.permission_group_id, permission.permission_index,
                permission_group.permission_group_name
            FROM system.permission
            LEFT JOIN system.permission_group ON permission_group.permission_group_id = permission.permission_group_id
            ORDER BY permission.permission_index
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getModulePermissions($data){
        $values = [
            "module_id" => 0
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT module.id module_id, module.name module_name, COALESCE(permissions,'[]') permissions
            FROM setting.module
            LEFT JOIN (
                SELECT module_permission.module_id, JSON_AGG(JSON_BUILD_OBJECT('permission_id',permission.permission_id ) ORDER BY permission.permission_id ASC) permissions
                FROM setting.module_permission
                LEFT JOIN system.permission ON module_permission.permission_id = permission.permission_id
                GROUP BY module_permission.module_id
            )module_permission ON module_permission.module_id = module.id
            WHERE module.id = :module_id
            ORDER BY module.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key => $row) {
            foreach ($row as $row_key => $value) {
                if($this->isJson($value)){
                    $result[$key][$row_key] = json_decode($value,true);
                }
            }
        }
        return $result;
    }

    public function patchModulePermissions($data){
        $values = [];
        $value = [
            "module_id" => 0,
            "permission_id" => [],
        ];
        $result = $data;
        foreach ($data as $key => $datas) {
            if($key==='data'){
                $result = [];
                foreach ($datas as $datas_key => $row) {
                    $value = [
                        "module_id" => 0,
                        "permission_id" => [],
                    ];
                    foreach ($row as $row_key => $row_value) {
                        if($row_key==='module_id'){
                            $value[$row_key] = $row_value;
                        }else if($row_key==='permissions' && gettype($row_value)==='array'){
                            $value['permission_id'] = array_map(function($permission){
                                if(array_key_exists('permission_id',$permission))
                                    return $permission['permission_id'];
                            },$row_value);
                        }
                    }
                    $values[] = $value;
                }
            }
        }
        $stmt_string = "";
        $stmt_array = [];
        foreach($values as $index => $value){
            if($stmt_string===""){
                $stmt_string .= "WHERE ";
            }else{
                $stmt_string .= "OR ";
            }
            $implode_string =
                implode(",",array_map(function($index,$permission){
                    return ":permission_id_{$index}_{$permission}";
                },array_fill(0, count($value['permission_id']), $index),array_keys($value['permission_id'])));
            $stmt_string .= "( module_id = :module_id_{$index} ". (empty($implode_string) ? ") " : "AND permission_id NOT IN ( {$implode_string} )) ");
            $stmt_array["module_id_{$index}"] = intval($value['module_id']);
            foreach($value['permission_id'] as $permission_index => $permission){
                $stmt_array["permission_id_{$index}_{$permission_index}"] = $permission;
            }
        }
        $sql = "DELETE FROM setting.module_permission
            {$stmt_string}
        ";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($stmt_array);
        if($stmt->execute($values)){
            $result = [
                'status' => 'success',
                'module_id' => $stmt->fetchColumn()
            ];
        } else {
            $result = [
                'status' => 'failed'
            ];
            return $result;

        }
        $stmt_string = "";
        $stmt_array = [];
        foreach($values as $index => $value){
            if($stmt_string!==""){
                $stmt_string .= ", ";
            }
            $stmt_string .=
                implode(",",array_map(function($index,$permission){
                    return "(:module_id_{$index}_{$permission},:permission_id_{$index}_{$permission})";
                },array_fill(0, count($value['permission_id']), $index),array_keys($value['permission_id'])));
            foreach($value['permission_id'] as $permission_index => $permission){
                $stmt_array["permission_id_{$index}_{$permission_index}"] = $permission;
                $stmt_array["module_id_{$index}_{$permission_index}"] = $value['module_id'];
            }
        }
        if(empty($stmt_string)){
            return $result;
        }
        $sql = "INSERT INTO setting.module_permission (module_id,permission_id)
            VALUES {$stmt_string}
            ON CONFLICT (module_id,permission_id)
            DO NOTHING
            RETURNING module_id,permission_id
        ";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($stmt_array);

        return $result;
    }

    public function getModulesPermissions($data){
        $sql = "SELECT module.id module_id, module.name module_name, STRING_AGG(permission.permission_name,',') permissions
            FROM setting.module
            LEFT JOIN setting.module_permission ON module.id = module_permission.module_id
            LEFT JOIN system.permission ON module_permission.permission_id = permission.permission_id
            GROUP BY module.id, module.name
            ORDER BY module.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getOwnPermissions($data){
        $values = [
            "user_id" => 0
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = $data[$key];
            }
        }
        $sql = "WITH permissions AS (
                SELECT DISTINCT permission.permission_id,
                    permission.permission_name, permission.permission_url, permission.permission_icon,
                    permission.permission_group_id, permission.permission_index,
                    permission_group.permission_group_name
                FROM setting.module_permission
                LEFT JOIN setting.module ON module.id = module_permission.module_id
                LEFT JOIN system.user_modal ON user_modal.module_id = module.id
                LEFT JOIN system.user ON \"user\".id = user_modal.uid
                LEFT JOIN system.permission ON module_permission.permission_id = permission.permission_id
                LEFT JOIN system.permission_group ON permission_group.permission_group_id = permission.permission_group_id
                WHERE \"user\".id = :user_id
                ORDER BY permission.permission_index
            )
            SELECT permissions.permission_group_id,permissions.permission_group_name,MIN(permissions.permission_index) permissions_index,
                    JSON_AGG(JSON_BUILD_OBJECT('permission_id',permissions.permission_id,
                    'permission_name',permissions.permission_name,'permission_url', permissions.permission_url,
                    'permission_icon',permissions.permission_icon, 'permission_index', permissions.permission_index)) permissions
            FROM permissions
            WHERE permissions.permission_group_id IS NOT NULL
            GROUP BY permissions.permission_group_id,permissions.permission_group_name
            UNION ALL(
                SELECT permissions.permission_group_id,permissions.permission_group_name,MIN(permissions.permission_index) permissions_index,
                    JSON_AGG(JSON_BUILD_OBJECT('permission_id',permissions.permission_id,
                    'permission_name',permissions.permission_name,'permission_url', permissions.permission_url,
                    'permission_icon',permissions.permission_icon, 'permission_index', permissions.permission_index)) permissions
                FROM permissions
                WHERE permissions.permission_group_id IS NULL
                GROUP BY permissions.permission_id,
                    permissions.permission_name, permissions.permission_url, permissions.permission_icon,
                    permissions.permission_group_id, permissions.permission_index,
                    permissions.permission_group_name
            )
            ORDER BY permissions_index
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key => $row) {
            foreach ($row as $row_key => $value) {
                if($this->isJson($value)){
                    $result[$key][$row_key] = json_decode($value,true);
                }
            }
        }
        return $result;
    }

    public function insertModule($data){
        $values = [
            "module_name" => ''
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = $data[$key];
            }
        }
        $color = ['-primary', '-secondary', '-success', '-danger',
        '-warning', '-info', '-light', '-dark'];
        $values['chat_id'] = 2320;
        $values['color'] = $color[array_rand($color)];
        $sql = "INSERT INTO setting.module
                (name, color, \"chatID\")
	            VALUES (:module_name, :color, :chat_id)
                RETURNING id;
        ";
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($values)){
            $result = [
                'status' => 'success',
                'module_id' => $stmt->fetchColumn()
            ];
        } else {
            $result = [
                'status' => 'failed'
            ];
        }
        return $result;
    }

    public function updateModule($data){
        $values = [
            "module_id" => 0,
            "module_name" => ''
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = $data[$key];
            }
        }
        $sql = "UPDATE setting.module
                SET name = :module_name
	            WHERE id = :module_id;
        ";
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($values)){
            $result = [
                'status' => 'success'
            ];
        } else {
            $result = [
                'status' => 'failed'
            ];
        }
        return $result;
    }

    public function deleteModule($data){
        $values = [
            "module_id" => 0
        ];

        $sql="INSERT INTO  system.user_modal(uid,module_id)
        SELECT  user_modal.uid , -1
        FROM system.user_modal
        LEFT JOIN (
            SELECT uid,COUNT(*) AS count
            FROM system.user_modal
            GROUP BY uid
        )as modulenum ON  modulenum.uid = user_modal.uid

        WHERE  modulenum.count = 1 AND user_modal.module_id = :module_id
        RETURNING uid,module_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':module_id', $data['module_id']);
        $stmt->execute();

        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = $data[$key];
            }
        }
        $sql = "DELETE FROM setting.module
                WHERE id = :module_id;
        ";
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($values)){
            $result = [
                'status' => 'success'
            ];
        } else {
            $result = [
                'status' => 'failed'
            ];
        }



        return $result;
    }

    public function event_log_insert($data)
    {
        $sql = "INSERT INTO setting.event_log(uri, method, arguments, get_query_params, get_parsed_body, e_mail, response, description)
                VALUES(:uri, :method, :arguments, :get_query_params, :get_parsed_body, :e_mail, :response, :description)";
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($data)){
            $result = [
                "status" => "success",
            ];
        }
        else{
            $result = [
                "status" => "failed",
            ];
        }
        return $result;
    }

    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    #region 人員關係表
    public function userRelationshipDataLoad($aRequest) {
        if (!array_key_exists("cQueryType", $aRequest)) { $this->SetError("query type not exist."); return; }
        $cQueryType = $aRequest["cQueryType"];
        $bIsAutoIndex = $cQueryType == "index";
        $bIsUserID = $cQueryType == "id";
        $bIsUserAccount = $cQueryType == "uid";
        $bIsUserName = $cQueryType == "name";
        if (!$bIsAutoIndex && !$bIsUserID && !$bIsUserAccount && !$bIsUserName) { $this->SetError("query type error."); return; }

        $cKeyword = $this->CheckRequestData("cKeyword", $aRequest, true, true); if ($this->bErrorOn) { return; }

        $iNowPage = array_key_exists("cur_page", $aRequest) ? $aRequest["cur_page"] : 1;
        if (!xInt::To($iNowPage)) { $this->SetError("cur_page format error."); return; }

        $iPageSize = array_key_exists("size", $aRequest) ? $aRequest["size"] : 20;
        if (!xInt::To($iPageSize)) { $this->SetError("size format error."); return; }

        $htSql = [ "cKeyword"=>$cKeyword ];
        $cSelect = <<<EOD
	        R."iAutoIndex",
	        R."iUserID", UL.uid AS "cAccount", UL.name AS "cName",
	        R."cRelationship",
	        R."iUserID_Relation2", UR.uid AS "cAccount_Relation2", UR.name AS "cName_Relation2"
EOD;
        $cFrom = <<<EOD
            system."user_TABLE_Relationship" AS R
	            INNER JOIN system.user AS UL ON UL.id = R."iUserID"
	            INNER JOIN system.user AS UR ON UR.id = R."iUserID_Relation2"
EOD;
        if ($bIsAutoIndex) { $cWhere = "R.\"iAutoIndex\" = :cKeyword"; }
        if ($bIsUserID) { $cWhere = "UL.id = :cKeyword OR UR.id = :cKeyword"; }
        if ($bIsUserAccount) { $cWhere = "UL.uid LIKE '%' || :cKeyword ||'%' OR UR.uid LIKE '%' || :cKeyword ||'%'"; }
        if ($bIsUserName) { $cWhere = "UL.name LIKE '%' || :cKeyword ||'%' OR UR.name LIKE '%' || :cKeyword ||'%'"; }
        $cOrderBy = "UL.uid";

        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "SELECT" . " COUNT(*) AS \"iRows\" " . " FROM " . $cFrom . " WHERE " . $cWhere;
        $dtCount = $this->oDbop->SelectSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $iRows = $dtCount[0]["iRows"];

        $cSql = "SELECT "
                . " ROW_NUMBER() OVER (" . " ORDER BY " . $cOrderBy . ") AS \"iOrder\", "
                . $cSelect
             . " FROM " . $cFrom . " WHERE " . $cWhere . " LIMIT " . ($iNowPage * $iPageSize);
        $cSql = "SELECT * FROM (" . $cSql . ") AS X WHERE X.\"iOrder\" > " . (($iNowPage - 1) * $iPageSize) . " LIMIT " . $iPageSize;
        $aRows = $this->oDbop->SelectSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $aData = ["iRows" => $iRows, "aRows" => $aRows];
        $this->SetOK(); return $aData;
    }
    public function userRelationshipDataSave($aRequest) {
        if (!array_key_exists('cSaveType', $aRequest)) { $this->SetError("save type not exist."); return; }
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";
        $bIsEdit = $cSaveType == "edit";
        $bIsDelete = $cSaveType == "delete";
        if (!$bIsNew && !$bIsEdit && !$bIsDelete) { $this->SetError("save type error."); return; }

        if (!$bIsNew) {
            $iRelationshipIndex = $this->CheckRequestData("iRelationshipIndex", $aRequest); if ($this->bErrorOn) { return; }
            $aRequest["iRelationshipIndex"] = $iRelationshipIndex;    //把轉型好的重新放進去
        }

        if ($bIsNew || $bIsEdit) {
            return $this->userRelationshipNewOrEdit($aRequest);
        } else {
            return $this->userRelationshipDelete($aRequest);
        }
    }
    private function userRelationshipNewOrEdit($aRequest) {
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";

        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = [];

        $cField = "iRelationshipIndex";
        if (isset($aRequest[$cField]) && !$bIsNew) { $htSql[$cField] = $aRequest[$cField]; }

        $cField = "iUserID";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "cRelationship";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "iUserID_Relation2";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        if ($bIsNew) {
            return $this -> userRelationshipNewOrEdit_New($htSql, $htLog);
        } else {
            return $this -> userRelationshipNewOrEdit_Edit($htSql, $htLog);
        }
    }
    private function userRelationshipNewOrEdit_New($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            INSERT INTO system."user_TABLE_Relationship" (
                "iUserID", "cRelationship", "iUserID_Relation2"
            ) VALUES (
                :iUserID, :cRelationship, :iUserID_Relation2
            )
            RETURNING "iAutoIndex";
EOD;
        $aRows = $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog($htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return $aRows;
    }
    private function userRelationshipNewOrEdit_Edit($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "UPDATE system.\"user_TABLE_Relationship\" SET ";

        $cList = "";
        if (array_key_exists("iUserID", $htSql)) { $cList .= ",\"iUserID\"=:iUserID"; }
        if (array_key_exists("cRelationship", $htSql)) { $cList .= ",\"cRelationship\"=:cRelationship"; }
        if (array_key_exists("iUserID_Relation2", $htSql)) { $cList .= ",\"iUserID_Relation2\"=:iUserID_Relation2"; }
        if (xString::IsEmpty($cList)) { $this->SetError("沒有傳入要修改的資料."); return; }
        $cSql .= substr($cList, 1);

        $cSql .= " WHERE \"iAutoIndex\"=:iRelationshipIndex";

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog($htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function userRelationshipDelete($aRequest) {
        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["iRelationshipIndex" => $aRequest["iRelationshipIndex"]];
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            DELETE FROM system."user_TABLE_Relationship" WHERE "iAutoIndex" = :iRelationshipIndex
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog($htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $this->SetOK(); return "";
    }

    private function newLog($htLog) {
        $cSql = <<<EOD
            INSERT INTO system."user_LOG_Relationship" (
                "iOperator", "cMemo", "dTime"
            ) VALUES (
                :iOperator, :cLog, :dNow
            );
EOD;
        $this->oDbop->RunSql($cSql, $htLog); if ($this->oDbop->bErrorOn) { return; }
    }
    #endregion

}
