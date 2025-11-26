<?php
namespace nknu\base;
use nknu\extend\xBool as xBool;
use nknu\extend\xInt as xInt;
use nknu\extend\xFloat as xFloat;
use nknu\extend\xString as xString;
use nknu\extend\xDateTime as xDateTime;

class xBase extends xEvent {
    public bool $bErrorOn = false;
    public string $cMessage = "";
    public function SetOK(string $cOKMessage = "") {
        $this->bErrorOn = false;
        $this->cMessage = $cOKMessage;
    }
    public function SetError(string $cErrorMessage) {
        $this->bErrorOn = true;
        $this->cMessage = $cErrorMessage;
        $this->Invoke("ErrorOn");
    }
    public function MakeResponse($response, $oData) {
        $result["status"] = $this->bErrorOn ? "failed" : "success";
		$result["message"] = $this->cMessage;
		$result["data"] = [];
		if (!$this->bErrorOn) {
            $bIsArray = is_array($oData);
            $result["data"] = $bIsArray && array_key_exists("data", $oData) ? $oData["data"] : $oData;
            if ($bIsArray && array_key_exists("total", $oData)) { $result["total"] = $oData["total"]; }
		}
        $response = $response->withHeader('Content-type', 'application/json');
		//$response = $response->withHeader('Access-Control-Allow-Origin', '*');
		//$response = $response->withHeader('Access-Control-Allow-Headers', 'content-type');
		//$response = $response->withHeader('Access-Control-Allow-Methods', 'PUT, GET, POST, DELETE, OPTIONS');
		$response = $response->withJson($result);
		return $response;
    }

    public function CheckRequestData($cField, $aRequest, $bRequired = true, $bAllowEmpty = false) {
        $cType = substr($cField, 0, 1);
        return $this->CheckArrayData($cField, $aRequest, $bRequired, $bAllowEmpty, $cType);
    }
    public function CheckArrayData($cField, $aArray, $bRequired = true, $bAllowEmpty = false, $cDataType = "c") {
        $bIsExists = array_key_exists($cField, $aArray);
		$oData = $bIsExists ? $aArray[$cField] : null;
        if ($oData == null) {
            if ($bRequired) { $this->SetError($cField . " not set."); }
			return null;
		}
        if ($bAllowEmpty && $cDataType != "c" && $cDataType != "a" && ($oData == null || $oData == "")) { return null; }

        $bIsFormatMatch = false;
        switch($cDataType) {
			case "c": $bIsFormatMatch = is_string($oData); if (!$bAllowEmpty && xString::IsEmpty($oData)) { $this->SetError($cField . " is empty."); return null; } break;
            case "i": $bIsFormatMatch = xInt::To($oData); break;
            case "f": $bIsFormatMatch = xFloat::To($oData); break;
            case "b": $bIsFormatMatch = $oData == 1 || $oData == 0 || xBool::To($oData); break;
            case "d": $bIsFormatMatch = xDateTime::ToDate($oData); break;
            case "dt": $bIsFormatMatch = xDateTime::ToDateTime($oData); break;
			case "a": $bIsFormatMatch = is_array($oData); if (!$bAllowEmpty && count($oData) == 0) { $this->SetError($cField . " is empty."); return null; } break;
        }
        if (!$bIsFormatMatch) { $this->SetError($cField . " format error."); return null; }

        return $oData;
    }
    function CallBack($bSuccess, $cMessage = null, $oData = null) {
		$callBack = new \stdClass();
		$callBack->status = $bSuccess ? "success" : "failed";
		$callBack->message = $cMessage;
		$callBack->data = $oData;
		return $callBack;
	}
    function GetDatabaseErrorMessage($stmt, $bSetError = true) {
		$oInfo = $stmt->errorInfo();
		$cMessage = $oInfo[2]; if ($cMessage == null) { $cMessage = "error"; }
		if ($bSetError)  { $this->SetError($cMessage); }

		return $this->CallBack(false, $cMessage);
	}
    public static function iOperator($iDefault = 0) {
        return isset($_SESSION['id']) ? $_SESSION['id'] : $iDefault;
    }
}
class xEvent {
    protected $aListens  =  array();

    public function On(string $cEventName, $mFunction, $once = false) {
        if(!is_callable($mFunction)) return false;
        $this->aListens[$cEventName][]   =  array('mFunction'=>$mFunction, 'bOnce'=>$once);
        return true;
    }

    public function One(string $cEventName, $mFunction) {
        return $this->On($cEventName, $mFunction, true);
    }

    public function Remove(string $cEventName, $index=null) {
        if(is_null($index))
            unset($this->aListens[$cEventName]);
        else
            unset($this->aListens[$cEventName][$index]);
    }

    public function Invoke() {
        if(!func_num_args()) return;
        $args = func_get_args();
        $cEventName = array_shift($args);
        if(!isset($this->aListens[$cEventName])) return false;
        foreach((array) $this->aListens[$cEventName] as $index=>$listen) {
            $mFunction = $listen['mFunction'];
            $listen['bOnce'] && $this->Remove($cEventName, $index);
            call_user_func_array($mFunction, $args);
        }
    }
}
?>
