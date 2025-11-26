<?php
//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 003 for TCPDF class
//               Custom Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
// require_once('tcpdf_include.php');


// Extend the TCPDF class to create custom Header and Footer
class tcpdf_print_rfid extends TCPDF {

	protected $starting_page_number = 1;

    //Page header
    public function Header($put_in=false, $result=array()) {
        $result = $this->custom_header_content;
		$form = $this->form;
        // $current_page = intval($this->getPage());
		if(in_array('途程單',$form)){

			$spacing = "";
			if($result['製令備註length']){
				$spacing = <<<EOD
					<span style="color:white">＿＿＿＿＿＿＿＿＿＿</span>
				EOD;
			}
			$html_process_information1 = <<<EOD
			
			<h5>
			<table border="none" style="width:100%">
				<tr >
					<td style="width:65%"></td>
					<td style="width:35%">圖號: {$result['圖號']}</td>
				</tr>
				<tr >
					<td style="width:10%">製令條碼 :</td>
					<td style="width:25%" rowspan="3">
						<tcpdf method="write1DBarcode" params="{$result['製令編號barcode']}" />
					</td>
					<td style="width:30%">
						<h2 style="text-align: center;">龍畿企業股份有限公司</h3>
					</td>
					<td style="width:25%" rowspan="3">
						<tcpdf method="write1DBarcode" params="{$result['圖號barcode']}" />
					</td>
				</tr>
				<tr ><td></td><td></td></tr>
				<tr ><td></td><td></td></tr>
				<tr >
					<td style="width:35%">製表日期: {$result["tmpdate"]}</td>
					<td style="width:30%">
						<h2 style="text-align: center;">途程單</h3>
					</td>
					<td style="width:15%"></td>
					<td style="width:20%">頁次: 
			EOD;
			$html_process_information2 = <<<EOD
					</td>
				</tr>
				<hr>
				<tr >
					<td style="width:25%">製令編號: {$result['製令編號_']}</td>
					<td style="width:30%">產品品號: {$result['產品品號']}</td>
					<td style="width:25%">生產廠別: {$result['生產廠別']}</td>
					<td style="width:20%">製令狀態: {$result['製令狀態']}</td>
				</tr>
				<tr >
					<td style="width:25%"></td>
					<td style="width:30%">品名<span style="color:white">＿＿</span>: {$result['MOCTA_品名']}</td>
					<td style="width:25%">入庫庫別: {$result['入庫庫別']}</td>
				</tr>
				<tr >
					<td style="width:25%"></td>
					<td style="width:30%">規格<span style="color:white">＿＿</span>: <span style="font-size:11px"></span></td>
					<td style="width:25%">預計產量: {$result['預計產量']}</td>
					<td style="width:20%">訂單數量: {$result['訂單數量']}</td>
				</tr>
				<tr >
					<td style="width:25%"></td>
					<td style="width:75%" colspan="3">{$result['規格']}</td>
				</tr>
				<tr >
					<td style="width:25%">開單日期: {$result['開單日期']}</td>
					<td style="width:30%">訂單編號: {$result['訂單編號']}</td>
					<td style="width:25%"></td>
					<td style="width:20%">客戶代號: {$result['客戶代號']}</td>
				</tr>
				<tr >
					<td style="width:100%" height="30">備註<span style="color:white">＿＿</span>: {$result['製令備註']}{$spacing}</td>
				</tr>
				<tr >
					<td style="width:100%"></td>
				</tr>
				</table>
				<hr>
				
				</h5>
				
				EOD;
				// $pdf->writeHTMLCell(0, 0, '', '', $html_process_information, 0, 1, 0, true, '', true);

				// $pdf->writeHTMLCell(0, 0, '', '', $html_process_information, 0, 1, 0, true, '', true);
				$html_header = <<<EOD
				<h5>
				<table  border="1" style="width:100%; margin-left:10px;" >
					<tr >
						<td style="width:12% ; text-align: center; " rowspan="2">
							<p >工序</p>
							<p >製程---生產線</p>
							
						</td>
						<td style="width:30% ; text-align: center;" rowspan="2">

							<p >加工順序條碼</p>
							<p >製程代號條碼</p>
						</td>
						<td style="width:10% ; text-align: center;" rowspan="2">

							<p >作業依據</p>
						</td>
						<td style="width:25% ; text-align: center;" colspan="4">

							<p >檢驗方式</p>
						</td>
						<td style="width:10% ; text-align: center;" colspan="2">
							<p >成品件數</p>
						</td>
						<td style="width:10% ; text-align: center;" rowspan="2">
							<p >作業人員</p>
							<p >簽名-日期</p>
						</td>
					</tr>
					<tr>
						<td style="width:5% ; text-align: cener;" >
							<p >檢驗項目</p>
						</td>
						<td style="width:5% ; text-align: cener;" >
							<p >首件檢查</p>
						</td>
						<td style="width:5% ; text-align: cener;" >
							<p >定期檢查</p>
						</td>
						<td style="width:10% ; text-align: cener;" >
							<p >稽核人員簽名-日期</p>
						</td>
						<td style="width:5% ; text-align: cener;" >
							<p >良好數</p>
						</td>
						<td style="width:5% ; text-align: cener;" >
							<p >不良數</p>
						</td>
					</tr>

				</table>
				</h5>
				EOD;
				// }
				// else{
				//     $html_process_information = "";
				//     $html_header = "";
				// }
				// Logo
				// $image_file = K_PATH_IMAGES.'logo_example.jpg';
				// $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
				// Set font
				$fontname = TCPDF_FONTS::addTTFfont(__DIR__ . DIRECTORY_SEPARATOR . '/fonts/droidsansfallback.ttf', 'TrueTypeUnicode', '', 96);

			$this->SetFont($fontname, '', 12, '', false);
			// Title
			// $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
			// if(intval($this->getPage()) > 2) $this->writeHTMLCell(0, 0, 6, '', $html_process_information . $html_header, 0, 1, 0, true, '', true);
			if($this->getMargins()["top"] === 91.8) {
				$this->setStartingPageNumber((-1 * intval($this->getPage())) + 3);
				$this->writeHTMLCell(0, 0, 6, '', $html_process_information1 . $this->getAliasNumPage() . " / " . $this->getAliasNbPages() . $html_process_information2 . $html_header, 0, 1, 0, true, '', true);
			}
		}
    }

