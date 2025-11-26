<?php
namespace nknu\controls;

class Time extends HtmlGenericControl {
    public Textbox $uTextbox;
    public Button $uIcon;
    public function __construct() {
        parent::__construct("div");
        $this->uTextbox = new TextBox();
        $this->uIcon = new Button();
    }
    public function setProperty($aPostData) {
        $cID = isset($aPostData["id"]) ? $aPostData["id"] : "_".str_replace(["{","}","-"], "", com_create_guid());
        $aProperty4Textbox = ["id"=>$cID];
        $this->uTextbox->setProperty($aProperty4Textbox);

        $aProperty4Button = ["id"=>$cID."_TimeIcon", "type"=>"time"];   //³]©w Button RenderType = Time
        $this->uIcon->setProperty($aProperty4Button);

        $this->aProperty["style"] = "width:7em";
        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["input-group"])) { $aClass[] = "input-group"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "input-group";
        }
        $this->aProperty["class"] = $cClass;

        $this->aControls["uTextbox"] = $this->uTextbox;
        $this->aControls["uIcon"] = $this->uIcon;
    }
    public function render() {
        $aData = parent::render();

        $cID = $this->uTextbox->aProperty["id"];
        $cID_TimeIcon = $this->uIcon->aProperty["id"];

        $aData["cScript"] = '
            $("#'.$cID.'").timepicker({ format: "HH:mm" });
            $("#'.$cID_TimeIcon.'").click(function (e) { e.preventDefault(); $("#'. $cID .'").focus(); });
        ';

        return $aData;
    }
}
?>