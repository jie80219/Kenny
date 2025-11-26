<?php
namespace nknu\utility\encoding;
use nknu\base\xBase;
use nknu\extend\xString as xString;
use nknu\utility\xStatic as xStatic;

class xAes extends xBase {
    protected int $iLevel_Default = 192;
    protected string $cKey_Default = "NkNu&CcXxAes@Mil||School";
	protected array $aIV_Default = [109, 152, 128, 35, 114, 91, 190, 245, 110, 190, 41, 244, 210, 157, 118, 247];
    public function __construct() {
    }

	function Encrypt(string $cText, int $iLevel = null, string $cKey = null, array $aIV = []) {
		if (!isset($iLevel)) { $iLevel = $this->iLevel_Default; }
		if (xString::IsNullOrEmpty($cKey)) { $cKey = $this->cKey_Default; }
		if (sizeof($aIV) == 0) { $aIV = $this->aIV_Default; }
		$cIV = implode(array_map("chr", $aIV));
        $cText = openssl_encrypt($cText, "aes-". $iLevel ."-cbc", $cKey, 0, $cIV);
        return $cText;
    }
	function Decrypt($cText, int $iLevel = null, string $cKey = null, array $aIV = []) {
		if (!isset($iLevel)) { $iLevel = $this->iLevel_Default; }
		if (xString::IsNullOrEmpty($cKey)) { $cKey = $this->cKey_Default; }
		if (sizeof($aIV) == 0) { $aIV = $this->aIV_Default; }
		$cIV = implode(array_map("chr", $aIV));
		$cText = openssl_decrypt($cText, "aes-". $iLevel ."-cbc", $cKey, 0, $cIV);
		if (is_bool($cText)) { $this->SetError("ѱK."); return null; }
		return $cText;
    }
}
