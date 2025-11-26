<?php
namespace nknu\controls;

class HtmlGenericControl extends \nknu\base\xBase {
    protected string $cTagName;
    public array $aProperty = [];
    public array $aControls = [];
    public array $aOptions = [];
    public string $cInnerHtml;

    public function __construct($tagName) {
        $this->cTagName = $tagName;
    }
    public function render() {
        //if (!isset($this->aProperty["id"])) {
        //    $this->aProperty["id"] = "_".str_replace(["{","}","-"], "", com_create_guid());
        //}

        $cProperty = "";
        foreach($this->aProperty as $cKey => $cValue) {
            if ($cValue == "") continue;
            $cProperty = $cProperty." ".$cKey.'="'.$cValue.'"';
        }
        $cHtml = "<".$this->cTagName.$cProperty.">";
        $cScript = "";
        if (isset($this->cInnerHtml)) {
            $cHtml = $cHtml.$this->cInnerHtml;
        } else {
            foreach($this->aControls as $oControl) {
                $aChildData = $oControl->render();
                $cHtml = $cHtml.$aChildData["cHtml"];
                $cScript = $cScript.$aChildData["cScript"];
            }
        }
        $cHtml = $cHtml."</".$this->cTagName.">";

        return ["cHtml"=>$cHtml, "cScript"=>$cScript];
    }
}
?>