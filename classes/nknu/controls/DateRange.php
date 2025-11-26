<?php
namespace nknu\controls;

class DateRange extends HtmlGenericControl {
    public Date $uDate_Start;
    public HtmlGenericControl $uTilde;
    public Date $uDate_End;
    public function __construct() {
        parent::__construct("div");
        $this->uDate_Start = new Date();
        $this->uTilde = new HtmlGenericControl("label");
        $this->uDate_End = new Date();
    }
    public function setProperty($aPostData) {
        $cID = isset($aPostData["id"]) ? $aPostData["id"] : "_".str_replace(["{","}","-"], "", com_create_guid());
        $aPostData["id"] = $cID."_Start";
        $this->uDate_Start->setProperty($aPostData);

        $this->uTilde->aProperty = ["class"=>"mx-2"];
        $this->uTilde->cInnerHtml = "~";

        $aPostData["id"] = $cID."_End";
        $this->uDate_End->setProperty($aPostData);

        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["input-group"])) { $aClass[] = "input-group"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "input-group";
        }
        $this->aProperty["class"] = $cClass;

        $this->aControls["uDate_Start"] = $this->uDate_Start;
        $this->aControls["uTilde"] = $this->uTilde;
        $this->aControls["uDate_End"] = $this->uDate_End;
    }
    public function render() {
        $aData = parent::render();
        return $aData;
    }
}
?>