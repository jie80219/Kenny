<?php
namespace nknu\controls;

class Date extends HtmlGenericControl {
    public Textbox $uTextbox;
    public Button $uIcon;
    public function __construct() {
        parent::__construct("div");
        $this->uTextbox = new Textbox();
        $this->uIcon = new Button();
    }
    public function setProperty($aPostData) {
        $cID = isset($aPostData["id"]) ? $aPostData["id"] : "_".str_replace(["{","}","-"], "", com_create_guid());
        $aProperty4Textbox = ["id"=>$cID];
        $this->uTextbox->setProperty($aProperty4Textbox);
        //$this->input->aProperty["placeholder"] = isset($aPostData["placeholder"]) ? $aPostData["placeholder"] : "日期";

        $aProperty4Button = ["id"=>$cID."_DateIcon", "type"=>"date"];   //設定 Button RenderType = Date
        $this->uIcon->setProperty($aProperty4Button);

        $this->aProperty["style"] = "width:10em";
        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["input-group"])) { $aClass[] = "input-group"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "input-group";
        }
        $this->aProperty["class"] = $cClass;

        if (isset($aPostData["mindate"])) { $this->aOptions["minDate"] = $aPostData["mindate"]; }
        if (isset($aPostData["maxdate"])) { $this->aOptions["maxdate"] = $aPostData["maxdate"]; }
        if (isset($aPostData["noweekends"])) { $this->aOptions["noweekends"] = $aPostData["noweekends"]; }

        $this->aControls["uTextbox"] = $this->uTextbox;
        $this->aControls["uIcon"] = $this->uIcon;
    }
    public function render() {
        $aData = parent::render();

        $cID = $this->uTextbox->aProperty["id"];
        $cID_DateIcon = $this->uIcon->aProperty["id"];

        $aOption = [];
        if (isset($this->aOptions["mindate"])) { $aOption["minDate"] = "new Date('".$this->aOptions["mindate"]."')"; }
        if (isset($this->aOptions["maxdate"])) { $aOption["maxDate"] = "new Date('".$this->aOptions["maxdate"]."')"; }
        if (isset($this->aOptions["noweekends"]) && $this->aOptions["noweekends"] == "true") { $aOption["beforeShowDay"] = "$.datepicker.noWeekends"; }
        $cOption = "";
        foreach($aOption as $cKey => $cValue) {
            $cOption = $cOption.$cKey.':'.$cValue;
        }

        $aData["cScript"] = '
            setDatePicker("'. $cID .'", {'.$cOption.'});
            $("#'. $cID_DateIcon .'").click(function (e) { e.preventDefault(); $("#'. $cID .'").focus(); });
        ';

        return $aData;
    }
}
?>