    // Page footer
    public function Footer($put_in=false, $result=array()) {
        $result = $this->custom_header_content;
		$form = $this->form;
		if(in_array('途程單',$form)){
			// if($put_in){
			$html_footer = <<<EOD
			<br/ ><table border="none" style="width:100%">
				<tr>
					<td style="width:50%">核准 : 嚴永強</td>
					<td style="width:50%">經辦: {$result['經辦']}</td>
				</tr>
			</table>
			EOD;
			// }
			// else $html_footer = "";
			// Position at 15 mm from bottom
			// $this->SetY(-15);
			// Set font
			$fontname = TCPDF_FONTS::addTTFfont(__DIR__ . DIRECTORY_SEPARATOR . '/fonts/droidsansfallback.ttf', 'TrueTypeUnicode', '', 96);

			$this->SetFont($fontname, '', 12, '', false);
			// Page number
			// $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			// if(intval($this->getPage()) > 2) $this->writeHTMLCell(0, 0, '', '', $html_footer, 0, 1, 0, true, '', true);
			// if($this->getMargins()["top"] === 79) $this->writeHTMLCell(0, 0, '', '', $html_footer, 0, 1, 0, true, '', true);
			if($this->getFooterMargin() === 11) $this->writeHTMLCell(0, 0, '', '', $html_footer, 0, 1, 0, true, '', true);
		}
    }

	public function setStartingPageNumber($num=1) {
		$this->starting_page_number = intval($num);
	}

