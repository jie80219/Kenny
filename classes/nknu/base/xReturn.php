<?php
namespace nknu\base;

class xReturn {
    public function __construct(bool $bErrorOn, string $cMessage = "", $oData = null) {
        $this->bErrorOn = $bErrorOn;
        $this->cMessage = $cMessage;
        $this->oData = $oData;
    }
    public bool $bErrorOn;
    public string $cMessage;
    public $oData;
}
?>
