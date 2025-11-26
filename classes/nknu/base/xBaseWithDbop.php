<?php
namespace nknu\base;
use nknu\database\xDatabaseOperator;
class xBaseWithDbop extends xBase {
    public $oDbop;
    public function __construct() {
        $this->oDbop = new xDatabaseOperator();
        $this->oDbop->On("ErrorOn", function() { $this->SetError($this->oDbop->cMessage); });
    }
    public function __destruct() {
        //$this->oDbop = null;
    }
}
?>
