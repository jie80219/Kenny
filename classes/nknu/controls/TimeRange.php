<?php
namespace nknu\controls;

class TimeRange extends HtmlGenericControl {
    public Time $uTime_Start;
    public HtmlGenericControl $uTilde;
    public Time $uTime_End;
    public function __construct() {
        parent::__construct("div");
        $this->uTime_Start = new Time();
        $this->uTilde = new HtmlGenericControl("label");
        $this->uTime_End = new Time();
    }
    public function setProperty($aPostData) {
        $cID = isset($aPostData["id"]) ? $aPostData["id"] : "_".str_replace(["{","}","-"], "", com_create_guid());
        $aPostData["id"] = $cID."_Start";
        $this->uTime_Start->setProperty($aPostData);

        $this->uTilde->aProperty = ["class"=>"mx-2"];
        $this->uTilde->cInnerHtml = "~";

        $aPostData["id"] = $cID."_End";
        $this->uTime_End->setProperty($aPostData);

        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["input-group"])) { $aClass[] = "input-group"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "input-group";
        }
        $this->aProperty["class"] = $cClass;

        $this->aControls["uTime_Start"] = $this->uTime_Start;
        $this->aControls["uTilde"] = $this->uTilde;
        $this->aControls["uTime_End"] = $this->uTime_End;
    }
    public function render() {
        $aData = parent::render();
        return $aData;
    }
}
?>