<?php
namespace nknu\controls;

class Textbox extends HtmlVoidControl {
    public function __construct() {
        parent::__construct("input");
    }
    public function setProperty($aPostData) {
        $this->aProperty["type"] = "text";
        if (isset($aPostData["id"])) { $this->aProperty["id"] = $aPostData["id"]; }
        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["form-control"])) { $aClass[] = "form-control"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "form-control";
        }
        $this->aProperty["class"] = $cClass;
    }
    public function render() {
        $aData = parent::render();
        return $aData;
    }
}
?>