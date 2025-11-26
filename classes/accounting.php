<?php

use nknu\base\xBaseWithDbop;
use nknu\extend\xBool as xBool;
use nknu\extend\xInt as xInt;
use nknu\extend\xString as xString;
use nknu\utility\xStatic;

class Accounting extends xBaseWithDbop {
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

    #region 會計科目
    public function subjectDataLoad($aRequest) {
        if (!array_key_exists('cQueryType', $aRequest)) { $this->SetError("query type not exist."); return; }
        $cQueryType = $aRequest["cQueryType"];
        $bIsSubjectID = $cQueryType == "id";
        $bIsSubjectName = $cQueryType == "name";
        if (!$bIsSubjectID && !$bIsSubjectName) { $this->SetError("query type error."); return; }

        $cKeyword = $this->CheckRequestData("cKeyword", $aRequest, true, true); if ($this->bErrorOn) { return; }

        $iNowPage = array_key_exists("cur_page", $aRequest) ? $aRequest["cur_page"] : 1;
        if (!xInt::To($iNowPage)) { $this->SetError("cur_page format error."); return; }

        $iPageSize = array_key_exists("size", $aRequest) ? $aRequest["size"] : 20;
        if (!xInt::To($iPageSize)) { $this->SetError("size format error."); return; }

        $htSql = [ 'cKeyword'=>$cKeyword ];
        $cSelect = "
            CAST(CASE WHEN Lv2.\"cID\" = :cKeyword OR Lv2.\"cName\" = :cKeyword THEN 1 ELSE 0 END AS bit) AS \"bIsMatch\",
            Lv0.\"cID\" AS \"cLv0_ID\", Lv0.\"cName\" AS \"cLv0_Name\",
            Lv1.\"cID\" AS \"cLv1_ID\", Lv1.\"cName\" AS \"cLv1_Name\",
	        Lv2.\"cID\" AS \"cLv2_ID\", Lv2.\"cName\" AS \"cLv2_Name\", Lv2.\"cName_English\",
            Lv2.\"cID\" AS \"cBelongSubject_ID\", Lv2.\"cName\" AS \"cBelongSubject_Name\",
            Lv2.\"iAttribute\" AS \"iAttribute_ID\", tA.\"cName\" AS \"cAttribute_Name\",
            Lv2.\"iKind\", Lv2.\"bSumMode\",
            Lv2.\"cCurrencyID\", tC.\"cName\" AS \"cCurrencyName\",
            Lv2.\"cMemo\"
        ";
        $cFrom = "
            accounting.\"TABLE_Subject_Lv2\" AS Lv2
                INNER JOIN accounting.\"TABLE_Subject_Lv1\" AS Lv1 ON Lv1.\"cID\" = Lv2.\"cLv1_ID\"
                INNER JOIN accounting.\"TABLE_Subject_Lv0\" AS Lv0 ON Lv0.\"cID\" = Lv1.\"cLv0_ID\"
                INNER JOIN accounting.\"TYPE_Currency\" AS tC ON tC.\"cID\" = Lv2.\"cCurrencyID\"
                INNER JOIN accounting.\"TYPE_Attribute\" AS tA ON tA.\"iID\" = Lv2.\"iAttribute\"
                LEFT JOIN accounting.\"TABLE_Subject_Lv2\" AS BL ON BL.\"cID\" = LEFT(Lv2.\"cID\", 4)
        ";
        if ($bIsSubjectID) { $cWhere = ":cKeyword = '' OR Lv2.\"cID\" LIKE :cKeyword || '%' OR BL.\"cID\" = LEFT(:cKeyword, 4)"; }
        if ($bIsSubjectName) { $cWhere = ":cKeyword = '' OR Lv2.\"cName\" LIKE '%' || :cKeyword ||'%'"; }
        $cOrderBy = "Lv2.\"cID\"";

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
    public function subjectDataSave($aRequest) {
        if (!array_key_exists('cSaveType', $aRequest)) { $this->SetError("save type not exist."); return; }
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";
        $bIsEdit = $cSaveType == "edit";
        $bIsDelete = $cSaveType == "delete";
        if (!$bIsNew && !$bIsEdit && !$bIsDelete) { $this->SetError("save type error."); return; }

        if (!array_key_exists("cLv2_ID", $aRequest)) { $this->SetError("cLv2_ID not set."); return; }
        $cLv2_ID = $aRequest["cLv2_ID"]; if (xString::IsNullOrEmpty($cLv2_ID)) { $this->SetError("cLv2_ID not set."); return; }

        if ($bIsNew || $bIsEdit) {
            return $this->subjectNewOrEdit($aRequest);
        } else {
            return $this->subjectDelete($aRequest);
        }
    }
    private function subjectNewOrEdit($aRequest) {
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";

        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["cLv2_ID" => $aRequest["cLv2_ID"]];

        $cLv2_Name = array_key_exists("cLv2_Name", $aRequest) ? $aRequest["cLv2_Name"] : null;
        if ($bIsNew && xString::IsNullOrEmpty($cLv2_Name)) { $this->SetError("cLv2_Name not set or empty."); return; }
        if (!xString::IsNull($cLv2_Name)) { $htSql["cLv2_Name"] = $cLv2_Name; }

        $cLv2_Name_English = array_key_exists("cLv2_Name_English", $aRequest) ? $aRequest["cLv2_Name_English"] : null;
        if ($bIsNew && xString::IsNull($cLv2_Name_English)) { $this->SetError("cLv2_Name_English not set."); return; }
        if (!xString::IsNull($cLv2_Name_English)) { $htSql["cLv2_Name_English"] = $cLv2_Name_English; }

        $iAttribute = $this->CheckRequestData("iAttribute", $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (!xInt::IsNull($iAttribute)) {
            if ($iAttribute < 0 || $iAttribute > 3) { $this->SetError("iAttribute must in 0 ~ 3."); return; }
            $htSql["iAttribute"] = $iAttribute;
        }

        $bSumMode = $this->CheckRequestData("bSumMode", $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (!xBool::IsNull($bSumMode)) { $htSql["bSumMode"] = [ "oValue" => $bSumMode ? "1" : "0", "iType"=>\PDO::PARAM_BOOL ]; }

        $cCurrencyID = array_key_exists("cCurrencyID", $aRequest) ? $aRequest["cCurrencyID"] : null;
        if ($bIsNew && xString::IsNullOrEmpty($cCurrencyID)) { $this->SetError("cCurrencyID not set or empty."); return; }
        if (!xString::IsNull($cCurrencyID)) { $htSql["cCurrencyID"] = $cCurrencyID; }

        $cMemo = array_key_exists("cMemo", $aRequest) ? $aRequest["cMemo"] : null;
        if ($bIsNew && xString::IsNull($cMemo)) { $cMemo = ""; }
        if (!xString::IsNull($cMemo)) { $htSql["cMemo"] = $cMemo; }

        if (!$bIsNew) {
            $cLv2_ID_New = array_key_exists("cLv2_ID_New", $aRequest) ? $aRequest["cLv2_ID_New"] : null;
            if (!xString::IsNull($cLv2_ID_New) && xString::IsEmpty($cLv2_ID_New)) { $this->SetError("cLv2_ID_New not allow empty."); return; }
            if (!xString::IsNull($cLv2_ID_New)) { $htSql["cLv2_ID_New"] = $cLv2_ID_New; }
        }

        #region 新增或修改原 ID 時
        $cLv2_ID = null;
        if ($bIsNew) {
            $cLv2_ID = $htSql["cLv2_ID"];
        } else if (!xString::IsNull($cLv2_ID_New)) {
            $cLv2_ID = $cLv2_ID_New;
        }
        if (!xString::IsNull($cLv2_ID)) {
            $htSql["cLv1_ID"] = substr($cLv2_ID, 0, 2);
            $htSql["cBelongID"] = substr($cLv2_ID, 0, 4);
            $htSql["iKind"] = substr($cLv2_ID, 0, 1) == "1" ? 0 : 3;
        }
        #endregion

        if ($bIsNew) {
            return $this -> subjectNewOrEdit_New($htSql, $htLog);
        } else {
            return $this -> subjectNewOrEdit_Edit($htSql, $htLog);
        }
    }
    private function subjectNewOrEdit_New($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            INSERT INTO accounting."TABLE_Subject_Lv2" (
                "cID", "cLv1_ID", "cBelongID", "cName", "cName_English", "iAttribute", "iKind", "bSumMode", "cCurrencyID", "cMemo"
            ) VALUES (
                :cLv2_ID, :cLv1_ID, :cBelongID, :cLv2_Name, :cLv2_Name_English, :iAttribute, :iKind, :bSumMode, :cCurrencyID, :cMemo
            );
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function subjectNewOrEdit_Edit($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "UPDATE accounting.\"TABLE_Subject_Lv2\" SET ";
        //"cID", "cLv1_ID", "cBelongID", "cName", "cName_English", "iAttribute", "iKind", "bSumMode", "cCurrencyID", "cMemo"
        $cLv2_ID_New = null;

        $cList = "";
        if (array_key_exists("cLv2_ID_New", $htSql)) { $cList .= ",\"cID\"=:cLv2_ID_New"; }
        if (array_key_exists("cLv1_ID", $htSql)) { $cList .= ",\"cLv1_ID\"=:cLv1_ID"; }
        if (array_key_exists("cBelongID", $htSql)) { $cList .= ",\"cBelongID\"=:cBelongID"; }
        if (array_key_exists("cLv2_Name", $htSql)) { $cList .= ",\"cName\"=:cLv2_Name"; }
        if (array_key_exists("cLv2_Name_English", $htSql)) { $cList .= ",\"cName_English\"=:cLv2_Name_English"; }
        if (array_key_exists("iAttribute", $htSql)) { $cList .= ",\"iAttribute\"=:iAttribute"; }
        if (array_key_exists("iKind", $htSql)) { $cList .= ",\"iKind\"=:iKind"; }
        if (array_key_exists("bSumMode", $htSql)) { $cList .= ",\"bSumMode\"=:bSumMode"; }
        if (array_key_exists("cCurrencyID", $htSql)) { $cList .= ",\"cCurrencyID\"=:cCurrencyID"; }
        if (array_key_exists("cMemo", $htSql)) { $cList .= ",\"cMemo\"=:cMemo"; }
        if (xString::IsEmpty($cList)) { $this->SetError("沒有傳入要修改的資料."); return; }
        $cSql .= substr($cList, 1);

        $cSql .= " WHERE \"cID\"=:cLv2_ID";

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function subjectDelete($aRequest) {
        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["cLv2_ID" => $aRequest["cLv2_ID"]];
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            DELETE FROM accounting."TABLE_Subject_Lv2" WHERE "cID" = :cLv2_ID
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $this->SetOK(); return "";
    }
    #endregion

    #region 總類項目
    public function groupDataLoad($aRequest) {
        if (!array_key_exists('cQueryType', $aRequest)) { $this->SetError("query type not exist."); return; }
        $cQueryType = $aRequest["cQueryType"];
        $bIsGroupID = $cQueryType == "id";
        $bIsGroupName = $cQueryType == "name";
        if (!$bIsGroupID && !$bIsGroupName) { $this->SetError("query type error."); return; }

        $cKeyword = $this->CheckRequestData("cKeyword", $aRequest, true, true); if ($this->bErrorOn) { return; }

        $iNowPage = array_key_exists("cur_page", $aRequest) ? $aRequest["cur_page"] : 1;
        if (!xInt::To($iNowPage)) { $this->SetError("cur_page format error."); return; }

        $iPageSize = array_key_exists("size", $aRequest) ? $aRequest["size"] : 20;
        if (!xInt::To($iPageSize)) { $this->SetError("size format error."); return; }

        $htSql = [ 'cKeyword'=>$cKeyword ];
        $cSelect = "
            CAST(CASE WHEN Lv0.\"cID\" = :cKeyword OR Lv0.\"cName\" = :cKeyword THEN 1 ELSE 0 END AS bit) AS \"bIsMatch\",
            Lv0.\"cID\" AS \"cLv0_ID\", Lv0.\"cName\" AS \"cLv0_Name\", Lv0.\"cName_English\",
            Lv0.\"cMemo\"
        ";
        $cFrom = "
            accounting.\"TABLE_Subject_Lv0\" AS Lv0
        ";
        if ($bIsGroupID) { $cWhere = ":cKeyword = '' OR Lv0.\"cID\" = :cKeyword"; }
        if ($bIsGroupName) { $cWhere = ":cKeyword = '' OR Lv0.\"cName\" LIKE '%' || :cKeyword ||'%'"; }
        $cOrderBy = "Lv0.\"cID\"";

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
    public function groupDataSave($aRequest) {
        if (!array_key_exists('cSaveType', $aRequest)) { $this->SetError("save type not exist."); return; }
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";
        $bIsEdit = $cSaveType == "edit";
        $bIsDelete = $cSaveType == "delete";
        if (!$bIsNew && !$bIsEdit && !$bIsDelete) { $this->SetError("save type error."); return; }

        if (!array_key_exists("cLv0_ID", $aRequest)) { $this->SetError("cLv0_ID not set."); return; }
        $cLv0_ID = $aRequest["cLv0_ID"]; if (xString::IsNullOrEmpty($cLv0_ID)) { $this->SetError("cLv0_ID not set."); return; }

        if ($bIsNew || $bIsEdit) {
            return $this->groupNewOrEdit($aRequest);
        } else {
            return $this->groupDelete($aRequest);
        }
    }
    private function groupNewOrEdit($aRequest) {
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";

        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["cLv0_ID" => $aRequest["cLv0_ID"]];

        $cLv0_Name = array_key_exists("cLv0_Name", $aRequest) ? $aRequest["cLv0_Name"] : null;
        if ($bIsNew && xString::IsNullOrEmpty($cLv0_Name)) { $this->SetError("cLv0_Name not set or empty."); return; }
        if (!xString::IsNull($cLv0_Name)) { $htSql["cLv0_Name"] = $cLv0_Name; }

        $cLv0_Name_English = array_key_exists("cLv0_Name_English", $aRequest) ? $aRequest["cLv0_Name_English"] : null;
        if ($bIsNew && xString::IsNull($cLv0_Name_English)) { $this->SetError("cLv0_Name_English not set."); return; }
        if (!xString::IsNull($cLv0_Name_English)) { $htSql["cLv0_Name_English"] = $cLv0_Name_English; }

        $cMemo = array_key_exists("cMemo", $aRequest) ? $aRequest["cMemo"] : null;
        if ($bIsNew && xString::IsNull($cMemo)) { $cMemo = ""; }
        if (!xString::IsNull($cMemo)) { $htSql["cMemo"] = $cMemo; }

        if (!$bIsNew) {
            $cLv0_ID_New = array_key_exists("cLv0_ID_New", $aRequest) ? $aRequest["cLv0_ID_New"] : null;
            if (!xString::IsNull($cLv0_ID_New) && xString::IsEmpty($cLv0_ID_New)) { $this->SetError("cLv0_ID_New not allow empty."); return; }
            if (!xString::IsNull($cLv0_ID_New)) { $htSql["cLv0_ID_New"] = $cLv0_ID_New; }
        }

        if ($bIsNew) {
            return $this -> groupNewOrEdit_New($htSql, $htLog);
        } else {
            return $this -> groupNewOrEdit_Edit($htSql, $htLog);
        }
    }
    private function groupNewOrEdit_New($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            INSERT INTO accounting."TABLE_Subject_Lv0" (
                "cID", "cName", "cName_English", "cMemo"
            ) VALUES (
                :cLv0_ID, :cLv0_Name, :cLv0_Name_English, :cMemo
            );
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function groupNewOrEdit_Edit($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "UPDATE accounting.\"TABLE_Subject_Lv0\" SET ";
        //"cID", "cName", "cName_English", "cMemo"
        $cLv0_ID_New = null;

        $cList = "";
        if (array_key_exists("cLv0_ID_New", $htSql)) { $cList .= ",\"cID\"=:cLv0_ID_New"; }
        if (array_key_exists("cLv0_Name", $htSql)) { $cList .= ",\"cName\"=:cLv0_Name"; }
        if (array_key_exists("cLv0_Name_English", $htSql)) { $cList .= ",\"cName_English\"=:cLv0_Name_English"; }
        if (array_key_exists("cMemo", $htSql)) { $cList .= ",\"cMemo\"=:cMemo"; }
        if (xString::IsEmpty($cList)) { $this->SetError("沒有傳入要修改的資料."); return; }
        $cSql .= substr($cList, 1);

        $cSql .= " WHERE \"cID\"=:cLv0_ID";

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function groupDelete($aRequest) {
        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["cLv0_ID" => $aRequest["cLv0_ID"]];
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            DELETE FROM accounting."TABLE_Subject_Lv0" WHERE "cID" = :cLv0_ID
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $this->SetOK(); return "";
    }

    #endregion

    #region 次類項目
    public function kindDataLoad($aRequest) {
        if (!array_key_exists('cQueryType', $aRequest)) { $this->SetError("query type not exist."); return; }
        $cQueryType = $aRequest["cQueryType"];
        $bIsKindID = $cQueryType == "id";
        $bIsKindName = $cQueryType == "name";
        if (!$bIsKindID && !$bIsKindName) { $this->SetError("query type error."); return; }

        $cKeyword = $this->CheckRequestData("cKeyword", $aRequest, true, true); if ($this->bErrorOn) { return; }

        $iNowPage = array_key_exists("cur_page", $aRequest) ? $aRequest["cur_page"] : 1;
        if (!xInt::To($iNowPage)) { $this->SetError("cur_page format error."); return; }

        $iPageSize = array_key_exists("size", $aRequest) ? $aRequest["size"] : 20;
        if (!xInt::To($iPageSize)) { $this->SetError("size format error."); return; }

        $htSql = [ 'cKeyword'=>$cKeyword ];
        $cSelect = "
            CAST(CASE WHEN Lv1.\"cID\" = :cKeyword OR Lv1.\"cName\" = :cKeyword THEN 1 ELSE 0 END AS bit) AS \"bIsMatch\",
            Lv0.\"cID\" AS \"cLv0_ID\", Lv0.\"cName\" AS \"cLv0_Name\",
            Lv1.\"cID\" AS \"cLv1_ID\", Lv1.\"cName\" AS \"cLv1_Name\", Lv1.\"cName_English\",
            Lv1.\"cMemo\"
        ";
        $cFrom = "
            accounting.\"TABLE_Subject_Lv1\" AS Lv1
                INNER JOIN accounting.\"TABLE_Subject_Lv0\" AS Lv0 ON Lv0.\"cID\" = Lv1.\"cLv0_ID\"
        ";
        if ($bIsKindID) { $cWhere = ":cKeyword = '' OR Lv1.\"cID\" LIKE :cKeyword ||'%'"; }
        if ($bIsKindName) { $cWhere = ":cKeyword = '' OR Lv1.\"cName\" LIKE '%' || :cKeyword ||'%'"; }
        $cOrderBy = "Lv1.\"cID\"";

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
    public function kindDataSave($aRequest) {
        if (!array_key_exists('cSaveType', $aRequest)) { $this->SetError("save type not exist."); return; }
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";
        $bIsEdit = $cSaveType == "edit";
        $bIsDelete = $cSaveType == "delete";
        if (!$bIsNew && !$bIsEdit && !$bIsDelete) { $this->SetError("save type error."); return; }

        if (!array_key_exists("cLv1_ID", $aRequest)) { $this->SetError("cLv1_ID not set."); return; }
        $cLv1_ID = $aRequest["cLv1_ID"]; if (xString::IsNullOrEmpty($cLv1_ID)) { $this->SetError("cLv1_ID not set."); return; }

        if ($bIsNew || $bIsEdit) {
            return $this->kindNewOrEdit($aRequest);
        } else {
            return $this->kindDelete($aRequest);
        }
    }
    private function kindNewOrEdit($aRequest) {
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";

        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["cLv1_ID" => $aRequest["cLv1_ID"]];

        $cLv1_Name = array_key_exists("cLv1_Name", $aRequest) ? $aRequest["cLv1_Name"] : null;
        if ($bIsNew && xString::IsNullOrEmpty($cLv1_Name)) { $this->SetError("cLv1_Name not set or empty."); return; }
        if (!xString::IsNull($cLv1_Name)) { $htSql["cLv1_Name"] = $cLv1_Name; }

        $cLv1_Name_English = array_key_exists("cLv1_Name_English", $aRequest) ? $aRequest["cLv1_Name_English"] : null;
        if ($bIsNew && xString::IsNull($cLv1_Name_English)) { $this->SetError("cLv1_Name_English not set."); return; }
        if (!xString::IsNull($cLv1_Name_English)) { $htSql["cLv1_Name_English"] = $cLv1_Name_English; }

        $cMemo = array_key_exists("cMemo", $aRequest) ? $aRequest["cMemo"] : null;
        if ($bIsNew && xString::IsNull($cMemo)) { $cMemo = ""; }
        if (!xString::IsNull($cMemo)) { $htSql["cMemo"] = $cMemo; }

        if (!$bIsNew) {
            $cLv1_ID_New = array_key_exists("cLv1_ID_New", $aRequest) ? $aRequest["cLv1_ID_New"] : null;
            if (!xString::IsNull($cLv1_ID_New) && xString::IsEmpty($cLv1_ID_New)) { $this->SetError("cLv1_ID_New not allow empty."); return; }
            if (!xString::IsNull($cLv1_ID_New)) { $htSql["cLv1_ID_New"] = $cLv1_ID_New; }
        }

        #region 新增或修改原 ID 時
        $cLv1_ID = null;
        if ($bIsNew) {
            $cLv1_ID = $htSql["cLv1_ID"];
        } else if (!xString::IsNull($cLv1_ID_New)) {
            $cLv1_ID = $cLv1_ID_New;
        }
        if (!xString::IsNull($cLv1_ID)) {
            $htSql["cLv0_ID"] = substr($cLv1_ID, 0, 1);
        }
        #endregion

        if ($bIsNew) {
            return $this -> kindNewOrEdit_New($htSql, $htLog);
        } else {
            return $this -> kindNewOrEdit_Edit($htSql, $htLog);
        }
    }
    private function kindNewOrEdit_New($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            INSERT INTO accounting."TABLE_Subject_Lv1" (
                "cID", "cLv0_ID", "cName", "cName_English", "cMemo"
            ) VALUES (
                :cLv1_ID, :cLv0_ID, :cLv1_Name, :cLv1_Name_English, :cMemo
            );
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function kindNewOrEdit_Edit($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "UPDATE accounting.\"TABLE_Subject_Lv1\" SET ";
        //"cID", "cLv0_ID", "cName", "cName_English", "cMemo"
        $cLv1_ID_New = null;

        $cList = "";
        if (array_key_exists("cLv1_ID_New", $htSql)) { $cList .= ",\"cID\"=:cLv1_ID_New"; }
        if (array_key_exists("cLv0_ID", $htSql)) { $cList .= ",\"cLv0_ID\"=:cLv0_ID"; }
        if (array_key_exists("cLv1_Name", $htSql)) { $cList .= ",\"cName\"=:cLv1_Name"; }
        if (array_key_exists("cLv1_Name_English", $htSql)) { $cList .= ",\"cName_English\"=:cLv1_Name_English"; }
        if (array_key_exists("cMemo", $htSql)) { $cList .= ",\"cMemo\"=:cMemo"; }
        if (xString::IsEmpty($cList)) { $this->SetError("沒有傳入要修改的資料."); return; }
        $cSql .= substr($cList, 1);

        $cSql .= " WHERE \"cID\"=:cLv1_ID";

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function kindDelete($aRequest) {
        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["cLv1_ID" => $aRequest["cLv1_ID"]];
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            DELETE FROM accounting."TABLE_Subject_Lv1" WHERE "cID" = :cLv1_ID
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Subject", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $this->SetOK(); return "";
    }

    #endregion

    #region 類別項目
    public function typeDataLoad($aRequest) {
        if (!array_key_exists("cTableName", $aRequest)) { $this->SetError("table name not exist."); return; }
        $cTableName = $aRequest["cTableName"];

        $bIsAllowTable = $cTableName == "Currency";
        $bIsAllowTable = $bIsAllowTable || $cTableName == "Attribute";
        $bIsAllowTable = $bIsAllowTable || $cTableName == "Voucher";
        if (!$bIsAllowTable) { $this->SetError("table name error."); return; }
        $htSql = [];
        $cSql = "SELECT * FROM accounting.\"TYPE_" . $cTableName ."\" ORDER BY \"iOrder\"";

        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $aRows = $this->oDbop->SelectSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return $aRows;
    }
    #endregion

    #region 傳票
    #region 傳票 -- Main
    public function voucherMainDataLoad($aRequest) {
        if (!array_key_exists("cQueryType", $aRequest)) { $this->SetError("query type not exist."); return; }
        $cQueryType = $aRequest["cQueryType"];
        $bIsBillNumber = $cQueryType == "billNumber";
        $bIsBillDate = $cQueryType == "billDate";
        if (!$bIsBillDate && !$bIsBillNumber) { $this->SetError("query type error."); return; }

        $htSql = [];
        if ($bIsBillNumber) {
            $cKeyword = $this->CheckRequestData("cKeyword", $aRequest); if ($this->bErrorOn) { return; }
            $htSql["cKeyword"] = $cKeyword;
        }
        if ($bIsBillDate) {
            $dStart = $this->CheckRequestData("dStart", $aRequest); if ($this->bErrorOn) { return; }
            $dEnd = $this->CheckRequestData("dEnd", $aRequest, true); if ($this->bErrorOn) { return; }
            $htSql["dStart"] = $dStart->format("Y-m-d"); $htSql["dEnd"] = $dEnd->format("Y-m-d");
        }

        $iNowPage = array_key_exists("cur_page", $aRequest) ? $aRequest["cur_page"] : 1;
        if (!xInt::To($iNowPage)) { $this->SetError("cur_page format error."); return; }

        $iPageSize = array_key_exists("size", $aRequest) ? $aRequest["size"] : 20;
        if (!xInt::To($iPageSize)) { $this->SetError("size format error."); return; }

        $cSelect = <<<EOD
            M."iBillNumber", M."cBillNumber",
            M."dBillDate",
            M."iKind" AS "iKind_ID", tV."cName" AS "cKind_Name",
            M."iRecords_Debit", M."fTotal_Debit",
            M."iRecords_Credit", M."fTotal_Credit",
            M."iOperator", UO.uid AS "cOperator_Account", UO.name AS "cOperator_Name",
            M."iChecker", UC.uid AS "cChecker_Account", UC.name AS "cChecker_Name",
            M."cExplain",
            M."fTotal_Debit_NTD", M."fTotal_Credit_NTD"
EOD;
        $cFrom = <<<EOD
            accounting."TABLE_Voucher_Main" AS M
                INNER JOIN accounting."TYPE_Voucher" AS tV ON tV."iID" = M."iKind"
	            INNER JOIN system.user AS UO ON UO.id = M."iOperator"
	            LEFT JOIN system.user AS UC ON UC.id = M."iChecker"
EOD;
        if ($bIsBillNumber) { $cWhere = " M.\"cBillNumber\" LIKE '%' || :cKeyword ||'%'"; }
        if ($bIsBillDate) { $cWhere = " M.\"dBillDate\" BETWEEN :dStart AND :dEnd"; }
        $cOrderBy = "M.\"iBillNumber\"";

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
    public function voucherMainDataSave($aRequest) {
        if (!array_key_exists('cSaveType', $aRequest)) { $this->SetError("save type not exist."); return; }
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";
        $bIsEdit = $cSaveType == "edit";
        $bIsDelete = $cSaveType == "delete";
        if (!$bIsNew && !$bIsEdit && !$bIsDelete) { $this->SetError("save type error."); return; }

        $cBillNumber = $this->CheckRequestData("cBillNumber", $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (!$bIsNew) {
            $iBillNumber = $this->CheckRequestData("iBillNumber", $aRequest); if ($this->bErrorOn) { return; }
            $aRequest["iBillNumber"] = $iBillNumber;    //把轉型好的重新放進去
        }

        if ($bIsNew || $bIsEdit) {
            return $this->voucherMainNewOrEdit($aRequest);
        } else {
            return $this->voucherMainDelete($aRequest);
        }
    }
    private function voucherMainNewOrEdit($aRequest) {
        $cSaveType = $aRequest["cSaveType"]; unset($aRequest["cSaveType"]);
        $bIsNew = $cSaveType == "new";

        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["iOperator" => $iOperator];

        $cField = "cBillNumber";
        if (isset($aRequest[$cField])) { $htSql[$cField] = $aRequest[$cField]; }
        $cField = "iBillNumber";
        if (isset($aRequest[$cField]) && !$bIsNew) { $htSql[$cField] = $aRequest[$cField]; }

        $cField = "dBillDate";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData->format("Y-m-d"); }

        $cField = "iKind";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "iChecker";
        $oData = $this->CheckRequestData($cField, $aRequest, false, true); if ($this->bErrorOn) { return; }
        if (!isset($oData) && $bIsNew) { $htSql[$cField] = null; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "cExplain";
        $oData = $this->CheckRequestData($cField, $aRequest, false, true); if ($this->bErrorOn) { return; }
        if (!isset($oData) && $bIsNew) { $oData = ""; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        if (!$bIsNew) {
            $cField = "iBillNumber_New";
            $oData = $this->CheckRequestData($cField, $aRequest, false); if ($this->bErrorOn) { return; }
            if (isset($oData)) { $htSql[$cField] = $oData; }
        }

        if ($bIsNew) {
            return $this -> voucherMainNewOrEdit_New($htSql, $htLog);
        } else {
            return $this -> voucherMainNewOrEdit_Edit($htSql, $htLog);
        }
    }
    private function voucherMainNewOrEdit_New($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            INSERT INTO accounting."TABLE_Voucher_Main" (
                "cBillNumber", "dBillDate", "iKind",
                "iRecords_Debit", "fTotal_Debit", "iRecords_Credit", "fTotal_Credit",
                "iOperator", "iChecker",
                "cExplain",
                "fTotal_Debit_NTD", "fTotal_Credit_NTD",
                "iBillNumber"
            )
            SELECT
                :cBillNumber, :dBillDate, :iKind,
                0, 0, 0, 0,
                :iOperator, :iChecker,
                :cExplain,
                0, 0,
                coalesce (
			        (SELECT MAX("iBillNumber") FROM accounting."TABLE_Voucher_Main" WHERE "dBillDate" = :dBillDate),
			        (CAST(TO_CHAR(:dBillDate, 'yyyymmdd000') as bigint))
		        ) + 1
            ;
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Voucher", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function voucherMainNewOrEdit_Edit($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "UPDATE accounting.\"TABLE_Voucher_Main\" SET ";

        $cList = "";
        if (array_key_exists("iBillNumber", $htSql)) { $cList .= ",\"iBillNumber\"=:iBillNumber"; }
        if (array_key_exists("cBillNumber", $htSql)) { $cList .= ",\"cBillNumber\"=:cBillNumber"; }
        if (array_key_exists("dBillDate", $htSql)) { $cList .= ",\"dBillDate\"=:dBillDate"; }
        if (array_key_exists("iKind", $htSql)) { $cList .= ",\"iKind\"=:iKind"; }

        if (array_key_exists("iRecords_Debit", $htSql)) { $cList .= ",\"iRecords_Debit\"=:iRecords_Debit"; }
        if (array_key_exists("fTotal_Debit", $htSql)) { $cList .= ",\"fTotal_Debit\"=:fTotal_Debit"; }
        if (array_key_exists("iRecords_Credit", $htSql)) { $cList .= ",\"iRecords_Credit\"=:iRecords_Credit"; }
        if (array_key_exists("fTotal_Credit", $htSql)) { $cList .= ",\"fTotal_Credit\"=:fTotal_Credit"; }

        if (array_key_exists("iOperator", $htSql)) { $cList .= ",\"iOperator\"=:iOperator"; }
        if (array_key_exists("iChecker", $htSql)) { $cList .= ",\"iChecker\"=:iChecker"; }
        if (array_key_exists("cExplain", $htSql)) { $cList .= ",\"cExplain\"=:cExplain"; }

        if (array_key_exists("fTotal_Debit_NTD", $htSql)) { $cList .= ",\"fTotal_Debit_NTD\"=:fTotal_Debit_NTD"; }
        if (array_key_exists("fTotal_Credit_NTD", $htSql)) { $cList .= ",\"fTotal_Credit_NTD\"=:fTotal_Credit_NTD"; }

        if (xString::IsEmpty($cList)) { $this->SetError("沒有傳入要修改的資料."); return; }
        $cSql .= substr($cList, 1);

        $cSql .= " WHERE \"iBillNumber\"=:iBillNumber";

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Voucher", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function voucherMainDelete($aRequest) {
        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["iBillNumber" => $aRequest["iBillNumber"]];
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            DELETE FROM accounting."TABLE_Voucher_Main" WHERE "iBillNumber" = :iBillNumber
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Voucher", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $this->SetOK(); return "";
    }
    #endregion
    #region 傳票 -- Detail
    public function voucherDetailDataLoad($aRequest) {
        if (!array_key_exists("cQueryType", $aRequest)) { $this->SetError("query type not exist."); return; }
        $cQueryType = $aRequest["cQueryType"];
        $bIsBillNumber = $cQueryType == "billNumber";
        $bIsBillDate = $cQueryType == "billDate";
        if (!$bIsBillDate && !$bIsBillNumber) { $this->SetError("query type error."); return; }

        $htSql = [];
        if ($bIsBillNumber) {
            $cKeyword = $this->CheckRequestData("cKeyword", $aRequest); if ($this->bErrorOn) { return; }
            $htSql["cKeyword"] = $cKeyword;
        }
        if ($bIsBillDate) {
            $dStart = $this->CheckRequestData("dStart", $aRequest); if ($this->bErrorOn) { return; }
            $dEnd = $this->CheckRequestData("dEnd", $aRequest, true); if ($this->bErrorOn) { return; }
            $htSql["dStart"] = $dStart->format("Y-m-d"); $htSql["dEnd"] = $dEnd->format("Y-m-d");
        }

        $iNowPage = array_key_exists("cur_page", $aRequest) ? $aRequest["cur_page"] : 1;
        if (!xInt::To($iNowPage)) { $this->SetError("cur_page format error."); return; }

        $iPageSize = array_key_exists("size", $aRequest) ? $aRequest["size"] : 20;
        if (!xInt::To($iPageSize)) { $this->SetError("size format error."); return; }

        $cSelect = <<<EOD
            VD."iBillNumber", VD."iRecordNumber",
            VD."cWayID", tW."cName" AS "cWayName",
            VD."cSubjectID", Lv2."cName" AS "cSubjectName",
            VD."cExplain",
            VD."fTotal",
            VD."cWashFlag", VD."iWashRecordNumber",
            VD."cDepartmentID",
            VD."fExrate",
            VD."cCurrencyID", tC."cName" AS "cCurrencyName",
            VD."fTotal_NTD"
EOD;
        $cFrom = <<<EOD
            accounting."TABLE_Voucher_Detail" AS VD
	    INNER JOIN accounting."TABLE_Voucher_Main" AS VM ON VM."iBillNumber" = VD."iBillNumber"
	    INNER JOIN accounting."TABLE_Subject_Lv2" AS Lv2 ON Lv2."cID" = VD."cSubjectID"
	    INNER JOIN accounting."TYPE_Way" AS tW ON tW."cID" = VD."cWayID"
	    INNER JOIN accounting."TYPE_Currency" AS tC ON tC."cID" = VD."cCurrencyID"
EOD;
        if ($bIsBillNumber) { $cWhere = " VM.\"cBillNumber\" LIKE '%' || :cKeyword ||'%'"; }
        if ($bIsBillDate) { $cWhere = " VM.\"dBillDate\" BETWEEN :dStart AND :dEnd"; }
        $cOrderBy = "VD.\"iBillNumber\", VD.\"iRecordNumber\"";

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
    public function voucherDetailDataSave($aRequest) {
        if (!array_key_exists('cSaveType', $aRequest)) { $this->SetError("save type not exist."); return; }
        $cSaveType = $aRequest["cSaveType"];
        $bIsNew = $cSaveType == "new";
        $bIsEdit = $cSaveType == "edit";
        $bIsDelete = $cSaveType == "delete";
        if (!$bIsNew && !$bIsEdit && !$bIsDelete) { $this->SetError("save type error."); return; }

        $iBillNumber = $this->CheckRequestData("iBillNumber", $aRequest); if ($this->bErrorOn) { return; }
        if (!$bIsNew) {
            $iRecordNumber = $this->CheckRequestData("iRecordNumber", $aRequest); if ($this->bErrorOn) { return; }
        }

        if ($bIsNew || $bIsEdit) {
            return $this->voucherDetailNewOrEdit($aRequest);
        } else {
            return $this->voucherDetailDelete($aRequest);
        }
    }
    private function voucherDetailNewOrEdit($aRequest) {
        $cSaveType = $aRequest["cSaveType"]; unset($aRequest["cSaveType"]);
        $bIsNew = $cSaveType == "new";

        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["iBillNumber" => $aRequest["iBillNumber"]];
        if (!$bIsNew) { $htSql["iRecordNumber"] = $aRequest["iRecordNumber"]; }

        $cField = "cWayID";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "cSubjectID";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "cExplain";
        $oData = $this->CheckRequestData($cField, $aRequest, false, true); if ($this->bErrorOn) { return; }
        if (!isset($oData) && $bIsNew) { $oData = ""; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "fTotal";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "cWashFlag";
        $oData = $this->CheckRequestData($cField, $aRequest, false, true); if ($this->bErrorOn) { return; }
        if (!isset($oData) && $bIsNew) { $oData = ""; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "iWashRecordNumber";
        $oData = $this->CheckRequestData($cField, $aRequest, false, true); if ($this->bErrorOn) { return; }
        if (isset($oData) || $bIsNew) { $htSql[$cField] = $oData; }

        $cField = "cDepartmentID";
        $oData = $this->CheckRequestData($cField, $aRequest, false, true); if ($this->bErrorOn) { return; }
        if (!isset($oData) && $bIsNew) { $oData = ""; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "fExrate";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        $cField = "cCurrencyID";
        $oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        if (isset($oData)) { $htSql[$cField] = $oData; }

        //$cField = "fTotal_NTD";
        //$oData = $this->CheckRequestData($cField, $aRequest, $bIsNew); if ($this->bErrorOn) { return; }
        //if (isset($oData)) { $htSql[$cField] = $oData; }

        if ($bIsNew) {
            return $this -> voucherDetailNewOrEdit_New($htSql, $htLog);
        } else {
            return $this -> voucherDetailNewOrEdit_Edit($htSql, $htLog);
        }
    }
    private function voucherDetailNewOrEdit_New($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            INSERT INTO accounting."TABLE_Voucher_Detail" (
                "iBillNumber",
                "cWayID",
                "cSubjectID",
                "cExplain",
                "fTotal",
                "cWashFlag", "iWashRecordNumber",
                "cDepartmentID",
                "fExrate",
                "cCurrencyID",
                "fTotal_NTD",
                "iRecordNumber"
            )
            SELECT
                :iBillNumber,
                :cWayID,
                :cSubjectID,
                :cExplain,
                :fTotal,
                :cWashFlag, :iWashRecordNumber,
                :cDepartmentID,
                :fExrate,
                :cCurrencyID,
                :fTotal::numeric * :fExrate::numeric,
                coalesce (
			        (SELECT MAX("iRecordNumber") FROM accounting."TABLE_Voucher_Detail" WHERE "iBillNumber" = :iBillNumber),
			        0
		        ) + 1
            ;
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Voucher", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->voucherMainRefreshByDetail($htSql["iBillNumber"], $htLog["iOperator"]); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function voucherDetailNewOrEdit_Edit($htSql, $htLog) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = "UPDATE accounting.\"TABLE_Voucher_Detail\" SET ";

        $cList = "";
        if (array_key_exists("iBillNumber", $htSql)) { $cList .= ",\"iBillNumber\"=:iBillNumber"; }
        if (array_key_exists("iRecordNumber", $htSql)) { $cList .= ",\"iRecordNumber\"=:iRecordNumber"; }

        $bExists_cWay = array_key_exists("cWayID", $htSql);
        if ($bExists_cWay) { $cList .= ",\"cWayID\"=:cWayID"; }

        if (array_key_exists("cSubjectID", $htSql)) { $cList .= ",\"cSubjectID\"=:cSubjectID"; }
        if (array_key_exists("cExplain", $htSql)) { $cList .= ",\"cExplain\"=:cExplain"; }

        $bExists_fTotal = array_key_exists("fTotal", $htSql);
        if ($bExists_fTotal) { $cList .= ",\"fTotal\"=:fTotal"; }
        $bExists_fExrate = array_key_exists("fExrate", $htSql);
        if ($bExists_fExrate) { $cList .= ",\"fExrate\"=:fExrate"; }

        if ($bExists_fTotal || $bExists_fExrate) {
            $cList .= ",\"fTotal_NTD\"=" .
                ($bExists_fTotal ? ":fTotal::numeric" : "\"fTotal\"") .
                " * " .
                ($bExists_fExrate ? ":fExrate::numeric" : "\"fExrate\"");
        }

        if (array_key_exists("cWashFlag", $htSql)) { $cList .= ",\"cWashFlag\"=:cWashFlag"; }
        if (array_key_exists("iWashRecordNumber", $htSql)) { $cList .= ",\"iWashRecordNumber\"=:iWashRecordNumber"; }
        if (array_key_exists("cDepartmentID", $htSql)) { $cList .= ",\"cDepartmentID\"=:cDepartmentID"; }
        if (array_key_exists("cCurrencyID", $htSql)) { $cList .= ",\"cCurrencyID\"=:cCurrencyID"; }

        if (xString::IsEmpty($cList)) { $this->SetError("沒有傳入要修改的資料."); return; }
        $cSql .= substr($cList, 1);

        $cSql .= " WHERE \"iBillNumber\"=:iBillNumber AND \"iRecordNumber\" = :iRecordNumber";

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Voucher", $htLog); if ($this->oDbop->bErrorOn) { return; }
        if ($bExists_cWay || $bExists_fTotal || $bExists_fExrate) {
            $this->voucherMainRefreshByDetail($htSql["iBillNumber"], $htLog["iOperator"]); if ($this->oDbop->bErrorOn) { return; }
        }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    private function voucherDetailDelete($aRequest) {
        $iOperator = $this::iOperator();
        $htLog = ["iOperator" => $iOperator, "cLog" => xStatic::ToJson($aRequest), "dNow" => date("Y-m-d H:i:s")];

        $htSql = ["iBillNumber" => $aRequest["iBillNumber"]];
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            DELETE FROM accounting."TABLE_Voucher_Detail" WHERE "iBillNumber" = :iBillNumber AND "iRecordNumber" = :iRecordNumber
EOD;
        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->newLog("LOG_Voucher", $htLog); if ($this->oDbop->bErrorOn) { return; }
        $this->voucherMainRefreshByDetail($htSql["iBillNumber"], $htLog["iOperator"]); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();

        $this->SetOK(); return "";
    }
    private function voucherMainRefreshByDetail($iBillNumber, $iOperator) {
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $cSql = <<<EOD
            UPDATE accounting."TABLE_Voucher_Main" AS M SET
	            "iRecords_Debit" = D."iRecords_Debit",
	            "fTotal_Debit" = D."fTotal_Debit",
	            "iRecords_Credit" = D."iRecords_Credit",
	            "fTotal_Credit" = D."fTotal_Credit",
	            "iOperator" = :iOperator,
	            "fTotal_Debit_NTD" = D."fTotal_Debit_NTD",
	            "fTotal_Credit_NTD" = D."fTotal_Credit_NTD"
	        FROM (
		        SELECT
			        "iBillNumber",
			        SUM(CASE WHEN "cWayID" = 'D' THEN 1 ELSE 0 END) AS "iRecords_Debit",
			        SUM(CASE WHEN "cWayID" = 'D' THEN "fTotal" ELSE 0 END) AS "fTotal_Debit",
			        SUM(CASE WHEN "cWayID" = 'D' THEN "fTotal_NTD" ELSE 0 END) AS "fTotal_Debit_NTD",
			        SUM(CASE WHEN "cWayID" = 'C' THEN 1 ELSE 0 END) AS "iRecords_Credit",
			        SUM(CASE WHEN "cWayID" = 'C' THEN "fTotal" ELSE 0 END) AS "fTotal_Credit",
			        SUM(CASE WHEN "cWayID" = 'C' THEN "fTotal_NTD" ELSE 0 END) AS "fTotal_Credit_NTD"
		        FROM accounting."TABLE_Voucher_Detail"
		        WHERE "iBillNumber" = :iBillNumber
		        GROUP BY "iBillNumber"
	        ) AS D
            WHERE M."iBillNumber" = D."iBillNumber";
EOD;
        $htSql = ["iOperator" => $iOperator, "iBillNumber" => $iBillNumber];

        $this->oDbop->RunSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return "";
    }
    #endregion
    #endregion

    private function newLog($cTableName, $htLog) {
        $cSql = <<<EOD
            INSERT INTO accounting."{$cTableName}" (
                "iOperator", "cMemo", "dTime"
            ) VALUES (
                :iOperator, :cLog, :dNow
            );
EOD;
        $this->oDbop->RunSql($cSql, $htLog); if ($this->oDbop->bErrorOn) { return; }
    }
}
