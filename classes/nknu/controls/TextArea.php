<?php
namespace nknu\controls;

class TextArea extends HtmlGenericControl {
    public function __construct() {
        parent::__construct("textarea");
    }
    public function setProperty($aPostData) {
        $this->aProperty["type"] = "text";
        if (isset($aPostData["id"])) { $this->aProperty["id"] = $aPostData["id"]; }
        if (isset($aPostData["rows"])) { $this->aProperty["rows"] = $aPostData["rows"]; }
        if (isset($aPostData["cols"])) { $this->aProperty["cols"] = $aPostData["cols"]; }
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