<?php
namespace nknu\extend;
use nknu\base\xBase;

class xDateTime extends xBase {
    public static function ToDate(& $object) : bool {
        if ($object instanceof \DateTime) {
            $object = $object->format("Y-m-d") . " 00:00:00";
        } else {
            if (xString::IsNullOrEmpty($object)) { return false; }
            $iCut = strpos($object, " ");
            if ($iCut > 0) { $object = substr($object, 0, $iCut); }
            $iCut = strpos($object, "T");
            if ($iCut > 0) { $object = substr($object, 0, $iCut); }
            $object = $object . " 00:00:00";
        }
        return xDateTime::objectToDateTime($object);
    }

    public static function ToDateTime(& $object) : bool {
        if ($object instanceof \DateTime) { return true; }
        if (xString::IsNullOrEmpty($object)) { return false; }
        return xDateTime::objectToDateTime($object);
    }
    private static function objectToDateTime(& $object) : bool {
        $object = str_replace("/","-", $object);
		$stamp = strtotime($object); if ($stamp === false) { return false; }
		$object = \DateTime::createFromFormat("Y-m-d H:m:s", date("Y-m-d H:m:s", $stamp));
        return true;
    }

    public static function IsNull($object) : bool {
        return !isset($object);
    }

    public static function IsNullOrToDate(& $object) : bool {
        if (xDateTime::IsNull($object)) { return true; }
        return xDateTime::ToDate($object);
    }
    public static function IsNullAndToDate(& $object) : bool {
        if (xDateTime::IsNull($object)) { return false; }
        return xDateTime::ToDate($object);
    }

    public static function IsNullOrToDateTime(& $object) : bool {
        if (xDateTime::IsNull($object)) { return true; }
        return xDateTime::ToDateTime($object);
    }
    public static function IsNullAndToDateTime(& $object) : bool {
        if (xDateTime::IsNull($object)) { return false; }
        return xDateTime::ToDateTime($object);
    }
}
