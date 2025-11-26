<?php
namespace nknu\controls;

class DateTime extends HtmlGenericControl {
    public Date $uDate;
    public Time $uTime;
    public function __construct() {
        parent::__construct("div");
        $this->uDate = new Date();
        $this->uTime = new Time();
    }
    public function setProperty($aPostData) {
        $cID = isset($aPostData["id"]) ? $aPostData["id"] : "_".str_replace(["{","}","-"], "", com_create_guid());
        $aPostData["id"] = $cID."_Date";
        $this->uDate->setProperty($aPostData);
        $aPostData["id"] = $cID."_Time";
        $this->uTime->setProperty($aPostData);

        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["input-group"])) { $aClass[] = "input-group"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "input-group";
        }
        $this->aProperty["class"] = $cClass;

        $this->aControls["uDate"] = $this->uDate;
        $this->aControls["uTime"] = $this->uTime;
    }
    public function render() {
        $aData = parent::render();
        return $aData;
    }
}
?>