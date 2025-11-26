<?php
namespace nknu\controls;

class DateTimeRange extends HtmlGenericControl {
    public DateTime $uDateTime_Start;
    public HtmlGenericControl $uTilde;
    public DateTime $uDateTime_End;
    public function __construct() {
        parent::__construct("div");
        $this->uDateTime_Start = new DateTime();
        $this->uTilde = new HtmlGenericControl("label");
        $this->uDateTime_End = new DateTime();
    }
    public function setProperty($aPostData) {
        $cID = isset($aPostData["id"]) ? $aPostData["id"] : "_".str_replace(["{","}","-"], "", com_create_guid());
        $aPostData["id"] = $cID."_Start";
        $this->uDateTime_Start->setProperty($aPostData);

        $this->uTilde->aProperty = ["class"=>"mx-2"];
        $this->uTilde->cInnerHtml = "~";

        $aPostData["id"] = $cID."_End";
        $this->uDateTime_End->setProperty($aPostData);

        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["input-group"])) { $aClass[] = "input-group"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "input-group";
        }
        $this->aProperty["class"] = $cClass;

        $this->aControls["uDateTime_Start"] = $this->uDateTime_Start;
        $this->aControls["uTilde"] = $this->uTilde;
        $this->aControls["uDateTime_End"] = $this->uDateTime_End;
    }
    public function render() {
        $aData = parent::render();
        return $aData;
    }
}
?>