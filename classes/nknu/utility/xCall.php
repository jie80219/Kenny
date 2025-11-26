<?php

namespace nknu\utility;

use nknu\base\xBase;

class xCall extends xBase
{
    public string $cApiServer = "http://192.168.2.43";
    public function WebFormApi($cJsonData)
    {
        return $this->serviceApi($this->cApiServer, "/app.aspx", $cJsonData);
    }
    public function WindowFormApi($cJsonData)
    {
        return $this->serviceApi($this->cApiServer, "/default.aspx", $cJsonData);
    }
    public function RfidReaderApi($cJsonData)
    {
        return $this->serviceApi($this->cApiServer, "/rfidReader.aspx", $cJsonData);
    }
    public function LabelPrnterApi($cJsonData)
    {
        return $this->serviceApiLabelPrinter($this->cApiServer, "/labelPrinter.aspx", $cJsonData);
    }
    private function serviceApi($cServer, $cUrl, $cJsonData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cServer . $cUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cJsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $cResponse = curl_exec($ch);
        $iErrorNo = curl_errno($ch);
        if ($iErrorNo == 0) {
            $oResponse = xStatic::ToClass($cResponse);
        } else {
            $this->SetError(curl_error($ch));
        }
        curl_close($ch);
        if ($iErrorNo > 0) {
            return null;
        }

        if ($oResponse->bErrOn == true) {
            $this->SetError($oResponse->cMessage);
            return null;
        } else {
            $this->SetOK();
            return $oResponse->oData;
        }
    }
    private function serviceApiLabelPrinter($cServer, $cUrl, $cJsonData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cServer . $cUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cJsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $cResponse = curl_exec($ch);
        $iErrorNo = curl_errno($ch);
        if ($iErrorNo == 0) {
            $oResponse = xStatic::ToClass($cResponse);
        } else {
            $this->SetError(curl_error($ch));
        }
        curl_close($ch);
        if ($iErrorNo > 0) {
            return null;
        }

        if ($oResponse->bErrOn == true) {
            $this->SetError($oResponse->cMessage);
            return null;
        } else {
            $this->SetOK();
            return $oResponse->oData;
        }
    }
}
