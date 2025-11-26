<?php

use \Psr\Container\ContainerInterface;
use \Slim\Views\PhpRenderer;
use Slim\Http\UploadedFile;

class ModelController extends Controller
{
    protected $container;
    protected $router;
    protected $routes;
    public function __construct()
    {
        global $container;
        $this->container = $container;
        $this->router = $this->container->get('router');
        $this->routes = $this->router->getRoutes();
        // IP camera 網址
        // $this->ip_webcam = 'rtsp://admin:123456@59.125.135.181:21254/stream1';
    }

    // 00001 頁面render
    public function renderModel($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/system/model.html', []);
    }
    // 00002 壓縮及處理圖片
    function compressImage($source = false, $destination = false, $quality = 80, $filters = false)
    {
        $info = getimagesize($source);
        switch ($info['mime']) {
            case 'image/jpeg':
                /* Quality: integer 0 - 100 */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromjpeg($source);

            case 'image/gif':
                return imagecreatefromgif($source);

            case 'image/png':
                /* Quality: Compression integer 0(none) - 9(max) */
                if (!is_int($quality) or $quality < 0 or $quality > 9) $quality = 6;
                return imagecreatefrompng($source);

            case 'image/webp':
                /* Quality: Compression 0(lowest) - 100(highest) */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromwebp($source);

            case 'image/bmp':
                /* Quality: Boolean for compression */
                if (!is_bool($quality)) $quality = true;
                return imagecreatefrombmp($source);

            default:
                return;
        }
    }
    // 00003 拿取多國語言管理
    public function get_language_manage($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);
        $result = $Component->get_language_manage($params);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00004 新增多國語言管理
    public function post_language_manage($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->post_language_manage($params, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00005 修改多國語言管理
    public function patch_language_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->patch_language_manage($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00006 刪除多國語言管理
    public function delete_language_manage($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $Component = new component($this->container->db);
        $last_edit_user_id = $_SESSION['id'];
        $result = $Component->delete_language_manage($data, $last_edit_user_id);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00007 拿取檔案
    public function get_file($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $Component = new component($this->container->db);

        $result = $Component->get_file_name($params);
        foreach ($result as $result_inner) {
            $result = $result_inner;
        }
        $diectory = $this->container->upload_directory;
        $file = $diectory . '/' . $result['file_name'];
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="' . basename($result['file_client_name']) . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', filesize($file));
        readfile($file);

        return $response;
    }
    // 00008 新增檔案
    public function post_file($request, $response, $args)
    {
        $data = $request->getParams();
        $data['files'] = $request->getUploadedFiles();
        $Component = new component($this->container->db);
        $file = $Component->uploadFile($data);
        unset($data['files']);
        $file['user_id'] = 0;

        $data['file_id'] = $Component->insertFile($file);
        if ($data['file_id'] == '' || $data['file_id'] == null) {
            $result['status'] = 'failed';
        } else {
            $result['file_id'] = $data['file_id'];
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    // 00009 影像重塑
    function PIPHP_ImageResize($image, $w, $h)
    {
        $oldw = imagesx($image);
        $oldh = imagesy($image);
        $temp = imagecreatetruecolor($w, $h);
        imagecopyresampled($temp, $image, 0, 0, 0, 0, $w, $h, $oldw, $oldh);
        return $temp;
    }
    // 00010 PDF範例製作
    public function post_pdf($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $oms = new oms($this->container->db);
        $values = [
            "process_header" => '',
            "process_body" => '',
            "process_footer" => '',
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }


        // create new PDF document
        $pdf = new TCPDF("P", PDF_UNIT, 'B4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('System');
        $pdf->SetTitle("pdf");
        $pdf->SetSubject('tmp pdf');
        $pdf->SetKeywords('TCPDF, PDF, pdf');

        // set default header data
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        // $pdf->SetHeaderData(array(0,64,255), array(0,64,128));
        // $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        // $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(0);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 12);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        // $pdf->SetFont('dejavusans', '', 14, '', true);

        // Set font
        $fontname = TCPDF_FONTS::addTTFfont(__DIR__ . DIRECTORY_SEPARATOR . '/fonts/droidsansfallback.ttf', 'TrueTypeUnicode', '', 96);

        // $pdf->addTTFfont('/Users/laichuanen/droidsansfallback.ttf'); 
        $pdf->SetFont($fontname, '', 10, '', false);
        // $pdf->SetFont('msungstdlight', '', 12);

        // 設定資料與頁面上方的間距 (依需求調整第二個參數即可)
        $pdf->SetMargins(10, 5, 5);

        $pdf->setCellHeightRatio(1.1);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        // $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // Set some content to print
        $html = <<<EOD
                {$values['process_header']}
        EOD;
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->AddPage();
        $html = <<<EOD
                {$values['process_body']}
        EOD;
        // {$values['process_body']}
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->AddPage();
        $html = <<<EOD
                {$values['process_footer']}
        EOD;
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);


        // set text shadow effect
        // $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // Set some content to print
        // $html = <<<EOD
        //     {$values['process_body']}
        // EOD;
        // $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        // $pdf->AddPage();

        // set text shadow effect
        // $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // Set some content to print
        // $html = <<<EOD
        //     {$values['process_footer']}
        // EOD;
        // $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // ---------------------------------------------------------

        $file_name = strval("tmp.pdf");
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output($file_name, 'D');
    }
    // 00011 EXCEL範例製作
    public function get_excel($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $values = [
            "customer_order_id" => '',
            "items" => []
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $oms = new oms($this->container->db);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row_count = 3;
        $rowArray = [
            ["", "", "", "途程單(Traveler Card)", "", "", "", ""],
            ["客戶", "", "品號", implode(',', array_map(function ($map_item) {
                return $map_item['item_no'];
            }, $values['items'])), "版別", "", "採購單號", $values["customer_order_id"]],
            ["材質", "", "入廠數量", "", "預計出貨日", "", "", ""]
        ];

        $spreadsheet->getActiveSheet()->fromArray($rowArray, NULL, 'A1');
        $spreadsheet->getActiveSheet()->getStyle("A1:H{$row_count}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="途程單匯出.xlsx"');
        return $response;
    }
    // 00012 檢查是否為JSON
    public function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    // 00013 簡易回傳
    public function response_return($response, $data)
    {
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($data);
        return $response;
    }
    // 00014 上傳檔案
    function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }
    // 00015 製作縮圖
    //Create thumbnail when the user successfully upload a video.
    public function create_thumbnail($video_information)
    {
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, 'jpg');
        foreach ($video_information as $key => $value) {
            $ffmpeg = FFMpeg\FFMpeg::create([
                'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/local/bin/ffprobe'
            ]);
            // $ffprobe = FFMpeg\FFProbe::create();

            $video = $this->container->upload_directory . DIRECTORY_SEPARATOR . $value['video_file_name'];
            // $video_dimensions = $ffprobe
            //     ->streams($video)   
            //     ->videos()
            //     ->first()
            //     ->getDimensions();
            // $width = $video_dimensions->getWidth();
            // $height = $video_dimensions->getHeight();
            // var_dump($width, $height);

            $video = new FFMpeg\Media\Video($video, $ffmpeg->getFFMpegDriver(), $ffmpeg->getFFProbe());
            $video
                ->filters()
                // ->resize(new FFMpeg\Coordinate\Dimension(320, 240), new FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_FIT, true)
                ->resize(new FFMpeg\Coordinate\Dimension(320, 240))
                // ->resize(new FFMpeg\Coordinate\Dimension($width, $height))
                ->synchronize();

            $video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
                ->save($this->container->upload_directory . DIRECTORY_SEPARATOR . "{$filename}");
        }
        return $filename;
    }
    // 00016 串流
    //Download the video and output as streaming video.
    private function rangeDownload($file)
    {

        $fp = @fopen($file, 'rb');

        $size   = filesize($file); // File size
        $length = $size;           // Content length
        $start  = 0;               // Start byte
        $end    = $size - 1;       // End byte
        $contenttype = mime_content_type($file);
        // Now that we've gotten so far without errors we send the accept range header
        /* At the moment we only support single ranges.
	 * Multiple ranges requires some more work to ensure it works correctly
	 * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
	 *
	 * Multirange support annouces itself with:
	 * header('Accept-Ranges: bytes');
	 *
	 * Multirange content must be sent with multipart/byteranges mediatype,
	 * (mediatype = mimetype)
	 * as well as a boundry header to indicate the various chunks of data.
	 */
        header("Accept-Ranges: 0-$length");
        // header('Accept-Ranges: bytes');
        // multipart/byteranges
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end   = $end;
            // Extract the range string
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            // Make sure the client hasn't sent us a multibyte range
            if (strpos($range, ',') !== false) {
                // (?) Shoud this be issued here, or should the first
                // range be used? Or should the header be ignored and
                // we output the whole content?
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            }
            // If the range starts with an '-' we start from the beginning
            // If not, we forward the file pointer
            // And make sure to get the end byte if spesified
            if ($range[0] == '-') {
                // The n-number of the last bytes is requested
                $c_start = $size - substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }
            /* Check the range and make sure it's treated according to the specs.
		 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
		 */
            // End bytes can not be larger than $end.
            $c_end = ($c_end > $end) ? $end : $c_end;
            // Validate the requested range and return an error if it's not correct.
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            }
            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1; // Calculate new content length
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }
        // Notify the client the byte range we'll be outputting
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");
        header("Content-Type: $contenttype");

        // Start buffered download
        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                // In case we're only outputtin a chunk, make sure we don't
                // read past the length
                $buffer = $end - $p + 1;
            }
            set_time_limit(0); // Reset time limit for big files
            echo fread($fp, $buffer);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        }

        fclose($fp);
    }
    // 00017 更加的串流使用方法
    //Get preview the uploaded video.
    public function get_preview_specific_video_or_file($request, $response, $args)
    {
        $data = $args;
        $stream = $this->container->upload_directory . "/" . $data['file_name'];
        $video = new VideoStream($stream);
        $video->start();
        // $response = $this->rangeDownload($stream);
        // return $response;
    }
    // 00018 拿取攝影機影像
    public function getCamera($request, $response, $args)
    {
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, 'jpg');
        $filename = $this->container->upload_directory . DIRECTORY_SEPARATOR . $filename;
        $ffmpeg = FFMpeg\FFMpeg::create([
            'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/local/bin/ffprobe'
        ]);
        // $video = $ffmpeg->open('rtsp://admin:admin@192.168.2.202:554/');
        shell_exec("/usr/local/bin/ffmpeg -rtsp_transport tcp -i {$this->ip_webcam} -frames 1 {$filename}");

        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'immage/jpg')
            ->withHeader('Content-Disposition', 'attachment;filename="' . $filename . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', filesize($filename));
        ob_clean();
        ob_end_flush();
        $source = imagecreatefromjpeg($filename);
        imagejpeg($source);
        unlink($filename);
        return $response;
    }
    // 00019 更新swagger文件
    public function swagger_generate($request, $response, $args)
    {
        try {
            // 從 Model 中生成 Swagger JSON
            $Model = new Model($this->container->db);
            $swaggerJson = $Model->generateSwaggerJson($this->routes, $this->container);

            // 將 JSON 寫入檔案
            $filePath = __DIR__ . '/../swagger/swagger.json'; // 設定檔案路徑
            file_put_contents($filePath, json_encode($swaggerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // 返回格式化的 JSON
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200)
                ->write(json_encode([
                    'message' => 'Swagger JSON generated successfully',
                    'file_path' => $filePath,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            // 捕獲異常並返回錯誤
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    }
    // 00020 獲得樣本輸入
    public function getSampleInput($methodName)
    {
        return $this->samples[$methodName]['input'] ?? null;
    }

    // 00021 獲得樣本输出
    public function getSampleOutput($methodName)
    {
        return $this->samples[$methodName]['output'] ?? null;
    }

    // 00022 動態載入範例資料
    public function loadSamples($custom_container)
    {
        // 取得目前類別中的所有方法名稱
        $methods = get_class_methods($custom_container);

        foreach ($methods as $method) {
            // 篩選以 "Sample" 結尾的方法
            if (str_ends_with($method, 'Sample')) {
                // 呼叫該方法並合併到 $samples
                $custom_container->samples = array_merge($custom_container->samples ? $custom_container->samples : [], $custom_container->$method());
            }
        }
    }
}
