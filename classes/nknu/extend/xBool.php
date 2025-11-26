<?php
namespace nknu\extend;
use nknu\base\xBase;

class xBool extends xBase {
    public static function To(& $object) : bool {
        if (is_bool($object)) { $object = (bool)$object; return true; }
        return false;
    }
    public static function IsNull($object) : bool {
        return !isset($object);
    }
    public static function IsNullOrTo(& $object) : bool {
        if (xBool::IsNull($object)) { return true; }
        return xBool::To($object);
    }
    public static function IsNotNullAndTo(& $object) : bool {
        if (xBool::IsNull($object)) { return false; }
        return xBool::To($object);
    }
}
