<?php
namespace nknu\database;
use nknu\base\xBase;
class xDatabaseOperator extends xBase {
    protected $container;
    public $oConnection;

    public function __construct($cConnectionConfig = null) {
        global $container;
        $this->container = $container;
        if (isset($cConnectionConfig)) { $this->Connect($cConnectionConfig);}
    }
    public function Connect($cConnectionConfig) {
        if (isset($this->oConnection)) { $this->oConnection = null; }
        try {
            switch ($cConnectionConfig) {
                case "db": $this->oConnection = $this->container->db; break;
                case "db_sierra": $this->oConnection = $this->container->db_sierra; break;
                case "db_sqlsrv_sierra": $this->oConnection = $this->container->db_sqlsrv_sierra; break;
                default: throw new \Exception("不確定的連線設定");
            }
            $this->oConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        catch(\PDOException $ex) {
            $this->SetError($ex->getMessage());
        }
    }
    public function Disconnect() {
        $this->oConnection = null;
    }
    public function SelectSql($cSql, $htSql) {
        try {
            $oStatement = $this->oConnection->prepare($cSql);
        }
        catch(\PDOException $ex) {
            $this->SetError($ex->getMessage()); return;
        }

        foreach($htSql as $cKey=>$oData) {
            if (is_array($oData)) {
                $oValue = $oData["oValue"];
                $iType = $oData["iType"];
                $oStatement->bindValue(':'.$cKey, $oValue, $iType);
            } else {
                $oStatement->bindValue(':'.$cKey, $oData);
            }
        }

        try {
            $oStatement->execute();
            $aData = $oStatement->fetchAll(\PDO::FETCH_ASSOC);
        }
        catch(\PDOException $ex) {
            $this->SetError($ex->getMessage());
            $aData = null;
        }
        return $aData;
    }
    public function RunSql($cSql, $htSql) {
        return $this->SelectSql($cSql, $htSql);
    }

    public function MakeInsertData($cTableName, $aFields, $aRows) {
        $aData = [ "cSql" => "", "htSql" => []];

        $iRow = 0; $cValues = ""; ;
        foreach($aRows as $row) {
            $iRow += 1;

            $cValue = "";
            foreach($aFields as $cField) {
                if (!isset($row[$cField])) { $this->SetError("未包含欲儲存的欄位. [".$cField."]"); return; }
                $cValue .= ",:".$cField."_".$iRow;

                switch(substr($cField, 0, 1)) {
                    case "f": $oValue = (float)$row[$cField]; break;
                    case "b": $oValue = ["oValue"=>$row[$cField], "iType"=>\PDO::PARAM_BOOL]; break;
                    default: $oValue = $row[$cField]; break;
                }
                $aData["htSql"][$cField."_".$iRow] = $oValue;
            }
            $cValues .= ",(".substr($cValue, 1).")";
        }
        if ($cValues == "") { $this->SetError("沒有傳入新增的資料."); return; }
        $cValues = substr($cValues, 1);
        $cFields = "(\"" . implode($aFields, "\",\"") . "\")";

        $aData["cSql"] = "INSERT INTO public.\"".$cTableName."\" ".$cFields." VALUES ".$cValues;

        return $aData;
    }

    public function __destruct() {
        $this->oConnection = null;
    }

}
