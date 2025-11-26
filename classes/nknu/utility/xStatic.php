<?php
namespace nknu\utility;
use nknu\base\xBase;
class xStatic extends xBase {
    public static function ToJson($object) : string {
        return json_encode($object);
    }
    public static function ToClass(string $cJson) : \stdClass {
        return json_decode($cJson);
    }
	public static function KeyMatchThenReplaceValue(&$aData, $bNoPatchThenRemove, &$aPatch, $bReplaceThenRemove) {
		foreach ($aData as $key => $value) {
			$aData = xStatic::KeyExistThenReplaceValue($aData, $key, $bNoPatchThenRemove, $aPatch, $key, $bReplaceThenRemove);
        }
        return $aData;
	}
	public static function KeyExistThenReplaceValue(&$aData, $cKey, $bNoPatchThenRemove, &$aPatch, $cKeyPatch, $bReplaceThenRemove) {
        if (array_key_exists($cKeyPatch, $aPatch)) {
			$aData[$cKey] = $aPatch[$cKeyPatch];
            if ($bReplaceThenRemove) { unset($aPatch[$cKeyPatch]); }
		} else if ($bNoPatchThenRemove) {
			unset($aData[$cKey]);
		}
        return $aData;
	}
	public static function KeyMatchThenJoinValue($aData, $bNoPatchThenRemove, &$aJoinValues, $bReplaceThenRemove) {
		$cJoin = "";
		foreach ($aData as $key => $value) {
            if (array_key_exists($key, $aJoinValues)) {
				$cJoin .= $aJoinValues[$key];
                if ($bReplaceThenRemove) { unset($aJoinValues[$key]); }
			} else if ($bNoPatchThenRemove) {
			    unset($aData[$key]);
			}
        }
        return $cJoin;
	}
    public static function ValueMatchThenRemove($aData, $cValue) {
		foreach ($aData as $key => $value) {
            if ($value === $cValue) {
			    unset($aData[$key]);
			}
        }
        return $aData;
	}

	public static function BindValue($oStatement, $htSql) {
		foreach ($htSql as $cKey => $oData) {
			if (is_array($oData)) {
				$oValue = $oData["oValue"];
				$iType = $oData["iType"];
				$oStatement->bindValue(':' . $cKey, $oValue, $iType);
			} else {
				$oStatement->bindValue(':' . $cKey, $oData, \PDO::PARAM_STR);
			}
		}
	}
}
