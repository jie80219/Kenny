<?php
namespace nknu\controls;

class Button extends HtmlGenericControl {
    public HtmlGenericControl $i;
    public HtmlGenericControl $span;
    public function __construct() {
        parent::__construct("button");
        $this->i = new HtmlGenericControl("i");
        $this->span = new HtmlGenericControl("span");
    }
    public function setProperty($aPostData) {
        $this->aProperty["type"] = "button";

        $cType = isset($aPostData["type"]) ? $aPostData["type"] : "";
        switch($cType) {
            case "save":
                $cColor = "primary";
                $cIcon = "fas fa-save";
                $cText = "儲存";
                break;
            case "search":
                $cColor = "secondary";
                $cIcon = "fas fa-search";
                $cText = "查詢";
                break;
            case "print":
                $cColor = "secondary";
                $cIcon = "fas fa-print";
                $cText = "列印";
                break;
            case "date":
                $cColor = "secondary";
                $cIcon = "far fa-calendar-alt";
                $cText = "";
                break;
            case "time":
                $cColor = "secondary";
                $cIcon = "far fa-clock";
                $cText = "";
                break;
            default:
                $cColor = "info";
                $cIcon = "fas fa-mouse-pointer";
                $cText = "";
                break;
        }
        if (isset($aPostData["class"])) {
            $aClass = explode(" ", $aPostData["class"]);
            if (!isset($aClass["btn"])) { $aClass[] = "btn"; }
            $cClass = join(" ", $aClass);
        } else {
            $cClass = "btn";
        }
        if (isset($aPostData["color"])) { $cColor = $aPostData["color"]; }
        if (isset($aPostData["icon"])) { $cIcon = $aPostData["icon"]; }
        if (isset($aPostData["text"])) { $cText = $aPostData["text"]; }

        $cClass = $cClass." btn-".$cColor;
        $this->aProperty["class"] = $cClass;

        $this->i->aProperty["class"] = $cIcon;
        if ($cText != "") {
            $this->span->cInnerHtml = $cText;
            $this->span->aProperty["class"] = "ml-2";
        }

        if (isset($aPostData["id"])) { $this->aProperty["id"] = $aPostData["id"]; }
        if (isset($aPostData["onclick"])) { $this->aProperty["onclick"] = $aPostData["onclick"]; }

        $this->aControls["i"] = $this->i;
        $this->aControls["span"] = $this->span;
    }
    public function render() {
        $aData = parent::render();
        return $aData;
    }
}
?>