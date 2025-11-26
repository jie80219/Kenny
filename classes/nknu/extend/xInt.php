<?php
namespace nknu\extend;
use nknu\base\xBase;

class xInt extends xBase {
    public static function To(& $object) : bool {
        if (is_numeric($object)) { $object = (int)$object; return true; }
        return false;
    }
    public static function IsNull(& $object) : bool {
        return !isset($object);
    }
    public static function IsNullOrTo(& $object) : bool {
        if (xInt::IsNull($object)) { return true; }
        return xInt::To($object);
    }
    public static function IsNotNullAndTo(& $object) : bool {
        if (xInt::IsNull($object)) { return false; }
        return xInt::To($object);
    }
}
