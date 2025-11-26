<?php
namespace nknu\utility;
use nknu\base\xBase;
class xFile extends xBase {
	public static function IsUTF8Bytes($data) : bool {
		$charByteCounter = 1;
		foreach ($data as $key => $curByte) {
			//$curByte = $data[$i];
			if ($charByteCounter == 1) {
				if ($curByte >= 0x80) {
					while ((($curByte <<= 1) & 0x80) != 0) {
						$charByteCounter++;
					}

					if ($charByteCounter == 1 || $charByteCounter > 6) { return false; }
				}
			} else {
				if (($curByte & 0xC0) != 0x80) { return false; }
				$charByteCounter--;
			}
		}
		if ($charByteCounter > 1) { throw new \Exception("Error byte format"); }
		return true;
	}
}

/*
<%@ Page Language="C#" Inherits="nknu.classPageBase" %>
<%@ Import  Namespace="System.Data" %>
<%@ Import  Namespace="System.Linq" %>
<%@ Import  Namespace="System.Linq.Dynamic" %>

<script runat="server">
	public class EncodingType {
		public static nknu.xResponse<(byte[] aBytes, System.Text.Encoding oEncoding)> ReadFileBytes(string cPathFile) {
			nknu.xResponse<(byte[] aBytes, System.Text.Encoding oEncoding)> oReturn;
			using (var fs = new System.IO.FileStream(cPathFile, System.IO.FileMode.Open, System.IO.FileAccess.Read)) {
				oReturn = ReadFileBytes(fs);
				fs.Close();
			}
			return oReturn;
		}

		public static nknu.xResponse<(byte[] aBytes, System.Text.Encoding oEncoding)> ReadFileBytes(System.IO.FileStream fs) {
			byte[] aBytes = null; {
				using (var br = new System.IO.BinaryReader(fs)) {
					var iLength = int.TryParse(fs.Length.ToString(), out var iTemp) ? iTemp : 0;
					if (iLength > 0) {
						aBytes = br.ReadBytes(iLength);
					}
					br.Close();
				}
			} if (aBytes == null) { return new nknu.xResponse<(byte[] aBytes, Encoding oEncoding)>() { bErrOn = true, cMessage = "ɮפӤj", oData = (aBytes, Encoding.Default) }; }
			if (aBytes.Length >= 3) {
				var b0 = aBytes[0]; var b1 = aBytes[1]; var b2 = aBytes[2];
				if ((b0 == 0xEF && b1 == 0xBB && b2 == 0xBF)) { return new nknu.xResponse<(byte[] aBytes, Encoding oEncoding)>() { bErrOn = false, oData = (aBytes, Encoding.UTF8) }; }
				if (b0 == 0xFE && b1 == 0xFF && b2 == 0x00) { return new nknu.xResponse<(byte[] aBytes, Encoding oEncoding)>() { bErrOn = false, oData = (aBytes, Encoding.BigEndianUnicode) }; }
				if (b0 == 0xFF && b1 == 0xFE && b2 == 0x41) { return new nknu.xResponse<(byte[] aBytes, Encoding oEncoding)>() { bErrOn = false, oData = (aBytes, Encoding.Unicode) }; }
			}
			if (IsUTF8Bytes(aBytes)) { return new nknu.xResponse<(byte[] aBytes, Encoding oEncoding)>() { bErrOn = false, oData = (aBytes, Encoding.UTF8) }; }
			return new nknu.xResponse<(byte[] aBytes, Encoding oEncoding)>() { bErrOn = false, oData = (aBytes, Encoding.Default) };
		}

		
	
	

*/
