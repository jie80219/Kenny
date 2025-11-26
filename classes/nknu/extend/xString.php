<?php

namespace nknu\extend;

use nknu\base\xBase;
use nknu\base\xReturn;

class xString extends xBase
{
    public static function IsNull(&$object): bool
    {
        if (!isset($object)) {
            return true;
        }   //null O null
        $object = (string)$object;
        return false;
    }
    public static function IsNullOrEmpty(&$object): bool
    {
        return xString::IsNull($object) || $object == "";
    }

    public static function IsEmpty(&$object): bool
    {
        return !xString::IsNull($object) && $object == "";
    }
    public static function IsNotEmpty(&$object): bool
    {
        return !xString::IsNull($object) && $object != "";
    }
    public static function IsJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        };
        $string = trim($string);
        if ($string === '') {
            return false;
        };
        if (!($string[0] == '{' || $string[0] == '[')) {
            return false;
        };
        return json_decode($string) !== false && json_last_error() === JSON_ERROR_NONE;
    }

    #region Encoding
    public static function ToAesData(string $cText): string
    {
        $oAes = new \nknu\utility\encoding\xAes();
        return $oAes->Encrypt($cText);
    }
    public static function ToSafeBase64AesData(string $cText): string
    {
        $cAesData = xString::ToAesData($cText);
        $cSafeBase64AesData = xString::Base64ToSafeBase64($cAesData);
        return $cSafeBase64AesData;
    }
    public static function DecryptAesData(string $cText): xReturn
    {
        $oAes = new \nknu\utility\encoding\xAes();
        $cText = $oAes->Decrypt($cText);
        return new xReturn($oAes->bErrorOn, $oAes->cMessage, $oAes->bErrorOn ? null : $cText);;
    }
    public static function DecryptSafeBase64AesData(string $cText): xReturn
    {
        $cText = xString::SafeBase64ToBase64($cText);
        return xString::DecryptAesData($cText);
    }
    #endregion

    public static function ToBase64(string $cData): string
    {
        return base64_encode($cData);
    }
    public static function ToSafeBase64($cData): string
    {
        $cBase64 = xString::ToBase64($cData);
        return xString::Base64ToSafeBase64($cBase64);
    }
    public static function Base64ToSafeBase64(string $cBase64): string
    {
        return str_replace(array('+', '/'), array('-', '_'), $cBase64);
    }
    public static function SafeBase64ToBase64(string $cSafeBase64): string
    {
        return str_replace(array('-', '_'), array('+', '/'), $cSafeBase64);
    }
    public static function SafeBase64ToString(string $cSafeBase64): string
    {
        $cBase64 = xString::SafeBase64ToBase64($cSafeBase64);
        return xString::Base64ToString($cBase64);
    }
    public static function Base64ToString(string $cBase64): string
    {
        return base64_decode($cBase64);
    }
}
