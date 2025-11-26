<?php
namespace nknu\controls;

class HtmlVoidControl extends \nknu\base\xBase {
    protected string $cTagName;
    public array $aProperty = [];

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
        $cHtml = "<".$this->cTagName.$cProperty." />";
        $cScript = "";

        return ["cHtml"=>$cHtml, "cScript"=>$cScript];
    }
}
?>