    public function setAutoPageBreak($auto, $margin=0) {
		$this->AutoPageBreak = $auto ? true : false;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

    public function getAutoPageBreak() {
		return $this->AutoPageBreak;
	}

    public function AcceptPageBreak() {
		if ($this->num_columns > 1) {
			// multi column mode
			if ($this->current_column < ($this->num_columns - 1)) {
				// go to next column
				$this->selectColumn($this->current_column + 1);
			} elseif ($this->AutoPageBreak) {
				// add a new page
				$this->AddPage();
				// $this->AddPage();
				// set first column
				$this->selectColumn(0);
			}
			// avoid page breaking from checkPageBreak()
			return false;
		}
		return $this->AutoPageBreak;
	}

    protected function checkPageBreak($h=0, $y=null, $addpage=true) {
		if (TCPDF_STATIC::empty_string($y)) {
			$y = $this->y;
		}
		$current_page = $this->page;
		if ((($y + $h) > $this->PageBreakTrigger) AND ($this->inPageBody()) AND ($this->AcceptPageBreak())) {
			if ($addpage) {
				//Automatic page break
				$x = $this->x;
				$this->AddPage($this->CurOrientation);
				$this->y = $this->tMargin;
				$oldpage = $this->page - 1;
				if ($this->rtl) {
					if ($this->pagedim[$this->page]['orm'] != $this->pagedim[$oldpage]['orm']) {
						$this->x = $x - ($this->pagedim[$this->page]['orm'] - $this->pagedim[$oldpage]['orm']);
					} else {
						$this->x = $x;
					}
				} else {
					if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
						$this->x = $x + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$oldpage]['olm']);
					} else {
						$this->x = $x;
					}
				}
			}
			return true;
		}
		if ($current_page != $this->page) {
			// account for columns mode
			return true;
		}
		return false;
	}






    public function Output($name='doc.pdf', $dest='I') {
		//Output PDF to some destination
		//Finish document if necessary
		if ($this->state < 3) {
			$this->Close();
		}
		//Normalize parameters
		if (is_bool($dest)) {
			$dest = $dest ? 'D' : 'F';
		}
		$dest = strtoupper($dest);
		// if ($dest[0] != 'F') {
		// 	$name = preg_replace('/[\s]+/', '_', $name);
		// 	$name = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $name);
		// }
		if ($this->sign) {
			// *** apply digital signature to the document ***
			// get the document content
			$pdfdoc = $this->getBuffer();
			// remove last newline
			$pdfdoc = substr($pdfdoc, 0, -1);
			// remove filler space
			$byterange_string_len = strlen(TCPDF_STATIC::$byterange_string);
			// define the ByteRange
			$byte_range = array();
			$byte_range[0] = 0;
			$byte_range[1] = strpos($pdfdoc, TCPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
			$byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
			$byte_range[3] = strlen($pdfdoc) - $byte_range[2];
			$pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
			// replace the ByteRange
			$byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
			$byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
			$pdfdoc = str_replace(TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
			// write the document to a temporary folder
			$tempdoc = TCPDF_STATIC::getObjFilename('doc', $this->file_id);
			$f = TCPDF_STATIC::fopenLocal($tempdoc, 'wb');
			if (!$f) {
				$this->Error('Unable to create temporary file: '.$tempdoc);
			}
			$pdfdoc_length = strlen($pdfdoc);
			fwrite($f, $pdfdoc, $pdfdoc_length);
			fclose($f);
			// get digital signature via openssl library
			$tempsign = TCPDF_STATIC::getObjFilename('sig', $this->file_id);
			if (empty($this->signature_data['extracerts'])) {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
			} else {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
			}
			// read signature
			$signature = file_get_contents($tempsign);
			// extract signature
			$signature = substr($signature, $pdfdoc_length);
			$signature = substr($signature, (strpos($signature, "%%EOF\n\n------") + 13));
			$tmparr = explode("\n\n", $signature);
			$signature = $tmparr[1];
			// decode signature
			$signature = base64_decode(trim($signature));
			// add TSA timestamp to signature
			$signature = $this->applyTSA($signature);
			// convert signature to hex
			$signature = current(unpack('H*', $signature));
			$signature = str_pad($signature, $this->signature_max_length, '0');
			// Add signature to the document
			$this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
			$this->bufferlen = strlen($this->buffer);
		}
		switch($dest) {
			case 'I': {
				// Send PDF to the standard output
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				if (php_sapi_name() != 'cli') {
					// send output to a browser
					header('Content-Type: application/pdf');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				} else {
					echo $this->getBuffer();
				}
				break;
			}
			case 'D': {
				// download PDF as file
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				header('Content-Description: File Transfer');
				if (headers_sent()) {
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				}
				header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
				//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				// force download dialog
				if (strpos(php_sapi_name(), 'cgi') === false) {
					header('Content-Type: application/force-download');
					header('Content-Type: application/octet-stream', false);
					header('Content-Type: application/download', false);
					header('Content-Type: application/pdf', false);
				} else {
					header('Content-Type: application/pdf');
				}
				// use the Content-Disposition header to supply a recommended filename
				header('Content-Disposition: attachment; filename="'.$name.'"');
				header('Content-Transfer-Encoding: binary');
				TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			}
			case 'F':
			case 'FI':
			case 'FD': {
				// save PDF to a local file
				$f = TCPDF_STATIC::fopenLocal($name, 'wb');
				if (!$f) {
					$this->Error('Unable to create output file: '.$name);
				}
				fwrite($f, $this->getBuffer(), $this->bufferlen);
				fclose($f);
				if ($dest == 'FI') {
					// send headers to browser
					header('Content-Type: application/pdf');
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				} elseif ($dest == 'FD') {
					// send headers to browser
					if (ob_get_contents()) {
						$this->Error('Some data has already been output, can\'t send PDF file');
					}
					header('Content-Description: File Transfer');
					if (headers_sent()) {
						$this->Error('Some data has already been output to browser, can\'t send PDF file');
					}
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					// force download dialog
					if (strpos(php_sapi_name(), 'cgi') === false) {
						header('Content-Type: application/force-download');
						header('Content-Type: application/octet-stream', false);
						header('Content-Type: application/download', false);
						header('Content-Type: application/pdf', false);
					} else {
						header('Content-Type: application/pdf');
					}
					// use the Content-Disposition header to supply a recommended filename
					header('Content-Disposition: attachment; filename="'.basename($name).'"');
					header('Content-Transfer-Encoding: binary');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				}
				break;
			}
			case 'E': {
				// return PDF as base64 mime multi-part email attachment (RFC 2045)
				$retval = 'Content-Type: application/pdf;'."\r\n";
				$retval .= ' name="'.$name.'"'."\r\n";
				$retval .= 'Content-Transfer-Encoding: base64'."\r\n";
				$retval .= 'Content-Disposition: attachment;'."\r\n";
				$retval .= ' filename="'.$name.'"'."\r\n\r\n";
				$retval .= chunk_split(base64_encode($this->getBuffer()), 76, "\r\n");
				return $retval;
			}
			case 'S': {
				// returns PDF as a string
				return $this->getBuffer();
			}
			default: {
				$this->Error('Incorrect output destination: '.$dest);
			}
		}
		return '';
	}


    // public function write1DBarcode($code, $type, $x=null, $y=null, $w=null, $h=null, $xres=null, $style=array(), $align='') {
	// 	if (TCPDF_STATIC::empty_string(trim($code))) {
	// 		return;
	// 	}
	// 	require_once(dirname(__FILE__).'/tcpdf_barcodes_1d.php');
	// 	// save current graphic settings
	// 	$gvars = $this->getGraphicVars();
	// 	// create new barcode object
	// 	$barcodeobj = new TCPDFBarcode($code, $type);
	// 	$arrcode = $barcodeobj->getBarcodeArray();
	// 	if (empty($arrcode) OR ($arrcode['maxw'] <= 0)) {
	// 		$this->Error('Error in 1D barcode string');
	// 	}
	// 	if ($arrcode['maxh'] <= 0) {
	// 		$arrcode['maxh'] = 1;
	// 	}
	// 	// set default values
	// 	if (!isset($style['position'])) {
	// 		$style['position'] = '';
	// 	} elseif ($style['position'] == 'S') {
	// 		// keep this for backward compatibility
	// 		$style['position'] = '';
	// 		$style['stretch'] = true;
	// 	}
	// 	if (!isset($style['fitwidth'])) {
	// 		if (!isset($style['stretch'])) {
	// 			$style['fitwidth'] = true;
	// 		} else {
	// 			$style['fitwidth'] = false;
	// 		}
	// 	}
	// 	if ($style['fitwidth']) {
	// 		// disable stretch
	// 		$style['stretch'] = false;
	// 	}
	// 	if (!isset($style['stretch'])) {
	// 		if (($w === '') OR ($w <= 0)) {
	// 			$style['stretch'] = false;
	// 		} else {
	// 			$style['stretch'] = true;
	// 		}
	// 	}
	// 	if (!isset($style['fgcolor'])) {
	// 		$style['fgcolor'] = array(0,0,0); // default black
	// 	}
	// 	if (!isset($style['bgcolor'])) {
	// 		$style['bgcolor'] = false; // default transparent
	// 	}
	// 	if (!isset($style['border'])) {
	// 		$style['border'] = false;
	// 	}
	// 	$fontsize = 0;
	// 	if (!isset($style['text'])) {
	// 		$style['text'] = false;
	// 	}
	// 	if ($style['text'] AND isset($style['font'])) {
	// 		if (isset($style['fontsize'])) {
	// 			$fontsize = $style['fontsize'];
	// 		}
	// 		$this->setFont($style['font'], '', $fontsize);
	// 	}
	// 	if (!isset($style['stretchtext'])) {
	// 		$style['stretchtext'] = 4;
	// 	}
	// 	if (TCPDF_STATIC::empty_string($x)) {
	// 		$x = $this->x;
	// 	}
	// 	if (TCPDF_STATIC::empty_string($y)) {
	// 		$y = $this->y;
	// 	}
	// 	// check page for no-write regions and adapt page margins if necessary
	// 	list($x, $y) = $this->checkPageRegions($h, $x, $y);
	// 	if (TCPDF_STATIC::empty_string($w) OR ($w <= 0)) {
	// 		if ($this->rtl) {
	// 			$w = $x - $this->lMargin;
	// 		} else {
	// 			$w = $this->w - $this->rMargin - $x;
	// 		}
	// 	}
	// 	// padding
	// 	if (!isset($style['padding'])) {
	// 		$padding = 0;
	// 	} elseif ($style['padding'] === 'auto') {
	// 		$padding = 10 * ($w / ($arrcode['maxw'] + 20));
	// 	} else {
	// 		$padding = floatval($style['padding']);
	// 	}
	// 	// horizontal padding
	// 	if (!isset($style['hpadding'])) {
	// 		$hpadding = $padding;
	// 	} elseif ($style['hpadding'] === 'auto') {
	// 		$hpadding = 10 * ($w / ($arrcode['maxw'] + 20));
	// 	} else {
	// 		$hpadding = floatval($style['hpadding']);
	// 	}
	// 	// vertical padding
	// 	if (!isset($style['vpadding'])) {
	// 		$vpadding = $padding;
	// 	} elseif ($style['vpadding'] === 'auto') {
	// 		$vpadding = ($hpadding / 2);
	// 	} else {
	// 		$vpadding = floatval($style['vpadding']);
	// 	}
	// 	// calculate xres (single bar width)
	// 	$max_xres = ($w - (2 * $hpadding)) / $arrcode['maxw'];
	// 	if ($style['stretch']) {
	// 		$xres = $max_xres;
	// 	} else {
	// 		if (TCPDF_STATIC::empty_string($xres)) {
	// 			$xres = (0.141 * $this->k); // default bar width = 0.4 mm
	// 		}
	// 		if ($xres > $max_xres) {
	// 			// correct xres to fit on $w
	// 			$xres = $max_xres;
	// 		}
	// 		if ((isset($style['padding']) AND ($style['padding'] === 'auto'))
	// 			OR (isset($style['hpadding']) AND ($style['hpadding'] === 'auto'))) {
	// 			$hpadding = 10 * $xres;
	// 			if (isset($style['vpadding']) AND ($style['vpadding'] === 'auto')) {
	// 				$vpadding = ($hpadding / 2);
	// 			}
	// 		}
	// 	}
	// 	if ($style['fitwidth']) {
	// 		$wold = $w;
	// 		$w = (($arrcode['maxw'] * $xres) + (2 * $hpadding));
	// 		if (isset($style['cellfitalign'])) {
	// 			switch ($style['cellfitalign']) {
	// 				case 'L': {
	// 					if ($this->rtl) {
	// 						$x -= ($wold - $w);
	// 					}
	// 					break;
	// 				}
	// 				case 'R': {
	// 					if (!$this->rtl) {
	// 						$x += ($wold - $w);
	// 					}
	// 					break;
	// 				}
	// 				case 'C': {
	// 					if ($this->rtl) {
	// 						$x -= (($wold - $w) / 2);
	// 					} else {
	// 						$x += (($wold - $w) / 2);
	// 					}
	// 					break;
	// 				}
	// 				default : {
	// 					break;
	// 				}
	// 			}
	// 		}
	// 	}
	// 	$text_height = $this->getCellHeight($fontsize / $this->k);
	// 	// height
	// 	if (TCPDF_STATIC::empty_string($h) OR ($h <= 0)) {
	// 		// set default height
	// 		$h = (($arrcode['maxw'] * $xres) / 3) + (2 * $vpadding) + $text_height;
	// 	}
	// 	$barh = $h - $text_height - (2 * $vpadding);
	// 	if ($barh <=0) {
	// 		// try to reduce font or padding to fit barcode on available height
	// 		if ($text_height > $h) {
	// 			$fontsize = (($h * $this->k) / (4 * $this->cell_height_ratio));
	// 			$text_height = $this->getCellHeight($fontsize / $this->k);
	// 			$this->setFont($style['font'], '', $fontsize);
	// 		}
	// 		if ($vpadding > 0) {
	// 			$vpadding = (($h - $text_height) / 4);
	// 		}
	// 		$barh = $h - $text_height - (2 * $vpadding);
	// 	}
	// 	// fit the barcode on available space
	// 	list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, false);
	// 	// set alignment
	// 	$this->img_rb_y = $y + $h;
	// 	// set alignment
	// 	if ($this->rtl) {
	// 		if ($style['position'] == 'L') {
	// 			$xpos = $this->lMargin;
	// 		} elseif ($style['position'] == 'C') {
	// 			$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
	// 		} elseif ($style['position'] == 'R') {
	// 			$xpos = $this->w - $this->rMargin - $w;
	// 		} else {
	// 			$xpos = $x - $w;
	// 		}
	// 		$this->img_rb_x = $xpos;
	// 	} else {
	// 		if ($style['position'] == 'L') {
	// 			$xpos = $this->lMargin;
	// 		} elseif ($style['position'] == 'C') {
	// 			$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
	// 		} elseif ($style['position'] == 'R') {
	// 			$xpos = $this->w - $this->rMargin - $w;
	// 		} else {
	// 			$xpos = $x;
	// 		}
	// 		$this->img_rb_x = $xpos + $w;
	// 	}
	// 	$xpos_rect = $xpos;
	// 	if (!isset($style['align'])) {
	// 		$style['align'] = 'C';
	// 	}
	// 	switch ($style['align']) {
	// 		case 'L': {
	// 			$xpos = $xpos_rect + $hpadding;
	// 			break;
	// 		}
	// 		case 'R': {
	// 			$xpos = $xpos_rect + ($w - ($arrcode['maxw'] * $xres)) - $hpadding;
	// 			break;
	// 		}
	// 		case 'C':
	// 		default : {
	// 			$xpos = $xpos_rect + (($w - ($arrcode['maxw'] * $xres)) / 2);
	// 			break;
	// 		}
	// 	}
	// 	$xpos_text = $xpos;
	// 	// barcode is always printed in LTR direction
	// 	$tempRTL = $this->rtl;
	// 	$this->rtl = false;
	// 	// print background color
	// 	if ($style['bgcolor']) {
	// 		$this->Rect($xpos_rect, $y, $w, $h, $style['border'] ? 'DF' : 'F', '', $style['bgcolor']);
	// 	} elseif ($style['border']) {
	// 		$this->Rect($xpos_rect, $y, $w, $h, 'D');
	// 	}
	// 	// set foreground color
	// 	$this->setDrawColorArray($style['fgcolor']);
	// 	$this->setTextColorArray($style['fgcolor']);
	// 	// print bars
	// 	foreach ($arrcode['bcode'] as $k => $v) {
	// 		$bw = ($v['w'] * $xres);
	// 		if ($v['t']) {
	// 			// draw a vertical bar
	// 			$ypos = $y + $vpadding + ($v['p'] * $barh / $arrcode['maxh']);
	// 			$this->Rect($xpos, $ypos, $bw, ($v['h'] * $barh / $arrcode['maxh']), 'F', array(), $style['fgcolor']);
	// 		}
	// 		$xpos += $bw;
	// 	}
	// 	// print text
	// 	if ($style['text']) {
	// 		if (isset($style['label']) AND !TCPDF_STATIC::empty_string($style['label'])) {
	// 			$label = $style['label'];
	// 		} else {
	// 			$label = $code;
	// 		}
	// 		$txtwidth = ($arrcode['maxw'] * $xres);
	// 		if ($this->GetStringWidth($label) > $txtwidth) {
	// 			$style['stretchtext'] = 2;
	// 		}
	// 		// print text
	// 		$this->x = $xpos_text;
	// 		$this->y = $y + $vpadding + $barh;
	// 		$cellpadding = $this->cell_padding;
	// 		$this->setCellPadding(0);
	// 		$this->Cell($txtwidth, 0, $label, 0, 0, 'C', false, '', $style['stretchtext'], false, 'T', 'T');
	// 		$this->cell_padding = $cellpadding;
	// 	}
	// 	// restore original direction
	// 	$this->rtl = $tempRTL;
	// 	// restore previous settings
	// 	$this->setGraphicVars($gvars);
	// 	// set pointer to align the next text/objects
	// 	switch($align) {
	// 		case 'T':{
	// 			$this->y = $y;
	// 			$this->x = $this->img_rb_x;
	// 			break;
	// 		}
	// 		case 'M':{
	// 			$this->y = $y + round($h / 2);
	// 			$this->x = $this->img_rb_x;
	// 			break;
	// 		}
	// 		case 'B':{
	// 			$this->y = $this->img_rb_y;
	// 			$this->x = $this->img_rb_x;
	// 			break;
	// 		}
	// 		case 'N':{
	// 			$this->setY($this->img_rb_y);
	// 			break;
	// 		}
	// 		default:{
	// 			break;
	// 		}
	// 	}
	// 	$this->endlinex = $this->img_rb_x;
	// }
}

// $pdf = new TCPDF_chinese('L', PDF_UNIT, "A4", true, 'UTF-8', false);

// // set document information
// $pdf->SetCreator(PDF_CREATOR);
// $pdf->SetAuthor('mil');
// $pdf->SetTitle("製令單");
// $pdf->SetSubject('製令單pdf');
// $pdf->SetKeywords('TCPDF, PDF, mil');

// // set default header data
// // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
// // $pdf->SetHeaderData(array(0,64,255), array(0,64,128));
// // $pdf->setFooterData(array(0,64,0), array(0,64,128));

// // remove default header/footer
// $pdf->setPrintHeader(false);
// // $pdf->setPrintFooter(false);

// // set header and footer fonts
// $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
// $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// // set default monospaced font
// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// // set margins
// $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
// // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// // set auto page breaks
// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// // set image scale factor
// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// // set some language-dependent strings (optional)
// if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
//     require_once(dirname(__FILE__) . '/lang/eng.php');
//     $pdf->setLanguageArray($l);
// }

// // ---------------------------------------------------------

// // set default font subsetting mode
// $pdf->setFontSubsetting(true);

// // Set font
// // dejavusans is a UTF-8 Unicode font, if you only need to
// // print standard ASCII chars, you can use core fonts like
// // helvetica or times to reduce file size.
// // $pdf->SetFont('dejavusans', '', 14, '', true);

// // Set font
// $fontname = TCPDF_FONTS::addTTFfont(__DIR__ . DIRECTORY_SEPARATOR . '/fonts/droidsansfallback.ttf', 'TrueTypeUnicode', '', 96);

// // $pdf->addTTFfont('/Users/laichuanen/droidsansfallback.ttf'); 
// $pdf->SetFont($fontname, '', 12, '', false);
// // $pdf->SetFont('msungstdlight', '', 12);

// // 設定資料與頁面上方的間距 (依需求調整第二個參數即可)
// $pdf->SetMargins(5, 5, 5);






// create new PDF document
// $pdf = new tcpdf_print_rfid(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// // set document information
// $pdf->SetCreator(PDF_CREATOR);
// $pdf->SetAuthor('Nicola Asuni');
// $pdf->SetTitle('TCPDF Example 003');
// $pdf->SetSubject('TCPDF Tutorial');
// $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// // set default header data
// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// // set header and footer fonts
// $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
// $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// // set default monospaced font
// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// // set margins
// $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
// $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// // set auto page breaks
// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// // set image scale factor
// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// // set some language-dependent strings (optional)
// if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
//     require_once(dirname(__FILE__).'/lang/eng.php');
//     $pdf->setLanguageArray($l);
// }

// // ---------------------------------------------------------

// // set font
// $pdf->SetFont('times', 'BI', 12);

// // add a page
// $pdf->AddPage();

// // set some text to print
// $txt = <<<EOD
// TCPDF Example 003
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// TCPDF
// Custom page header and footer are defined by extending the TCPDF class and overriding the Header() and Footer() methods.
// EOD;

// // print a block of text using Write()
// $pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

// // ---------------------------------------------------------

// //Close and output PDF document
// $pdf->Output('example_003.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+