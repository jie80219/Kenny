<?php
namespace nknu\extend;
use nknu\base\xBase;

class xFloat extends xBase {
    public static function To(& $object) : bool {
        if (is_numeric($object)) { $object = (float)$object; return true; }
        return false;
    }
    public static function IsNull($object) : bool {
        return !isset($object);
    }
    public static function IsNullOrTo(& $object) : bool {
        if (xFloat::IsNull($object)) { return true; }   //null O null
        return xFloat::To($object);
    }
    public static function IsNotNullAndTo(& $object) : bool {
        if (!isset($object)) { return false; }   //null O null
        return xFloat::To($object);
    }
}
