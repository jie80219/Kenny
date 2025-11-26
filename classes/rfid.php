<?php

use nknu\base\xBaseWithDbop;
use nknu\extend\xString;

class RFID extends xBaseWithDbop
{
    protected $container;
    protected $db;
    protected $db_sqlsrv;
    protected $sync_url;
    protected $cServer;
    protected $cUrl_RFID;
    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->container = $container;
        $this->db = $container->db;
        $this->db_sqlsrv = $container->db_sqlsrv;
        $this->sync_url = "http://localhost/hash_key";
        $this->cServer = "http://192.168.2.43";
        $this->cUrl_RFID = "/rfidReader.aspx";
    }
    public function getProcessDetail($data){
        $values = [
            'TA001' => 0,
            'TA002' => 0,

        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT CMSMW.MW002, SFCTA.*
        ,CMSMW.MW202 \"檢驗項目\"
        ,CMSMW.MW201 \"作業依據\"
        ,RTRIM(LTRIM([MOCTA].[TA001]))+'-'+RTRIM(LTRIM([MOCTA].[TA002]))+';'+RTRIM(LTRIM(SFCTA.TA003))+';'+RTRIM(LTRIM(SFCTA.TA004))+';'+RTRIM(LTRIM(SFCTA.TA006)) \"加工順序條碼\"
        FROM [MIL].[dbo].MOCTA
        RIGHT OUTER JOIN MIL.dbo.COPTD ON (COPTD.TD001=MOCTA.TA026 and COPTD.TD002=MOCTA.TA027 and COPTD.TD003=MOCTA.TA028)
        LEFT JOIN [MIL].[dbo].SFCTA ON SFCTA.TA001 = MOCTA.TA001 AND SFCTA.TA002 = MOCTA.TA002
        LEFT JOIN [MIL].[dbo].CMSMW ON CMSMW.MW001 = SFCTA.TA004

        WHERE   RTRIM(LTRIM([MOCTA].[TA001]))=  RTRIM(LTRIM(:TA001)) AND  RTRIM(LTRIM([MOCTA].[TA002])) =  RTRIM(LTRIM(:TA002))

        ORDER BY SFCTA.TA003 ASC
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>$stmt->errorInfo()];
        $result = [];
        $result=$stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row_key => $row) {
            foreach ($row as $key => $value) {
                if ($this->isJson($value)) {
                    $result[$row_key][$key] = json_decode($value, true);
                }
            }

        }

        return $result;
    }
    public function getPrintDetail($data){
        $values = [
            'TA001' => 0,
            'TA002' => 0,

        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        // var_dump($values);
        $sql = " SELECT TOP 100 RTRIM(LTRIM([MOCTA].[TA024]))+' '+RTRIM(LTRIM([MOCTA].[TA025]))\"母製令單\"
            -- ,MOCTA.TA003 \"開單日期\"
            -- ,MOCTA.TA009 \"預計開工\"
            -- ,MOCTA.TA010 \"預計完工\"
            ,(CAST((CAST(SUBSTRING(MOCTA.TA003,1,4) AS INTEGER) - 1911) AS VARCHAR)) + '/' + SUBSTRING(MOCTA.TA003,5,2) + '/' + SUBSTRING(MOCTA.TA003,7,2) \"開單日期\"
            ,(CAST((CAST(SUBSTRING(MOCTA.TA009,1,4) AS INTEGER) - 1911) AS VARCHAR)) + '/' + SUBSTRING(MOCTA.TA009,5,2) + '/' + SUBSTRING(MOCTA.TA009,7,2) \"預計開工\"
            ,(CAST((CAST(SUBSTRING(MOCTA.TA010,1,4) AS INTEGER) - 1911) AS VARCHAR)) + '/' + SUBSTRING(MOCTA.TA010,5,2) + '/' + SUBSTRING(MOCTA.TA010,7,2) \"預計完工\"
            ,GETDATE()  \"製表日期\"
            -- ,MOCTA.TA010 \"預計完工\"
            ,MOCTA.TA020 + '    ' + CMSMC.MC002 \"入庫庫別\"
            ,MOCTA.TA021 + '    ' + CMSMD.MD002 \"生產線別\"
            ,COPTD.TD008 \"訂單數量\"
            ,MOCTA.TA007 \"訂單單位\"
            -- ,COPTD.TD010 \"訂單單位\"
            ,CMSXB.XB002 \"材質\"
            ,COPTD.TD206 \"硬度\"
            ,' ' \"焊接爐號\"
            ,PURMA.MA002 \"加工廠商\"
            ,COPTD.TD020 \"訂單備註事項\"
            ,COPTC.TC015 \"訂單單頭備註\"
            -- ,COPTD.TD015 \"訂單單頭備註\"
            ,MOCTA.TA029 \"製令備註\"
            ,PURMA.MA002 \"加工廠商\"
            ,RTRIM(LTRIM([MOCTA].[TA001]))+'-'+RTRIM(LTRIM([MOCTA].[TA002]))\"MOCTA_PK\"
            ,RTRIM(LTRIM([MOCTA].[TA001]))+' '+RTRIM(LTRIM([MOCTA].[TA002]))\"製令編號\"
            ,RTRIM(LTRIM([MOCTA].[TA026]))+'-'+RTRIM(LTRIM([MOCTA].[TA027])) +'-'+RTRIM(LTRIM([MOCTA].[TA028]))\"COPTD_PK\"
            ,RTRIM(LTRIM([MOCTA].[TA026]))+' '+RTRIM(LTRIM([MOCTA].[TA027])) +' '+RTRIM(LTRIM([MOCTA].[TA028]))\"訂單單號\"
            ,MOCTA.TA201  \"預計熱處理日期\"
            ,MOCTA.TA006+'_' \"產品品號\"
            ,MOCTA.TA034  \"MOCTA_品名\"
            ,MOCTA.TA035  \"規格\"
            -- ,COPTD.TD004+'_' \"產品品號\"
            -- ,COPTD.TD005  \"COPTD_品名\"
            -- ,COPTD.TD006  \"規格\"
            ,COPTC.TC004  \"客戶代號\"
            ,COPTC.TC012  \"客戶單號\"
            ,COPTC.TC020  \"代理訂單\"
            ,COPTD.TD201  \"圖號\"
            ,COPTD.TD214  \"圖面版次\"
            -- ,COPTD.TD215  \"預計生產完成日\"
            ,(CAST((CAST(SUBSTRING(COPTD.TD215,1,4) AS INTEGER) - 1911) AS VARCHAR)) + '/' + SUBSTRING(COPTD.TD215,5,2) + '/' + SUBSTRING(COPTD.TD215,7,2) \"預計生產完成日\"
            ,COPTD.TD204  \"鍍鈦方式\"
            ,COPTD.TD207  \"印logo\"
            ,COPTD.TD038  \"生產包裝資訊\"
            ,COPTD.TD200  \"加印文字內容\"
            ,COPTD.TD015 \"訂單單身備註\"
            ,COPTD.TD013 \"訂單交期\"
            ,CAST(MOCTA.TA015 AS INT) \"預計產量\"
            ,MOCTB.TB003 \"材料品號\"
            ,MOCTB.TB012 \"品名\"
            ,MOCTB.TB013 \"材料規格\"
            ,MOCTB.TB004 \"需領用量\"
            ,MOCTB.TB007 \"單位\"
            ,MOCTB.TB200 \"長度\"
            ,MOCTB.TB017 \"備註\"
            ,RTRIM(LTRIM([MOCTA].[TA001]))+'-'+RTRIM(LTRIM([MOCTA].[TA002]))\"製令編號_\"
            ,RTRIM(LTRIM([MOCTA].[TA026]))+'-'+RTRIM(LTRIM([MOCTA].[TA027])) +'-'+RTRIM(LTRIM([MOCTA].[TA028]))\"訂單編號\"
            ,CASE
                WHEN MOCTA.TA011 = '1' THEN '未生產'
                WHEN MOCTA.TA011 = '2' THEN '已發料'
                WHEN MOCTA.TA011 = '3' THEN '生產中'
                WHEN MOCTA.TA011 = 'Y' THEN '已完工'
                WHEN MOCTA.TA011 = 'y' THEN '指定完工'
                ELSE ''
            END \"製令狀態\"
            ,CMSMV.MV002 \"經辦\",
            CASE
                WHEN CMSMQ.MQ017 = '1' THEN '廠內'
                WHEN CMSMQ.MQ017 = '2' THEN '託外'
                ELSE ''
            END AS \"生產廠別\"

            -- MOCTA.TA001  \"製令單號\",
            -- MOCTA.TA002,
            -- COPTD.TD013 \"訂單交期\",
            -- COPTD.TD008 \"訂單數量\",
            -- MOCTA.TA009 \"預計生產完成日\",
            -- COPTD.TD201 order_name,
            -- COPTD.TD201 \"客戶圖號\",
            -- COPTD.TD001,
            -- COPTD.TD002,
            -- COPTD.TD003,
            -- ROW_NUMBER() OVER (ORDER BY MOCTA.TA002 ASC) \"key\"
        FROM MIL.dbo.MOCTA
        RIGHT OUTER JOIN MIL.dbo.COPTD ON (COPTD.TD001=MOCTA.TA026 and COPTD.TD002=MOCTA.TA027 and COPTD.TD003=MOCTA.TA028)
        LEFT JOIN MIL.dbo.MOCTB ON  ( MOCTA.TA001 =  MOCTB.TB001 AND MOCTA.TA002 =  MOCTB.TB002)
        LEFT JOIN MIL.dbo.CMSMC ON  MOCTA.TA020 =CMSMC.MC001
        LEFT JOIN MIL.dbo.CMSMD ON  MOCTA.TA021=CMSMD.MD001
        LEFT JOIN MIL.dbo.CMSXB ON  CMSXB.XB001 = COPTD.TD205
        LEFT JOIN MIL.dbo.PURMA ON  MOCTA.TA032 = PURMA.MA001
        LEFT JOIN MIL.dbo.COPTC ON  COPTC.TC001 = COPTD.TD001 AND COPTC.TC002 = COPTD.TD002
        LEFT JOIN MIL.dbo.CMSMV ON  CMSMV.MV001 = MOCTA.CREATOR
        LEFT JOIN MIL.dbo.CMSMQ ON  CMSMQ.MQ001 = MOCTA.TA001

        -- WHERE MOCTA.TA001  IS nOT NULL AND MOCTA.TA002  IS nOT NULL
        WHERE   RTRIM(LTRIM([MOCTA].[TA001]))=  RTRIM(LTRIM(:TA001)) AND  RTRIM(LTRIM([MOCTA].[TA002])) =  RTRIM(LTRIM(:TA002))
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>$stmt->errorInfo()];
        $result = [];
        $result=$stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row_key => $row) {
            foreach ($row as $key => $value) {
                if ($this->isJson($value)) {
                    $result[$row_key][$key] = json_decode($value, true);
                }
            }
        }
        // var_dump($stmt->errorInfo());
        return $result;
    }
    public function get_order_detail($data){
        $values = [
            'TA001' => 0,
            'TA002' => 0,

        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT CMSXC.XC002 \"鍍鈦方式\", COPTD.TD203 \"訂單備註事項\", COPTD.TD020 \"訂單單身備註\", COPTD.TD216 \"品質注意事項\",
                        CMSMW.MW002, SFCTA.TA200 \"途程備註\"
                FROM MIL.dbo.MOCTA
                RIGHT OUTER JOIN MIL.dbo.COPTD ON (COPTD.TD001 = MOCTA.TA026 and COPTD.TD002 = MOCTA.TA027 and COPTD.TD003 = MOCTA.TA028)
                LEFT JOIN MIL.dbo.CMSXC ON CMSXC.XC001 = COPTD.TD204
                LEFT JOIN [MIL].[dbo].SFCTA ON SFCTA.TA001 = MOCTA.TA001 AND SFCTA.TA002 = MOCTA.TA002
                LEFT JOIN [MIL].[dbo].CMSMW ON CMSMW.MW001 = SFCTA.TA004
                WHERE RTRIM(LTRIM([MOCTA].[TA001]))=  RTRIM(LTRIM(:TA001)) AND  RTRIM(LTRIM([MOCTA].[TA002])) =  RTRIM(LTRIM(:TA002))
                    --AND LEN(SFCTA.TA200) != 0
                GROUP BY CMSXC.XC002, COPTD.TD203, COPTD.TD020, COPTD.TD216, CMSMW.MW002, SFCTA.TA200, SFCTA.TA003
                ORDER BY SFCTA.TA003";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>$stmt->errorInfo()];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_process_control($data){
        $values = [
            'TA001' => 0,
            'TA002' => 0,

        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT SFCTA.TA003, SFCTA.TA004, CMSMW.MW002, SFCTA.TA024,
                		CASE WHEN SFCTA.TA007 IS NULL THEN '' ELSE SFCTA.TA007 END AS MD002,
                        (CAST((CAST(SUBSTRING(SFCTA.TA008,1,4) AS INTEGER) - 1911) AS VARCHAR) + '/' +
                        SUBSTRING(SFCTA.TA008,5,2) + '/' + SUBSTRING(SFCTA.TA008,7,2)) AS TA008, SFCTA.TA034
                FROM MIL.dbo.MOCTA
                LEFT JOIN [MIL].[dbo].SFCTA ON SFCTA.TA001 = MOCTA.TA001 AND SFCTA.TA002 = MOCTA.TA002
                LEFT JOIN [MIL].[dbo].CMSMW ON CMSMW.MW001 = SFCTA.TA004
                LEFT JOIN [MIL].[dbo].CMSMD ON CMSMD.MD001 = SFCTA.TA006
                WHERE RTRIM(LTRIM([MOCTA].[TA001]))=  RTRIM(LTRIM(:TA001)) AND  RTRIM(LTRIM([MOCTA].[TA002])) =  RTRIM(LTRIM(:TA002))
                    AND (SFCTA.TA004 IN (305,3051,421,425,4251,103) OR SFCTA.TA005 = 2)
                GROUP BY SFCTA.TA003, SFCTA.TA004, SFCTA.TA005, CMSMW.MW002, SFCTA.TA024, SFCTA.TA006,
                        SFCTA.TA007, SFCTA.TA008, SFCTA.TA034
                ORDER BY SFCTA.TA003";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>$stmt->errorInfo()];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_resource_contents_table($data){
        $values = [
            'TA001' => 0,
            'TA002' => 0,

        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key . "_1"] = $data[$key];
                $values[$key . "_2"] = $data[$key];
            }
        }
        unset($values["TA001"]);
        unset($values["TA002"]);
        $sql = "SELECT TOP 1 *
                FROM
                (
                    SELECT \"材料品號\", \"品名\", \"材料規格\",
                            \"需領用量\",
                            \"單位\",
                            -- CAST(\"長度\" AS INTEGER) AS
                            \"長度\",
                            \"備註\", CREATOR, modify_row_number,
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE MODI_DATE END AS MODI_DATE,
                            CASE
                                WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL
                                WHEN \"需求日期\" IS NULL THEN NULL
                                ELSE (CAST((CAST(SUBSTRING(\"需求日期\",1,4) AS INTEGER) - 1911) AS VARCHAR) + '/' +
                                    SUBSTRING(\"需求日期\",5,2) + '/' + SUBSTRING(\"需求日期\",7,2))
                            END AS \"需求日期\",
                            CASE
                                WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL
                                WHEN \"單據日期\" IS NULL THEN NULL
                                ELSE (CAST((CAST(SUBSTRING(\"單據日期\",1,4) AS INTEGER) - 1911) AS VARCHAR) + '/' +
                                    SUBSTRING(\"單據日期\",5,2) + '/' + SUBSTRING(\"單據日期\",7,2))
                            END AS \"單據日期\",
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE \"請購單別\" END AS \"請購單別\",
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE \"請購單號\" END AS \"請購單號\",
                            CASE
                                WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL
                                WHEN \"請購數量\" IS NULL THEN NULL
                                ELSE CAST(\"請購數量\" AS INTEGER)
                            END AS \"請購數量\",
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE \"請購人員名稱\" END AS \"請購人員名稱\"
                    FROM
                    (
                        SELECT MOCTB.TB003 \"材料品號\"
                                ,MOCTB.TB012 \"品名\"
                                ,MOCTB.TB013 \"材料規格\"
                                ,MOCTB.TB004 \"需領用量\"
                                ,MOCTB.TB007 \"單位\"
                                ,MOCTB.TB200 \"長度\"
                                ,MOCTB.TB017 \"備註\"
                                ,MOCTA.CREATOR
                                ,ROW_NUMBER () OVER (PARTITION BY MOCTB.TB003 ORDER BY PURTB.MODI_DATE DESC) AS modify_row_number
                                ,PURTB.MODI_DATE
                                ,PURTB.TB011 \"需求日期\"
                                ,PURTA.TA013 \"單據日期\"
                                ,PURTB.TB001 \"請購單別\"
                                ,PURTB.TB002 \"請購單號\"
                                ,PURTB.TB009 \"請購數量\"
                                ,CMSMV.MV002 \"請購人員名稱\"
                        FROM MIL.dbo.MOCTA
                        LEFT JOIN MIL.dbo.MOCTB ON (MOCTA.TA001 = MOCTB.TB001 AND MOCTA.TA002 = MOCTB.TB002)
                        LEFT JOIN [MIL].[dbo].[PURTB] ON PURTB.TB004 = MOCTB.TB003 AND MOCTA.CREATOR = PURTB.CREATOR
                        LEFT JOIN [MIL].[dbo].[PURTA] ON PURTA.TA001 = PURTB.TB001 AND PURTA.TA002 = PURTB.TB002
                        LEFT JOIN MIL.dbo.CMSMV ON  CMSMV.MV001 = PURTB.CREATOR
                        WHERE RTRIM(LTRIM([MOCTA].[TA001]))=  RTRIM(LTRIM(:TA001_1)) AND  RTRIM(LTRIM([MOCTA].[TA002])) =  RTRIM(LTRIM(:TA002_1))
                        --WHERE RTRIM(LTRIM([MOCTA].[TA001])) = RTRIM(LTRIM('5123')) AND  RTRIM(LTRIM([MOCTA].[TA002])) = RTRIM(LTRIM('1110914013'))
                        -- WHERE RTRIM(LTRIM([MOCTA].[TA001])) = RTRIM(LTRIM('5000')) AND  RTRIM(LTRIM([MOCTA].[TA002])) = RTRIM(LTRIM('1110721005'))
                        --WHERE RTRIM(LTRIM([MOCTA].[TA001])) = RTRIM(LTRIM('5123')) AND  RTRIM(LTRIM([MOCTA].[TA002])) = RTRIM(LTRIM('1110920002'))
                                AND MOCTB.TB011 = 1 AND MOCTB.TB012 NOT LIKE '%' + '碳化鎢' + '%'
                        GROUP BY MOCTA.TA001, MOCTA.TA002, MOCTB.TB003, MOCTB.TB012, MOCTB.TB013, MOCTB.TB004,
                                MOCTB.TB007, MOCTB.TB200, MOCTB.TB017, MOCTA.CREATOR, PURTB.TB011, PURTA.TA013,
                                PURTB.TB001, PURTB.TB002, PURTB.TB009, CMSMV.MV002, PURTB.MODI_DATE
                        --ORDER BY MOCTB.TB003, PURTB.MODI_DATE DESC
                    ) contents
                    WHERE modify_row_number = 1

                    UNION ALL

                    SELECT \"材料品號\", \"品名\", \"材料規格\",
                            \"需領用量\",
                            \"單位\",
                            -- CAST(\"長度\" AS INTEGER) AS
                            \"長度\",
                            \"備註\", CREATOR, modify_row_number,
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE MODI_DATE END AS MODI_DATE,
                            CASE
                                WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL
                                WHEN \"需求日期\" IS NULL THEN NULL
                                ELSE (CAST((CAST(SUBSTRING(\"需求日期\",1,4) AS INTEGER) - 1911) AS VARCHAR) + '/' +
                                    SUBSTRING(\"需求日期\",5,2) + '/' + SUBSTRING(\"需求日期\",7,2))
                            END AS \"需求日期\",
                            CASE
                                WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL
                                WHEN \"單據日期\" IS NULL THEN NULL
                                ELSE (CAST((CAST(SUBSTRING(\"單據日期\",1,4) AS INTEGER) - 1911) AS VARCHAR) + '/' +
                                    SUBSTRING(\"單據日期\",5,2) + '/' + SUBSTRING(\"單據日期\",7,2))
                            END AS \"單據日期\",
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE \"請購單別\" END AS \"請購單別\",
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE \"請購單號\" END AS \"請購單號\",
                            CASE
                                WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL
                                WHEN \"請購數量\" IS NULL THEN NULL
                                ELSE CAST(\"請購數量\" AS INTEGER)
                            END AS \"請購數量\",
                            CASE WHEN \"品名\" LIKE '%' + '鋼料' + '%' THEN NULL ELSE \"請購人員名稱\" END AS \"請購人員名稱\"
                    FROM
                    (
                        SELECT MOCTB.TB003 \"材料品號\"
                                ,MOCTB.TB012 \"品名\"
                                ,MOCTB.TB013 \"材料規格\"
                                ,MOCTB.TB004 \"需領用量\"
                                ,MOCTB.TB007 \"單位\"
                                ,MOCTB.TB200 \"長度\"
                                ,MOCTB.TB017 \"備註\"
                                ,MOCTA.CREATOR
                                ,ROW_NUMBER () OVER (PARTITION BY MOCTB.TB003 ORDER BY PURTB.MODI_DATE DESC) AS modify_row_number
                                ,PURTB.MODI_DATE
                                ,PURTB.TB011 \"需求日期\"
                                ,PURTA.TA013 \"單據日期\"
                                ,PURTB.TB001 \"請購單別\"
                                ,PURTB.TB002 \"請購單號\"
                                ,PURTB.TB009 \"請購數量\"
                                ,CMSMV.MV002 \"請購人員名稱\"
                        FROM MIL.dbo.MOCTA
                        LEFT JOIN MIL.dbo.MOCTB ON (MOCTA.TA001 = MOCTB.TB001 AND MOCTA.TA002 = MOCTB.TB002)
                        LEFT JOIN [MIL].[dbo].[PURTB] ON PURTB.TB004 = MOCTB.TB003
                        LEFT JOIN [MIL].[dbo].[PURTA] ON PURTA.TA001 = PURTB.TB001 AND PURTA.TA002 = PURTB.TB002
                        LEFT JOIN MIL.dbo.CMSMV ON  CMSMV.MV001 = PURTB.CREATOR
                        WHERE RTRIM(LTRIM([MOCTA].[TA001]))=  RTRIM(LTRIM(:TA001_2)) AND  RTRIM(LTRIM([MOCTA].[TA002])) =  RTRIM(LTRIM(:TA002_2))
                        --WHERE RTRIM(LTRIM([MOCTA].[TA001])) = RTRIM(LTRIM('5123')) AND  RTRIM(LTRIM([MOCTA].[TA002])) = RTRIM(LTRIM('1110914013'))
                        -- WHERE RTRIM(LTRIM([MOCTA].[TA001])) = RTRIM(LTRIM('5000')) AND  RTRIM(LTRIM([MOCTA].[TA002])) = RTRIM(LTRIM('1110721005'))
                        --WHERE RTRIM(LTRIM([MOCTA].[TA001])) = RTRIM(LTRIM('5123')) AND  RTRIM(LTRIM([MOCTA].[TA002])) = RTRIM(LTRIM('1110920002'))
                                AND MOCTB.TB011 = 1 AND MOCTB.TB012 LIKE '%' + '碳化鎢' + '%'
                        GROUP BY MOCTA.TA001, MOCTA.TA002, MOCTB.TB003, MOCTB.TB012, MOCTB.TB013, MOCTB.TB004,
                                MOCTB.TB007, MOCTB.TB200, MOCTB.TB017, MOCTA.CREATOR, PURTB.TB011, PURTA.TA013,
                                PURTB.TB001, PURTB.TB002, PURTB.TB009, CMSMV.MV002, PURTB.MODI_DATE
                        --ORDER BY MOCTB.TB003, PURTB.MODI_DATE DESC
                    ) contents
                    WHERE modify_row_number = 1
                ) products
                ORDER BY \"請購單號\" DESC";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>$stmt->errorInfo()];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function dataSave($aRows)
    {
        $aFields = ["cReaderName", "cIP", "cTagID", "iAntennaID", "cTagEvent", "dTime", "bTest"];
        $aData_Insert = $this->oDbop->MakeInsertData("public.\"RFID_TABLE_Log\"", $aFields, $aRows);
        if ($this->bErrorOn) {
            return;
        }

        $this->oDbop->Connect("db");
        if ($this->oDbop->bErrorOn) {
            return;
        }
        $result = $this->oDbop->RunSql($aData_Insert["cSql"], $aData_Insert["htSql"]);
        if ($this->oDbop->bErrorOn) {
            return;
        }
        $this->oDbop->Disconnect();

        $this->SetOK();
        return "";
    }
    public function dataLoad($data) {
        $iQueryType = -1;
        $bCheckInput = false;
        if (array_key_exists('dTime', $data)) {
            $iQueryType = 0;
            $dTime = $data["dTime"];
            $cSql = "
                SELECT \"iAutoIndex\", \"cReaderName\", \"cIP\", \"cTagID\", \"iAntennaID\", \"cTagEvent\", \"dTime\"
	            FROM public.\"RFID_TABLE_Log\"
                WHERE ABS(extract(epoch from (:dTime - \"dTime\" ))) < 50
                ORDER BY ABS(extract(epoch from (:dTime - \"dTime\" ))) ASC
            ";
            $bCheckInput = true;
        } else if (array_key_exists('dTime_Start', $data) && array_key_exists('dTime_End', $data)) {
            $iQueryType = 1;
            $dTime_Start = $data["dTime_Start"];
            $dTime_End = $data["dTime_End"];
            $cSql = "
                SELECT \"iAutoIndex\", \"cReaderName\", \"cIP\", \"cTagID\", \"iAntennaID\", \"cTagEvent\", \"dTime\"
	            FROM public.\"RFID_TABLE_Log\"
                WHERE \"dTime\" BETWEEN :dTime_Start AND :dTime_End
                ORDER BY \"iAutoIndex\" DESC
            ";
            $bCheckInput = true;
        } else if (array_key_exists('cIndexList', $data)) {
            $iQueryType = 2;
            $cIndexList = $data["cIndexList"];
            $cSql = "
                SELECT \"iAutoIndex\", \"cReaderName\", \"cIP\", \"cTagID\", \"iAntennaID\", \"cTagEvent\", \"dTime\"
	            FROM public.\"RFID_TABLE_Log\"
                WHERE \"iAutoIndex\" IN ({$cIndexList})
                ORDER BY \"iAutoIndex\"
            ";
            $bCheckInput = true;
        }
        if (!$bCheckInput) { $this->SetError("傳入參數不正確"); return; }

        if ($iQueryType == 0) {
            $htSql = [ 'dTime'=>$dTime ];;
        } else if ($iQueryType == 1) {
            $htSql = [ 'dTime_Start'=>$dTime_Start, 'dTime_End'=>$dTime_End ];;
        } else {    //$iQueryType == 2
            $htSql = [];
        }
        $this->oDbop->Connect("db"); if ($this->oDbop->bErrorOn) { return; }
        $result = $this->oDbop->SelectSql($cSql, $htSql); if ($this->oDbop->bErrorOn) { return; }
        $this->oDbop->Disconnect();
        $this->SetOK(); return $result;
    }

	public function printLabel($data) {
		$oData = new stdClass();

		if (!isset($data["IP"])) { $this->SetError("No data. IP"); return; }
		$oData->IP = $data["IP"];

		$oData->LabelSetting = ["PrintWidth" => "", "LabelLength" => ""];
		if (isset($data["LabelSetting"])) {
			$LabelSetting = $data["LabelSetting"];
			if (isset($LabelSetting["LabelLength"])) { $oData->LabelSetting["LabelLength"] = "^LL" . $LabelSetting["LabelLength"] . "\n"; }
			if (isset($LabelSetting["PrintWidth"])) { $oData->LabelSetting["PrintWidth"] = "^PW" . $LabelSetting["PrintWidth"] . "\n"; }
		}

		$oData->Text = [];
		if (isset($data["Text"])) {
			foreach ($data["Text"] as $key => $Text) {
				$cData = ""; {
					$Location = $Text["Location"];
					$cData = $cData . "^FO" . $Location["X"] . "," . $Location["Y"] . "\n";

					$FontSize = $Text["FontSize"];
					$cData = $cData . "^A0N," . $FontSize["Height"] . "," . $FontSize["Width"] . "\n";

					$cData = $cData . "^FD" . $Text["Content"] . "^FS" . "\n";
				}
				$oData->Text[] = $cData;
			}
		}

		$oData->BarCode = [];
		if (isset($data["BarCode"])) {
			foreach ($data["BarCode"] as $key => $BarCode) {
				$cData = ""; {
					$cData = "^BY" . $BarCode["ModuleWidth"] . "," . $BarCode["Ratio"] . "," . $BarCode["Height"] . "\n";
					$cData = $cData . "^BC" . "," . $BarCode["Height"] . ",N,N,N,A" . "\n";

					$Location = $BarCode["Location"];
					$cData = $cData . "^FO" . $Location["X"] . "," . $Location["Y"] . "\n";

					$cData = $cData . "^FD" . $Text["Content"] . "^FS" . "\n";
				}
				$oData->BarCode[] = $cData;
			}
		}

		$cCommand = "";
		$cCommand = $cCommand . "^XA" . "\n";
		$cCommand = $cCommand . $oData->LabelSetting["LabelLength"];
		$cCommand = $cCommand . $oData->LabelSetting["PrintWidth"];

		foreach ($oData->Text as $key => $Text) {
			$cCommand = $cCommand . $Text;
		}
		foreach ($oData->BarCode as $key => $BarCode) {
			$cCommand = $cCommand . $BarCode;
		}

		$cCommand = $cCommand . "^RFR,H^FN1^FS" . "\n";
		$cCommand = $cCommand . "^FH^HV1,,,,L" . "\n";
		$cCommand = $cCommand . "^XZ" . "\n";
        //return $cCommand;

		if (($conn = fsockopen($oData->IP, 9100, $errno, $errstr)) === false) {
			$this->SetError('Connection Failed' . $errno . $errstr); return;
		}
		$cEPIC = ""; {
			if (!stream_set_timeout($conn, 1)) { $this->SetError('Set Timeout Failed'); return; }

			fputs($conn, $cCommand, strlen($cCommand));
			while (($buffer = fgets($conn)) !== false) {
				$buffer = trim($buffer); if ($buffer == "") { break; }
				$cEPIC = $cEPIC . $buffer;
			}
		}
		fclose($conn);

		return $cEPIC;
		/*
		if (!isset($data["cPrinterName"])) {
			// $this->SetError("No data. cPrinterName");
			$data["cPrinterName"] = "Printer01";
			// goto EndFunction;
		}
		if (!isset($data["cLine1"])) {
			$this->SetError("No data. cLine1");
			goto EndFunction;
		}
		if (!isset($data["cLine2"])) {
			$this->SetError("No data. cLine2");
			goto EndFunction;
		}

		$cData = nknu\utility\xStatic::ToJson(["cPrinterName" => $data["cPrinterName"], "cLine1" => $data["cLine1"], "cLine2" => $data["cLine2"]]);
		$cJsonData = nknu\utility\xStatic::ToJson(["apiName" => "print", "action" => "default", "data" => $cData]);
		$oCall = new nknu\utility\xCall();
		$cEPIC = $oCall->LabelPrnterApi($cJsonData);
		if ($oCall->bErrorOn) {
			$this->SetError($oCall->cMessage);
			goto EndFunction;
		}
		$this->SetOK();
		return $cEPIC;
		*/
	}
	public function findTag($data)
    {
        if (!isset($data["cReaderList"])) {
            $this->SetError("No data. cReaderList");
            goto EndFunction;
        }
        if (!isset($data["cTagID"])) {
            $this->SetError("No data. cTagID");
            goto EndFunction;
        }

        $cJsonData = nknu\utility\xStatic::ToJson(["apiName" => "rfid", "action" => "findTag", "cReaderList" => $data["cReaderList"], "cTagID" => $data["cTagID"]]);
        $oCall = new nknu\utility\xCall();
        $oCall->RfidReaderApi($cJsonData);
        if ($oCall->bErrorOn) {
            $this->SetError($oCall->cMessage);
            goto EndFunction;
        }
        $this->SetOK();

        EndFunction:
        return "";
    }

    public function status_rfid_record_tags($data){
        $values = [
            "dTime" => date("Y-m-d H:i:s"),
            "cIP" => '',
            "iAntennaID" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key] = $data[$key];
        }
        $sql = <<<EOD
            SELECT order_processes."TA001" || '-' || order_processes."TA002" order_serial,"RFID_TABLE_Log"."cTagID",COUNT(*) count
            FROM (
                SELECT *
                FROM public."RFID_TABLE_Log"
                WHERE "dTime" > :dTime
            )"RFID_TABLE_Log"
            INNER JOIN public.rfid_tag ON rfid_tag.rfid_tag = "RFID_TABLE_Log"."cTagID"
            INNER JOIN (
                SELECT fk->>'TA001' "TA001",REPLACE((fk->'TA002')::text,'','') "TA002",order_processes_tag.rfid_tag_id
                FROM public.order_processes
                LEFT JOIN public.order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                GROUP BY fk->>'TA001',fk->'TA002',order_processes_tag.rfid_tag_id
            )order_processes ON rfid_tag.rfid_tag_id = order_processes.rfid_tag_id
                WHERE
                    "RFID_TABLE_Log"."cIP" = :cIP AND
                    "RFID_TABLE_Log"."iAntennaID" = :iAntennaID
            GROUP BY order_processes."TA001" || '-' || order_processes."TA002","RFID_TABLE_Log"."cTagID"
        EOD;
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failed"];
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function status_rfid_record($data){
        /* http://192.168.2.43 */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->cServer . $this->cUrl_RFID);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["apiName" => "gridView", "action" => "tagList"]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $cResponse = curl_exec($ch);
        $cResponse = json_decode($cResponse,true);
        $values = [
            "oData"=>"[]"
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$cResponse)&&$values[$key]=$cResponse[$key];
        }
        $record = $values["oData"];
        $values = [
            "dTime" => date("Y-m-d H:i:s"),
            "cIP" => '',
            "iAntennaID" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key] = $data[$key];
        }
        $values['dTime']=str_replace(' ','T',$values['dTime']);
        $values['dTime']=str_replace('+','T',$values['dTime']);
        $sql = <<<EOD
            SELECT order_processes."TA001" || '-' || order_processes."TA002" order_serial, "RFID_TABLE_Log"."dTime","RFID_TABLE_Log"."cTagID"
            FROM (
                SELECT *
                FROM json_to_recordset('$record')
                AS "RFID_TABLE_Log"("cReaderName" text,"cIP" text, "cTagID" text, "cTagEvent" text,"iAntennaID" text,
                "dTime" text)
                WHERE "dTime" > :dTime
            )"RFID_TABLE_Log"
            LEFT JOIN public.rfid_tag ON rfid_tag.rfid_tag = "RFID_TABLE_Log"."cTagID"
            LEFT JOIN (
                SELECT fk->>'TA001' "TA001",REPLACE((fk->'TA002')::text,'','') "TA002",order_processes_tag.rfid_tag_id
                FROM public.order_processes
                LEFT JOIN public.order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                GROUP BY fk->>'TA001',fk->'TA002',order_processes_tag.rfid_tag_id
            )order_processes ON rfid_tag.rfid_tag_id = order_processes.rfid_tag_id
                WHERE
                    "RFID_TABLE_Log"."cIP" = :cIP AND
                    "RFID_TABLE_Log"."iAntennaID" = :iAntennaID
            ORDER BY "RFID_TABLE_Log"."dTime" DESC;
        EOD;
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failed"];
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function status_rfid_list($data){
        $sql = <<<EOD
            SELECT rfid_name."tAddress",rfid_name."tName"
            FROM rfid.rfid_name
        EOD;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_status_rfid_detail($data){
        $values = [
            'rfid_outer_name' => '',
            'cIP' => '',
            'iAntennaID' => '',
            'iTransmitPowerIndex'=>200
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $cJson = json_encode(
            [
                $values['rfid_outer_name']=>[
                    'cIP'=>$values['cIP'],
                    'iPort'=>5084, //寫死
                    "aAntennas"=>[
                        $values['iAntennaID']=>[
                            "iTransmitPowerIndex"=>$values['iTransmitPowerIndex']
                        ]
                    ]
                ]
            ]
        );
        $this->callApiByArray(["apiName" => "setting", "action" => "set", "rfidReader" => $cJson]);
        return ['status'=>'success'];
    }

    public function status_rfid_detail($data){
        $values = [
            'tAddress' => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $aRequest = ["apiName" => "status", "action" => "get"];
        $rfidReader = (isset($aData) && array_key_exists("rfidReader", $aData)) ? $aData["rfidReader"] : "";
        $aRequest["rfidReader"] = $rfidReader;
        $values['rfid_data'] = json_encode($this->callApiByArray($aRequest));
        $sql = <<<EOD
            SELECT machine.machine_code,rfid_outer."iAntennaID",rfid_antenna_machine.status,rfid_outer."iTransmitPowerIndex",rfid_outer."cIP",rfid_outer.rfid_outer_name
            FROM (
                SELECT rfid_outer.detail->>'cIP' "cIP",rfid_outer.detail->>'iPort' "iPort",rfid_antenna_outer.key "iAntennaID"
                    ,(rfid_antenna_outer.value::json)->>'iTransmitPowerIndex' "iTransmitPowerIndex",rfid_outer.rfid_outer_name
                FROM(
                    SELECT dt.key "rfid_outer_name", dt.value::jsonb "detail"
                    FROM json_each(:rfid_data)
                        AS dt
                )rfid_outer
                CROSS JOIN json_each((rfid_outer.detail->>'aAvailableAntennas')::json) rfid_antenna_outer
            )rfid_outer
            LEFT JOIN public.rfid_address ON rfid_address."tAddress" = rfid_outer."cIP" AND rfid_outer."iPort" = rfid_address.port
            LEFT JOIN public.rfid_antenna ON rfid_address.id = rfid_antenna.address_id AND rfid_outer."iAntennaID" = rfid_antenna."iAntennaID"::text
            LEFT JOIN public.rfid_antenna_machine ON rfid_antenna_machine.antenna_id = rfid_antenna.id
            LEFT JOIN public.machine ON machine.machine_id = rfid_antenna_machine.machine_id
            WHERE rfid_outer."cIP" = :tAddress;
        EOD;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function statusDataLoad($aData) {
        $aRequest = ["apiName" => "status", "action" => "get"];
        $rfidReader = (isset($aData) && array_key_exists("rfidReader", $aData)) ? $aData["rfidReader"] : "";
        $aRequest["rfidReader"] = $rfidReader;

        return $this->callApiByArray($aRequest);
    }
    public function statusDataSave($aRfidReader)
    {
        $cJson = json_encode($aRfidReader);
        return $this->callApiByArray(["apiName" => "setting", "action" => "set", "rfidReader" => $cJson]);
    }

    public function settingDataLoad($aData) {
        $aRequest = ["apiName" => "setting", "action" => "get"];
        $rfidReader = (isset($aData) && array_key_exists("rfidReader", $aData)) ? $aData["rfidReader"] : "";
        $aRequest["rfidReader"] = $rfidReader;

        return $this->callApiByArray($aRequest);
    }
    public function settingDataSave($aRfidReader) {
        $cJson = json_encode($aRfidReader);
        $cJson = xString::ToSafeBase64AesData($cJson);

        return $this->callApiByArray(["apiName" => "setting", "action" => "set", "rfidReader" => $cJson]);
    }
    public function callApiByArray($aData)
    {
        $cJsonData = nknu\utility\xStatic::ToJson($aData);
        return $this->callApiByJson($cJsonData);
    }
    public function callApiByJson($cJsonData)
    {
        $oCall = new nknu\utility\xCall();
        $cJsonResult = $oCall->RfidReaderApi($cJsonData);
        if ($oCall->bErrorOn) {
            $this->SetError($oCall->cMessage);
            return null;
        }
        $oCallBack = $cJsonResult == null ? true : nknu\utility\xStatic::ToClass($cJsonResult);
        $this->SetOK();
        return $oCallBack;
    }
    public function callApiByArray_default($aData)
    {
        $cJsonData = nknu\utility\xStatic::ToJson($aData);
        return $this->callApiByJson_default($cJsonData);
    }
    public function callApiByJson_default($cJsonData)
    {
        $oCall = new nknu\utility\xCall();
        $cJsonResult = $oCall->LabelPrnterApi($cJsonData);
        if ($oCall->bErrorOn) {
            $this->SetError($oCall->cMessage);
            return null;
        }
        $oCallBack = $cJsonResult == null ? true : $cJsonResult;
        $this->SetOK();
        return $oCallBack;
    }

    /*
    public function getZRecord($data)
    {
        if (array_key_exists('time', $data)) {
            $sql = "SELECT *
                FROM(
                    SELECT \"iAutoIndex\", \"fValue\", \"dTime\", \"bDisconnect\", ROW_NUMBER() OVER()
	                FROM public.\"Z_TABLE_Log\"
                    WHERE ABS(extract(epoch from (:time - \"dTime\" ))) < 30
                    ORDER BY ABS(extract(epoch from (:time - \"dTime\" ))) ASC
                    limit 60
                )result
                ORDER BY \"iAutoIndex\" DESC;
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':time', $data['time']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $result = [
                "status" => "success",
                "data" => $result
            ];
            return $result;
        }
        return ["status" => "failed", "message" => "time欄位不存在"];
    }
    public function getZRecordPicture($data)
    {
        if (array_key_exists('data', $data)) {
            foreach ($data['data'] as $key => $value) {
                if ($value['row_number'] == 1) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://172.25.25.34/Z/ajaxImage.aspx?iAutoIndex=" . $value['iAutoIndex']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    $head = curl_exec($ch);
                    $result = json_decode($head, true);
                    curl_close($ch);
                    foreach ($result as $key => $value) {
                        if ($key == 'oData') {
                            $data['src'] = 'data:image/png;base64,' . $value;
                        }
                    }
                }
            }
            return $data;
        }
        return $data;
    }
    public function getSparkRecord($data)
    {
        if (array_key_exists('time', $data)) {
            $sql = "SELECT *
                FROM(
                    SELECT \"iAutoIndex\", \"iCenterX\", \"iCenterY\", CONCAT(\"iCenterX\", ',' , \"iCenterY\") AS \"火花亮點\", \"iRadius\" AS \"火花大小\", \"iBright\", \"dTime\", ROW_NUMBER() OVER()
                    FROM public.\"Discharge_TABLE_Log\"
                    WHERE ABS(extract(epoch from (:time - \"dTime\" ))) < 30
                    ORDER BY ABS(extract(epoch from (:time - \"dTime\" ))) ASC
                    limit 60
                )result
                ORDER BY \"dTime\" ASC
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':time', $data['time']);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $time_simu = 0.0;
            $last = [];
            $ack = [];
            foreach ($result as $key => $value) {
                if (empty($last)) {
                    $last = $value;
                } else if (abs($value['iCenterX'] - $last['iCenterX']) < 10 && abs($value['iCenterY'] - $last['iCenterY']) < 10 && $value['火花大小'] > 100) {
                    $date = new DateTime($last['dTime']);
                    $date2 = new DateTime($value['dTime']);
                    $diffInSeconds = $date2->getTimestamp() - $date->getTimestamp();
                    $time_simu += $diffInSeconds;
                } else {
                    $time_simu = 0;
                }
                $result[$key]['火花持續時間'] = $time_simu;
                $last = $value;
                array_unshift($ack, $result[$key]);
            }
            $result = [
                "status" => "success",
                "data" => $ack
            ];
            return $result;
        }
        return ["status" => "failed", "message" => "time欄位不存在"];
    }
    public function getSparkRecordPicture($data)
    {
        if (array_key_exists('data', $data)) {
            foreach ($data['data'] as $key => $value) {
                if ($value['row_number'] == 1) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://172.25.25.34/Discharge/ajaxImage.aspx?iAutoIndex=" . $value['iAutoIndex']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    $head = curl_exec($ch);
                    $result = json_decode($head, true);
                    curl_close($ch);
                    foreach ($result as $key => $value) {
                        if ($key == 'oData') {
                            $data['src'] = 'data:image/png;base64,' . $value;
                        }
                    }
                }
            }
            return $data;
        }
        return $data;
    }
    */

    public function get_order_processes_status_detail($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $length = $values['cur_page']*$values['size'];
        $start = $length-$values['size'];

        $values = [
            "status" => 'waiting',
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $values["start"] = $start;
        $values["length"] = $length;
        if($values['status']==='bad'){
            $with = "WITH order_processes_status_detail AS (
                SELECT TRIM(order_processes.fk->>'TA001') \"TA001\",TRIM(order_processes.fk->>'TA002') \"TA002\",ROW_NUMBER() OVER (ORDER BY TRIM(order_processes.fk->>'TA001'),TRIM(order_processes.fk->>'TA002')) \"key\"
                FROM rfid.problem_tag
                LEFT JOIN rfid.problem ON problem_tag.problem_id = problem.problem_id
                LEFT JOIN order_processes_tag ON problem_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                LEFT JOIN order_processes ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                WHERE problem.problem_name = '不良品' AND 'bad' = :status
                GROUP BY TRIM(order_processes.fk->>'TA001'),TRIM(order_processes.fk->>'TA002')
            )";
        }else{
            $with = "WITH order_processes_status_detail AS (
                SELECT dt.\"TA001\",dt.\"TA002\",ROW_NUMBER() OVER (ORDER BY dt.\"TA001\",dt.\"TA002\") \"key\"
                FROM(
                    SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",MAX(order_processes_record.end_time)end_time,
                        MAX(order_processes_record.status)status,
                        ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
                    FROM public.order_processes
                    LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                    WHERE TRIM(order_processes.fk->>'TA003') != ''
                    GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes_record.status
                )dt
                GROUP BY dt.\"TA001\",dt.\"TA002\"
                HAVING COALESCE(MAX(CASE WHEN row_num =1 THEN dt.status END),'waiting') = :status
            )";
        }
        $sql = $with."SELECT TRIM(dt.\"TA001\") || '-' || TRIM(dt.\"TA002\") order_processes
            FROM(
                SELECT *
                FROM order_processes_status_detail
                LIMIT :length
            )dt
            WHERE \"key\" > :start
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $order_processes = $stmt->fetchALl(PDO::FETCH_COLUMN);
        $order_processes_stmt = implode(',',array_map(function($_){return '?';},$order_processes));
        $order_detail = '[]';
        if(count($order_processes)!=0){
            $sql = "SELECT (RTRIM(LTRIM(MOCTA.TA001)) + '-' + RTRIM(LTRIM(MOCTA.TA002))) [製令單別單號],(RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003))) [訂單單別單號],MOCTA.TA015 [預計生產數量],CMSMV.MV002 [製令開單人],COPTD.TD201 [客戶圖號],COPTD.TD215 [預計生產完成日]
                FROM MIL.dbo.COPTD
                LEFT JOIN MIL.dbo.MOCTA ON (RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003))) = RTRIM(LTRIM(MOCTA.TA026)) + '-' + RTRIM(LTRIM(MOCTA.TA027)) + '-' + RTRIM(LTRIM(MOCTA.TA028))
                LEFT JOIN MIL.dbo.CMSMV ON CMSMV.MV001 = MOCTA.CREATOR
                WHERE (RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003))) IN (
                    SELECT RTRIM(LTRIM(MOCTA.TA026)) + '-' + RTRIM(LTRIM(MOCTA.TA027)) + '-' + RTRIM(LTRIM(MOCTA.TA028))
                    FROM MIL.dbo.MOCTA
                    WHERE (RTRIM(LTRIM(MOCTA.TA001)) + '-' + RTRIM(LTRIM(MOCTA.TA002))) IN (
                        $order_processes_stmt
                    )
                )
            ";
            $stmt = $this->db_sqlsrv->prepare($sql);
            $stmt->execute($order_processes);
            $order_detail = $stmt->fetchALl(PDO::FETCH_ASSOC);
            $order_detail = json_encode($order_detail);
        }
        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $result = [
            'total'=>0,
            'data' => []
        ];
        $query = "SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",MAX(order_processes_record.end_time)end_time,
                MAX(order_processes_record.status)status,MAX(lines_machines_processes_outer.\"machine_code\")||'-'||MAX(lines_machines_processes_outer.\"machine_name\") \"目前機台\",
                ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
            FROM public.order_processes
            LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
            LEFT JOIN json_to_recordset('{$lines_machines_processes_outer}')
                AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
            WHERE TRIM(order_processes.fk->>'TA003') != ''
            GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes_record.status
        ";
        if($values['status']==='bad'){
            $query = "SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",MAX(order_processes_record.end_time)end_time,
                    MAX(order_processes_record.status)status,MAX(lines_machines_processes_outer.\"machine_code\")||'-'||MAX(lines_machines_processes_outer.\"machine_name\") \"目前機台\",
                    ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
                FROM public.order_processes
                LEFT JOIN (
                    SELECT order_processes_record.machine_code,order_processes_problem.order_processes_id,order_processes_record.start_time,order_processes_record.end_time,'bad' status
                    FROM (
                        SELECT order_processes.order_processes_id
                        FROM rfid.problem_tag
                        LEFT JOIN rfid.problem ON problem_tag.problem_id = problem.problem_id
                        LEFT JOIN order_processes_tag ON problem_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                        LEFT JOIN order_processes ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                        WHERE problem.problem_name = '不良品' AND 'bad' = :status
                        GROUP BY order_processes.order_processes_id
                    )order_processes_problem
                    LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes_problem.order_processes_id
                )order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                LEFT JOIN json_to_recordset('{$lines_machines_processes_outer}')
                    AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                    ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
                WHERE TRIM(order_processes.fk->>'TA003') != ''
                GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes_record.status
            ";
        }
        $sql = $with . ",order_detail_outer AS (
                SELECT *
                FROM jsonb_to_recordset('$order_detail') AS
                    (\"製令單別單號\" text, \"訂單單別單號\" text, \"預計生產數量\" text, \"製令開單人\" text, \"客戶圖號\" text, \"預計生產完成日\" text)
            )".
            "SELECT dt.\"TA001\"||'-'||dt.\"TA002\" \"製令單別單號\",
                \"訂單單別單號\" \"訂單單別單號\",
                \"預計生產數量\" \"預計生產數量\",
                \"製令開單人\" \"製令開單人\",
                \"客戶圖號\" \"客戶圖號\",
                \"預計生產完成日\" \"預計生產完成日\",
                COALESCE(\"目前機台\",'') \"目前機台\",
                \"key\"
            FROM(
                SELECT dt.\"TA001\",dt.\"TA002\",COUNT(CASE WHEN dt.end_time IS NULL THEN 1 END) unfinish,
                    COALESCE(MAX(CASE WHEN row_num =1 THEN dt.status END),'waiting')status,MAX(dt.end_time)end_time,
                    STRING_AGG(CASE WHEN row_num =1 THEN dt.\"目前機台\" END,'') \"目前機台\",
                    ROW_NUMBER() OVER (ORDER BY dt.\"TA001\",dt.\"TA002\") \"key\"
                FROM(
                    $query
                )dt
                GROUP BY dt.\"TA001\",dt.\"TA002\"
                HAVING COALESCE(MAX(CASE WHEN row_num =1 THEN dt.status END),'waiting') = :status
                LIMIT :length
            )dt
            LEFT JOIN order_detail_outer ON order_detail_outer.\"製令單別單號\" = TRIM(dt.\"TA001\") || '-' || TRIM(dt.\"TA002\")
            WHERE \"key\" > :start
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sql = $with."SELECT COUNT(*)
            FROM order_processes_status_detail
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute(['status'=>$values['status']]);
        $result['total'] = $stmt->fetchColumn(0);
        return $result;
    }
    public function getOrderProcessesDetail($data)
    {
        // $sql = "SELECT TRIM(dt.\"TA001\") || '-' || TRIM(dt.\"TA002\") order_processes
        //     FROM(
        //         SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",MAX(order_processes_record.end_time)end_time,
        //             MAX(order_processes_record.status)status,
        //             ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
        //         FROM public.order_processes
        //         LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
        //         WHERE TRIM(order_processes.fk->>'TA003') != ''
        //         GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes_record.status
        //     )dt
        //     GROUP BY dt.\"TA001\",dt.\"TA002\"
        // ";
        // $stmt = $this->db->prepare($sql);
        // $stmt->execute();
        // $order_processes = $stmt->fetchALl(PDO::FETCH_COLUMN);
        // $order_processes_stmt = implode(',',array_map(function($_){return '?';},$order_processes));
        $order_detail = '[]';
        // if(count($order_processes)!=0){
        //     $sql = "SELECT (RTRIM(LTRIM(MOCTA.TA001)) + '-' + RTRIM(LTRIM(MOCTA.TA002))) [製令單別單號],(RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003))) [訂單單別單號],MOCTA.TA015 [預計生產數量],CMSMV.MV002 [製令開單人],COPTD.TD201 [客戶圖號],COPTD.TD215 [預計生產完成日]
        //         FROM MIL.dbo.COPTD
        //         LEFT JOIN MIL.dbo.MOCTA ON (RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003))) = RTRIM(LTRIM(MOCTA.TA026)) + '-' + RTRIM(LTRIM(MOCTA.TA027)) + '-' + RTRIM(LTRIM(MOCTA.TA028))
        //         LEFT JOIN MIL.dbo.CMSMV ON CMSMV.MV001 = MOCTA.CREATOR
        //         WHERE (RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003))) IN (
        //             SELECT RTRIM(LTRIM(MOCTA.TA026)) + '-' + RTRIM(LTRIM(MOCTA.TA027)) + '-' + RTRIM(LTRIM(MOCTA.TA028))
        //             FROM MIL.dbo.MOCTA
        //             WHERE (RTRIM(LTRIM(MOCTA.TA001)) + '-' + RTRIM(LTRIM(MOCTA.TA002))) IN (
        //                 $order_processes_stmt
        //             )
        //         )
        //     ";
        // }
        // $stmt = $this->db_sqlsrv->prepare($sql);
        // $stmt->execute($order_processes);
        // $order_detail = $stmt->fetchALl(PDO::FETCH_ASSOC);
        // $order_detail = json_encode($order_detail);
        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $sql = "WITH order_detail_outer AS (
            SELECT *
            FROM jsonb_to_recordset('$order_detail') AS
                (\"製令單別單號\" text, \"訂單單別單號\" text, \"預計生產數量\" text, \"製令開單人\" text, \"客戶圖號\" text, \"預計生產完成日\" text)
        )
            SELECT COUNT(*) total,
                COUNT(CASE WHEN dt.unfinish=0 THEN 1 END)*100/NULLIF(COUNT(*),0) percentage,
                COUNT(CASE WHEN dt.unfinish=0 THEN 1 END) finish,
                COUNT(CASE WHEN dt.unfinish!=0 THEN 1 END) unfinished,
                JSON_BUILD_OBJECT(
                    'count',COUNT(CASE WHEN dt.status='ready' THEN 1 END),
                    'percentage',COUNT(CASE WHEN dt.status='ready' THEN 1 END)*100/NULLIF(COUNT(CASE WHEN dt.unfinish!=0 THEN 1 END),0),
                    'detail',COALESCE(JSON_AGG(JSON_BUILD_OBJECT(
                        '製令單別單號',dt.\"TA001\"||'-'||dt.\"TA002\",
						'訂單單別單號',\"訂單單別單號\",
						'預計生產數量',\"預計生產數量\",
						'製令開單人',\"製令開單人\",
						'客戶圖號',\"客戶圖號\",
						'預計生產完成日',\"預計生產完成日\"
                    )) FILTER (WHERE dt.status='ready'), '[]')
                ) ready,
                JSON_BUILD_OBJECT(
                    'count',COUNT(CASE WHEN dt.status='running' THEN 1 END),
                    'percentage',COUNT(CASE WHEN dt.status='running' THEN 1 END)*100/NULLIF(COUNT(CASE WHEN dt.unfinish!=0 THEN 1 END),0),
                    'detail',COALESCE(JSON_AGG(JSON_BUILD_OBJECT(
                        '製令單別單號',dt.\"TA001\"||'-'||dt.\"TA002\",
						'訂單單別單號',\"訂單單別單號\",
						'預計生產數量',\"預計生產數量\",
						'製令開單人',\"製令開單人\",
						'客戶圖號',\"客戶圖號\",
						'預計生產完成日',\"預計生產完成日\"
                    )) FILTER (WHERE dt.status='running'), '[]')
                ) processing,
                JSON_BUILD_OBJECT(
                    'count',COUNT(CASE WHEN dt.status='waiting' THEN 1 END),
                    'percentage',COUNT(CASE WHEN dt.status='waiting' THEN 1 END)*100/NULLIF(COUNT(CASE WHEN dt.unfinish!=0 THEN 1 END),0),
                    'detail',COALESCE(JSON_AGG(JSON_BUILD_OBJECT(
                        '製令單別單號',dt.\"TA001\"||'-'||dt.\"TA002\",
						'訂單單別單號',\"訂單單別單號\",
						'預計生產數量',\"預計生產數量\",
						'製令開單人',\"製令開單人\",
						'客戶圖號',\"客戶圖號\",
						'預計生產完成日',\"預計生產完成日\"
                    )) FILTER (WHERE dt.status='waiting'), '[]')
                ) waiting,
                JSON_BUILD_OBJECT('count',0,'percentage',0,'detail','[]') defect,
                JSON_BUILD_OBJECT(
                    'count',COUNT(CASE WHEN abnormal.bad='bad' THEN 1 END),
                    'percentage',COUNT(CASE WHEN abnormal.bad='bad' THEN 1 END)*100/NULLIF(COUNT(CASE WHEN dt.unfinish!=0 THEN 1 END),0)
                ) abnormal
            FROM(
                SELECT dt.\"TA001\",dt.\"TA002\",COUNT(CASE WHEN dt.end_time IS NULL THEN 1 END) unfinish,
                    COALESCE(MAX(CASE WHEN row_num =1 THEN dt.status END),'waiting')status,MAX(dt.end_time)end_time
                FROM(
                    SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",MAX(order_processes_record.end_time)end_time,
                        MAX(order_processes_record.status)status,
                        ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
                    FROM public.order_processes
                    LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                    LEFT JOIN json_to_recordset('{$lines_machines_processes_outer}')
                        AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                        ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
                    WHERE TRIM(order_processes.fk->>'TA003') != ''
                    GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes_record.status
                )dt
                GROUP BY dt.\"TA001\",dt.\"TA002\"
            )dt
            LEFT JOIN order_detail_outer ON order_detail_outer.\"製令單別單號\" = TRIM(dt.\"TA001\") || '-' || TRIM(dt.\"TA002\")
            LEFT JOIN (
                SELECT TRIM(order_processes.fk->>'TA001')\"TA001\",TRIM(order_processes.fk->>'TA002')\"TA002\",'bad' bad
                FROM rfid.problem_tag
                LEFT JOIN rfid.problem ON problem_tag.problem_id = problem.problem_id
                LEFT JOIN order_processes_tag ON problem_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                LEFT JOIN order_processes ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                WHERE problem.problem_name = '不良品'
                GROUP BY TRIM(order_processes.fk->>'TA001'),TRIM(order_processes.fk->>'TA002')
            )abnormal ON TRIM(abnormal.\"TA001\") || '-' || TRIM(abnormal.\"TA002\") = TRIM(dt.\"TA001\") || '-' || TRIM(dt.\"TA002\")
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        foreach ($result as $index => &$row) {
            $this->isJson($row)&&$row=json_decode($row,true);
        }
        return $result;

        // if (array_key_exists('start', $data) && array_key_exists('end', $data) && array_key_exists('processes_id', $data)) {
        //     $sql = "SELECT DISTINCT ON (order_id) order_processes.order_id, order_processes_detail.order_processes_id, amount, status, time, order_processes.order_processes_index, order_max.order_max
        //         FROM order_processes_detail
        //         LEFT JOIN order_processes ON order_processes_detail.order_processes_id = order_processes.order_processes_id
        //         LEFT JOIN processes ON order_processes.processes_id = processes.processes_id
        //         LEFT JOIN json_to_recordset('{$allProcess}')as process_outer (id text,name text)
        //             ON process_outer.name = processes.processes_name
        //         LEFT JOIN (
        //             SELECT order_id, MAX(order_processes_index) order_max FROM order_processes
        //             GROUP BY order_id
        //         ) order_max ON order_processes.order_id = order_max.order_id
        //         WHERE :start::DATE <= time AND time <= :end::DATE AND trim(process_outer.id) = trim(:processes_id)
        //         AND amount IS NOT NULL AND order_processes.order_id IS NOT NULL
        //         ORDER BY order_id ASC, time DESC
        //     ";
        //     $stmt = $this->container->db->prepare($sql);
        //     $stmt->bindValue(':start', $data['start']);
        //     $stmt->bindValue(':end', $data['end']);
        //     $stmt->bindValue(':processes_id', $data['processes_id']);
        //     $stmt->execute();
        //     $result = $stmt->fetchAll();
        //     $result = [
        //         "status" => "success",
        //         "data" => $result
        //     ];
        //     return $result;
        // }
        // return ["status" => "failed", "message" => "time欄位不存在"];
    }
    public function getMachineProblem($data)
    {
        if (array_key_exists('date', $data)) {
            if ($data['size'] < 0) {
                $length = '';
                $start = 0;
                $limit = '';
            } else {
                $length = $data['cur_page'] * $data['size'];
                $start = $length - $data['size'];
                $limit = 'LIMIT';
            }
            $sql = "SELECT * FROM (
                SELECT machine_problem_id, machine_problem.machine_id, machine_name, problem, time, ROW_NUMBER() OVER (ORDER BY machine_problem_id) row_num
                FROM public.machine_problem
                LEFT JOIN machine ON machine_problem.machine_id = machine.machine_id
                WHERE CAST(:date AS DATE) = time
                {$limit} {$length}
                ) mp
                WHERE mp.row_num > {$start}
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':date', $data['date']);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                "status" => "success",
                "data" => $result
            ];
            return $result;
        }
        return ["status" => "failed", "message" => "time欄位不存在"];
    }
    public function getOrderProcesses($data)
    {
        if (array_key_exists('start', $data) && array_key_exists('end', $data)) {
            if ($data['size'] < 0) {
                $length = '';
                $start = 0;
                $limit = '';
            } else {
                $length = $data['cur_page'] * $data['size'];
                $start = $length - $data['size'];
                $limit = 'LIMIT';
            }
            if ($data['type'] == 'ready') {
                $type = 'NOT';
            } else {
                $type = '';
            }
            if (array_key_exists('machine_id', $data)) {
                $condition = ' WHERE line_machine.machine_id = :machine_id';
            } else {
                $condition = '';
            }
            $sql = "SELECT order_processes.order_processes_id, order_processes.order_id, \"item\".name production_name, line_name, processes_name, default_amount preset_count, line_machine.machine_id,
                time, status, work_time, coptd_file.file_id
                FROM (
                        SELECT inside.order_processes_id, order_id, line_machine_processes_id, processes_id, default_amount, work_time FROM(
                            SELECT order_processes.order_processes_id, order_id, line_machine_processes_id, processes_id, default_amount, work_time, ROW_NUMBER() OVER (ORDER BY order_processes.order_processes_id) row_num
                            FROM order_processes
                            LEFT JOIN order_processes_detail ON order_processes.order_processes_id = order_processes_detail.order_processes_id AND status = 'ready'
                            WHERE time IS {$type} NULL
                            GROUP BY order_processes.order_processes_id
                            {$limit} {$length}
                        ) inside
                        LEFT JOIN order_processes_detail ON inside.order_processes_id = order_processes_detail.order_processes_id
                        WHERE CAST(:start AS DATE) <= time AND time <= CAST(:end AS DATE) AND row_num > {$start}
                        GROUP BY inside.order_processes_id, order_id, line_machine_processes_id, processes_id, default_amount, work_time
                ) order_processes
                LEFT JOIN order_processes_detail ON order_processes.order_processes_id = order_processes_detail.order_processes_id
                LEFT JOIN public.\"order\" ON order_processes.order_id = \"order\".order_id
                LEFT JOIN \"item\" ON \"order\".item_id = \"item\".id
                LEFT JOIN line_machine_processes ON order_processes.line_machine_processes_id = line_machine_processes.line_machine_processes_id
                LEFT JOIN line_machine ON line_machine_processes.line_machine_id = line_machine.line_machine_id
                LEFT JOIN line ON line_machine.line_id = line.line_id
                LEFT JOIN processes ON order_processes.processes_id = processes.processes_id
                LEFT JOIN machine ON line_machine.machine_id = machine.machine_id
                LEFT JOIN phasegallery.coptd_file ON \"order\".order_id = coptd_file.order_id
                {$condition}
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':start', $data['start']);
            $stmt->bindValue(':end', $data['end']);
            if (array_key_exists('machine_id', $data)) {
                $stmt->bindValue(':machine_id', $data['machine_id']);
            }
            $stmt->execute();
            $result = $stmt->fetchAll();
            $result = [
                "status" => "success",
                "data" => $result
            ];
            return $result;
        }
    }
    public function readLastWeekAmount($params)
    {
        $values = [
            'start'=> date("Ymd",strtotime("-7 day")),
            "end"=> date("Ymd")
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$params)){
                $values[$key] = $params[$key];
            }
        }
        // MOCTA.TA001 +'-'+MOCTA.TA002 "(製令單別)+(製令單號)",MOCTA.TA009 "預計生產完成日",COPTD.TD201 "客戶圖號"
        $sql = "SELECT [SFCTA].[TA004] as processes_id, [CMSMW].[MW002] as processes_name,COUNT(*) AS amount,COUNT(*) AS [current],
                (COUNT(*)-COUNT(CASE WHEN SFCTA.TA032='Y' THEN 1 END)-COUNT(CASE WHEN SFCTA.TA032='N' THEN 1 END)) AS [bad],
                COUNT(CASE WHEN SFCTA.TA032='N' THEN 1 END) AS unfinish,COUNT(CASE WHEN SFCTA.TA032='Y' THEN 1 END) AS [done],[SFCTA].[TA009],
                ROW_NUMBER () OVER (
                    PARTITION BY [SFCTA].[TA004]
                    ORDER BY [SFCTA].[TA004], [SFCTA].[TA009]
                ) row_number
            FROM [MIL].[dbo].[CMSMW],[MIL].[dbo].[COPTD],[MIL].[dbo].[MOCTA],[MIL].[dbo].[SFCTA]
            WHERE CMSMW.MW001=SFCTA.TA004
            and COPTD.TD001=MOCTA.TA026
            and COPTD.TD002=MOCTA.TA027
            and COPTD.TD003=MOCTA.TA028
            and SFCTA.TA001=MOCTA.TA001
            and SFCTA.TA002=MOCTA.TA002
            AND MOCTA.TA001=SFCTA.TA001
            and MOCTA.TA002=SFCTA.TA002
            AND ([SFCTA].[TA009] BETWEEN CONVERT(NVARCHAR,'{$values['start']}',112) AND CONVERT(NVARCHAR,'{$values['end']}',112))
            AND SFCTA.TA005=1
            GROUP BY [CMSMW].[MW002],[SFCTA].[TA004],[SFCTA].[TA009]
            ORDER BY [SFCTA].[TA004], [SFCTA].[TA009]
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // $sql = "SELECT
            //         ROW_NUMBER () OVER (
            //             PARTITION BY processes.processes_id
            //             ORDER BY processes.processes_id, order_processes_detail.time
            //         ),
            //         processes.processes_id, processes.processes_name, order_processes_detail.amount, order_processes_detail.time
            //     FROM order_processes_detail
            //     LEFT JOIN order_processes ON order_processes.order_processes_id = order_processes_detail.order_processes_id
            //     LEFT JOIN processes ON processes.processes_id = order_processes.processes_id
            //     WHERE order_processes_detail.status = 'ready'
            //         AND :minus_seven_date::DATE <= order_processes_detail.time AND order_processes_detail.time <= :request_date::DATE
            // ";
            // $stmt = $this->container->db->prepare($sql);
            // $stmt->bindValue(':request_date', $params['request_date']);
            // $stmt->bindValue(':minus_seven_date', $params['minus_seven_date']);
            // $stmt->execute();
            // $result = $stmt->fetchAll();
            return $result;
        // }
        return ["status" => "failed", "message" => "date欄位不存在"];
    }
    public function doLinearRegression($x, $y)
    {
        // calculate number points
        $n = count($x);

        // ensure both arrays of points are the same size
        if ($n != count($y)) {
            trigger_error("doLinearRegression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
        }

        // calculate sums
        $x_sum = array_sum($x);
        $y_sum = array_sum($y);

        $xx_sum = 0;
        $xy_sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $xy_sum += ($x[$i] * $y[$i]);
            $xx_sum += ($x[$i] * $x[$i]);
        }

        // calculate slope
        $slope = (($n * $xx_sum) - ($x_sum * $x_sum))==0?0:(($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

        // calculate intercept
        $intercept = ($y_sum - ($slope * $x_sum)) / $n;

        // return result
        return array("slope" => $slope, "intercept" => $intercept);
    }
    public function groupingLRResponse($params, $lr_result, $sql_row,$count)
    {
        $group = [
            "processes_id" => $sql_row["processes_id"],
            "processes_name" => $sql_row["processes_name"],
            "predict_sum" => 0
        ];
        $i = 1;
        $end = $count;
        while ($i <= $end) {
            $predict_date = date("Y-m-d", strtotime($params["request_date"] . " + {$i} days"));  /* request_date + 1~7 */
            if (date("N", strtotime($predict_date)) < ($count-1)) {  /* weekend check */
                $predict_x = $count + $i;  /* x = row_number */
                $predict_y = round($predict_x * $lr_result["slope"] + $lr_result["intercept"]);  /* y = amount */
                $group["predict_sum"] += $predict_y;
            } else {
                $end++;
            }
            $i++;
        }
        return $group;
    }
    public function iterateNextWeekPredictAmount($params, $last_week_amount,$count)
    {
        $result = [];
        $last_x = [];
        $last_y = [];
        $idx = 1;
        foreach ($last_week_amount as $key => $value) {
            array_push($last_x, $value["row_number"]);
            array_push($last_y, $value["amount"]);
            if ($idx % $count === 0) {  /* 7 days per processes_id */
                $lr_result = $this->doLinearRegression($last_x, $last_y);
                array_push($result, $this->groupingLRResponse($params, $lr_result, $value,$count));
                $last_x = [];  /* reinit */
                $last_y = [];
                $idx = 0;
            }
            $idx++;
        }
        return $result;
    }
    public function mergePredictAmount($data){
        $result = $data['today'];
        foreach ($result as $index => $row) {
            foreach ($data['week'] as $key => $value) {
                if($value['processes_id']===$row['processes_id']){
                    $result[$index]['predict_sum_week'] = $value['predict_sum'];
                    break;
                }
            }
            foreach ($data['five_days'] as $key => $value) {
                if($value['processes_id']===$row['processes_id']){
                    $result[$index]['predict_sum_5days'] = $value['predict_sum'];
                    break;
                }
            }
            foreach ($data['three_days'] as $key => $value) {
                if($value['processes_id']===$row['processes_id']){
                    $result[$index]['predict_sum_3days'] = $value['predict_sum'];
                    break;
                }
            }
        }
        return $result;
    }
    public function getMachineAreaPosition($data)
    {
        $values=[
            "machines_area_id" => 0
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = ($data[$key]);
            }
        }
        $sql = "SELECT machines_area.machines_area_id,machines_area.machines_area_name,machine.*,position.*,point.point
        FROM public.machine
        LEFT JOIN rfid.machines_area ON machines_area.machines_area_id = machine.machines_area_id
        LEFT JOIN public.position ON position.position_id = machine.position_id
        LEFT JOIN (
            SELECT position_id,
            '[' || STRING_AGG (
            '[\"' || x::TEXT || '\",\"' || y::TEXT || '\"]',
                ','
                ORDER BY point_id
            ) || ']' point
            FROM public.point
            GROUP BY position_id
        ) AS point ON point.position_id = position.position_id
        WHERE machines_area.machines_area_id =:machines_area_id ;
        ";
        $stmt = $this->container->db->prepare($sql);
        if($stmt->execute($values)){
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row_key => $row) {
                foreach ($row as $key => $value) {
                    if ($this->isJson($value)) {
                        $result[$row_key][$key] = json_decode($value, true);
                    }
                }
            }
            $result = [
                "status" => "success",
                "data" => $result
            ];
        }else{
            $result = [
                "status" => "failed"
            ];
        }

        return $result;
    }
    public function getMachinePositionStatus($data)
    {
        if (array_key_exists('floor_id', $data)) {
            $sql = "SELECT machine.machine_id, machine.floor_id, machine.position_id, point.point_list, machine_code, canvas_width, canvas_height, machines_area.machines_area_id,
                    rfid_antenna_machine.status,point_all.point_1_x,point_all.point_1_y,point_all.point_2_x,point_all.point_2_y,point_all.point_3_x,point_all.point_3_y,
                    point_all.point_4_x,point_all.point_4_y
                FROM machine
                LEFT JOIN rfid.machines_area ON machine.machines_area_id = machines_area.machines_area_id
                LEFT JOIN position ON machine.position_id = position.position_id
                LEFT JOIN (
                    SELECT position_id,json_agg(json_build_array(
                        point.x,point.y
                    )) point_list
                    FROM point
                    GROUP BY position_id
                )point ON position.position_id = point.position_id
                LEFT JOIN(
                    SELECT position_id,
                        STRING_AGG(CASE WHEN row_num = 1 THEN x::text END,',') \"point_1_x\",
                        STRING_AGG(CASE WHEN row_num = 1 THEN y::text END,',') \"point_1_y\",
                        STRING_AGG(CASE WHEN row_num = 2 THEN x::text END,',') \"point_2_x\",
                        STRING_AGG(CASE WHEN row_num = 2 THEN y::text END,',') \"point_2_y\",
                        STRING_AGG(CASE WHEN row_num = 3 THEN x::text END,',') \"point_3_x\",
                        STRING_AGG(CASE WHEN row_num = 3 THEN y::text END,',') \"point_3_y\",
                        STRING_AGG(CASE WHEN row_num = 4 THEN x::text END,',') \"point_4_x\",
                        STRING_AGG(CASE WHEN row_num = 4 THEN y::text END,',') \"point_4_y\"
                    FROM (
                        SELECT *,ROW_NUMBER() OVER(PARTITION BY position_id ORDER BY point_id)row_num
                        FROM point
                    )point
                    GROUP BY position_id
                )point_all ON position.position_id = point_all.position_id
                LEFT JOIN public.rfid_antenna_machine ON rfid_antenna_machine.machine_id = machine.machine_id
                WHERE machine.floor_id = :floor_id
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':floor_id', $data['floor_id']);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $index => $row) {
                foreach ($row as $key => $value) {
                    $this->isJson($value)&&$result[$index][$key]=json_decode($value,true);
                }
            }
            $result = [
                "status" => "success",
                "data" => $result
            ];
            return $result;
        }
    }
    public function getMachinePosition($data)
    {
        if (array_key_exists('floor_id', $data)) {
            $sql = "SELECT machine.machine_id, machine.floor_id, machine.position_id, x, y, machine_code, canvas_width, canvas_height, machines_area.machines_area_id
                FROM machine
                LEFT JOIN rfid.machines_area ON machine.machines_area_id = machines_area.machines_area_id
                LEFT JOIN position ON machine.position_id = position.position_id
                LEFT JOIN point ON position.position_id = point.position_id
                WHERE machine.floor_id = :floor_id
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':floor_id', $data['floor_id']);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                "status" => "success",
                "data" => $result
            ];
            return $result;
        }
    }
    public function postMachinePosition($data)
    {
        if (array_key_exists('floor_id', $data)) {
            $sql = "INSERT INTO position(
                    canvas_width, canvas_height)
                    VALUES (:canvas_width, :canvas_height)
                    RETURNING position_id;
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':canvas_width', $data['canvas_width']);
            $stmt->bindValue(':canvas_height', $data['canvas_height']);
            $stmt->execute();
            $position_id = $stmt->fetch(PDO::FETCH_ASSOC);
            foreach ($data['point_list'] as $point) {
                $sql = "INSERT INTO point(
                    x, y, position_id)
                    VALUES (:x, :y, :position_id);
                ";
                $stmt = $this->container->db->prepare($sql);
                $stmt->bindValue(':x', $point['px']);
                $stmt->bindValue(':y', $point['py']);
                $stmt->bindValue(':position_id', $position_id['position_id']);
                $stmt->execute();
            }
            $sql = "INSERT INTO machine(
                floor_id, position_id)
                VALUES (:floor_id, :position_id)
                RETURNING machine_id;
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->bindValue(':floor_id', $data['floor_id']);
            $stmt->bindValue(':position_id', $position_id['position_id']);
            if ($stmt->execute()) {
                $result = [
                    "status" => "success",
                    "data" => $stmt->fetch()['machine_id']
                ];
            } else {
                $result = [
                    "status" => "fail"
                ];
            }
            return $result;
        }
    }
    public function updateFloorImage($data)
    {
        $sql = "UPDATE floor
            SET file_name=:file_name, file_client_name=:file_client_name
            WHERE floor_id = :floor_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':file_name', $data['file_name'], PDO::PARAM_STR);
        $stmt->bindParam(':file_client_name', $data['file_client_name'], PDO::PARAM_STR);
        $stmt->bindParam(':floor_id', $data['floor_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return [
                "status" => "success",
                "file_client_name" => $data['file_client_name']
            ];
        } else {
            return [
                "status" => "failed"
            ];
        }
    }
    public function getFloorImage($data)
    {
        $sql = "SELECT file_name
            FROM floor
            WHERE floor_id = :floor_id;
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $data['floor_id'], PDO::PARAM_INT);
        $stmt->execute();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($files as $file) {
            return $this->container->upload_directory . '/' . $file['file_name'];
        }
    }
    public function getFloor()
    {
        $sql = "SELECT floor_id, floor_name, file_client_name
                FROM floor
                WHERE floor_name IS NOT NULL
                ORDER BY floor_name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function insertNewFloor($params)
    {
        $sql = "INSERT INTO floor (floor_name)
                VALUES (NULL)
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return ["status" => "fail"];
        }
    }
    public function readNewestFloor()
    {
        $sql = "SELECT floor_id, floor_name
                FROM floor
                ORDER BY floor_id DESC LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function updateFloor($body)
    {
        $sql = "UPDATE floor
                SET floor_name = :floor_name
                WHERE floor_id = :floor_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_name', $body['floor_name'], PDO::PARAM_STR);
        $stmt->bindParam(':floor_id', $body['floor_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return [
                "status" => "failure",
                "error_info" => $stmt->errorInfo()
            ];
        }
    }
    public function deleteFloor($body)
    {
        $sql = "DELETE FROM floor
                WHERE floor_id = :floor_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $body['floor_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return [
                "status" => "failure",
                "error_info" => $stmt->errorInfo()
            ];
        }
    }
    public function deleteMachinePosition($data)
    {
        $sql = "DELETE FROM machine
            WHERE machine_id = :machine_id
            RETURNING position_id;
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':machine_id', $data['machine_id'], PDO::PARAM_INT);
        $stmt->execute();
        $position_id = $stmt->fetch();
        $sql = "DELETE FROM point
            WHERE position_id = :position_id;
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':position_id', $position_id['position_id'], PDO::PARAM_INT);
        $stmt->execute();
        $sql = "DELETE FROM position
            WHERE position_id = :position_id;
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':position_id', $position_id['position_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return ["status" => "fail"];
        }
    }
    public function updateMachinePosition($data)
    {
        $values = [
            'machines_area_id' => null
        ];
        foreach (array_keys($values) as $key) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $stmt_string="";
        $stmt_array=[];
        if(!is_null($values['machines_area_id'])){
            $stmt_string=",machines_area_id=:machines_area_id";
            $stmt_array = $values;
        }
        $stmt_array['machine_code']=$data['machine_code'];
        $stmt_array['machine_name']=$data['machine_code'];
        $stmt_array['machine_id']=$data['machine_id'];
        $sql = "UPDATE machine
            SET machine_name = :machine_name, machine_code = :machine_code
                {$stmt_string}
            WHERE machine_id = :machine_id
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
            return ["status" => "fail"];
        }
        // $sql = "INSERT INTO public.rfid_antenna_machine(machine_id, antenna_id, status)
        //     VALUES (:machine_id, :antenna_id, :status)
        //     ON CONFLICT (antenna_id)
        //     DO UPDATE SET machine_id=EXCLUDED.machine_id,status=EXCLUDED.status;
        // ";
        // $stmt = $this->db->prepare($sql);
        // $stmt->bindParam(':machine_id', $data['machine_id'], PDO::PARAM_INT);
        // $stmt->bindParam(':antenna_id', $data['antenna_id'], PDO::PARAM_INT);
        // $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
        // if ($stmt->execute()) {
        //     return ["status" => "success"];
        // } else {
        //     return ["status" => "fail"];
        // }
    }

    public function get_employee()
    {
        $sql = "SELECT RTRIM(LTRIM([MOCTA].[CREATOR])) AS employee_id, RTRIM(LTRIM([CMSMV].[MV002])) AS employee_name
                FROM [MIL].[dbo].[MOCTA]
                LEFT JOIN [MIL].[dbo].[CMSMV] ON [MIL].[dbo].[CMSMV].[MV001] = [MIL].[dbo].[MOCTA].[CREATOR]
                WHERE [MOCTA].[CREATOR] IS NOT NULL AND [CMSMV].[MV002] IS NOT NULL AND
                        (
                            MOCTA.TA001 IS NULL
                            OR
                            MOCTA.TA001  NOT IN  ( '5202','5205','5198','5199','5207','5203','5204' )
                        )
                GROUP BY [MOCTA].[CREATOR], [CMSMV].[MV002]
                ORDER BY
                    CASE
                        WHEN RTRIM(LTRIM([MOCTA].[CREATOR])) = '096021' THEN 1
                        WHEN RTRIM(LTRIM([MOCTA].[CREATOR])) = '084002' THEN 2
                        WHEN RTRIM(LTRIM([MOCTA].[CREATOR])) = '100013' THEN 3
                        WHEN RTRIM(LTRIM([MOCTA].[CREATOR])) = '103038' THEN 4
                        ELSE [MOCTA].[CREATOR]
                    END ASC";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if (!$stmt->execute()) {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get_order_processes_detail_outer($data){
        $values = [
            'date_begin' => '',
            'date_end' => date('Ymd'),
            'size' => 10,
            'cur_page' => 1,
            'keyword' => '',
            'build' => '已開單',
            'employee_id' => "000000",
            'order_processes' => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $employee_codition = "";
        if($values["employee_id"] === "000000") unset($values["employee_id"]);
        else $employee_codition = " AND RTRIM(LTRIM(MOCTA.CREATOR)) = RTRIM(LTRIM(:employee_id)) ";

        $order_processes_codition = "";
        if(!is_null($values["order_processes"])) {
            if(count($values["order_processes"])!=0){
                $order_processes_codition .= " AND (";
                foreach ($values["order_processes"] as $rows) {
                    $order_processes_codition .= "( RTRIM(LTRIM(MOCTA.TA001)) = RTRIM(LTRIM('{$rows['TA001']}')) AND RTRIM(LTRIM(MOCTA.TA002)) = RTRIM(LTRIM('{$rows['TA002']}')) ) OR";
                }
                $order_processes_codition = rtrim($order_processes_codition,"OR");
                $order_processes_codition .= ")";
            }
        }
        unset($values["order_processes"]);

        $keyword = $values['keyword'];
        if(empty($keyword)){
            if(empty($values['date_begin']))
                $values['date_begin'] = date('Ymd');
        }else{
            if(empty($values['date_begin']))
                $values['date_begin'] = '10000101';
        }
        $length = $values['cur_page']*$values['size'];
        $start = $length-$values['size'];
        $build = $values['build'];
        unset($values['keyword']);
        unset($values['cur_page']);
        unset($values['size']);
        unset($values['build']);
        $query = [
            "MOCTA" => "AND MOCTA.TA003 BETWEEN :date_begin AND :date_end",
            "COPTC" => "INNER JOIN (
                SELECT COPTD.*
                FROM MIL.dbo.COPTD
                LEFT JOIN MIL.dbo.COPTC ON (COPTD.TD001=COPTC.TC001 and COPTD.TD002=COPTC.TC002)
            )COPTD
            ",
            "not_in_mocta" => ""
        ];
        if($build == '未開單'){
            $query['MOCTA'] = "";
            $query['COPTC'] = "RIGHT OUTER JOIN (
                SELECT COPTD.*
                FROM MIL.dbo.COPTD
                LEFT JOIN MIL.dbo.COPTC ON (COPTD.TD001=COPTC.TC001 and COPTD.TD002=COPTC.TC002)
                WHERE COPTC.TC039 BETWEEN :date_begin AND :date_end
            )COPTD
            ";
            $query['not_in_mocta'] = " AND MOCTA.TA026 IS NULL AND MOCTA.TA027 IS NULL AND MOCTA.TA028 IS NULL";
        }
        $keyword_array = ['MOCTA.TA001'=>'keyword_MOCTA_TA001','MOCTA.TA002'=>'keyword_MOCTA_TA002','COPTD.TD001'=>'keyword_COPTD_TD001','COPTD.TD002'=>'keyword_COPTD_TD002','COPTD.TD003'=>'keyword_COPTD_TD003',"(RTRIM(LTRIM(MOCTA.TA001)) + '-' + RTRIM(LTRIM(MOCTA.TA002)))"=>'keyword_MOCTA_combined_with_line',"(RTRIM(LTRIM(MOCTA.TA001)) + RTRIM(LTRIM(MOCTA.TA002)))"=>'keyword_MOCTA_combined'];
        $keyword_string = implode(' OR ',array_map(function($key,$value){
            return " $key LIKE '%'+:{$value}+'%' ";
        },array_keys($keyword_array),array_values($keyword_array)));
        $keyword_string =  ' AND ( ' . $keyword_string . ')';
        foreach ($keyword_array as $array) {
            $values[$array] = $keyword;
        }
        $with = "WITH dt as (
            SELECT CAST(MOCTA.TA015 AS INT) \"總數量\", CAST(MOCTA.TA015 AS INT) \"預計產量\", MOCTA.TA001 \"製令單別\",MOCTA.TA001,MOCTA.TA002 \"製令單號\",MOCTA.TA003 \"製令開單日期\", RTRIM(LTRIM(MOCTA.CREATOR)) employee_id,CMSMV.MV002 \"製令開單人\",MOCTA.TA201 \"預計熱處理日期\",COPTC.TC039 \"訂單開單日期\",MOCTA.TA002,COPTD.TD013 \"訂單交期\",COPTD.TD008 \"訂單數量\",MOCTA.TA009 \"預計生產完成日\",COPTD.TD201 order_name,COPTD.TD201 \"客戶圖號\", COPTD.TD001, COPTD.TD002, COPTD.TD003,ROW_NUMBER() OVER (ORDER BY MOCTA.TA001 ASC,MOCTA.TA002 ASC) \"key\"
            FROM (
                SELECT *
                FROM[MIL].[dbo].[MOCTA]
                WHERE 1=1
                {$query['MOCTA']}
                {$order_processes_codition}
            )[MOCTA]
                {$query['COPTC']}
            ON (COPTD.TD001=MOCTA.TA026 and COPTD.TD002=MOCTA.TA027 and COPTD.TD003=MOCTA.TA028)
                LEFT JOIN MIL.dbo.COPTC ON (COPTD.TD001=COPTC.TC001 and COPTD.TD002=COPTC.TC002)
                LEFT JOIN MIL.dbo.CMSMV ON CMSMV.MV001 = MOCTA.CREATOR
            WHERE
            1=1 {$query["not_in_mocta"]}
            --(
                --MOCTA.TA001  Is Null
                --OR
                --MOCTA.TA001  NOT IN  ( '5202','5205','5198','5199','5207','5203','5204'  )
            --)
            {$employee_codition}
            {$keyword_string}
        )";
        $sql = $with."SELECT *,CASE WHEN dt.TA001 IS NOT NULL THEN dt.TA001+'_'+dt.TA002 ELSE TD001+'-'+TD002+'-'+TD003 END \"key\"
            FROM (
                SELECT TOP {$length} *
                FROM dt
            )dt
            WHERE \"key\" > {$start}
            ORDER BY dt.TA002 ASC
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        $result = [];
        $result["data"]=$stmt->fetchAll(PDO::FETCH_ASSOC);

        // $result['data'] = json_encode($result['data']);
        $result['data'] = json_encode(unserialize(str_replace(array('NAN;','INF;'),'0;',serialize($result['data']))));

        $sql = $with."SELECT COUNT(*)
            FROM dt
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        $result["total"] = $stmt->fetchColumn(0);
        return $result;
    }
    
    function get_filtered_hot_date($data){
        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $values = [
            'hot_date_begin'=>date('Ymd'),
            'hot_date_end'=>date('Ymd')
            // 'date_begin'=>date('Ymd', strtotime('20220610')),
            // 'date_end'=>date('Ymd', strtotime('20220610'))
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $filter_hot = "";
        $filter_hot = " AND lines_machines_processes_outer.line_name LIKE '%熱處理%' ";
        $sql = "SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\"
            FROM public.order_processes
            LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
            LEFT JOIN json_to_recordset('$lines_machines_processes_outer')
                AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                ON lines_machines_processes_outer.processes like '%' || (order_processes.fk->>'TA004')::text || '%'
            WHERE 1=1
            {$filter_hot}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $order_processes_codition = "";
        if(!is_null($result)) {
            if(count($result)!=0){
                $order_processes_codition .= " AND (";
                foreach ($result as $rows) {
                    $order_processes_codition .= "( RTRIM(LTRIM(MOCTA.TA001)) = RTRIM(LTRIM('{$rows['TA001']}')) AND RTRIM(LTRIM(MOCTA.TA002)) = RTRIM(LTRIM('{$rows['TA002']}')) ) OR";
                }
                $order_processes_codition = rtrim($order_processes_codition,"OR");
                $order_processes_codition .= ")";
            }
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        unset($result);
        $this->db_sqlsrv->exec('USE [MIL]');
        $sql = "SELECT MOCTA.TA001,MOCTA.TA002
            FROM dbo.MOCTA
            WHERE MOCTA.TA009 BETWEEN :hot_date_begin AND :hot_date_end
            {$order_processes_codition}
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function get_order_processes_cutting_overview($data){
        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $processes_outer = $this->get_processes($data);
        $processes_outer = json_encode($processes_outer);
        $data['date_begin'] = '10000101';//預設從前到現在
        $values = [
            'size' => 10,
            'cur_page' => 1,
            'problem'=>'不良品'
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $length = $values['cur_page']*$values['size'];
        $start = $length-$values['size'];
        unset($values['size']);
        unset($values['cur_page']);
        $filter_hot = "";
        $filter_problem = "";
        $filter_hot_codition = "";
        if($values['problem']==='不良品'){
            $filter_problem = " AND order_processes_now.process_name LIKE '%品管%'";
        }else if($values['problem']==='熱處理'){
            $filter_hot_date = $this->get_filtered_hot_date($data);
            if(!is_null($filter_hot_date)) {
                if(count($filter_hot_date)!=0){
                    $filter_hot_codition .= " AND (";
                    foreach ($filter_hot_date as $rows) {
                        $filter_hot_codition .= "( TRIM(order_processes.fk->>'TA001') = TRIM('{$rows['TA001']}') AND TRIM(order_processes.fk->>'TA002') = TRIM('{$rows['TA002']}') ) OR";
                    }
                    $filter_hot_codition = rtrim($filter_hot_codition,"OR");
                    $filter_hot_codition .= ")";
                }else{
                    $filter_hot_codition = " AND 1=0";
                }
            }
            $filter_hot = " AND lines_machines_processes_outer.line_name LIKE '%熱處理%'";
        }
        unset($values['problem']);

        $sql = "WITH processes_outer AS (
                SELECT *
                FROM json_to_recordset('$processes_outer')
                AS processes_outer(\"process_id\" text,\"process_name\" text)
            )
            SELECT *
            FROM(
                SELECT order_processes_now.\"TA001\",order_processes_now.\"TA002\",order_processes_now.process_name,
                    ROW_NUMBER() OVER (ORDER BY order_processes_now.\"TA002\" DESC) row_num,order_processes_now.machine_code,
                    floor.floor_name
                FROM(
                    SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA004',processes_outer.process_name,
                        ROW_NUMBER() OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE WHEN order_processes_record.start_time IS NULL THEN order_processes.fk->>'TA003' ELSE '1' END ASC,order_processes.fk->>'TA003' DESC) row_num,
                        lines_machines_processes_outer.\"line_name\",order_processes_record.machine_code
                    FROM (
						SELECT *
						FROM public.order_processes
						WHERE 1=1
                            {$filter_hot_codition}
					)order_processes
                    LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                    LEFT JOIN (
                        SELECT processes_outer.\"process_id\",processes_outer.\"process_name\"
                        FROM processes_outer
                    )processes_outer ON TRIM(processes_outer.\"process_id\") = TRIM(order_processes.fk->>'TA004')
                    LEFT JOIN json_to_recordset('$lines_machines_processes_outer')
                        AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                        ON lines_machines_processes_outer.processes like '%' || (order_processes.fk->>'TA004')::text || '%'
                    WHERE 1=1
                    {$filter_hot}
                )order_processes_now
				LEFT JOIN machine ON order_processes_now.machine_code = machine.machine_code
				LEFT JOIN floor ON machine.floor_id = floor.floor_id
                WHERE order_processes_now.row_num = 1
                    {$filter_problem}
                LIMIT {$length} 
            )dt
            WHERE \"row_num\" > {$start}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $data['order_processes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "WITH processes_outer AS (
                SELECT *
                FROM json_to_recordset('$processes_outer')
                AS processes_outer(\"process_id\" text,\"process_name\" text)
            )
                SELECT order_processes_now.\"TA001\",order_processes_now.\"TA002\",order_processes_now.process_name,
                    ROW_NUMBER() OVER (ORDER BY order_processes_now.\"TA002\" DESC) row_num,order_processes_now.machine_code,
                    floor.floor_name
                FROM(
                    SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA004',processes_outer.process_name,
                        ROW_NUMBER() OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE WHEN order_processes_record.start_time IS NULL THEN order_processes.fk->>'TA003' ELSE '1' END ASC,order_processes.fk->>'TA003' DESC) row_num,
                        lines_machines_processes_outer.\"line_name\",order_processes_record.machine_code
                    FROM (
                        SELECT *
                        FROM public.order_processes
                        WHERE 1=1
                            {$filter_hot_codition}
                    )order_processes
                    LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                    LEFT JOIN (
                        SELECT processes_outer.\"process_id\",processes_outer.\"process_name\"
                        FROM processes_outer
                    )processes_outer ON TRIM(processes_outer.\"process_id\") = TRIM(order_processes.fk->>'TA004')
                    LEFT JOIN json_to_recordset('$lines_machines_processes_outer')
                        AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                        ON lines_machines_processes_outer.processes like '%' || (order_processes.fk->>'TA004')::text || '%'
                    WHERE 1=1
                    {$filter_hot}
                )order_processes_now
                LEFT JOIN machine ON order_processes_now.machine_code = machine.machine_code
                LEFT JOIN floor ON machine.floor_id = floor.floor_id
                WHERE order_processes_now.row_num = 1
                    {$filter_problem}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result=[];
        $result['total']=$stmt->rowCount();
        if(count($data['order_processes'])!=0){
            $order_processes_codition = "";
            if(!is_null($data["order_processes"])) {
                if(count($data["order_processes"])!=0){
                    $order_processes_codition .= " AND (";
                    foreach ($data["order_processes"] as $rows) {
                        $order_processes_codition .= "( RTRIM(LTRIM(MOCTA.TA001)) = RTRIM(LTRIM('{$rows['TA001']}')) AND RTRIM(LTRIM(MOCTA.TA002)) = RTRIM(LTRIM('{$rows['TA002']}')) ) OR";
                    }
                    $order_processes_codition = rtrim($order_processes_codition,"OR");
                    $order_processes_codition .= ")";
                }
            }
            $sql = "SELECT CAST(MOCTA.TA015 AS INT) \"總數量\", CAST(MOCTA.TA015 AS INT) \"預計產量\", MOCTA.TA001 \"製令單別\",MOCTA.TA001,MOCTA.TA002 \"製令單號\",MOCTA.TA003 \"製令開單日期\", RTRIM(LTRIM(MOCTA.CREATOR)) employee_id,CMSMV.MV002 \"製令開單人\",MOCTA.TA201 \"預計熱處理日期\",COPTC.TC039 \"訂單開單日期\",MOCTA.TA002,COPTD.TD013 \"訂單交期\",COPTD.TD008 \"訂單數量\",MOCTA.TA009 \"預計生產完成日\",COPTD.TD201 order_name,COPTD.TD201 \"客戶圖號\", COPTD.TD001, COPTD.TD002, COPTD.TD003,ROW_NUMBER() OVER (ORDER BY MOCTA.TA001 ASC,MOCTA.TA002 ASC) \"key\"
                FROM (
                    SELECT *
                    FROM[MIL].[dbo].[MOCTA]
                    WHERE 1=1
                    {$order_processes_codition}
                )[MOCTA]
                INNER JOIN (
                    SELECT COPTD.*
                    FROM MIL.dbo.COPTD
                    LEFT JOIN MIL.dbo.COPTC ON (COPTD.TD001=COPTC.TC001 and COPTD.TD002=COPTC.TC002)
                )COPTD 
                ON (COPTD.TD001=MOCTA.TA026 and COPTD.TD002=MOCTA.TA027 and COPTD.TD003=MOCTA.TA028)
                    LEFT JOIN MIL.dbo.COPTC ON (COPTD.TD001=COPTC.TC001 and COPTD.TD002=COPTC.TC002)
                    LEFT JOIN MIL.dbo.CMSMV ON CMSMV.MV001 = MOCTA.CREATOR
            ";
            $stmt = $this->db_sqlsrv->prepare($sql);
            if(!$stmt->execute()) return ["status"=>"failure"];
            $result['data']=json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }else{
            $result = [
                'data'=>'[]',
                'total'=>0
            ];
        }
        $values = [
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $data['order_processes'] = json_encode($data['order_processes']);
        $sql = "SELECT \"製令單別\" || '-' || \"製令單號\" \"製令單別單號\",TRIM(order_processes_outer.\"TD001\") || '-' || TRIM(order_processes_outer.\"TD002\") || '-' || TRIM(order_processes_outer.\"TD003\") \"訂單單號\",
                order_processes_outer.\"製令開單人\",order_processes_outer.\"客戶圖號\",order_processes_outer.\"總數量\", 
                order_processes_outer.\"總數量\"-COALESCE(order_processes_rfid_tag_id_problem_tag.problem_number,0) \"合格數量\",
                COALESCE(order_processes_rfid_tag_id_problem_tag.problem_number,0) \"不良品數量\",COALESCE(order_processes_rfid_tag_id_problem_tag.hot_number,0) \"熱處理數量\",
                COALESCE(order_processes_floor.floor_name,'-') \"樓層\",
                order_processes_tag.rfid_tag_id
            FROM json_to_recordset('{$result['data']}') 
                as order_processes_outer(\"總數量\" integer,\"預計產量\" text,\"製令單別\" text,\"製令單號\" text,\"TA001\" text,\"TA002\" text,\"訂單交期\" text,\"訂單數量\" text,\"預計生產完成日\" text,\"客戶圖號\" text,\"order_name\" text,\"TD001\" text,\"TD002\" text,\"TD003\" text,\"key\" text,\"製令開單日期\" text,employee_id text, \"製令開單人\" text,\"預計熱處理日期\" text,\"訂單開單日期\" text)
            LEFT JOIN (
                SELECT order_processes_rfid_tag_id.\"TA001\",order_processes_rfid_tag_id.\"TA002\",
                    COALESCE(SUM(CASE WHEN problem.problem_name = '不良品' THEN problem_tag.problem_number END),0) problem_number,
                    COALESCE(SUM(CASE WHEN problem.problem_name = '熱處理' THEN problem_tag.problem_number END),0) hot_number
                FROM rfid.problem_tag
                LEFT JOIN rfid.problem ON problem.problem_id = problem_tag.problem_id
                LEFT JOIN (
                    SELECT TRIM(order_processes.fk->>'TA001')\"TA001\",TRIM(order_processes.fk->>'TA002')\"TA002\",order_processes_tag.rfid_tag_id
                    FROM order_processes_tag
                    LEFT JOIN order_processes ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                    GROUP BY TRIM(order_processes.fk->>'TA001'),TRIM(order_processes.fk->>'TA002'),rfid_tag_id
                )order_processes_rfid_tag_id ON order_processes_rfid_tag_id.rfid_tag_id = problem_tag.rfid_tag_id
                GROUP bY order_processes_rfid_tag_id.\"TA001\",order_processes_rfid_tag_id.\"TA002\"
            )order_processes_rfid_tag_id_problem_tag ON 
                TRIM(order_processes_outer.\"TA001\") = order_processes_rfid_tag_id_problem_tag.\"TA001\"
                AND TRIM(order_processes_outer.\"TA002\") = order_processes_rfid_tag_id_problem_tag.\"TA002\"
            LEFT JOIN (
                SELECT TRIM(order_processes.fk->>'TA001')\"TA001\",TRIM(order_processes.fk->>'TA002')\"TA002\",MAX(order_processes_tag.rfid_tag_id)rfid_tag_id
                FROM order_processes_tag
                LEFT JOIN order_processes ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                GROUP BY TRIM(order_processes.fk->>'TA001'),TRIM(order_processes.fk->>'TA002')
            )order_processes_tag ON TRIM(order_processes_outer.\"TA001\") = order_processes_tag.\"TA001\"
                AND TRIM(order_processes_outer.\"TA002\") = order_processes_tag.\"TA002\"
            LEFT JOIN (
                SELECT *
                FROM json_to_recordset('{$data['order_processes']}')
                 AS order_processes_floor(\"TA001\" text,\"TA002\" text,floor_name text)
            )order_processes_floor ON 
                TRIM(order_processes_outer.\"TA001\") = TRIM(order_processes_floor.\"TA001\")
                AND TRIM(order_processes_outer.\"TA002\") = TRIM(order_processes_floor.\"TA002\")
            ORDER BY order_processes_outer.\"TA002\" DESC
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure","error"=>$stmt->errorInfo()];
        $result["data"]=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result["data"] as $index => $row) {
            foreach ($row as $key => $value) {
                if($this->isJson($value) && gettype(json_decode($value,true)) !== "double") $result["data"][$index][$key] = json_decode($value,true);
            }
        }
        return $result;
    }

    public function getOrderProcessesOuter($data)
    {
        $result = $this->get_order_processes_detail_outer($data);
        $sql = "WITH countTB AS (
                SELECT order_name,COUNT(*),
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'user_name',tmpdb.user_name,
                            'rfid_tag',rfid_tag,
                            'rfid_tag_time',to_char(rfid_tag_time,'YYYY-MM-DD HH24:MI')
                        )
                        ORDER BY rfid_tag_time DESC
                    )rfid_tag_record
                FROM (
                    SELECT replace(CONCAT(order_processes.fk->'TA001' ::TEXT ,'-', order_processes.fk->'TA002'),'\"','') AS order_name,rfid_tag.*,COALESCE(\"user\".name,'-') user_name
                    FROM public.order_processes
                    LEFT JOIN order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                    LEFT JOIN rfid_tag ON rfid_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                    LEFT JOIN system.\"user\" ON \"user\".id = rfid_tag.user_id
                    GROUP BY replace(CONCAT(order_processes.fk->'TA001' ::TEXT ,'-', order_processes.fk->'TA002'),'\"','') , rfid_tag.rfid_tag_id,\"user\".name
                ) AS tmpdb
                GROUP BY order_name
            ),order_processes_outer AS (
                  SELECT *
                FROM json_to_recordset('{$result['data']}') as order_processes_outer(\"製令單別\" text,\"製令單號\" text,\"TA001\" text,\"TA002\" text,\"訂單交期\" text,\"訂單數量\" text,\"預計生產完成日\" text,\"客戶圖號\" text,\"order_name\" text,\"TD001\" text,\"TD002\" text,\"TD003\" text,\"key\" text,\"製令開單日期\" text,employee_id text, \"製令開單人\" text,\"預計熱處理日期\" text,\"訂單開單日期\" text)

            )
            SELECT order_processes_outer.*,COALESCE(order_processes.rfid_tag_time::TEXT,'-') AS \"列印時間\", COALESCE(order_processes.count,0) AS \"列印次數\", COALESCE(\"user\".name,'-') AS \"列印人\",COALESCE(order_processes.rfid_tag_record,'[]')rfid_tag_record
            FROM order_processes_outer
            LEFT JOIN (
                SELECT order_processes.order_processes_id, replace(CONCAT(order_processes.fk->'TA001') ,'\"','') AS \"TA001\",replace(CONCAT(order_processes.fk->'TA002') ,'\"','') AS \"TA002\",rfid_tag.user_id,rfid_tag.rfid_tag_time,countTB.count,
		            ROW_NUMBER() OVER(PARTITION BY replace(CONCAT(order_processes.fk->'TA001' ::TEXT ,'-', order_processes.fk->'TA002'),'\"','') ORDER BY rfid_tag_time DESC) AS rownum,countTB.rfid_tag_record
                FROM public.order_processes
                LEFT JOIN order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                LEFT JOIN rfid_tag ON rfid_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                LEFT JOIN countTB ON RTRIM(LTRIM(countTB.order_name)) = RTRIM(LTRIM(replace(CONCAT(order_processes.fk->'TA001' ::TEXT ,'-', order_processes.fk->'TA002'),'\"','')))
                ORDER BY rfid_tag_time DESC,order_processes.order_processes_id ASC
            )AS order_processes ON RTRIM(LTRIM(order_processes_outer.\"TA001\")) = RTRIM(LTRIM(order_processes.\"TA001\"))
                AND RTRIM(LTRIM(order_processes_outer.\"TA002\"))  = RTRIM(LTRIM(order_processes.\"TA002\"))  AND  order_processes.rownum = 1
            LEFT JOIN system.\"user\" ON \"user\".id = order_processes.user_id
            ORDER BY COALESCE(order_processes.count,0) DESC, \"製令單別\" || '-' || \"製令單號\" ASC
        ";


        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute())  return ["status"=>"failure"];
        $result["data"]=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result["data"] as $index => $row) {
            foreach ($row as $key => $value) {
                if($this->isJson($value) && gettype(json_decode($value,true)) !== "double") $result["data"][$index][$key] = json_decode($value,true);
            }
        }
        return $result;
    }
    public function getOrderProcessesLabel($data)
    {
        $values = [
            'TA001' => '',
            'TA002' => '',
            'order_name' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $result = [
            "cLine1" => "{$data['TA001']}-{$data['TA002']}",
            "cLine2" => "{$data['order_name']}"
        ];
        return $result;
    }
    public function insertRFIDTag($data)
    {
        $values = [
            "rfid_tag" => '',
            "user_id" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "INSERT INTO rfid_tag (rfid_tag,user_id)
            VALUES (:rfid_tag,:user_id)
            RETURNING rfid_tag_id;
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchColumn(0);
    }
    public function insertOrderProcessesTag($data)
    {
        $values = [
            "rfid_tag_id" => 0,
            "order_processes_id" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
    }
    public function getOrderProcessesFK($data)
    {
        $result = [];
        foreach ($data['RFID'] as $row) {
            $values = [
                "TA001" => '',
                "TA002" => '',
                "TA003" => '',
                "TA004" => ''
            ];
            foreach ($values as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $values[$key] = $row[$key];
                }
            }
            $jsonfk = [
                "fk" => json_encode($values)
            ];

            $sql = "SELECT order_processes_id
                FROM order_processes
                WHERE fk = :fk
            ";
            $stmt = $this->container->db->prepare($sql);
            $stmt->execute($jsonfk);
            if ($stmt->rowCount() != 0) {
                $result[] = $stmt->fetchColumn(0);
            } else {
                //order_id
                // $sql = "SELECT order_id FROM public.\"order\"
                //     WHERE fk->>'coptd_td001' = :coptd_td001 AND fk->>'coptd_td002' = :coptd_td002 AND fk->>'coptd_td003' = :coptd_td003
                //     ORDER BY order_id ASC;
                // ";
                // $stmt = $this->container->db->prepare($sql);
                // $stmt->bindValue(':coptd_td001', $values['TD001'], PDO::PARAM_STR);
                // $stmt->bindValue(':coptd_td002', $values['TD002'], PDO::PARAM_STR);
                // $stmt->bindValue(':coptd_td003', $values['TD003'], PDO::PARAM_STR);
                // $stmt->execute();
                // $jsonb_fk['order_id'] = intval($stmt->fetchColumn(0));
                //processes_id
                // $sql = "SELECT processes_id FROM public.\"processes_fk\"
                //     WHERE processes_fk_key = 'CMSMW.MW001' AND TRIM(processes_fk_value) = :processes_fk_value
                //     ORDER BY processes_id ASC;
                // ";
                // $stmt = $this->container->db->prepare($sql);
                // $stmt->bindValue(':processes_fk_value', $values['TA004'], PDO::PARAM_STR);
                // $stmt->execute();
                // $jsonb_fk['processes_id'] = intval($stmt->fetchColumn(0));
                //order_processes_index
                // $jsonfk['order_processes_index'] = intval($values['TA003']);
                // $jsonfk['amount'] = $row['TA015'];
                // $jsonfk['amount'] = intval($jsonfk['amount']);
                // $jsonfk['order_id'] = 0;
                // $jsonfk['order_id'] = 0;
                $sql = "INSERT INTO public.order_processes (fk)
                    VALUES (:fk)
                    RETURNING order_processes_id;
                ";
                $stmt = $this->container->db->prepare($sql);
                $stmt->execute($jsonfk);
                $result[] = $stmt->fetchColumn(0);
            }
        }
        return $result;
    }
    public function insertOrderProcessesRFIDTag($data)
    {
        $values = [
            "order_processes_id" => [],
            "rfid_tag_id" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $stmt_string = implode(
            ",",
            array_map(function($index){
                return "(:order_processes_id_{$index},:rfid_tag_id_{$index})";
            },range(0,count($values['order_processes_id'])-1))
        );
        $stmt_array = [];

        foreach($values['order_processes_id'] as $index => $order_processes){
            $stmt_array["order_processes_id_{$index}"] = $order_processes;
            $stmt_array["rfid_tag_id_{$index}"] = $values['rfid_tag_id'];
        }

        $sql = "INSERT INTO public.order_processes_tag(order_processes_id, rfid_tag_id)
            VALUES {$stmt_string}
            RETURNING order_processes_tag_id;
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($stmt_array);
        return [
            "status" => "success"
        ];
    }
    public function createAddress()
    {
        $sql = "INSERT INTO rfid_address (port)
                VALUES (5084)
                RETURNING id address_id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            return [
                'status' => 'success',
                'address_id' => $stmt->fetchColumn()
            ];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function readAddress()
    {
        $sql = "SELECT id address_id, \"tAddress\" address, port
                FROM rfid_address
                ORDER BY id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function updateAddress($body)
    {
        /* $body = "
        [
            {
              \"id\":1, //address_id
              \"tAddress\":\"192.0.0.2\",
              \"port\":5084,
              \"antenna\":[
                {
                  \"iAntennaID\":1, //第幾支天線
                  \"machine_id\":1,
                  \"status\":\"running\"
                }
              ]
            }
          ]
        "; */
        foreach ($body as $body_index => $data) {
            $sql = "UPDATE rfid_address
                    SET \"tAddress\" = :tAddress, port = :port
                    WHERE id = :id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':tAddress', $data['tAddress'], PDO::PARAM_STR);
            $stmt->bindValue(':port', $data['port'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
            if (!$stmt->execute()) {
                return [
                    'status' => 'failure',
                    'error_info' => $stmt->errorInfo(),
                    'message' => [
                        'address' => $body_index
                    ]
                ];
            }
            if(array_key_exists("antenna",$data)){
                $antennas = $data['antenna'];
                foreach ($antennas as $antenna_index => $antenna) {
                    $values = [
                        "address_id"=>$data['id'],
                        "iAntennaID" => 0,
                        "TransmitPowerIndex" => null
                    ];
                    foreach ($values as $key => $value) {
                        if(array_key_exists($key,$antenna))
                            $values[$key] = $antenna[$key];
                    }
                    /*  */
                    $sql = "INSERT INTO rfid_antenna(address_id,\"iAntennaID\",\"TransmitPowerIndex\")
                        VALUES(:address_id,:iAntennaID,:TransmitPowerIndex)
                        ON CONFLICT(address_id,\"iAntennaID\")
                        DO UPDATE SET \"iAntennaID\" = rfid_antenna.\"iAntennaID\",address_id = rfid_antenna.address_id, \"TransmitPowerIndex\" = :TransmitPowerIndex
                        RETURNING id
                    ";
                    $stmt = $this->db->prepare($sql);
                    if(!$stmt->execute($values)){
                        return [
                            'status' => 'failure',
                            'error_info' => $stmt->errorInfo(),
                            'message' => [
                                'antenna' => $antenna_index,
                                'address' => $body_index
                            ]
                        ];
                    }
                    /*  */
                    unset($antenna['antenna_id']);
                    $values = [
                        'antenna_id' => $stmt->fetchColumn(0),
                        'status' => null,
                        'machine_id' => 0
                    ];
                    foreach ($values as $key => $value) {
                        if(array_key_exists($key,$antenna))
                            $values[$key] = $antenna[$key];
                    }
                    $sql = "INSERT INTO public.rfid_antenna_machine(machine_id,antenna_id,status)
                        VALUES(:machine_id,:antenna_id,:status)
                        ON CONFLICT(antenna_id)
                        DO UPDATE SET \"machine_id\" = EXCLUDED.\"machine_id\",status = EXCLUDED.status
                    ";
                    $stmt = $this->db->prepare($sql);
                    if(!$stmt->execute($values)){
                        return [
                            'status' => 'failure',
                            'error_info' => $stmt->errorInfo(),
                            'message' => [
                                'antenna' => $antenna_index,
                                'address' => $body_index
                            ]
                        ];
                    }
                }
            }
        }
    }
    public function deleteAddress($body)
    {
        $sql = "DELETE FROM rfid_antenna
                WHERE address_id = :address_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':address_id', $body['address_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            $sql = "DELETE FROM rfid_address
                    WHERE id = :id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $body['address_id'], PDO::PARAM_INT);
            if ($stmt->execute()) {
                return ['status' => 'success',];
            } else {
                return [
                    'status' => 'failure',
                    'error_info' => $stmt->errorInfo()
                ];
            }
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function createAntenna($body)
    {
        $sql = "INSERT INTO rfid_antenna (address_id)
                VALUES (:address_id)
                RETURNING id antenna_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':address_id', $body['address_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return [
                'status' => 'success',
                'antenna_id' => $stmt->fetchColumn()
            ];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function readAntenna($params)
    {
        $sql = "SELECT id antenna_id, \"iAntennaID\" antenna_code,\"TransmitPowerIndex\"
                FROM rfid_antenna
                WHERE address_id = :address_id
                ORDER BY id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':address_id', $params['address_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function updateAntenna($body)
    {
        $sql = "UPDATE rfid_antenna
                SET \"iAntennaID\" = :iAntennaID
                WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':iAntennaID', $body['antenna_code'], PDO::PARAM_STR);
        $stmt->bindValue(':id', $body['antenna_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['status' => 'success',];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function deleteAntenna($body)
    {
        $sql = "DELETE FROM rfid_antenna
                WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $body['antenna_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['status' => 'success',];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function createOrderProcessesReferenceKey($key, $value)
    {
        $sql = "INSERT INTO reference_key(outer_key, local_key)
                VALUES (:outer_key, :local_key)
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->bindValue(':outer_key', $key, PDO::PARAM_STR);
        $stmt->bindValue(':local_key', $value, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function readOrderProcessesReferenceKey($params)
    {
        $sql = "SELECT outer_key, local_key, meaning
                FROM reference_key
                WHERE local_key = :local_key
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->bindValue(':local_key', $params, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function readOrderProcessesReferenceValue($params)
    {
        $sql = "SELECT order_processes_value AS local_val, \"{$params['outer_key']}\" outer_val
                FROM order_processes_fk,
                    JSONB_TO_RECORDSET(order_processes_fk.order_processes_jsonb) AS outer_val(\"{$params['outer_key']}\" TEXT)
                WHERE order_processes_key = :local_key
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->bindValue(':local_key', $params['local_key'], PDO::PARAM_STR);
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo(),
            ];
        }
    }
    public function readAddressDetail($params)
    {
        $sql = "SELECT rfid_address.id address_id, \"tAddress\" address,
                COALESCE(rfid_antenna_machine.antennas,'[]') antennas
            FROM rfid_address
            LEFT JOIN (
                SELECT rfid_antenna.address_id,
                    JSON_AGG(JSON_BUILD_OBJECT('TransmitPowerIndex',\"TransmitPowerIndex\",'status',rfid_antenna_machine.status,'antenna_id', rfid_antenna.id, 'antenna_code', rfid_antenna.\"iAntennaID\", 'machine_id', machine.machine_id, 'machine_code', machine.machine_code))antennas
                FROM rfid_antenna_machine
                LEFT JOIN rfid_antenna ON rfid_antenna_machine.antenna_id = rfid_antenna.id
                LEFT JOIN machine ON machine.machine_id = rfid_antenna_machine.machine_id
                GROUP BY rfid_antenna.address_id
            )rfid_antenna_machine ON rfid_antenna_machine.address_id = rfid_address.id
            ORDER BY rfid_address.id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row_key => $row) {
                foreach ($row as $key => $value) {
                    if ($this->isJson($value)) {
                        $result[$row_key][$key] = json_decode($value, true);
                    }
                }
            }
            return $result;
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    public function insertRFIDAntennaMachine($data)
    {
        $sql = "INSERT INTO public.rfid_antenna_machine(
                antenna_id, machine_id)
                VALUES (:antenna_id, :machine_id)
                ON CONFLICT (machine_id)
                DO UPDATE SET antenna_id = :antenna_id;
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->bindValue(':antenna_id', $data['antenna_id'], PDO::PARAM_INT);
        $stmt->bindValue(':machine_id', $data['machine_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function createAntennaMachine($body)
    {
        $sql = "INSERT INTO rfid_antenna_machine (machine_id, antenna_id)
                VALUES (:machine_id, :antenna_id)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':machine_id', $body['machine_id'], PDO::PARAM_INT);
        $stmt->bindValue(':antenna_id', $body['antenna_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['status' => 'success',];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function readAntennaMachine($params)
    {
        $sql = "SELECT rfid_antenna_machine.machine_id, rfid_antenna_machine.antenna_id, rfid_antenna.address_id
                FROM rfid_antenna_machine
                LEFT JOIN rfid_antenna ON rfid_antenna.id = rfid_antenna_machine.antenna_id
                LEFT JOIN machine ON machine.machine_id = rfid_antenna_machine.machine_id
                WHERE machine.floor_id = :floor_id
                ORDER BY machine_id, antenna_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':floor_id', $params['floor_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function syncAddress($data)
    {
        $local = $this->readAddress();
        foreach($data as $cReaderName => $oReader) {
            $iID = (int)substr($cReaderName, strlen("Reader")) - 1;
            if (isset($local[$iID])) {
                $sql = "UPDATE rfid_address
                        SET \"tAddress\" = :tAddress, port = :port
                        WHERE id = :id
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':tAddress', $data[$cReaderName]['cIP'], PDO::PARAM_STR);
                $stmt->bindValue(':port', $data[$cReaderName]['iPort'], PDO::PARAM_STR);
                $stmt->bindValue(':id', $local[$iID]['address_id'], PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $sql = "INSERT INTO rfid_address (\"tAddress\", port)
                        VALUES (:tAddress, :port)
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':tAddress', $data[$cReaderName]['cIP'], PDO::PARAM_STR);
                $stmt->bindValue(':port', $data[$cReaderName]['iPort'], PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
    public function readMachineStatus($params)
    {
        $sql = "SELECT machine_id, status
                FROM machine
                ORDER BY machine_id
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function getRFIDOuter($data)
    {
        $values = [
            'TA001' => '',
            'TA002' => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT TOP 1000 MOCTA.TA001
                    ,MOCTA.TA001,MOCTA.TA002,SFCTA.TA003,SFCTA.TA004
                    ,MOCTA.TA015

                FROM [MIL].[dbo].[MOCTA]
                LEFT JOIN MIL.dbo.SFCTA ON MOCTA.TA001 = SFCTA.TA001 AND MOCTA.TA002 = SFCTA.TA002
                WHERE
                (
                    MOCTA.TA001  =  :TA001
                    AND
                    MOCTA.TA002  =  :TA002
                )
                ORDER BY MOCTA.TA003 ASC
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function readCurrentOriginMaterialSupplier($params)
    {
        $sql = "SELECT origin_material_supplier.supplier_id, supplier.supplier_name,
                    origin_material_handler.receiver_user_id, \"user\".name receiver_name,
                    origin_material_supplier.origin_material_id
                /* require select more in origin_material_supplier */
                FROM origin_material_supplier
                LEFT JOIN origin_material_handler
                    ON origin_material_handler.origin_material_supplier_id = origin_material_supplier.origin_material_supplier_id
                LEFT JOIN supplier ON supplier.supplier_id = origin_material_supplier.supplier_id
                LEFT JOIN system.\"user\" ON \"user\".id = origin_material_handler.receiver_user_id
                /* LEFT JOIN purchase_order ? */
                WHERE origin_material_handler.receiver_user_id IN (
                    SELECT DISTINCT ON (user_id) user_id
                    FROM system.user_rfid_tag
                    LEFT JOIN system.user_modal ON user_modal.uid = user_rfid_tag.user_id
                    LEFT JOIN setting.module ON module.id = user_modal.module_id
                    WHERE rfid_tag IN
                    (
                        SELECT \"cTagID\"
                        FROM public.\"RFID_TABLE_Log\"
                        WHERE \"iAntennaID\" = 3
                            AND ('2022-12-31 00:00:00'::TIMESTAMP - INTERVAL '5 SECONDS') <= \"dTime\" AND \"dTime\" <= '2022-12-31 00:00:00'::TIMESTAMP  /* change timestamp to now(), fixed last 5 secs */
                        GROUP BY \"cTagID\"
                        ORDER BY \"dTime\" DESC, \"cTagID\" ASC
                    )
                    /* OR origin_material_supplier.supplier_id IN ({same as receiver}) */
                        AND user_modal.module_id IN (12, 13)  /* fixed (供應商, 收貨人) */
                    ORDER BY user_id, rfid_tag
                )
        ";
        $stmt = $this->db->prepare($sql);
        // $stmt->bindValue(':antenna_id', $params['antenna_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function get_line_machine_outer($data){
        $sql = "SELECT RTRIM(LTRIM(CMSMD.MD001)) [line_code],RTRIM(LTRIM(CMSMD.MD002)) [line_name],
            Stuff((
                SELECT RTRIM(LTRIM(t.MX001)) [machine_code],RTRIM(LTRIM(t.MX003)) [machine_name]
                FROM [MIL].[dbo].CMSMX t
                WHERE t.MX002 = CMSMD.MD001
                FOR XML PATH),1,0,''
            )[machines]
            FROM [MIL].[dbo].CMSMD
            WHERE CMSMD.MD001 NOT IN ('C', 'E')
            GROUP BY CMSMD.MD001,CMSMD.MD002
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (isset($result)) {
            foreach ($result as $key_result => $value) {
                $tmpvalue = $value['machines'];
                $tmpArrs = [];
                $xml = simplexml_load_string("<a>$tmpvalue</a>");
                if ($tmpvalue == "") {
                    $result[$key_result]['machines'] = $tmpArrs;
                    goto Endquotation;
                }
                foreach ($xml as $t) {
                    $tmpArr = [];
                    foreach ($t as $a => $b) {
                        $tmpArr[$a] = '';
                        foreach ((array)$b as $c => $d) {
                            $tmpArr[$a] = $d;
                        }
                    }
                    $tmpArrs[] = $tmpArr;
                }
                $result[$key_result]['machines'] = $tmpArrs;
                Endquotation:
            }
        }
        return $result;
    }
    public function get_lines_machines_processes_outer($data){
        $sql = "SELECT RTRIM(LTRIM(MX001)) [machine_code],RTRIM(LTRIM(MX003)) [machine_name], RTRIM(LTRIM(CMSMD.MD001)) line_code,RTRIM(LTRIM(CMSMD.MD002)) line_name,
                STUFF((
                    SELECT ','+RTRIM(LTRIM(MW001))
                    FROM [MIL].[dbo].CMSMW
                    WHERE CMSMW.MW005 = CMSMD.MD001
                FOR XML PATH('')
            ), 1, 1, '') processes
            FROM [MIL].[dbo].CMSMX
            LEFT JOIN [MIL].[dbo].CMSMD ON CMSMX.MX002 = CMSMD.MD001;
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_machines_outer($data){
        $sql = "SELECT MX001 [machine_code],MX003 [machine_name],CMSMD.MD001 [line_code],CMSMD.MD002 [line_name]
        -- , MW001 [processes_id]
                FROM [MIL].[dbo].CMSMX
                LEFT JOIN [MIL].[dbo].CMSMD ON CMSMX.MX002 = CMSMD.MD001
                -- LEFT JOIN [MIL].[dbo].CMSMW ON CMSMW.MW005 = CMSMD.MD001
        ";
        // $stmt = $this->db_sqlsrv->prepare($sql);
        // if ($stmt->execute()) {
        //     $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //     return $result;
        // } else {
        //     return [
        //         'status' => 'failure',
        //         'error_info' => $stmt->errorInfo()
        //     ];
        // }
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_machine_process($data){
        $sql = "SELECT MX001 [machine_code],MX003 [machine_name], MW001 [processes_id]
                FROM [MIL].[dbo].CMSMX
                LEFT JOIN [MIL].[dbo].CMSMD ON CMSMX.MX002 = CMSMD.MD001
                LEFT JOIN [MIL].[dbo].CMSMW ON CMSMW.MW005 = CMSMD.MD001
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
        // $sql = "SELECT MX001 \"machine_code\",MX003 \"machine_name\",
        //         Stuff((
        //             SELECT MW001 \"processes_id\",MW002 \"processes_name\"
        //             FROM [MIL].[dbo].CMSMW
        //             WHERE CMSMW.MW005 = CMSMD.MD001
        //             FOR XML PATH),1,0,''
        //         )\"processes\"
        //     FROM [MIL].[dbo].CMSMX
        //     LEFT JOIN [MIL].[dbo].CMSMD ON CMSMX.MX002 = CMSMD.MD001
        // ";
        // $stmt = $this->db_sqlsrv->prepare($sql);
        // if ($stmt->execute()) {
        //     $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //     if (isset($result)) {
        //         foreach ($result as $key_result => $value) {
        //             $tmpvalue = $value['processes'];
        //             $tmpArrs = [];
        //             $xml = simplexml_load_string("<a>$tmpvalue</a>");
        //             if ($tmpvalue == "") {
        //                 $result[$key_result]['processes'] = $tmpArrs;
        //                 goto Endquotation;
        //             }
        //             foreach ($xml as $t) {
        //                 $tmpArr = [];
        //                 foreach ($t as $a => $b) {
        //                     $tmpArr[$a] = '';
        //                     foreach ((array)$b as $c => $d) {
        //                         $tmpArr[$a] = $d;
        //                     }
        //                 }
        //                 $tmpArrs[] = $tmpArr;
        //             }
        //             $result[$key_result]['processes'] = $tmpArrs;
        //             Endquotation:
        //         }
        //     }
        //     return $result;
        // } else {
        //     return [
        //         'status' => 'failure',
        //         'error_info' => $stmt->errorInfo()
        //     ];
        // }
    }
    public function get_rfid_order_processes_machine_area($data){
        $sql = "SELECT COPTD.TD001,COPTD.TD002,COPTD.TD003
        FROM [MIL].[dbo].[MOCTA]
        LEFT JOIN MIL.dbo.COPTD dt ON (dt.TD001=MOCTA.TA026 and dt.TD002=MOCTA.TA027 and dt.TD003=MOCTA.TA028)
        WHERE MOCTA.TA011 NOT IN ( 'Y' )
            AND
            (
                MOCTA.TA001  Is Null
                OR
                MOCTA.TA001  NOT IN  ( '5202','5205','5198','5199','5207','5203','5204'  )
            )
        ";
        $values = [
            "floor_id" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $sql = "SELECT machines_area_id, machines_area_name, machines_area_floor_serial
            FROM rfid.machines_area
            WHERE floor_id=:floor_id
            ORDER BY machines_area_floor_serial
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return["status"=>"failed"];

    }
    public function get_rfid_order_processes_machine($data){
        $with = $this->get_rfid_order_processes_detail($data);
        $mode = [
            "detail" => true
        ];
        foreach ($mode as $key => $value) {
            array_key_exists($key,$data)&&$mode[$key]=$data[$key];
        }
        $stmt_array = [
        ];
        $stmt_string = "";
        if($mode["detail"]){
            $stmt_array = [
                "status" => 'running'
            ];
            $stmt_string = "WHERE current_machine_status = :status";
            foreach ($stmt_array as $key => $value) {
                array_key_exists($key,$data)&&$stmt_array[$key]=$data[$key];
            }
        }
        $sql = $with;
        $sql .= "SELECT *
                FROM \"with\"
                {$stmt_string}
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($stmt_array)) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row_key => $row) {
                foreach ($row as $key => $value) {
                    if ($this->isJson($value)) {
                        $result[$row_key][$key] = json_decode($value, true);
                    }
                }
            }
            return $result;
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function get_machine_set($data){
        $machines = $this->get_machines_outer($data);
        $machines = json_encode($machines);
        $sql = "SELECT machine.machine_id,COALESCE(TRIM(machine_outer.machine_code),machine.machine_code) machine_code,TRIM(machine_outer.machine_name)machine_name
                FROM machine
                LEFT JOIN json_to_recordset('$machines')
                as machine_outer(machine_code text,machine_name text,line_code text,line_name text) ON trim(machine.machine_code) = trim(machine_outer.machine_code)
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }

    public function schedule_order_processes_record($data){
        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $sql = "SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes_tag.rfid_tag_id,MIN(rfid_tag.rfid_tag_time)rfid_tag_time,
                JSON_AGG(JSON_BUILD_OBJECT('order_processes_id',order_processes.order_processes_id)) order_processes_ids
            FROM public.order_processes
            LEFT JOIN order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
            LEFT JOIN rfid_tag ON rfid_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
            WHERE (order_processes.fk->>'TA001',order_processes.fk->>'TA002') IN (
                SELECT order_processes.\"TA001\",order_processes.\"TA002\"
                FROM(
                    SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"TA003\"
                    FROM public.order_processes
                    CROSS JOIN (
                        SELECT 'running' status
                    )status
                    LEFT JOIN rfid.order_processes_record ON order_processes.order_processes_id = order_processes_record.order_processes_id AND status.status = order_processes_record.status
                    GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003'
                    HAVING MAX(order_processes_record.end_time) IS NULL
                )order_processes
                GROUP BY order_processes.\"TA001\",order_processes.\"TA002\"
            ) AND order_processes.fk->>'TA003' IS NOT NULL AND TRIM(order_processes.fk->>'TA003') != ''
            GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes_tag.rfid_tag_id
            ORDER BY order_processes.fk->>'TA001',order_processes.fk->>'TA002';
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $order_processes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($order_processes as $order_process){
            $sql = "SELECT order_processes.order_processes_id,order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"TA003\"
                FROM public.order_processes
                WHERE (order_processes.fk->>'TA001',order_processes.fk->>'TA002') IN (
                    SELECT order_processes.fk->>'TA001',order_processes.fk->>'TA002'
                    FROM public.order_processes
                    LEFT JOIN rfid.order_processes_record ON order_processes.order_processes_id = order_processes_record.order_processes_id
                    GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002'
                ) AND order_processes.fk->>'TA001' = :TA001 AND order_processes.fk->>'TA002' = :TA002 AND order_processes.order_processes_id IN (
                    SELECT order_processes_id
                    FROM jsonb_to_recordset('{$order_process['order_processes_ids']}')
                        AS dt(order_processes_id integer)
                )
                AND order_processes.fk->>'TA003' IS NOT NULL AND TRIM(order_processes.fk->>'TA003') != ''
                GROUP BY order_processes.order_processes_id,order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003'
                ORDER BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003';
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                [
                    "TA001" => $order_process['TA001'],
                    "TA002" => $order_process['TA002'],
                ]
            );
            $order_process_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sql = "WITH \"New\" AS(
                    SELECT \"cTagID\",\"cReaderName\", \"cIP\", \"iAntennaID\",\"dTime\" \"New\",COALESCE(LEAD(\"dTime\",1) OVER (
                        PARTITION BY \"cTagID\",\"cReaderName\", \"cIP\", \"iAntennaID\"
                        ORDER BY \"cTagID\",\"dTime\"
                    ),LEAD(\"dTime\",1) OVER (
                        PARTITION BY \"cTagID\"
                        ORDER BY \"cTagID\",\"dTime\"
                    )) \"Next\",\"RFID_TABLE_Log\".\"cTagEvent\"
                    FROM public.\"RFID_TABLE_Log\"
                    WHERE \"cTagEvent\" = 'New' AND \"RFID_TABLE_Log\".\"dTime\" > :rfid_tag_time
                    ORDER BY \"cTagID\",\"dTime\"
                )
                SELECT ROW_NUMBER() OVER (PARTITION BY rfid_tag.\"TA001\", rfid_tag.\"TA002\" ORDER BY \"dt\".\"New\" ASC, rfid_tag.\"TA003\" DESC ),rfid_tag.order_processes_id,rfid_tag.\"TA001\", rfid_tag.\"TA002\", rfid_tag.\"TA003\", rfid_tag.\"TA004\",machine.machine_code,dt.\"New\",dt.\"Gone\",rfid_antenna_machine.status,lines_machines_processes_outer.processes
                FROM (
                    SELECT order_processes.order_processes_id,fk->>'TA001' \"TA001\",fk->>'TA002' \"TA002\",fk->>'TA003' \"TA003\",fk->>'TA004' \"TA004\",rfid_tag.rfid_tag
                    FROM public.order_processes
                    LEFT JOIN order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                    LEFT JOIN rfid_tag ON rfid_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                )rfid_tag
                LEFT jOIN (
                    SELECT \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\".\"New\",\"New\".\"Next\",\"Back\",COALESCE(\"Gone\",\"New\".\"Next\")\"Gone\"
                    FROM(
                        SELECT \"cTagID\",\"cReaderName\", \"cIP\", \"iAntennaID\",\"New\",\"Next\"
                        FROM \"New\"
                    )\"New\"
                    LEFT JOIN(
                        SELECT \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\",MAX(\"dTime\") \"Back\"
                        FROM \"New\"
                        LEFT JOIN public.\"RFID_TABLE_Log\" ON \"New\".\"cTagID\" = \"RFID_TABLE_Log\".\"cTagID\" AND \"New\".\"cIP\" = \"RFID_TABLE_Log\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"RFID_TABLE_Log\".\"iAntennaID\"
                            AND \"New\".\"New\" < \"RFID_TABLE_Log\".\"dTime\" AND COALESCE(\"New\".\"Next\",NOW()) > \"RFID_TABLE_Log\".\"dTime\"
                        WHERE \"RFID_TABLE_Log\".\"cTagEvent\" = 'Back'
                        GROUP BY \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\"
                    )\"Back\" ON \"New\".\"cTagID\" = \"Back\".\"cTagID\" AND \"New\".\"cIP\" = \"Back\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"Back\".\"iAntennaID\" AND \"New\".\"New\" = \"Back\".\"New\" AND COALESCE(\"New\".\"Next\"::text,'null') = COALESCE(\"Back\".\"Next\" ::text,'null')
                    LEFT JOIN(
                        SELECT \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\",MAX(\"dTime\") \"Gone\"
                        FROM \"New\"
                        LEFT JOIN public.\"RFID_TABLE_Log\" ON \"New\".\"cTagID\" = \"RFID_TABLE_Log\".\"cTagID\" AND \"New\".\"cIP\" = \"RFID_TABLE_Log\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"RFID_TABLE_Log\".\"iAntennaID\"
                            AND \"New\".\"New\" < \"RFID_TABLE_Log\".\"dTime\" AND COALESCE(\"New\".\"Next\",NOW()) > \"RFID_TABLE_Log\".\"dTime\"
                        WHERE \"RFID_TABLE_Log\".\"cTagEvent\" = 'Gone'
                        GROUP BY \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\"
                    )\"Gone\" ON \"New\".\"cTagID\" = \"Gone\".\"cTagID\" AND \"New\".\"cIP\" = \"Gone\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"Gone\".\"iAntennaID\" AND \"New\".\"New\" = \"Gone\".\"New\" AND COALESCE(\"New\".\"Next\"::text,'null') = COALESCE(\"Gone\".\"Next\" ::text,'null')
                )dt ON dt.\"cTagID\" = rfid_tag.rfid_tag
                INNER JOIN rfid_address ON rfid_address.\"tAddress\" = dt.\"cIP\"
                INNER JOIN rfid_antenna ON rfid_antenna.address_id = rfid_address.id AND dt.\"iAntennaID\" = rfid_antenna.\"iAntennaID\"
                INNER JOIN rfid_antenna_machine ON rfid_antenna_machine.antenna_id = rfid_antenna.id
                INNER JOIN 
                (
                    SELECT machine_id, machine_name, floor_id, machine_code, position_id, machines_area_id
                    FROM machine
                    WHERE machine.machine_id NOT IN (
                        SELECT machine_id
                        FROM rfid_antenna_machine
                        WHERE status = 'waiting'
                    )
                    UNION ALL (
                        SELECT machine.machine_id,COALESCE(machine_other.machine_name,machine.machine_name)machine_name,
                        machine.floor_id,COALESCE(machine_other.machine_code,machine.machine_code)machine_code,
                        machine.position_id,machine.machines_area_id
                        FROM machine
                        LEFT JOIN (
                            SELECT *
                            FROM machine
                            WHERE machine.machine_id NOT IN (
                                SELECT machine_id
                                FROM rfid_antenna_machine
                                WHERE status = 'waiting'
                            )
                        ) machine_other ON machine_other.machines_area_id = machine.machines_area_id
                        WHERE machine.machine_id IN (
                            SELECT machine_id
                            FROM rfid_antenna_machine
                            WHERE status = 'waiting'
                        )
                    )
                )
                machine ON machine.machine_id = rfid_antenna_machine.machine_id
                INNER JOIN json_to_recordset('$lines_machines_processes_outer')
                    AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                    ON lines_machines_processes_outer.\"machine_code\" = machine.machine_code AND lines_machines_processes_outer.processes like '%' || rfid_tag.\"TA004\" || '%'
                WHERE rfid_tag.\"TA001\" = :TA001 AND rfid_tag.\"TA002\" = :TA002
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                [
                    "TA001" => $order_process['TA001'],
                    "TA002" => $order_process['TA002'],
                    "rfid_tag_time" => $order_process['rfid_tag_time'],
                ]
            );
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $order_process_list_ = array_shift($order_process_list);
            foreach ($records as $record_index => $record) {
                restart_compare:
                if(
                    $order_process_list_['TA001'] ===  $record['TA001']
                    &
                    $order_process_list_['TA002'] ===  $record['TA002']
                    &
                    $order_process_list_['TA003'] ===  $record['TA003']
                ){
                    !array_key_exists($record['status'],$order_process_list_)&&
                        $order_process_list_[$record['status']] = [
                            'start_time' => null,
                            'end_time' => null,
                        ];
                    is_null($order_process_list_[$record['status']]['start_time'])&&$order_process_list_[$record['status']]['start_time']=$record['New'];
                    $order_process_list_[$record['status']]['end_time']=$record['Gone'];
                    $order_process_list_[$record['status']]['machine_code']=$record['machine_code'];
                }else if(
                    array_key_exists('running',$order_process_list_)
                ){
                    to_update:
                    foreach(['waiting','ready','running'] as $status){
                        if(array_key_exists($status,$order_process_list_)){
                            $sql = "INSERT INTO rfid.order_processes_record (order_processes_id, status, start_time, end_time, machine_code)
                                VALUES (?,?,?,?,?)
                                ON CONFLICT (order_processes_id, status)
                                DO UPDATE
                                SET start_time = EXCLUDED.start_time, end_time = EXCLUDED.end_time
                            ";
                            $stmt = $this->db->prepare($sql);
                            $stmt->execute([
                                $order_process_list_['order_processes_id'],
                                $status,
                                $order_process_list_[$status]['start_time'],
                                $order_process_list_[$status]['end_time'],
                                $order_process_list_[$status]['machine_code'],
                            ]);
                        }
                    }
                    $order_process_list_ = array_shift($order_process_list);
                    if(is_null($order_process_list_))
                        break;
                    goto restart_compare;
                }
                if(
                    (
                        array_key_exists('waiting',$order_process_list_)
                        |
                        array_key_exists('ready',$order_process_list_)
                        |
                        array_key_exists('running',$order_process_list_)
                    )
                    &
                    COUNT($records)-1 === $record_index
                ){
                    goto to_update;
                }
                // var_dump('-----');
                // var_dump($order_process_list_);
                // var_dump($record);
            }
            // if(
            //     array_key_exists('waiting',$order_process_list_)
            //     |
            //     array_key_exists('ready',$order_process_list_)
            //     |
            //     array_key_exists('running',$order_process_list_)
            // )
            // exit(0);
        }
        // exit(0);
    }

    public function get_rfid_history($data){
        $values = [
            'number' => null
        ];
        foreach (array_keys($values) as $key) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $sql = "SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",(order_processes.fk->>'TA001') || '-' || (order_processes.fk->>'TA002') number,order_processes_record.status,order_processes_record.start_time actual_in_time,order_processes_record.end_time actual_out_time,lines_machines_processes_outer.line_code,lines_machines_processes_outer.line_name,
                order_processes_record.machine_code,
                lines_machines_processes_outer.machine_name,
                COALESCE(order_processes_record.end_time,NOW())::timestamp-order_processes_record.start_time::text::timestamp work_time
            FROM rfid.order_processes_record
            LEFT JOIN public.order_processes ON order_processes_record.order_processes_id = order_processes.order_processes_id
            INNER JOIN json_to_recordset('$lines_machines_processes_outer')
                AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
            WHERE TRIM(order_processes.fk->>'TA001') || '-' || TRIM(order_processes.fk->>'TA002') = :number
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_rfid_order_processes_detail($data){

        foreach ($data as $key => $value) {
            in_array($key,['process_id','line_id','machine_id'])&&$data[$key]=urldecode($value);
            $this->isJson($value)&&$data[$key]=json_decode($value,true);
        }
        $machines = $this->get_machines_outer($data);
        $machines = json_encode($machines);
        $data['processes_id'] = $this->get_processes_filter($data);

        $result = $this->get_order_processes_outer_detail($data);
        $orderprocesses = json_encode($result['data']);

        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);
        $values = [
            "order"=>[]
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $order = [
            "name" => null,
            "sort" => null
        ];
        foreach ($order as $key => $value) {
            array_key_exists($key,$values["order"])&&$order[$key]=$values["order"][$key];
        }
        $sort = " ORDER BY dt.\"TA001\" || '-' || dt.\"TA002\" ";
        if(!is_null($order['name'])&&!is_null($order['sort'])){
            switch ($order['name']) {
                case 'order_serial':
                    $sort = " ORDER BY dt.\"TD001\" || '-' || dt.\"TD002\" || '-' || dt.\"TD003\" ";
                    break;
                case 'order_processes_serial':
                    $sort = " ORDER BY dt.\"TA001\" || '-' || dt.\"TA002\" ";
                    break;
                case 'date':
                    $sort = " ORDER BY dt.\"TA009\" ";
                    break;
            }
            if(strtolower($order['sort'])==='descend') $sort.=" desc ";
        }
        $SFT = "SELECT dt.\"TA001\", dt.\"TA002\",dt.\"TA001\" || '-' || dt.\"TA002\" number,
            dt.line_code,dt.line_name line_name,dt.machine_code machine_code,
            dt.machine_name machine_name,dt.\"WR007\",
            dt.machine_name current_machine,
            dt.machine_code current_machine_code,
            'ready' current_machine_status,
            COALESCE(dt.running_out,NOW())::timestamp-dt.running_in::text::timestamp work_time,
            dt.\"running_out\" actual_out_time,
            dt.\"running_in\" actual_in_time,
            null preset_in_time,
            null preset_out_time
        FROM(
            SELECT ROW_NUMBER() OVER (PARTITION BY order_processes_outer.\"TA001\", order_processes_outer.\"TA002\" ORDER BY \"SFT\".\"WR007\" DESC ) row_number,*
            FROM order_processes_outer
            LEFT JOIN jsonb_to_recordset(order_processes_outer.\"SFT\") AS \"SFT\"(\"ID\" text,\"WR007\" text,\"WR008\" text,line_code text,line_name text,machine_code text,machine_name text,waiting_in timestamp,waiting_out timestamp,running_in timestamp,running_out timestamp,ready_in timestamp,ready_out timestamp)
                ON TRIM(order_processes_outer.\"TA001\")||'-'||TRIM(order_processes_outer.\"TA002\") = \"SFT\".\"ID\"
        )dt";
        if(array_key_exists('type',$data)){
            if($data['type']==='RFID'){
                $SFT = "SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",(order_processes.fk->>'TA001') || '-' || (order_processes.fk->>'TA002') number,'ready' current_machine_status,MIN(order_processes_record.start_time) actual_in_time,MAX(order_processes_record.end_time) actual_out_time,lines_machines_processes_outer.line_code,lines_machines_processes_outer.line_name,
                        order_processes_record.machine_code current_machine_code,
                        order_processes_record.machine_code,
                        lines_machines_processes_outer.machine_name,
                        lines_machines_processes_outer.machine_name current_machine,
                        MAX(COALESCE(order_processes_record.end_time,NOW()))::timestamp-MIN(order_processes_record.start_time)::text::timestamp work_time,
                        null preset_in_time,
                        null preset_out_time
                    FROM rfid.order_processes_record
                    LEFT JOIN public.order_processes ON order_processes_record.order_processes_id = order_processes.order_processes_id
                    INNER JOIN json_to_recordset('$lines_machines_processes_outer')
                        AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                        ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
                    GROUP BY order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',(order_processes.fk->>'TA001') || '-' || (order_processes.fk->>'TA002'),
                        lines_machines_processes_outer.line_code,lines_machines_processes_outer.line_name,
                        order_processes_record.machine_code,
                        order_processes_record.machine_code,
                        lines_machines_processes_outer.machine_name,
                        lines_machines_processes_outer.machine_name
                ";
            }
        }
        $with = "WITH order_processes_outer AS (
                SELECT *
                FROM json_to_recordset('{$orderprocesses}')
                AS order_processes_outer(\"key\" integer,\"TA001\" text,\"TA002\" text,\"TA009\" text,\"TA012\" text,\"TA014\" text,\"TD004\" text, \"TD006\" text, \"XB002\" text,preset_count text,production_name text,number text,preset_time text, \"order\" jsonb, \"order_processes\" jsonb,\"SFT\" jsonb)
            ),\"with\" AS (
                SELECT '/3DConvert/PhaseGallery/order_image/' || COALESCE(coptd_file.file_id,0) img,ROW_NUMBER() OVER({$sort}) \"key\",*
                FROM(
                    SELECT dt.\"TD001\",dt.\"TD002\",dt.\"TD003\",dt.order_amount,TRIM(dt.\"TD001\") || '-' || TRIM(dt.\"TD002\") || '-' || TRIM(dt.\"TD003\") order_serial,
                        dt.\"TA001\",dt.\"TA002\",dt.\"TA001\" || '-' || dt.\"TA002\" order_processes_serial,TO_CHAR(dt.\"TA009\"::timestamp, 'YYYY-MM-DD') date,dt.\"TA009\",
                        JSON_AGG(JSON_BUILD_OBJECT(
                            'order_processes_serial',dt.\"TA001\" || '-' || dt.\"TA002\",
                            'order_processes_order',dt.order_processes_order,
                            'preset_time',dt.preset_time,
                            'preset_in_time',order_processes_outer_detail_preset_in_time,
                            'preset_out_time',order_processes_outer_detail_preset_out_time,
                            'MW002',dt.\"MW002\",'line_name',dt.\"MD002\",'machine_code',dt.machine_code,
                            'machine_name',dt.machine_name,'in_time',to_char(dt.\"New\"::timestamp, 'YYYY-MM-DD HH24:MI:SS'::text),
                            'out_time',to_char(dt.\"Gone\"::timestamp, 'YYYY-MM-DD HH24:MI:SS'::text),
                            'status',dt.status) ORDER BY regexp_replace(dt.order_processes_order, '[^0-9]', '', 'g')::numeric ASC
                        ) history,
                        dt.preset_count,
                        STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.machine_name END,',') current_machine,
                        STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.machine_code END,',') current_machine_code,
                        STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.line_name END,',') current_line_name,
                        STRING_AGG(CASE WHEN dt.row_num = 1 AND dt.status IS NOT NULL THEN dt.\"MW002\" END,',') current_procsses,
                        STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.status END,',') current_machine_status,
                        (STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.\"Gone\"::text END,'')::timestamp)-(STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.\"New\"::text END,'')::timestamp) work_time,
                        STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.\"Gone\"::text END,'')::timestamp current_actual_out_time,
                        STRING_AGG(CASE WHEN dt.row_num = 1 THEN dt.\"New\"::text END,'')::timestamp current_actual_in_time,
                        dt.order_processes_outer_actual_in_time actual_in_time,
                        dt.order_processes_outer_actual_out_time actual_out_time,
                        dt.production_name,
                        dt.\"TA009\" preset_in_time,
                        dt.order_processes_outer_preset_time preset_out_time,
                        dt.\"TD004\" itemno,
                        dt.\"TD006\" spec,
                        dt.\"XB002\" material
                    FROM (
                        SELECT order_processes_outer.\"TA012\" order_processes_outer_actual_in_time,order_processes_outer.\"TA014\" order_processes_outer_actual_out_time,order_processes_outer.preset_time order_processes_outer_preset_time,order_processes_outer.preset_count,order_processes_outer.production_name,order_processes_outer.\"TA009\",order_processes_outer.order->>'TD001' \"TD001\",order_processes_outer.order->>'TD002' \"TD002\",order_processes_outer.order->>'TD003' \"TD003\",order_processes_outer.order->>'TD008' order_amount,TRIM(order_processes_outer.order->>'TD001') || '-' || TRIM(order_processes_outer.order->>'TD002') || '-' || TRIM(order_processes_outer.order->>'TD003') order_serial,
                            dt.current_machine,
                            dt.current_machine_code,
                            dt.current_machine_status,
                            dt.work_time,
                            dt.actual_in_time,
                            dt.actual_out_time,
                            dt.preset_in_time,
                            dt.preset_out_time,
                            order_processes_outer_detail.\"TA001\",order_processes_outer_detail.\"TA002\",order_processes_outer_detail.order_processes_order,order_processes_outer_detail.\"MW002\",
                            dt.line_name,
                            dt.machine_code,
                            dt.machine_name,
                            dt.actual_in_time \"New\",
                            dt.actual_out_time \"Gone\",
                            dt.current_machine_status status,
                            ROW_NUMBER() OVER (PARTITION BY order_processes_outer_detail.\"TA001\",order_processes_outer_detail.\"TA002\" ORDER BY
                                CASE WHEN dt.current_machine_status IS NOT NULL THEN 0 ELSE 1
                                END ASC,dt.\"WR007\" DESC,order_processes_outer_detail.order_processes_order DESC) row_num,order_processes_outer_detail.preset_time,
                            order_processes_outer_detail.\"MD002\",order_processes_outer.\"TD004\",order_processes_outer.\"TD006\",order_processes_outer.\"XB002\",order_processes_outer_detail.\"TA008\" order_processes_outer_detail_preset_in_time,order_processes_outer_detail.\"TA009\" order_processes_outer_detail_preset_out_time
                        FROM order_processes_outer
                        LEFT JOIN jsonb_to_recordset(order_processes_outer.order_processes) order_processes_outer_detail(\"TA001\" text,\"TA002\" text,\"TA003\" text,\"TA006\" text,\"TA008\" text,\"TA009\" text,\"MD002\" text,preset_time text,\"order_processes_order\" text, \"MW002\" text) ON true
                        LEFT JOIN (
                            {$SFT}
                        )dt ON TRIM(dt.\"TA001\") = TRIM(order_processes_outer_detail.\"TA001\") AND TRIM(dt.\"TA002\") = TRIM(order_processes_outer_detail.\"TA002\")
                            AND TRIM(dt.\"WR007\") = TRIM(order_processes_outer_detail.\"TA003\")
                    )dt
                    GROUP BY dt.\"TD001\",dt.\"TD002\",dt.\"TD003\",dt.order_amount,dt.\"TA001\",dt.\"TA002\",dt.\"TA009\",dt.production_name,dt.preset_count,dt.order_processes_outer_preset_time,dt.order_processes_outer_actual_in_time,dt.order_processes_outer_actual_out_time,dt.\"TD004\",dt.\"TD006\",dt.\"XB002\"
                )dt
                LEFT JOIN phasegallery.coptd_file ON TRIM(coptd_file.coptd_td001) = TRIM(dt.\"TD001\") AND TRIM(coptd_file.coptd_td002) = TRIM(dt.\"TD002\") AND TRIM(coptd_file.coptd_td003) = TRIM(dt.\"TD003\")
                {$sort}
            )
        ";
        /*
SELECT order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",(order_processes.fk->>'TA001') || '-' || (order_processes.fk->>'TA002') number,'ready' current_machine_status,order_processes_record.start_time actual_in_time,order_processes_record.end_time actual_out_time,lines_machines_processes_outer.line_code,lines_machines_processes_outer.line_name,
	order_processes_record.machine_code current_machine_code,
	order_processes_record.machine_code,
	lines_machines_processes_outer.machine_name,
	lines_machines_processes_outer.machine_name current_machine,
	COALESCE(order_processes_record.end_time,NOW())::timestamp-order_processes_record.start_time::text::timestamp work_time,
	null preset_in_time,
	null preset_out_time
FROM rfid.order_processes_record
LEFT JOIN public.order_processes ON order_processes_record.order_processes_id = order_processes.order_processes_id
INNER JOIN json_to_recordset('')
	AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
	ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code

         */
/*
\"New\" AS (
                SELECT \"cTagID\",\"cReaderName\", \"cIP\", \"iAntennaID\",\"dTime\" \"New\",LEAD(\"dTime\",1) OVER (
                    PARTITION BY \"cTagID\",\"cReaderName\", \"cIP\", \"iAntennaID\"
                    ORDER BY \"cTagID\",\"dTime\"
                ) \"Next\"
                FROM public.\"RFID_TABLE_Log\"
                WHERE \"cTagEvent\" = 'New'
                ORDER BY \"cTagID\",\"dTime\"
            ),\"Record_org\" AS(
                SELECT ROW_NUMBER() OVER (PARTITION BY rfid_tag.\"TA001\", rfid_tag.\"TA002\" ORDER BY \"dt\".\"New\" DESC ),rfid_tag.\"TA001\", rfid_tag.\"TA002\",machine_outer.line_code,machine_outer.line_name,machine_outer.machine_code,machine_outer.machine_name,dt.\"New\",dt.\"Gone\",rfid_antenna_machine.status
                FROM (
                    SELECT fk->>'TA001' \"TA001\",fk->>'TA002' \"TA002\",rfid_tag.rfid_tag
                    FROM public.order_processes
                    LEFT JOIN order_processes_tag ON order_processes.order_processes_id = order_processes_tag.order_processes_id
                    LEFT JOIN rfid_tag ON rfid_tag.rfid_tag_id = order_processes_tag.rfid_tag_id
                    GROUP BY fk->>'TA001',fk->>'TA002',rfid_tag.rfid_tag
                )rfid_tag
                LEFT jOIN (
                    SELECT \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\".\"New\",\"New\".\"Next\",\"Back\",\"Gone\"
                    FROM(
                        SELECT \"cTagID\",\"cReaderName\", \"cIP\", \"iAntennaID\",\"New\",\"Next\"
                        FROM \"New\"
                    )\"New\"
                    LEFT JOIN(
                        SELECT \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\",MAX(\"dTime\") \"Back\"
                        FROM \"New\"
                        LEFT JOIN public.\"RFID_TABLE_Log\" ON \"New\".\"cTagID\" = \"RFID_TABLE_Log\".\"cTagID\" AND \"New\".\"cIP\" = \"RFID_TABLE_Log\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"RFID_TABLE_Log\".\"iAntennaID\"
                            AND \"New\".\"New\" < \"RFID_TABLE_Log\".\"dTime\" AND COALESCE(\"New\".\"Next\",NOW()) > \"RFID_TABLE_Log\".\"dTime\"
                        WHERE \"cTagEvent\" = 'Back'
                        GROUP BY \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\"
                    )\"Back\" ON \"New\".\"cTagID\" = \"Back\".\"cTagID\" AND \"New\".\"cIP\" = \"Back\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"Back\".\"iAntennaID\" AND \"New\".\"New\" = \"Back\".\"New\" AND COALESCE(\"New\".\"Next\"::text,'null') = COALESCE(\"Back\".\"Next\" ::text,'null')
                    LEFT JOIN(
                        SELECT \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\",MAX(\"dTime\") \"Gone\"
                        FROM \"New\"
                        LEFT JOIN public.\"RFID_TABLE_Log\" ON \"New\".\"cTagID\" = \"RFID_TABLE_Log\".\"cTagID\" AND \"New\".\"cIP\" = \"RFID_TABLE_Log\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"RFID_TABLE_Log\".\"iAntennaID\"
                            AND \"New\".\"New\" < \"RFID_TABLE_Log\".\"dTime\" AND COALESCE(\"New\".\"Next\",NOW()) > \"RFID_TABLE_Log\".\"dTime\"
                        WHERE \"cTagEvent\" = 'Gone'
                        GROUP BY \"New\".\"cTagID\",\"New\".\"cReaderName\", \"New\".\"cIP\", \"New\".\"iAntennaID\",\"New\",\"Next\"
                    )\"Gone\" ON \"New\".\"cTagID\" = \"Gone\".\"cTagID\" AND \"New\".\"cIP\" = \"Gone\".\"cIP\"  AND \"New\".\"iAntennaID\" = \"Gone\".\"iAntennaID\" AND \"New\".\"New\" = \"Gone\".\"New\" AND COALESCE(\"New\".\"Next\"::text,'null') = COALESCE(\"Gone\".\"Next\" ::text,'null')
                )dt ON dt.\"cTagID\" = rfid_tag.rfid_tag
                INNER JOIN rfid_address ON rfid_address.\"tAddress\" = dt.\"cIP\"
                INNER JOIN rfid_antenna ON rfid_antenna.address_id = rfid_address.id AND dt.\"iAntennaID\" = rfid_antenna.\"iAntennaID\"
                INNER JOIN rfid_antenna_machine ON rfid_antenna_machine.antenna_id = rfid_antenna.id
                INNER JOIN machine ON machine.machine_id = rfid_antenna_machine.machine_id
                LEFT JOIN json_to_recordset('$machines')
                    as machine_outer(machine_code text,machine_name text,line_code text,line_name text) ON trim(machine.machine_code) = trim(machine_outer.machine_code)
            ),\"running_in\" AS (
                SELECT \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name,\"Record_org\".status,MIN(\"New\") \"running_in\"
                FROM \"Record_org\"
                WHERE \"Record_org\".status = 'running'
                GROUP BY \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name,\"Record_org\".status
            ),\"running_out\" AS (
                SELECT \"running_out\".\"TA001\",\"running_out\".\"TA002\",\"running_out\".line_code,\"running_out\".line_name,\"running_out\".machine_code,\"running_out\".machine_name,\"running_out\".status,\"Record\".\"running_in\",MIN(running_out.\"Gone\") running_out
                FROM \"running_in\" \"Record\"
                INNER JOIN \"Record_org\" running_out ON \"Record\".\"TA001\" = running_out.\"TA001\" AND \"Record\".\"TA002\" = running_out.\"TA002\" AND \"Record\".line_code = running_out.line_code AND \"Record\".line_name = running_out.line_name AND \"Record\".machine_code = running_out.machine_code AND \"Record\".status = running_out.status
                GROUP BY \"running_out\".\"TA001\",\"running_out\".\"TA002\",\"running_out\".line_code,\"running_out\".line_name,\"running_out\".machine_code,\"running_out\".machine_name,\"running_out\".status,\"Record\".\"running_in\"
            ),\"waiting_in\" AS (
                SELECT \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name,\"Record_org\".status,MIN(\"New\") \"waiting_in\"
                FROM \"running_out\"
                INNER JOIN \"Record_org\" ON \"running_out\".\"running_out\" > \"Record_org\".\"New\" AND \"Record_org\".\"TA001\" = running_out.\"TA001\" AND \"Record_org\".\"TA002\" = running_out.\"TA002\" AND \"Record_org\".line_code = running_out.line_code AND \"Record_org\".line_name = running_out.line_name AND \"Record_org\".machine_code = running_out.machine_code AND \"Record_org\".status = running_out.status
                WHERE \"Record_org\".status = 'waiting'
                GROUP BY \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_code,\"Record_org\".machine_name,\"Record_org\".status
            ),\"waiting_out\" AS(
                SELECT \"waiting_out\".\"TA001\",\"waiting_out\".\"TA002\",\"waiting_out\".line_code,\"waiting_out\".line_name,\"waiting_out\".machine_code,\"waiting_out\".machine_name,\"waiting_out\".status,\"Record\".\"waiting_in\",MIN(\"waiting_out\".\"Gone\") \"waiting_out\"
                FROM \"waiting_in\" \"Record\"
                INNER JOIN \"Record_org\" waiting_out ON \"Record\".\"TA001\" = waiting_out.\"TA001\" AND \"Record\".\"TA002\" = waiting_out.\"TA002\" AND \"Record\".line_code = waiting_out.line_code AND \"Record\".line_name = waiting_out.line_name AND \"Record\".machine_code = waiting_out.machine_code AND \"Record\".status = waiting_out.status
                WHERE \"Record\".waiting_in = waiting_out.\"New\"
                GROUP BY \"waiting_out\".\"TA001\",\"waiting_out\".\"TA002\",\"waiting_out\".line_code,\"waiting_out\".line_name,\"waiting_out\".machine_code,\"waiting_out\".machine_name,\"waiting_out\".status,\"Record\".\"waiting_in\"
            ),\"ready_in\" AS(
                SELECT \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name,\"Record_org\".status,MIN(\"New\") \"ready_in\"
                FROM \"running_out\"
                INNER JOIN \"Record_org\" ON \"running_out\".\"running_out\" < \"Record_org\".\"New\" AND \"Record_org\".\"TA001\" = running_out.\"TA001\" AND \"Record_org\".\"TA002\" = running_out.\"TA002\" AND \"Record_org\".line_code = running_out.line_code AND \"Record_org\".line_name = running_out.line_name AND \"Record_org\".machine_code = running_out.machine_code AND \"Record_org\".status = running_out.status
                WHERE \"Record_org\".status = 'ready'
                GROUP BY \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_code,\"Record_org\".machine_name,\"Record_org\".status
            ),\"ready_out\" AS (
                SELECT \"ready_out\".\"TA001\",\"ready_out\".\"TA002\",\"ready_out\".line_code,\"ready_out\".line_name,\"ready_out\".machine_code,\"ready_out\".machine_name,\"ready_out\".status,\"Record\".\"ready_in\",MIN(ready_out.\"Gone\") ready_out
                FROM \"ready_in\" \"Record\"
                INNER JOIN \"Record_org\" \"ready_out\" ON \"Record\".\"TA001\" = ready_out.\"TA001\" AND \"Record\".\"TA002\" = ready_out.\"TA002\" AND \"Record\".line_code = ready_out.line_code AND \"Record\".line_name = ready_out.line_name AND \"Record\".machine_code = ready_out.machine_code AND \"Record\".status = ready_out.status
                GROUP BY \"ready_out\".\"TA001\",\"ready_out\".\"TA002\",\"ready_out\".line_code,\"ready_out\".line_name,\"ready_out\".machine_code,\"ready_out\".machine_name,\"ready_out\".status,\"Record\".\"ready_in\"
            ),
                        LEFT JOIN(
                            SELECT dt.\"TA001\", dt.\"TA002\",dt.\"TA001\" || '-' || dt.\"TA002\" number,
                                dt.line_code,dt.line_name,dt.machine_code,dt.machine_name,
                                STRING_AGG(CASE WHEN dt.row_number = 1 THEN dt.machine_name END,',') current_machine,
                                STRING_AGG(CASE WHEN dt.row_number = 1 THEN dt.machine_code END,',') current_machine_code,
                                STRING_AGG(
                                    CASE WHEN dt.row_number = 1 THEN
                                        CASE WHEN dt.running_in IS NOT NULL AND dt.running_out IS NULL THEN 'running'
                                                WHEN dt.waiting_in IS NOT NULL AND dt.waiting_out IS NULL THEN 'waiting'
                                                WHEN dt.ready_in IS NOT NULL AND dt.ready_out IS NULL THEN 'ready'
                                                WHEN dt.running_in IS NOT NULL AND dt.running_out IS NOT NULL THEN 'ready'
                                                ELSE 'waiting'
                                            END
                                    END
                                ,',') current_machine_status,
                                (STRING_AGG(CASE WHEN dt.row_number = 1 THEN COALESCE(dt.running_out,NOW())::text END,'')::timestamp)-(STRING_AGG(CASE WHEN dt.row_number = 1 THEN dt.running_in::text END,'')::timestamp) work_time,
                                STRING_AGG(CASE WHEN dt.row_number = 1 THEN dt.\"running_out\"::text END,'')::timestamp actual_out_time,
                                STRING_AGG(CASE WHEN dt.row_number = 1 THEN dt.\"running_in\"::text END,'')::timestamp actual_in_time,
                                null preset_in_time,
                                null preset_out_time
                            FROM(
                                SELECT ROW_NUMBER() OVER (PARTITION BY \"Record_org\".\"TA001\", \"Record_org\".\"TA002\" ORDER BY \"running_out\".\"running_in\" DESC ) row_number,\"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name,waiting_out.waiting_in,waiting_out.waiting_out,running_out.running_in,running_out.running_out,\"ready_out\".\"ready_in\",\"ready_out\".\"ready_out\"
                                FROM (
                                    SELECT \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name
                                    FROM \"Record_org\"
                                    GROUP BY \"Record_org\".\"TA001\",\"Record_org\".\"TA002\",\"Record_org\".line_code,\"Record_org\".line_name,\"Record_org\".machine_code,\"Record_org\".machine_name
                                )\"Record_org\"
                                LEFT JOIN \"waiting_out\" ON \"waiting_out\".\"TA001\" = \"Record_org\".\"TA001\" AND \"waiting_out\".\"TA002\" = \"Record_org\".\"TA002\" AND \"waiting_out\".line_code = \"Record_org\".line_code AND \"waiting_out\".line_name = \"Record_org\".line_name AND \"waiting_out\".machine_code = \"Record_org\".machine_code AND \"waiting_out\".machine_name = \"Record_org\".machine_name
                                LEFT JOIN \"running_out\" ON \"Record_org\".\"TA001\" = running_out.\"TA001\" AND \"Record_org\".\"TA002\" = running_out.\"TA002\" AND \"Record_org\".line_code = running_out.line_code AND \"Record_org\".line_name = running_out.line_name AND \"Record_org\".machine_code = running_out.machine_code AND \"Record_org\".machine_name = running_out.machine_name
                                LEFT JOIN \"ready_out\" ON \"Record_org\".\"TA001\" = ready_out.\"TA001\" AND \"Record_org\".\"TA002\" = ready_out.\"TA002\" AND \"Record_org\".line_code = ready_out.line_code AND \"Record_org\".line_name = ready_out.line_name AND \"Record_org\".machine_code = ready_out.machine_code AND \"Record_org\".machine_name = ready_out.machine_name
                            )dt
                            GROUP BY dt.\"TA001\",dt.\"TA002\",dt.machine_name,dt.machine_code,dt.line_code,dt.line_name,dt.machine_code,dt.machine_name
                            ORDER BY dt.\"TA001\" ASC, dt.\"TA002\" ASC
                        )dt ON TRIM(dt.\"TA001\") = TRIM(order_processes_outer_detail.\"TA001\") AND TRIM(dt.\"TA002\") = TRIM(order_processes_outer_detail.\"TA002\") AND TRIM(dt.line_code) = TRIM(order_processes_outer_detail.\"TA006\")
         */
        return $with;
    }
    public function get_rfid_order_processes($data){
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $length = $values['cur_page']*$values['size'];
        $start = $length-$values['size'];
        $with = $this->get_rfid_order_processes_detail($data);
        $mode = [
            "detail" => false
        ];
        foreach ($mode as $key => $value) {
            array_key_exists($key,$data)&&$mode[$key]=$data[$key];
        }
        $stmt_array = [
        ];
        $stmt_string = "";
        if($mode["detail"]){
            $stmt_array = [
                "status" => 'running'
            ];
            $stmt_string = "WHERE current_machine_status = :status";
            foreach ($stmt_array as $key => $value) {
                array_key_exists($key,$data)&&$stmt_array[$key]=$data[$key];
            }
        }
        foreach ($data as $key => $value) {
            $this->isJson($value)&&$data[$key]=json_decode($value,true);
        }
        $values = [
            "order"=>[]
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $order = [
            "name" => null,
            "sort" => null
        ];
        foreach ($order as $key => $value) {
            array_key_exists($key,$values["order"])&&$order[$key]=$values["order"][$key];
        }
        $sort = " ORDER BY \"with\".\"TA001\" || '-' || \"with\".\"TA002\" ";
        if(!is_null($order['name'])&&!is_null($order['sort'])){
            switch ($order['name']) {
                case 'order_serial':
                    $sort = " ORDER BY \"with\".\"TD001\" || '-' || \"with\".\"TD002\" || '-' || \"with\".\"TD003\" ";
                    break;
                case 'order_processes_serial':
                    $sort = " ORDER BY \"with\".\"TA001\" || '-' || \"with\".\"TA002\" ";
                    break;
                case 'date':
                    $sort = " ORDER BY \"with\".\"TA009\" ";
                    break;
            }
            if(strtolower($order['sort'])==='descend') $sort.=" desc ";
        }
        $sql = $with;
        $sql .= "SELECT *
            FROM(
                SELECT *,ROW_NUMBER() OVER({$sort}) \"key2\"
                FROM \"with\"
                {$stmt_string}
                LIMIT {$length}
            )dt
            WHERE dt.\"key2\" > {$start}
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
            return [
                "status" => "failure"
            ];
        }

        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result['data'] as $row_key => $row) {
            foreach ($row as $key => $value) {
                if ($this->isJson($value)) {
                    $result['data'][$row_key][$key] = json_decode($value, true);
                }
            }
        }
        $sql = $with;
        $sql .= "SELECT COUNT(*) count
            FROM \"with\"
            {$stmt_string}
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
        }
        $result['total'] = $stmt->fetchColumn(0);
        return $result;
    }
    public function get_order_processes_outer_detail($data){
        $values = [
            'keyword' => '',
            'date_begin' => date("Ymd"),
            'date_end' => date("Ymd"),
            'processes_id' => [],
            // 'cur_page'=>1,
            // 'size'=>10,
            'done'=>'false'
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                if($key === "date_begin" || $key === "date_end"){
                    $values[$key] = implode("", array_map(function($value){
                        return $value;
                    }, array_values(explode("-", $data[$key]))));
                }
                else $values[$key] = $data[$key];
            }
        }
        $stmt_string = [];
        $stmt_array = [];
        $stmt_string['done'] = "AND LOWER(MOCTA.TA011) NOT IN  ( 'y'  )";
        if(strtolower($values['done'])==='true' || strtolower($values['done'])){
            $stmt_string['done'] = "AND LOWER(MOCTA.TA011) IN  ( 'y'  )";
        }
        $stmt_string['processes_id'] = "";
        if(!empty($values['processes_id'])){
            $stmt_string['processes_id'] = implode(',',array_map(function($prefix,$postfix){return 'LTRIM(RTRIM(:'.$prefix.'_'.$postfix.'))';},array_fill(0,count($values['processes_id']),'processes_id'),array_keys($values['processes_id'])));
            $stmt_array = array_merge($stmt_array,array_reduce($values['processes_id'],function($all,$tmp){$all['processes_id_'.count($all)]=$tmp;return $all;},[]));
            $stmt_string['processes_id'] = "
                INNER JOIN (
                    SELECT [SFCTA].TA001,[SFCTA].TA002
                    FROM MIL.dbo.[SFCTA]
                    WHERE LTRIM(RTRIM(TA004)) IN ({$stmt_string['processes_id']})
                    GROUP BY [SFCTA].TA001,[SFCTA].TA002
                )[SFCTA] ON [MOCTA].TA001 = [SFCTA].[TA001] AND [MOCTA].TA002 = [SFCTA].[TA002]
            ";
        }else{
            return [
                'data'=>[],
                'total' => 0
            ];
        }
        $stmt_array +=[
            "date_begin" => $values['date_begin'],
            "date_end" => $values['date_end'],
        ];
        if(!empty($values['keyword'])){
            $stmt_string['keyword'] = " AND ( RTRIM(LTRIM(MOCTA.TA001)) + '-' + RTRIM(LTRIM(MOCTA.TA002)) LIKE '%' + :keyword_1 + '%'  OR RTRIM(LTRIM(COPTD.TD001)) + '-' + RTRIM(LTRIM(COPTD.TD002)) + '-' + RTRIM(LTRIM(COPTD.TD003)) LIKE '%' + :keyword_2 + '%' ) ";
            $stmt_array['keyword_1'] = $values['keyword'];
            $stmt_array['keyword_2'] = $values['keyword'];
        }else{
            $stmt_string['keyword'] = "";
            unset($values['keyword']);
        }
        $sort = [
            "order"=>[]
        ];
        foreach ($sort as $key => $value) {
            array_key_exists($key,$data)&&$sort[$key]=$data[$key];
        }
        $order = [
            "name" => null,
            "sort" => null
        ];
        foreach ($order as $key => $value) {
            array_key_exists($key,$sort["order"])&&$order[$key]=$sort["order"][$key];
        }
        $sort = "ORDER BY \"with\".TA002 ";
        if(!is_null($order['name'])&&!is_null($order['sort'])){
            switch ($order['name']) {
                case 'order_serial':
                    $sort = " ORDER BY \"with\".\"TD001\" + '-' + \"with\".\"TD002\" + '-' + \"with\".\"TD003\" ";
                    break;
                case 'order_processes_serial':
                    $sort = " ORDER BY \"with\".\"TA001\" + '-' + \"with\".\"TA002\" ";
                    break;
                case 'date':
                    $sort = " ORDER BY \"with\".\"TA009\" ";
                    break;
            }
            if(strtolower($order['sort'])==='descend') $sort.=" desc ";
        }

        $this->db_sqlsrv->exec('USE [SFT_MIL]');

        $with = "WITH MOCTA_WHERE AS (
                SELECT *
                FROM [MIL].[dbo].[MOCTA]
                WHERE MOCTA.TA009 BETWEEN :date_begin AND :date_end
                    {$stmt_string['done']}
                    AND
                    (
                        MOCTA.TA001  Is Null
                        OR
                        MOCTA.TA001  NOT IN  ( '5202','5205','5198','5199','5207','5203','5204'  )
                    )
            ), checkInOut AS (
                SELECT dt.ID, dt.[WSID], dt.[EQID], dt.ERP_OPSEQ WR007, dt.WR008, MIN(CASE WHEN dt.[EXECUTETYPE] = 'checkIn' THEN dt.[EXECUTETIME] END) checkIn, MAX(CASE WHEN dt.[EXECUTETYPE] = 'checkOut' THEN dt.[EXECUTETIME] END) checkOut
                FROM [SFT_MIL].[dbo].[SFT_WS_RUN] dt
                WHERE dt.[EXECUTETYPE] != 'release' AND LTRIM(RTRIM(dt.[ID])) IN (
                    SELECT RTRIM(LTRIM(MOCTA_WHERE.TA001))+'-'+RTRIM(LTRIM(MOCTA_WHERE.TA002))
                    FROM MOCTA_WHERE
                )
                GROUP BY dt.ID, dt.[WSID], dt.[EQID], dt.ERP_OPSEQ, dt.WR008
            ), [SFT] AS (
                SELECT LTRIM(RTRIM(dt.[ID])) [ID],dt.[WSID] line_code,CMSMD.MD002 line_name, dt.[EQID] machine_code,[CMSMX].[MX003] machine_name, dt.WR007, dt.WR008
                    ,dt.checkIn AS waiting_in
                    ,dt.checkIn AS waiting_out
                    ,dt.checkIn AS running_in
                    ,dt.checkIn AS running_out
                    ,dt.checkOut AS ready_in
                    ,dt.checkOut AS ready_out
                FROM checkInOut dt
                LEFT JOIN [MIL].[dbo].CMSMD ON dt.[WSID] = [CMSMD].[MD001]
                LEFT JOIN [MIL].[dbo].[CMSMX] ON dt.[EQID] = [CMSMX].[MX001]
            ), [with] AS (
                SELECT *
                FROM(
                    SELECT RTRIM(LTRIM(MOCTA.TA001))+'-'+RTRIM(LTRIM(MOCTA.TA002)) [ID2],MOCTA.TA001,MOCTA.TA002,MOCTA.TA015 [preset_count],MOCTA.TA034 [production_name],MOCTA.TA001 +'-' +MOCTA.TA002 [number],
                        MOCTA.TA010 [preset_time],MOCTA.TA004 [delivery_date],MOCTA.TA009,MOCTA.TA012,MOCTA.TA014,dt.TD001,dt.TD002,dt.TD003,dt.TD004,dt.TD006,[CMSXB].XB002,
                        STUFF((
                            SELECT COPTD.TD001, COPTD.TD002, COPTD.TD003, COPTD.TD008
                            FROM MIL.dbo.COPTD
                            WHERE (COPTD.TD001=MOCTA.TA026 and COPTD.TD002=MOCTA.TA027 and COPTD.TD003=MOCTA.TA028)
                                {$stmt_string['keyword']}
                        FOR XML PATH),1,0,''
                        )[order],
                        STUFF((
                            SELECT SFCTA.TA001,SFCTA.TA002,SFCTA.TA003,SFCTA.TA004,SFCTA.TA006,SFCTA.TA008,SFCTA.TA009,SFCTA.TA010,CMSMW.MW002,ROW_NUMBER() OVER (PARTITION BY SFCTA.TA001,SFCTA.TA002 ORDER BY SFCTA.TA003 ASC) order_processes_order,
                                DATEDIFF(DAY,CAST([TA008] AS DATETIME ),CAST([TA009] AS DATETIME )) preset_time,CMSMD.MD002
                            FROM MIL.dbo.[SFCTA]
                            LEFT JOIN MIL.dbo.CMSMW ON CMSMW.MW001 = SFCTA.TA004
                            LEFT JOIN [MIL].[dbo].CMSMD ON CMSMW.MW005 = CMSMD.MD001
                            WHERE SFCTA.TA001 = MOCTA.TA001 AND SFCTA.TA002 = MOCTA.TA002
                            ORDER BY SFCTA.TA001,SFCTA.TA002,SFCTA.TA003
                        FOR XML PATH),1,0,''
                        )[order_processes]
                    FROM MOCTA_WHERE MOCTA
                    LEFT JOIN MIL.dbo.COPTD dt ON (dt.TD001=MOCTA.TA026 and dt.TD002=MOCTA.TA027 and dt.TD003=MOCTA.TA028)
                    LEFT JOIN [MIL].[dbo].[CMSXB] ON [CMSXB].XB001 = dt.TD205
                    {$stmt_string['processes_id']}
                )dt
                WHERE  [order] IS NOT NULL
            )";
/*
        $length = $values['cur_page']*$values['size'];
        $start = $length-$values['size'];
        $sql = $with;
        $sql .= "SELECT *
            FROM(
                SELECT TOP {$length} *,ROW_NUMBER() OVER ({$sort}) \"key\"
                FROM \"with\"
            )dt
            WHERE \"key\" > {$start}
        ";
         */
        $sql = $with;
        $sql .= "SELECT *
            FROM(
                SELECT *
                FROM \"with\"
            )dt
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
            return [
                "status" => "failure"
            ];
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['order'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['order'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs = $tmpArr;
            }
            $result[$key_result]['order'] = $tmpArrs;
            Endquotation:

            $tmpvalue = $value['order_processes'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['order_processes'] = $tmpArrs;
                goto Endquotation2;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['order_processes'] = $tmpArrs;
            Endquotation2:

            // $tmpvalue = $value['SFT'];
            // $tmpArrs = [];
            // $xml = simplexml_load_string("<a>$tmpvalue</a>");
            // if ($tmpvalue == "") {
            //     $result[$key_result]['SFT'] = $tmpArrs;
            //     goto Endquotation3;
            // }
            // foreach ($xml as $t) {
            //     $tmpArr = [];
            //     foreach ($t as $a => $b) {
            //         $tmpArr[$a] = '';
            //         foreach ((array)$b as $c => $d) {
            //             $tmpArr[$a] = $d;
            //         }
            //     }
            //     $tmpArrs[] = $tmpArr;
            // }
            // $result[$key_result]['SFT'] = $tmpArrs;
            // Endquotation3:
        }
        $result = [
            "data"=> $result
        ];

        $sql = $with;
        $sql .= "SELECT SFT.*
            FROM SFT
            ORDER BY SFT.[ID],SFT.WR007
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
        }
        $result['SFT'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result['data'] as $index => $row) {
            foreach ($result['SFT'] as $sft_index => $sft) {
                if((trim($row['TA001'])."-".trim($row['TA002'])) === $sft['ID']){
                    !isset($result['data'][$index]['SFT'])&&$result['data'][$index]['SFT'] = [];
                    $result['data'][$index]['SFT'][] = $sft;
                }
            }
        }
        // $sql = $with;
        // $sql .= "SELECT COUNT(*) count
        //     FROM \"with\"
        // ";
        // $stmt = $this->db_sqlsrv->prepare($sql);
        // if (!$stmt->execute($stmt_array)) {
        // }
        // $result['total'] = current($stmt->fetchAll(PDO::FETCH_ASSOC))['count'];
        return $result;
    }
    public function get_rfid_status()
    {
        $sql = "SELECT rfid_status_name, rfid_status_color
                FROM setting.rfid_status
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function post_rfid_status($datas)
    {
        $values = [
            "rfid_status_name" => '',
            "rfid_status_color" => null,
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$datas)){
                $values[$key] = $datas[$key];
            }
        }

        $sql = "INSERT INTO setting.rfid_status(rfid_status_name, rfid_status_color)
                VALUES (:rfid_status_name, :rfid_status_color)
                ON CONFLICT(rfid_status_name)
                DO UPDATE SET rfid_status_color = :rfid_status_color
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return [
                'status' => 'success'
            ];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function patch_rfid_status($datas)
    {
        $values = [
            "rfid_status_name" => '',
            "rfid_status_color" => null,
        ];

        foreach ($values as $key => $value) {
            if(array_key_exists($key,$datas)){
                $values[$key] = $datas[$key];
            }
        }

        $sql = "UPDATE setting.rfid_status
                SET rfid_status_color=:rfid_status_color
                WHERE rfid_status_name = :rfid_status_name
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return [
                'status' => 'success'
            ];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }
    public function delete_rfid_status($data)
    {
        $condition = "";
        $values = [];
        foreach ($data as $key => $value) {
            $condition .= ":name_{$key}, ";
            $values["name_{$key}"] = $value;
        }
        $condition = rtrim($condition, ", ");

        $sql = "DELETE FROM setting.rfid_status
                WHERE rfid_status_name IN ({$condition})
                ";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($values)) {
            return [
                'status' => 'success'
            ];
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }


    public function get_lines($data){
        $values = [
            'line_id' => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=trim($data[$key]);
        }
        $stmt_string = '';
        $stmt_array = [];
        if(!is_null($values['line_id'])){
            $stmt_string = ' WHERE CMSMD.MD001 = :line_id ';
            $stmt_array = $values;
        }
        $sql = "SELECT TOP 1000 MD001 AS line_id , MD002 AS line_name
            FROM MIL.[dbo].CMSMD
            {$stmt_string}
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($stmt_array)){
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_machines($data){
        $values = [
            'line_id' => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=trim($data[$key]);
        }
        $stmt_string = '';
        $stmt_array = [];
        if(!is_null($values['line_id'])){
            $stmt_string = ' WHERE CMSMD.MD001 = :line_id ';
            $stmt_array = $values;
        }
        $sql = "SELECT TOP 1000 MX001 AS machine_id , MX003 AS machine_name
            FROM MIL.[dbo].CMSMX
            LEFT JOIN MIL.[dbo].CMSMD ON CMSMX.MX002 = CMSMD.MD001
            {$stmt_string}
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($stmt_array)){
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_processes($data){
        $values = [
            'line_id' => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=trim($data[$key]);
        }
        $stmt_string = '';
        $stmt_array = [];
        if(!is_null($values['line_id'])){
            $stmt_string = ' AND CMSMD.MD001 = :line_id ';
            $stmt_array = $values;
        }
        $sql = "SELECT MW001 AS process_id , MW002 AS process_name
            FROM MIL.[dbo].CMSMW
            INNER JOIN [MIL].[dbo].CMSMD ON CMSMW.MW005 = CMSMD.MD001
            WHERE CMSMD.MD001 NOT IN ('C', 'E')
                AND RTRIM(LTRIM(CMSMW.MW001)) NOT IN ('001','002','003','004','005','006','007','008','009','010','011','012','013','014','015','016','017','018','019','020','021','022','023','024','025','026','030','031','033','034','035','036','037','038','039','040','041','042','043','044','045','046','047','055','056','057','058','059','060','062','063','064','065','066','067','068','069','070','071','072','073','074','075','076','077','078','079','082','083','084','085','086','087','088','089','090','091','092','093')
            {$stmt_string}
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($stmt_array)){
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_processes_filter($data){
        // var_dump( $data);

        $values = [
            "process_id" => null,
            "line_id" => null,
            "machine_id" => null,
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }





        // var_dump($machineArr);

        $stmt_string = '';
        $stmt_array = [];
        if(!is_null($values['line_id'])){
            $stmt_string .= ' AND RTRIM(LTRIM(CMSMD.MD001)) = RTRIM(LTRIM(:line_id)) ';
            $stmt_array['line_id'] = $values['line_id'];
        }
        if(!is_null($values['machine_id'])){
            $stmt_string .= ' AND RTRIM(LTRIM(CMSMX.MX001)) = RTRIM(LTRIM(:machine_id)) ';
            $stmt_array['machine_id'] = $values['machine_id'];
        }else if(array_key_exists('machines_area_id',$data) ){
            $machineArr = $this->getmachinebyArea(["machines_area_id"=>$data["machines_area_id"]]);
            $machineArr = explode(",", $machineArr);

            if(count($machineArr) > 0){
                $tmpStr = "(";
                foreach($machineArr AS $key => $value){
                    $tmpStr  .= " RTRIM(LTRIM(:machine_id_{$key})),";
                    $stmt_array["machine_id_{$key}"] = $value;
                }
                $tmpStr = substr_replace($tmpStr, ")", -1);

                $stmt_string .= ' AND RTRIM(LTRIM(CMSMX.MX001)) IN ';
                $stmt_string .=$tmpStr;

            }



        }
        if(!is_null($values['process_id'])){
            $stmt_string .= ' AND RTRIM(LTRIM(CMSMW.MW001)) = RTRIM(LTRIM(:process_id)) ';
            $stmt_array['process_id'] = $values['process_id'];
        }
        // var_dump($stmt_string);
        // var_dump($stmt_array);

        $sql = "SELECT CMSMW.MW001
            FROM [MIL].[dbo].CMSMW
            LEFT JOIN [MIL].[dbo].CMSMD ON CMSMW.MW005 = CMSMD.MD001
            LEFT JOIN [MIL].[dbo].CMSMX ON CMSMX.MX002 = CMSMD.MD001
            WHERE CMSMD.MD001 NOT IN ('C', 'E')
            {$stmt_string}
            GROUP BY CMSMW.MW001
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute($stmt_array)){
            return [
                "status" => "failure"
            ];
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getmachinebyArea($data){
        $values  = [
            "machines_area_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if(array_key_exists($key,$data)){
                $values[$key] = intval($data[$key]);
            }
        }

        $sql = "SELECT
            machines_area_id,
            STRING_AGG (machine_code
            ,
                ','
            ORDER BY
                machine_id
            )  machine

        FROM public.machine
        WHERE machines_area_id = :machines_area_id
        GROUP BY machines_area_id";
        $stmt = $this->db->prepare($sql);
        if( $stmt->execute($values)){
            $result = $stmt->fetchColumn(1);
            return $result;
        }

    }
    public function get_machines_area($data){
        $values = [
            "machines_area_id" => null,
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $stmt_array = array_filter($values,function($value){return !is_null($value);});
        $stmt_string = "";
        if(!is_null($values['machines_area_id'])) $stmt_string = "WHERE machines_area_id = :machines_area_id";
        $sql = "SELECT machines_area_id, machines_area_name, machines_area_code, floor_id, machines_area_floor_serial
            FROM rfid.machines_area
            {$stmt_string}
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($stmt_array)) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function post_machines_area($data){
        $values = [
            "machines_area_name" => '',
            "machines_area_code" => '',
            "floor_id" => 0,
            "machines_area_floor_serial" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $sql = "INSERT INTO rfid.machines_area(machines_area_name, machines_area_code, floor_id, machines_area_floor_serial)
            VALUES (:machines_area_name, :machines_area_code, :floor_id, :machines_area_floor_serial)
            RETURNING *;
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function patch_machines_area($data){
        $values = [
            "machines_area_id" => null,
            "machines_area_name" => null,
            "machines_area_code" => null,
            "floor_id" => null,
            "machines_area_floor_serial" => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $stmt_array = array_filter($values,function($value){return !is_null($value);});
        $stmt_string = "";
        if(array_key_exists('machines_area_name',$values)) $stmt_string .= ", machines_area_name = :machines_area_name";
        if(array_key_exists('machines_area_code',$values)) $stmt_string .= ", machines_area_code = :machines_area_code";
        if(array_key_exists('floor_id',$values)) $stmt_string .= ", floor_id = :floor_id";
        if(array_key_exists('machines_area_floor_serial',$values)) $stmt_string .= ", machines_area_floor_serial = :machines_area_floor_serial";
        $sql = "UPDATE rfid.machines_area
            SET machines_area_id = :machines_area_id{$stmt_string}
            WHERE machines_area_id = :machines_area_id
            RETURNING *;
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($stmt_array)) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function delete_machines_area($data){
        $values = [
            "machines_area_id" => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $sql = "DELETE FROM rfid.machines_area
            WHERE machines_area_id = :machines_area_id
            RETURNING *;
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_machines_area_floor($data){
/*  */
        $sql = "WITH [MOCTA] AS (
                SELECT *
                FROM [MIL].[dbo].[MOCTA]
                WHERE MOCTA.TA011 NOT IN ( 'Y', 'y' )
                    AND
                    (
                        MOCTA.TA001  Is Null
                        OR
                        MOCTA.TA001  NOT IN  ( '5202','5205','5198','5199','5207','5203','5204'  )
                    )
                    AND
                        DATEDIFF(MONTH,CONVERT(NVARCHAR,LEFT(MOCTA.TA002,3)+1911)+RIGHT(LEFT(MOCTA.TA002,7),4),GETDATE())<6
            ),[SFCTA_DETAIL] AS (
                SELECT SFCTA.TA001,SFCTA.TA002,
                    MAX(CASE WHEN SFCTA.TA030!='' THEN SFCTA.TA003 END)TA030,
                    MAX(CASE WHEN SFCTA.TA031!='' THEN SFCTA.TA003 END)TA031,
                    MAX(SFCTA.TA003)TA003
                FROM [MIL].[dbo].[SFCTA]
                INNER JOIN [MOCTA] ON [SFCTA].[TA001] = [MOCTA].[TA001] AND [SFCTA].[TA002] = [MOCTA].[TA002]
                GROUP BY SFCTA.TA001,SFCTA.TA002
            ), SFCTA_STATUS AS (
                SELECT COPTD_SFCTA.TD001,COPTD_SFCTA.TD002,COPTD_SFCTA.TD003,COPTD_SFCTA.TA001,COPTD_SFCTA.TA002,SFCTA.TA004,COPTD_SFCTA.status,
		            [MOCTA].TA015
                FROM(
                    SELECT COPTD.TD001,COPTD.TD002,COPTD.TD003,SFCTA.TA001,SFCTA.TA002,
                        CASE
                            WHEN SFCTA.TA030>SFCTA.TA031
                            THEN SFCTA.TA030
                            WHEN SFCTA.TA030=SFCTA.TA031
                            THEN SFCTA.SFCTA_UNDO
                            ELSE
                                SFCTA.TA030
                        END TA003,
                        CASE
                            WHEN SFCTA.TA030>SFCTA.TA031
                            THEN 'running'
                            WHEN SFCTA.TA030=SFCTA.TA031
                            THEN 'waiting'
                            ELSE
                                'running'
                        END status
                    FROM [MOCTA]
                    INNER JOIN MIL.dbo.COPTD ON (COPTD.TD001=MOCTA.TA026 and COPTD.TD002=MOCTA.TA027 and COPTD.TD003=MOCTA.TA028)
                    LEFT JOIN (
                        SELECT [SFCTA_DETAIL].TA001,[SFCTA_DETAIL].TA002,[SFCTA_DETAIL].TA030,[SFCTA_DETAIL].TA031,[SFCTA_DETAIL].TA003,MIN(SFCTA.TA003) SFCTA_UNDO
                        FROM[SFCTA_DETAIL]
                        LEFT JOIN (
                            SELECT SFCTA.TA001,SFCTA.TA002,SFCTA.TA003
                            FROM [MIL].[dbo].[SFCTA]
                            INNER JOIN [MOCTA] ON [SFCTA].[TA001] = [MOCTA].[TA001] AND [SFCTA].[TA002] = [MOCTA].[TA002]
                        )SFCTA ON [SFCTA_DETAIL].TA001 = SFCTA.TA001 AND [SFCTA_DETAIL].TA002 = SFCTA.TA002 AND SFCTA.TA003 > [SFCTA_DETAIL].TA031
                        GROUP BY [SFCTA_DETAIL].TA001,[SFCTA_DETAIL].TA002,[SFCTA_DETAIL].TA030,[SFCTA_DETAIL].TA031,[SFCTA_DETAIL].TA003
                    )[SFCTA] ON [SFCTA].[TA001] = [MOCTA].[TA001] AND [SFCTA].[TA002] = [MOCTA].[TA002]
                    WHERE ((SFCTA.TA003 != [SFCTA].[TA031])AND SFCTA.TA003 IS NOT NULL)
                )COPTD_SFCTA
                LEFT JOIN [MIL].[dbo].[SFCTA] ON SFCTA.TA001 = COPTD_SFCTA.TA001 AND SFCTA.TA002 = COPTD_SFCTA.TA002 AND SFCTA.TA003 = COPTD_SFCTA.TA003
                LEFT JOIN [MIL].[dbo].[MOCTA] ON MOCTA.TA001 = COPTD_SFCTA.TA001 AND MOCTA.TA002 = COPTD_SFCTA.TA002
            )
            SELECT *,
                STUFF((
                    SELECT MX001
                    FROM MIL.dbo.CMSMW
                    LEFT JOIN [MIL].[dbo].[CMSMX] ON LTRIM(RTRIM(CMSMW.MW005)) = LTRIM(RTRIM(CMSMX.MX002))
                    WHERE MW003 NOT LIKE '%停用%' AND LTRIM(RTRIM(CMSMW.MW001)) = LTRIM(RTRIM(SFCTA_STATUS.TA004))
                    FOR XML PATH),1,0,''
                )sfcta_machines
            FROM SFCTA_STATUS
            ORDER BY SFCTA_STATUS.TD002
        ";
        $stmt = $this->db_sqlsrv->prepare($sql);
        if(!$stmt->execute())
            return ["status"=>"failure"];
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['sfcta_machines'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['sfcta_machines'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                count($tmpArr)!==0&&$tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['sfcta_machines'] = $tmpArrs;
            Endquotation:
        }
        $SFCTA_STATUS = json_encode($result);
/*  */
        $values = [
            "floor_id"=>0
        ];
        foreach (array_keys($values) as $key) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $sql = "SELECT
        dt.machines_area_id,
        dt.machines_area_name,
        SUM( CASE WHEN dt.status = 'running' THEN dt.count END) \"processing_count\",
        SUM( CASE WHEN dt.status = 'running' THEN dt.sum END) \"processing_amount\",
        SUM( CASE WHEN dt.status = 'waiting' THEN dt.count END) \"waiting_count\",
        SUM( CASE WHEN dt.status = 'waiting' THEN dt.sum END) \"waiting_amount\",
        0 incoming_count,
        0 incoming_amount
        FROM(
            SELECT
                machines_area.machines_area_id,
                machines_area.machines_area_name,
                tmp_status.status,
                tmp_status.count,
                0 sum
            FROM
                rfid.machines_area
            LEFT JOIN public.machine ON machine.machines_area_id = machines_area.machines_area_id
            CROSS JOIN (
                SELECT 'waiting' status,0 count
                UNION ALL(
                    SELECT 'running' status,0 count
                )
                UNION ALL(
                    SELECT 'ready' status,0 count
                )
            )tmp_status
            WHERE machines_area.floor_id=:floor_id
            UNION ALL(
                SELECT
                    machines_area.machines_area_id,
                    machines_area.machines_area_name,
                    sfcta_status.status,
                    SUM(sfcta_status.count) count,
                    SUM(sfcta_status.sum) sum
                FROM
                    rfid.machines_area
                LEFT JOIN public.machine ON machine.machines_area_id = machines_area.machines_area_id
                LEFT JOIN (
                    SELECT sfcta_status.status,sfcta_status.\"MX001\",COUNT(*) count,SUM(sfcta_status.\"TA015\") sum
                    FROM(
                        SELECT sfcta_status.\"TD001\",sfcta_status.\"TD002\",sfcta_status.\"TD003\",sfcta_status.status,STRING_AGG(sfcta_status_detail.\"MX001\",',')\"MX001\",sfcta_status.\"TA015\"
                            FROM json_to_recordset('{$SFCTA_STATUS}')
                                    as sfcta_status(\"TD001\" text,\"TD002\" text,\"TD003\" text,status text,\"TA015\" double precision,sfcta_machines jsonb,\"TA004\" text)
                        LEFT JOIN jsonb_to_recordset(sfcta_status.sfcta_machines) as sfcta_status_detail(\"MX001\" text) ON TRUE
                        GROUP BY sfcta_status.\"TD001\",sfcta_status.\"TD002\",sfcta_status.\"TD003\",sfcta_status.status,sfcta_status.\"TA015\"
                    )sfcta_status
                    GROUP BY sfcta_status.status,sfcta_status.\"MX001\"
                )sfcta_status ON TRIM(sfcta_status.\"MX001\") LIKE '%' || TRIM(machine.machine_code) || '%'
                WHERE
                    machines_area.floor_id=:floor_id AND sfcta_status.status IS NOT NULL
                GROUP BY     machines_area.machines_area_id,machines_area.machines_area_name,sfcta_status.status
            )
        )dt
        GROUP BY
            dt.machines_area_id,
            dt.machines_area_name
        ORDER BY
            dt.machines_area_id,
            dt.machines_area_name
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)){
            return ["status"=>"failure"];
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_rfid_printer($data){
        $values = [];
        $printer_outer = $this->callApiByArray_default(["apiName" => "setting", "action" => "get", "labelPrinter" => ""]);
        $printer_outer = json_encode($printer_outer);
        $sql = "INSERT INTO rfid.printer(printer_ip, printer_port,printer_name)
            SELECT printer_detail.\"cIP\",printer_detail.\"iPort\",printer.key
            FROM json_each('{$printer_outer}') printer
            LEFT JOIN json_to_record(printer.value) as printer_detail(\"cIP\" text,\"iPort\" text) ON TRUE
            ON CONFLICT (printer_ip, printer_port,printer_name)
            DO UPDATE SET printer_ip = EXCLUDED.printer_ip
            RETURNING printer_id,printer_name
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_rfid_printer_outer($data){
        $values = [
            "printer_id" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $printer_outer = $this->callApiByArray_default(["apiName" => "setting", "action" => "get", "labelPrinter" => ""]);
        $printer_outer = json_encode($printer_outer);
        $sql = "SELECT printer_outer.\"cPrinterName\"
            FROM rfid.printer
            LEFT JOIN (
                SELECT printer.key \"cPrinterName\",printer_detail.\"cIP\" printer_ip,printer_detail.\"iPort\" printer_port
                FROM json_each('{$printer_outer}') printer
                LEFT JOIN json_to_record(printer.value) as printer_detail(\"cIP\" text,\"iPort\" text) ON TRUE
            ) printer_outer ON printer.printer_ip = printer_outer.printer_ip AND printer.printer_port = printer_outer.printer_port
            WHERE printer.printer_id = :printer_id
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return $stmt->fetchColumn(0);
    }
    public function get_user_machine($data){
        $values = [
            "machines_area_id" => null,
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $stmt_array = array_filter($values,function($value){return !is_null($value);});
        $stmt_string = "";
        if(!is_null($values['machines_area_id'])) $stmt_string = "WHERE rfid.machines_area.machines_area_id = :machines_area_id";
        $sql = "SELECT rfid.user_machine.user_machine_id, system.\"user\".id user_id, system.\"user\".name user_name, public.machine.machine_name,
                    rfid.machines_area.machines_area_id, rfid.machines_area.machines_area_name
                FROM rfid.user_machine
                LEFT JOIN system.\"user\" ON system.\"user\".id = rfid.user_machine.user_id
                LEFT JOIN public.machine ON public.machine.machine_id = rfid.user_machine.machine_id
                LEFT JOIN rfid.machines_area ON rfid.machines_area.machines_area_id = public.machine.machines_area_id
                {$stmt_string}
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($stmt_array)) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function post_user_machine($data){
        $bind_many_people = $data['bind_many_people'];
        if(!$bind_many_people) {
            $values = [];
            $condition = "";
            foreach ($data['machine_id'] as $key => $machine_id) {
                $condition .= ":machine_id_{$key},";
                $values["machine_id_{$key}"] = $machine_id;
            }
            $condition = rtrim($condition, ",");

            $sql = "SELECT rfid.user_machine.user_machine_id
                    FROM rfid.user_machine
                    WHERE rfid.user_machine.machine_id IN ($condition)
            ";
            $stmt = $this->db->prepare($sql);
            if(!$stmt->execute($values)) return ["status"=>"failure"];
            $machine_id_conflict = $stmt->fetchColumn(0);
        }
        
        $values = [
            'user_id' => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }

        $condition = "";
        foreach ($data['machine_id'] as $key => $machine_id) {
            $condition .= "(:user_id, :machine_id_{$key}),";
            $values["machine_id_{$key}"] = $machine_id;
        }
        $condition = rtrim($condition, ",");

        if(!$bind_many_people) {
            $sql = "INSERT INTO rfid.user_machine(user_id, machine_id)
                    VALUES {$condition}
                    ON CONFLICT (machine_id)
                    DO NOTHING
            ";
            $stmt = $this->db->prepare($sql);

            if(!$stmt->execute($values)) return ["status"=>"failure"];
            else if($machine_id_conflict) return ["status"=>"on conflict"];
            else return ["status"=>"success"];
        }
        else {
            $sql = "INSERT INTO rfid.user_machine(user_id, machine_id)
                    VALUES {$condition}
            ";
            $stmt = $this->db->prepare($sql);

            if(!$stmt->execute($values)) return ["status"=>"failure"];
            else return ["status"=>"success"];
        }
        
    }
    public function patch_user_machine($data){
        $values = [
            "user_id" => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }

        $sql = "DELETE FROM rfid.user_machine
            WHERE user_id = :user_id
        ";
        $stmt = $this->db->prepare($sql);

        if(!$stmt->execute($values)) return ["status"=>"failure"];

        if(count($data['machine_id']) == 0){
            return ["status"=>"success"];
        }
        return $this->post_user_machine($data);
    }
    public function delete_user_machine($data){

        $values = [];
        $stmt_string = "";

        if(!is_null($data['user_id'])){
            $values = [
                "user_id" => null
            ];
            foreach ($values as $key => $value) {
                array_key_exists($key,$data)&&$values[$key]=$data[$key];
            }

            $stmt_string = "user_id = :user_id";
        }

        $condition = '';
        if(!is_null($data['user_machine_id'])) {
            foreach ($data['user_machine_id'] as $key => $user_machine_id) {
                $condition .= ":user_machine_id_{$key},";
                $values["user_machine_id_{$key}"] = $user_machine_id;
            }
            $condition = rtrim($condition, ",");
            $stmt_string = "user_machine_id IN ({$condition})";
        };

        $sql = "DELETE FROM rfid.user_machine
            WHERE {$stmt_string}
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
    public function convertDateFormat($params)
    {
        $params['start'] = str_replace('-', '', $params['start']);
        $params['end'] = str_replace('-', '', $params['end']);
        return $params;
    }
    public function get_staff_productivity($params)
    {
        $bind_values = [
            'start' => '',
            'end' => '',
            'uid' => '',
            'line' => ''
        ];
        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            }
            else {
                unset($bind_values[$key]);
            }
        }

        $line_condition = "";
        $uid_condition = "";

        if (isset($bind_values['line'])) {
            $line_condition = "AND SFCTB.TB005 = :line";
        }

        if (isset($bind_values['uid'])) {
            $uid_condition = "AND SFCTC.CREATOR = :uid";
        }

        $sql = "SELECT RTRIM(CMSMV3.MV001) 工號, CMSMV3.MV002 姓名, SFCTB.TB005 移出部門, SFCTB.TB006 移出部門名稱
                FROM SFCTB
                INNER JOIN SFCTC ON (SFCTB.TB001 = SFCTC.TC001 AND SFCTB.TB002 = SFCTC.TC002)
                RIGHT OUTER JOIN MOCTA ON (
                    MOCTA.TA001 = SFCTC.TC004
                        AND MOCTA.TA002 = SFCTC.TC005
                        AND SFCTC.TC004 = MOCTA.TA001
                        AND SFCTC.TC005 = MOCTA.TA002
                )
                INNER JOIN CMSMV CMSMV3 ON (SFCTC.CREATOR = CMSMV3.MV001)
                WHERE SFCTB.TB013 IN ('Y')
                    AND SFCTB.TB015 BETWEEN :start AND :end
                    AND SFCTC.TC013 NOT IN ('5', '6')
                    AND SFCTB.TB006 IN (
                        SELECT RTRIM(CMSMD.MD002)
                        FROM MIL.dbo.CMSMD
                    ) {$uid_condition} {$line_condition}
                GROUP BY CMSMV3.MV001, CMSMV3.MV002, SFCTB.TB005, SFCTB.TB006
                ORDER BY CMSMV3.MV001, SFCTB.TB006
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($bind_values);
        $staff_line = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $bind_values = [
            'date_begin' => '2022-01-01',
            'date_end' => '2023-01-01'
        ];
        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            }
        }

        $sql = "SELECT system.\"user\".id user_id, staff.工號, staff.姓名, staff.移出部門, staff.移出部門名稱, 
                    user_machine_working_days.machine_code machine_name, user_machine_working_days.date, 
                    STRING_AGG(user_machine_working_days.time, ',') \"time\", 
                    SUM(user_machine_working_days.working_hour) working_hour
                FROM (
                    SELECT staff_line.工號, staff_line.姓名,
                        STRING_AGG(staff_line.移出部門, ',' ORDER BY staff_line.移出部門) 移出部門, STRING_AGG(staff_line.移出部門名稱, ',' ORDER BY staff_line.移出部門)移出部門名稱
                    FROM jsonb_to_recordset('$staff_line') as staff_line(工號 text, 姓名 text, 移出部門 text, 移出部門名稱 text)
                    GROUP BY staff_line.工號, staff_line.姓名
                )staff
                LEFT JOIN system.\"user\" ON system.\"user\".uid = staff.工號
                LEFT JOIN (
                    SELECT rfid.user_order_processes.user_id, order_processes_record.machine_code, 
                        to_char(rfid.order_processes_record.start_time,'YYYY-MM-DD') as \"date\", 
                        CONCAT(to_char(rfid.order_processes_record.start_time,'YYYY-MM-DD HH24:MI:SS'), 
                            '~', to_char(rfid.order_processes_record.end_time,'YYYY-MM-DD HH24:MI:SS')) \"time\", 
                        SUM(ROUND((EXTRACT(epoch FROM (rfid.order_processes_record.end_time - rfid.order_processes_record.start_time))/3600)::numeric, 2)) AS working_hour
                    FROM rfid.user_order_processes
                    LEFT JOIN rfid.order_processes_record ON rfid.order_processes_record.order_processes_id = rfid.user_order_processes.order_processes_id
                    WHERE rfid.order_processes_record.status = 'running'
                    GROUP BY rfid.user_order_processes.user_id, order_processes_record.machine_code, 
                        rfid.order_processes_record.start_time, rfid.order_processes_record.end_time
                )user_machine_working_days ON user_machine_working_days.user_id = system.\"user\".id
                WHERE user_machine_working_days.working_hour IS NOT NULL
					AND user_machine_working_days.date <= :date_end
					AND user_machine_working_days.date >= :date_begin
                GROUP BY system.\"user\".id, staff.工號, staff.姓名, staff.移出部門, staff.移出部門名稱, 
                    user_machine_working_days.machine_code, user_machine_working_days.date
                ORDER BY staff.工號,user_machine_working_days.machine_code
        ";

        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($bind_values)) return ["status"=>"failure","error"=>$stmt->errorInfo()];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_notify_access_token()
    {
        $sql = "SELECT token FROM rfid.notify_access_token";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute()) return ["status" => "failed"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_hash_key() { date_default_timezone_set('Asia/Taipei'); $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')); $encrypted = openssl_encrypt(date("Y-m-d H:i:s"), 'aes-256-cbc', 'key', 0, $iv); return base64_encode($encrypted . '::' . $iv); }
    function hashing($key, $garble) { list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2); return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv); }

    public function get_rfid_address()
    {
        $sql = "SELECT public.rfid_address.id, public.rfid_address.\"tAddress\",
                    public.rfid_address.port, public.rfid_address.hash_key
                FROM public.rfid_address
                ORDER BY public.rfid_address.id ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute()) return ["status" => "failed"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_rfid_address_id()
    {
        $sql = "SELECT public.rfid_address.id
                FROM public.rfid_address
                ORDER BY public.rfid_address.id ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute()) return ["status" => "failed"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function patch_rfid_address_hash_key($data){
        $values = [
            "id" => null,
            "hash_key" => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->sync_url); //, "http://localhost/hash_key"
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        $hash_key = json_decode($head, true);
        curl_close($ch);

        $values['hash_key'] = $hash_key;

        $sql = "UPDATE public.rfid_address
            SET hash_key = :hash_key
            WHERE public.rfid_address.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }

    public function get_license($data){
        $values = [
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        $sql = "SELECT \"key\"
            FROM rfid.license
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        $key = $stmt->fetchColumn(0);
        return ["deadline"=>$this->hashing("key",$key)];
    }
    public function patch_license($data){
        $values = [
            "key" => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }
        if(is_null($values['key'])){
            return ["status"=>"failure"];
        }
        $sql = "UPDATE rfid.license
            SET key = :key
            WHERE true
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
    public function get_user_rfid_tag($data){
        $bind_values = [
            'start'=> date("Ymd",strtotime("-1 year")),
            "line" => null
        ];

        $line_condition = '';
        if(array_key_exists('line', $data)) {
            $bind_values['line'] = $data['line'];
            $line_condition = " AND SFCTB.TB005 = :line";
        }
        else {
            unset($bind_values['line']);
        }

        $sql = "SELECT RTRIM(CMSMV3.MV001) uid, CMSMV3.MV002 name, SFCTB.TB005 line, SFCTB.TB006 line_name
                FROM SFCTB
                INNER JOIN SFCTC ON (SFCTB.TB001 = SFCTC.TC001 AND SFCTB.TB002 = SFCTC.TC002)
                RIGHT OUTER JOIN MOCTA ON (
                    MOCTA.TA001 = SFCTC.TC004
                        AND MOCTA.TA002 = SFCTC.TC005
                        AND SFCTC.TC004 = MOCTA.TA001
                        AND SFCTC.TC005 = MOCTA.TA002
                )
                INNER JOIN CMSMV CMSMV3 ON (SFCTC.CREATOR = CMSMV3.MV001)
                WHERE SFCTB.TB013 IN ('Y')
                    AND SFCTB.TB015 >= :start 
                    AND SFCTC.TC013 NOT IN ('5', '6') 
                    AND SFCTB.TB006 IN (
                        SELECT RTRIM(CMSMD.MD002)
                        FROM MIL.dbo.CMSMD
                    )
                    {$line_condition}
                GROUP BY CMSMV3.MV001, CMSMV3.MV002, SFCTB.TB005, SFCTB.TB006
                ORDER BY CMSMV3.MV001, SFCTB.TB006
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($bind_values);
        $staff_line = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $bind_values = [
            "uid" => null
        ];

        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $bind_values[$key] = $data[$key];
            }
            else {
                unset($bind_values[$key]);
            }
        }

        $condition = '';

        if(array_key_exists('uid', $data)) {
            $condition .= " AND system.\"user\".uid = :uid";
        }
        if(array_key_exists('line', $data)) {
            $condition .= " AND staff_lines.line IS NOT NULL";
        }

        $sql = "SELECT system.\"user\".id user_id, user_modal.module_name module_name, 
                    system.\"user\".name user_name, 
                    CASE WHEN system.\"user\".uid IS NOT NULL THEN system.\"user\".uid
                        ELSE staff_lines.uid
                    END uid, 
                    CASE WHEN staff_lines.uid_name IS NOT NULL THEN staff_lines.uid_name 
                        ELSE CONCAT(system.\"user\".uid, '_', system.\"user\".name) 
                    END uid_name, 
                    STRING_AGG(CAST(rfid_tags.rfid_tag_id AS TEXT), ',' ORDER BY rfid_tags.rfid_tag_id) rfid_tag_id, 
                    STRING_AGG(rfid_tags.rfid_tag, ',' ORDER BY rfid_tags.rfid_tag_id) rfid_tag, 
                    STRING_AGG(rfid_tags.printer, ',' ORDER BY rfid_tags.printer) printer, 
                    STRING_AGG(rfid_tags.start_time, ',' ORDER BY rfid_tags.start_time) start_time, 
                    STRING_AGG(rfid_tags.print_reason, ',' ORDER BY rfid_tags.print_reason) print_reason, 
                    machines.machine_name, staff_lines.line, staff_lines.line_name
                FROM (
                    SELECT staff_line.uid, CONCAT(staff_line.uid, '_', staff_line.name) uid_name, 
                        STRING_AGG(staff_line.line, ',' ORDER BY staff_line.line) line, 
                        STRING_AGG(staff_line.line_name, ',' ORDER BY staff_line.line) line_name
                    FROM jsonb_to_recordset('{$staff_line}') as staff_line(uid text, name text, line text, line_name text)
                    GROUP BY staff_line.uid, staff_line.name
                )staff_lines
                LEFT JOIN system.\"user\" ON staff_lines.uid = system.\"user\".uid
                LEFT JOIN (
                    SELECT rfid.user_machine.user_id, 
                        STRING_AGG(CONCAT(public.machine.machine_id, '=>', public.machine.machine_name), ',') machine_name
                    FROM rfid.user_machine
                    LEFT JOIN public.machine ON public.machine.machine_id = rfid.user_machine.machine_id
                    GROUP BY rfid.user_machine.user_id
                )machines ON machines.user_id = system.\"user\".id
                LEFT JOIN (
                    SELECT rfid.rfid_tag.rfid_tag_id, rfid.user_rfid_tag.user_id, rfid.rfid_tag.rfid_tag, printer.name printer, to_char(rfid.rfid_tag.start_time,'YYYY-MM-DD') start_time,
                        rfid.print_reason.print_reason
                    FROM rfid.user_rfid_tag
                    LEFT JOIN rfid.rfid_tag ON rfid.rfid_tag.rfid_tag_id = rfid.user_rfid_tag.rfid_tag_id
                    LEFT JOIN system.\"user\" AS printer ON printer.id = rfid.rfid_tag.printer_user_id
                    LEFT JOIN rfid.print_reason ON rfid.print_reason.print_reason_id = rfid.rfid_tag.print_reason_id
                )rfid_tags ON rfid_tags.user_id = system.\"user\".id
                LEFT JOIN (
                    SELECT system.user_modal.uid, STRING_AGG(setting.module.name, ',') module_name
                    FROM system.user_modal
                    LEFT JOIN setting.module ON setting.module.id = system.user_modal.module_id
                    GROUP BY user_modal.uid
                )user_modal ON user_modal.uid = system.\"user\".id
                WHERE TRUE {$condition}
                GROUP BY system.\"user\".id, user_modal.module_name, 
                    system.\"user\".uid, system.\"user\".name, 
                    staff_lines.uid, staff_lines.uid_name, machines.machine_name, 
                    staff_lines.line, staff_lines.line_name
                ORDER BY machines.machine_name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function post_user_rfid_tag($data){
        $values = [
            'user_id' => null,
            'rfid_tag_id' => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }

        $sql = "INSERT INTO rfid.user_rfid_tag(user_id, rfid_tag_id)
                VALUES (:user_id, :rfid_tag_id)
                ON CONFLICT (user_id, rfid_tag_id)
                DO NOTHING
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
    public function patch_user_rfid_tag($data){
        $values = [
            "user_id" => null,
            "rfid_tag_id" => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }

        $sql = "DELETE FROM rfid.user_rfid_tag
            WHERE user_id = :user_id AND rfid_tag_id NOT IN (:rfid_tag_id)
        ";
        $stmt = $this->db->prepare($sql);

        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
    public function delete_user_rfid_tag($data){

        $values = [];
        $stmt_string = "";

        $values = [
            "user_id" => null,
            "rfid_tag_id" => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key,$data)&&$values[$key]=$data[$key];
        }

        $sql = "DELETE FROM rfid.user_rfid_tag
            WHERE user_id = :user_id AND rfid_tag_id = :rfid_tag_id
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
    public function get_all_user($data){

        $sql = "SELECT system.\"user\".id, system.\"user\".name
                FROM system.\"user\"
                ORDER BY system.\"user\".id
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute()) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_all_machine($data){

        $sql = "SELECT public.machine.machine_id, public.machine.machine_name
                FROM public.machine
                WHERE public.machine.machine_name IS NOT NULL
                ORDER BY public.machine.machine_id
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute()) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_all_line() {
        $sql = "SELECT RTRIM(CMSMD.MD001) line, CMSMD.MD002 line_name
                FROM MIL.dbo.CMSMD
        ";

        // $sql = "SELECT TOP 100 CMSMX.*
        //         FROM MIL.dbo.CMSMX
        // ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_all_print_reason(){

        $sql = "SELECT rfid.print_reason.print_reason_id, rfid.print_reason.print_reason
                FROM rfid.print_reason
        ";
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute()) return ["status"=>"failure"];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function post_rfid_tag($data){
        $values = [
            'rfid_tag' => null,
            'printer_user_id' => null,
            'print_reason_id' => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] != "") {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO rfid.rfid_tag(rfid_tag, printer_user_id, start_time, print_reason_id)
                VALUES (:rfid_tag, :printer_user_id, NOW(), :print_reason_id)
                RETURNING rfid_tag_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public function get_staff_uid($params){
        $bind_values = [
            'line' => ''
        ];
        foreach ($bind_values as $key => $value) {
            if (array_key_exists($key, $params)) {
                $bind_values[$key] = $params[$key];
            }
            else {
                unset($bind_values[$key]);
            }
        }

        $line_condition = "";

        if (isset($bind_values['line'])) {
            $line_condition = "AND SFCTB.TB005 = :line";
        }

        $sql = "SELECT RTRIM(CMSMV3.MV001) uid, RTRIM(CMSMV3.MV001) + '_' + CMSMV3.MV002 name
                FROM SFCTB
                INNER JOIN SFCTC ON (SFCTB.TB001 = SFCTC.TC001 AND SFCTB.TB002 = SFCTC.TC002)
                RIGHT OUTER JOIN MOCTA ON (
                    MOCTA.TA001 = SFCTC.TC004
                        AND MOCTA.TA002 = SFCTC.TC005
                        AND SFCTC.TC004 = MOCTA.TA001
                        AND SFCTC.TC005 = MOCTA.TA002
                )
                INNER JOIN CMSMV CMSMV3 ON (SFCTC.CREATOR = CMSMV3.MV001)
                WHERE SFCTB.TB013 IN ('Y')
                    AND SFCTC.TC013 NOT IN ('5', '6') {$line_condition}
                GROUP BY SFCTB.TB005, CMSMV3.MV001, CMSMV3.MV002
                ORDER BY SFCTB.TB005, CMSMV3.MV001
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($bind_values);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_unbinding_line_machine($data){
        $values = [];
        $condition = '';
        if(array_key_exists('line', $data)) {
            $condition = 'WHERE RTRIM(CMSMD.MD001) IN (';
            foreach($data['line'] as $key => $line) {
                $condition .= ":line_{$key},";
                $values["line_{$key}"] = $line;
            }
            $condition = rtrim($condition, ',');
            $condition .= ')';
        }

        $sql = "SELECT RTRIM(CMSMD.MD001) line, CMSMD.MD002 line_name,
                    RTRIM(CMSMX.MX001) machine_code, CMSMX.MX003 machine_name
                FROM MIL.dbo.CMSMD
                LEFT JOIN CMSMX ON RTRIM(CMSMX.MX002) = RTRIM(CMSMD.MD001)
                {$condition}
                GROUP BY RTRIM(CMSMD.MD001), CMSMD.MD002, CMSMX.MX001, CMSMX.MX003
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($values);
        $line_machine = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $sql = "SELECT public.machine.machine_id, line_machine.line, line_machine.line_name, line_machine.machine_code, line_machine.machine_name
                FROM jsonb_to_recordset('{$line_machine}') AS line_machine(line text, line_name text, machine_code text, machine_name text)
                LEFT JOIN public.machine ON public.machine.machine_code = line_machine.machine_code
                WHERE public.machine.machine_id IS NOT NULL 
                    AND public.machine.machine_id NOT IN (
                        SELECT rfid.user_machine.machine_id
                        FROM rfid.user_machine 
                    )

        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_line_machine($data){
        $values = [];
        $condition = '';
        if(array_key_exists('line', $data)) {
            $condition = 'WHERE RTRIM(CMSMD.MD001) IN (';
            foreach($data['line'] as $key => $line) {
                $condition .= ":line_{$key},";
                $values["line_{$key}"] = $line;
            }
            $condition = rtrim($condition, ',');
            $condition .= ')';
        }

        $sql = "SELECT RTRIM(CMSMD.MD001) line, CMSMD.MD002 line_name,
                    RTRIM(CMSMX.MX001) machine_code, CMSMX.MX003 machine_name
                FROM MIL.dbo.CMSMD
                LEFT JOIN CMSMX ON RTRIM(CMSMX.MX002) = RTRIM(CMSMD.MD001)
                {$condition}
                GROUP BY RTRIM(CMSMD.MD001), CMSMD.MD002, CMSMX.MX001, CMSMX.MX003
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($values);
        $line_machine = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $sql = "SELECT public.machine.machine_id, line_machine.line, line_machine.line_name, line_machine.machine_code, line_machine.machine_name
                FROM jsonb_to_recordset('{$line_machine}') AS line_machine(line text, line_name text, machine_code text, machine_name text)
                LEFT JOIN public.machine ON public.machine.machine_code = line_machine.machine_code
                WHERE public.machine.machine_id IS NOT NULL

        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_problem($data){

        $sql = "SELECT *
                FROM rfid.problem

        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function uid_to_user_id($data){

        $bind_values = [
            'uid' => $data['uid']
        ];

        $sql = "SELECT system.\"user\".id
                FROM system.\"user\"
                WHERE system.\"user\".uid = :uid
                
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchColumn(0);

        if($result) {
            return $result;
        }

        $sql = "SELECT CMSMV3.MV002 name
                FROM SFCTB
                INNER JOIN SFCTC ON (SFCTB.TB001 = SFCTC.TC001 AND SFCTB.TB002 = SFCTC.TC002)
                RIGHT OUTER JOIN MOCTA ON (
                    MOCTA.TA001 = SFCTC.TC004
                        AND MOCTA.TA002 = SFCTC.TC005
                        AND SFCTC.TC004 = MOCTA.TA001
                        AND SFCTC.TC005 = MOCTA.TA002
                )
                INNER JOIN CMSMV CMSMV3 ON (SFCTC.CREATOR = CMSMV3.MV001)
                WHERE SFCTB.TB013 IN ('Y')
                    AND SFCTC.TC013 NOT IN ('5', '6') AND RTRIM(CMSMV3.MV001) = :uid
                GROUP BY SFCTB.TB005, CMSMV3.MV001, CMSMV3.MV002
                ORDER BY SFCTB.TB005, CMSMV3.MV001
        ";

        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($bind_values);
        $user_name = $stmt->fetchColumn(0);

        $bind_values = [
            'uid' => $data['uid'],
            'name' => $user_name
        ];

        $sql = "INSERT INTO system.\"user\"(uid, name)
                VALUES(:uid, :name)
                RETURNING system.\"user\".id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public function get_user_id($data){

        $bind_values = [
            'uid' => $data['uid']
        ];

        $sql = "SELECT system.\"user\".id
                FROM system.\"user\"
                WHERE system.\"user\".uid = :uid
                
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public function post_user_uid($data){

        $bind_values = [
            'uid' => $data['uid'],
            'name' => $data['name']
        ];

        $sql = "INSERT INTO system.\"user\"(uid, name)
                VALUES(:uid, :name)
                RETURNING system.\"user\".id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public function get_assign_order($data){

        $bind_values = [
            'start'=> date("Ymd",strtotime("-1 year"))
        ];

        $uid_condition = "";
        if (array_key_exists('uid', $data)) {
            $bind_values['uid'] = $data['uid'];
            $uid_condition = " AND RTRIM(CMSMV3.MV001) = :uid";
        }

        $sql = "SELECT RTRIM(CMSMV3.MV001) uid, CMSMV3.MV002 name, SFCTB.TB005 line, SFCTB.TB006 line_name
                FROM SFCTB
                INNER JOIN SFCTC ON (SFCTB.TB001 = SFCTC.TC001 AND SFCTB.TB002 = SFCTC.TC002)
                RIGHT OUTER JOIN MOCTA ON (
                    MOCTA.TA001 = SFCTC.TC004
                        AND MOCTA.TA002 = SFCTC.TC005
                        AND SFCTC.TC004 = MOCTA.TA001
                        AND SFCTC.TC005 = MOCTA.TA002
                )
                INNER JOIN CMSMV CMSMV3 ON (SFCTC.CREATOR = CMSMV3.MV001)
                WHERE SFCTB.TB013 IN ('Y') AND SFCTC.TC013 NOT IN ('5', '6')
                    AND SFCTB.TB006 IN (
                        SELECT RTRIM(CMSMD.MD002)
                        FROM MIL.dbo.CMSMD
                    )
                    AND SFCTB.TB015 >= :start {$uid_condition}
                GROUP BY RTRIM(CMSMV3.MV001), CMSMV3.MV002, SFCTB.TB005, SFCTB.TB006
        ";
        
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($bind_values);
        $uid_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $uid_name = json_encode($uid_name);

        unset($bind_values['start']);

        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);

        $line_condition = "";
        $line_machine_data = [];
        if (array_key_exists('line', $data)) {
            $bind_values['line'] = $data['line'];
            array_push($line_machine_data, $data['line']);
            $line_condition = " AND line_machine.line = :line";
        }
        else {
            unset($bind_values['line']);
        }
        if (array_key_exists('uid', $data)) {
            $uid_condition = "WHERE system.\"user\".uid = :uid";
        }
        
        $line_machine = $this->get_line_machine($line_machine_data);
        $line_machine = json_encode($line_machine);

        $sql = "SELECT DISTINCT lines_processes.order_processes_id, lines_processes.\"TA001\", lines_processes.\"TA002\", 
                    line_machine.line, line_machine.line_name, line_machine.machine_code, line_machine.machine_name, 
                    user_machine.user_name, user_machine.user_id
                FROM (
                    SELECT rfid.user_machine.*, uid_name.name user_name
                    FROM rfid.user_machine
                    LEFT JOIN system.\"user\" ON system.\"user\".id = rfid.user_machine.user_id
                    LEFT JOIN (
                        SELECT *
                        FROM json_to_recordset('{$uid_name}') AS uid_name(\"uid\" text,\"name\" text, \"line\" text,\"line_name\" text)
                    )uid_name ON TRIM(uid_name.uid) = TRIM(system.\"user\".uid)
                    {$uid_condition}
                )user_machine
                LEFT JOIN public.machine ON public.machine.machine_id = user_machine.machine_id
                LEFT JOIN (
                    SELECT *
                    FROM json_to_recordset('{$line_machine}')
                    AS line_machine(\"line\" text, \"line_name\" text, \"machine_code\" text, \"machine_name\" text)
                )line_machine ON line_machine.machine_code = public.machine.machine_code
                LEFT JOIN (
                    WITH lines_machines_processes_outer AS (
                        SELECT *
                        FROM json_to_recordset('{$lines_machines_processes_outer}')
                        AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                    )
                    SELECT order_processes_history_lastest.\"TA001\",order_processes_history_lastest.\"TA002\",lines_machines_processes_outer.\"line_code\",
                        order_processes_history_lastest.order_processes_id
                    FROM(
                        SELECT order_processes_history.\"TA001\",order_processes_history.\"TA002\",STRING_AGG(CASE WHEN row_num =1 THEN order_processes_history.\"TA004\" END,'')\"TA004\",
                            COALESCE(MAX(CASE WHEN row_num =1 THEN order_processes_history.status END),'waiting')status,MAX(CASE WHEN row_num =1 THEN order_processes_history.end_time END)end_time,MAX(CASE WHEN row_num =1 THEN order_processes_history.order_processes_id END)order_processes_id
                        FROM(
                            SELECT order_processes.order_processes_id,order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",order_processes.fk->>'TA004' \"TA004\",MAX(order_processes_record.end_time)end_time,
                                MAX(order_processes_record.status)status,
                                ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE WHEN order_processes_record.status IS NULL THEN order_processes.fk->>'TA003' ELSE '0' END ASC,order_processes.fk->>'TA003' DESC,CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
                            FROM public.order_processes
                            LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                            LEFT JOIN lines_machines_processes_outer ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
                            WHERE TRIM(order_processes.fk->>'TA003') != ''
                            GROUP BY order_processes.order_processes_id,order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes.fk->>'TA004',order_processes_record.status
                        )order_processes_history
                            GROUP BY order_processes_history.\"TA001\",order_processes_history.\"TA002\"
                            HAVING COALESCE(MAX(CASE WHEN row_num =1 THEN order_processes_history.status END),'waiting') = 'waiting'
                    )order_processes_history_lastest
                    LEFT JOIN (
                        SELECT \"line_code\",STRING_AGG(\"processes\",',')\"processes\"
                        FROM lines_machines_processes_outer
                        GROUP BY \"line_code\"
                    )lines_machines_processes_outer ON lines_machines_processes_outer.\"processes\" LIKE '%' || order_processes_history_lastest.\"TA004\" || '%'
                )lines_processes ON lines_processes.line_code = line_machine.line
                WHERE lines_processes.order_processes_id IS NOT NULL {$line_condition}
                ORDER BY lines_processes.\"TA001\", lines_processes.\"TA002\"
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $lines_processes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $params['order_processes'] = [];
        foreach($lines_processes as $value) {
            $order_processes = [
                'TA001' => $value['TA001'],
                'TA002' => $value['TA002']
            ];
            array_push($params['order_processes'], $order_processes);
        }
        $params['date_begin'] = '10000101';//預設從前到現在
        $order_processes_detail = $this->get_order_processes_detail_outer(array_merge($params,["size"=>1000]));
        $order_processes_detail = $order_processes_detail['data'];

        $lines_processes = json_encode($lines_processes);

        $sql = "SELECT DISTINCT lines_processes.order_processes_id, lines_processes.\"TA001\", 
                    lines_processes.\"TA002\", lines_processes.\"line\", lines_processes.\"line_name\", 
                    lines_processes.\"machine_code\", lines_processes.\"machine_name\", 
                    CASE WHEN rfid.user_order_processes.user_order_processes_id IS NOT NULL 
                        THEN lines_processes.\"user_name\" ELSE NULL 
                    END user_name, order_processes_detail.預計生產數量, 
                    order_processes_detail.製令開單人, order_processes_detail.訂單單號
                FROM json_to_recordset('{$lines_processes}')
                    AS lines_processes(\"order_processes_id\" text,\"TA001\" text,\"TA002\" text,\"line\" text,
                        \"line_name\" text, \"machine_code\" text, \"machine_name\" text, \"user_name\" text, 
                    \"user_id\" text)
                LEFT JOIN (
                    SELECT order_processes_detail.\"TA001\", order_processes_detail.\"TA002\", order_processes_detail.\"預計產量\" 預計生產數量, 
                        order_processes_detail.製令開單人, 
                        CONCAT(order_processes_detail.\"TD001\", '-', RTRIM(order_processes_detail.\"TD002\"), '-', order_processes_detail.\"TD003\") \"訂單單號\"
                    FROM json_to_recordset('{$order_processes_detail}') AS order_processes_detail (\"總數量\" text, \"預計產量\" text, \"製令單別\" text, \"TA001\" text, 
                        \"製令單號\" text, \"製令開單日期\" text, \"employee_id\" text, \"製令開單人\" text, 
                        \"預計熱處理日期\" text, \"訂單開單日期\" text, \"TA002\" text, \"訂單交期\" text, 
                        \"訂單數量\" text, \"預計生產完成日\" text, \"order_name\" text, \"客戶圖號\" text, 
                        \"TD001\" text, \"TD002\" text, \"TD003\" text, \"key\" text
                    )
                )order_processes_detail ON TRIM(order_processes_detail.\"TA001\") = TRIM(lines_processes.\"TA001\")
                        AND TRIM(order_processes_detail.\"TA002\") = TRIM(lines_processes.\"TA002\")
                LEFT JOIN rfid.user_order_processes ON rfid.user_order_processes.order_processes_id = CAST(lines_processes.order_processes_id AS INT)
                    AND rfid.user_order_processes.user_id = CAST(lines_processes.user_id AS INT)
                ORDER BY lines_processes.order_processes_id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function post_assign_order($data){
        $values = [
            'user_id' => null,
            'order_processes_id' => null,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] != "") {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO rfid.user_order_processes(user_id, order_processes_id)
                VALUES (:user_id, :order_processes_id)
                ON CONFLICT (order_processes_id)
                DO NOTHING
        ";

        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }

    public function get_problem_tag($data){

        $values = [
            'problem_id' => 1
        ];

        if (array_key_exists('problem_id', $data) && $data['problem_id'] != "") {
            $values['problem_id'] = $data['problem_id'];
        }

        $sql = "SELECT DISTINCT rfid.problem_tag.problem_tag_id, rfid.problem_tag.rfid_tag_id, 
                    rfid.problem.problem_name, rfid.problem_tag.problem_number, rfid.problem_tag.problem_reason, 
                    to_char(rfid.problem_tag.start_time,'YYYY-MM-DD HH24:MI:SS') start_time, 
                    to_char(rfid.problem_tag.end_time,'YYYY-MM-DD HH24:MI:SS') end_time,
                    TRIM(order_processes.fk->>'TA001') \"TA001\", TRIM(order_processes.fk->>'TA002') \"TA002\"
                FROM rfid.problem_tag
                LEFT JOIN rfid.problem ON rfid.problem.problem_id = rfid.problem_tag.problem_id
                LEFT JOIN public.order_processes_tag ON public.order_processes_tag.rfid_tag_id = rfid.problem_tag.rfid_tag_id
                LEFT JOIN public.order_processes ON public.order_processes.order_processes_id = public.order_processes_tag.order_processes_id
                WHERE rfid.problem.problem_id = :problem_id
                ORDER BY to_char(rfid.problem_tag.start_time,'YYYY-MM-DD HH24:MI:SS') DESC, to_char(rfid.problem_tag.end_time,'YYYY-MM-DD HH24:MI:SS') DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $problem_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $params['order_processes'] = [];
        foreach($problem_tags as $value) {
            $order_processes = [
                'TA001' => $value['TA001'],
                'TA002' => $value['TA002']
            ];
            if($value['TA001'] != NULL && $value['TA002'] != NULL) {
                array_push($params['order_processes'], $order_processes);
            }
        }

        $params['date_begin'] = '10000101';//預設從前到現在
        $order_processes_detail = $this->get_order_processes_detail_outer($params);
        $order_processes_detail = $order_processes_detail['data'];
        $problem_tags = json_encode($problem_tags);

        $sql = "SELECT problem_tags.*, order_processes_detail.製令開單人, order_processes_detail.客戶圖號, 
                    order_processes_detail.訂單單號
                FROM json_to_recordset('{$problem_tags}')
                    AS problem_tags(\"problem_tag_id\" text,\"rfid_tag_id\" text,\"problem_name\" text,
                        \"problem_number\" text, \"problem_reason\" text, 
                        \"start_time\" text,\"end_time\" text, \"TA001\" text, \"TA002\" text)
                LEFT JOIN (
                    SELECT order_processes_detail.\"TA001\", order_processes_detail.\"TA002\", order_processes_detail.\"製令開單人\", 
                        order_processes_detail.客戶圖號, 
                        CONCAT(order_processes_detail.\"TD001\", '-', RTRIM(order_processes_detail.\"TD002\"), '-', order_processes_detail.\"TD003\") \"訂單單號\"
                    FROM json_to_recordset('{$order_processes_detail}') AS order_processes_detail (\"總數量\" text, \"預計產量\" text, \"製令單別\" text, \"TA001\" text, 
                        \"製令單號\" text, \"製令開單日期\" text, \"employee_id\" text, \"製令開單人\" text, 
                        \"預計熱處理日期\" text, \"訂單開單日期\" text, \"TA002\" text, \"訂單交期\" text, 
                        \"訂單數量\" text, \"預計生產完成日\" text, \"order_name\" text, \"客戶圖號\" text, 
                        \"TD001\" text, \"TD002\" text, \"TD003\" text, \"key\" text
                    )
                )order_processes_detail ON TRIM(order_processes_detail.\"TA001\") = TRIM(problem_tags.\"TA001\")
                    AND TRIM(order_processes_detail.\"TA002\") = TRIM(problem_tags.\"TA002\")
                ORDER BY problem_tags.start_time DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_user_assigned_order($data){

        $bind_values = [
            'start'=> date("Ymd",strtotime("-1 year")),
        ];

        $sql = "SELECT RTRIM(CMSMV3.MV001) uid, CMSMV3.MV002 name, SFCTB.TB005 line, SFCTB.TB006 line_name
                FROM SFCTB
                INNER JOIN SFCTC ON (SFCTB.TB001 = SFCTC.TC001 AND SFCTB.TB002 = SFCTC.TC002)
                RIGHT OUTER JOIN MOCTA ON (
                    MOCTA.TA001 = SFCTC.TC004
                        AND MOCTA.TA002 = SFCTC.TC005
                        AND SFCTC.TC004 = MOCTA.TA001
                        AND SFCTC.TC005 = MOCTA.TA002
                )
                INNER JOIN CMSMV CMSMV3 ON (SFCTC.CREATOR = CMSMV3.MV001)
                WHERE SFCTB.TB013 IN ('Y')
                    AND SFCTB.TB015 >= :start 
                    AND SFCTC.TC013 NOT IN ('5', '6')
                    AND SFCTB.TB006 IN (
                        SELECT RTRIM(CMSMD.MD002)
                        FROM MIL.dbo.CMSMD
                    )
                GROUP BY CMSMV3.MV001, CMSMV3.MV002, SFCTB.TB005, SFCTB.TB006
                ORDER BY CMSMV3.MV001, SFCTB.TB006
        ";
        
        $stmt = $this->db_sqlsrv->prepare($sql);
        $stmt->execute($bind_values);
        $staff_detail = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $staff_detail = json_encode($staff_detail);

        $bind_values = [];
        $condition = '';
        if (array_key_exists('line', $data)) {
            $bind_values['line'] = $data['line'];
            $condition .= " AND staff_detail.line = :line";
        }
        if (array_key_exists('uid', $data)) {
            $bind_values['uid'] = $data['uid'];
            $condition .= " AND staff_detail.uid = :uid";
        }

        $sql = "SELECT DISTINCT system.\"user\".id user_id, staff_detail.uid, staff_detail.name, TRIM(public.order_processes.fk->>'TA001') \"TA001\", 
                    TRIM(public.order_processes.fk->>'TA002') \"TA002\", staff_detail.line, staff_detail.line_name
                FROM (
                    SELECT staff_detail.uid, staff_detail.name, 
                        STRING_AGG(staff_detail.line, ',') line, 
                        STRING_AGG(staff_detail.line_name, ',') line_name
                    FROM json_to_recordset('{$staff_detail}') AS staff_detail(\"uid\" text,\"name\" text, \"line\" text,\"line_name\" text)
                    WHERE TRUE {$condition}
                    GROUP BY staff_detail.uid, staff_detail.name
                )staff_detail
                LEFT JOIN system.\"user\" ON TRIM(staff_detail.uid) = TRIM(system.\"user\".uid)
                LEFT JOIN rfid.user_order_processes ON system.\"user\".id = rfid.user_order_processes.user_id
                LEFT JOIN public.order_processes ON public.order_processes.order_processes_id = rfid.user_order_processes.order_processes_id
                LEFT JOIN rfid.order_processes_record ON rfid.order_processes_record.order_processes_id = public.order_processes.order_processes_id AND rfid.order_processes_record.status = 'waiting'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind_values);
        $user_order_processes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $params['order_processes'] = [];
        foreach($user_order_processes as $value) {
            $order_processes = [
                'TA001' => $value['TA001'],
                'TA002' => $value['TA002']
            ];
            if($value['TA001'] != NULL && $value['TA002'] != NULL) {
                array_push($params['order_processes'], $order_processes);
            }
        }

        $params['date_begin'] = '10000101';//預設從前到現在
        $order_processes_detail = $this->get_order_processes_detail_outer($params);
        $order_processes_detail = $order_processes_detail['data'];
        $user_order_processes = json_encode($user_order_processes);

        $sql = "SELECT DISTINCT user_order_processes.*, order_processes_detail.製令開單人, order_processes_detail.客戶圖號, 
                    order_processes_detail.訂單單號
                FROM json_to_recordset('{$user_order_processes}')
                    AS user_order_processes(\"user_id\" text,\"uid\" text, \"name\" text,\"TA001\" text, \"TA002\" text,\"line\" text, \"line_name\" text)
                LEFT JOIN (
                    SELECT order_processes_detail.\"TA001\", order_processes_detail.\"TA002\", order_processes_detail.\"製令開單人\", 
                        order_processes_detail.客戶圖號, 
                        CONCAT(order_processes_detail.\"TD001\", '-', RTRIM(order_processes_detail.\"TD002\"), '-', order_processes_detail.\"TD003\") \"訂單單號\"
                    FROM json_to_recordset('{$order_processes_detail}') AS order_processes_detail (\"總數量\" text, \"預計產量\" text, \"製令單別\" text, \"TA001\" text, 
                        \"製令單號\" text, \"製令開單日期\" text, \"employee_id\" text, \"製令開單人\" text, 
                        \"預計熱處理日期\" text, \"訂單開單日期\" text, \"TA002\" text, \"訂單交期\" text, 
                        \"訂單數量\" text, \"預計生產完成日\" text, \"order_name\" text, \"客戶圖號\" text, 
                        \"TD001\" text, \"TD002\" text, \"TD003\" text, \"key\" text
                    )
                )order_processes_detail ON TRIM(order_processes_detail.\"TA001\") = TRIM(user_order_processes.\"TA001\")
                    AND TRIM(order_processes_detail.\"TA002\") = TRIM(user_order_processes.\"TA002\")
                ORDER BY user_order_processes.uid
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_order_processes_tag($data){
        $values = [];
        
        $condition = '';
        foreach($data['order_processes_id'] as $order_processes_key => $order_processes_id) {
            foreach($data['rfid_tag_id'] as $rfid_tag_id_key => $rfid_tag_id) {
                $condition .= "(:order_processes_id_{$order_processes_key}, :rfid_tag_id_{$rfid_tag_id_key}),";
                $values["rfid_tag_id_{$rfid_tag_id_key}"] = $rfid_tag_id;
            }
            $values["order_processes_id_{$order_processes_key}"] = $order_processes_id;
        }
        $condition = rtrim($condition, ',');

        $sql = "INSERT INTO public.order_processes_tag(order_processes_id, rfid_tag_id)
                VALUES {$condition}
        ";
        
        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }

    public function post_problem_tag($data){
        $values = [
            'problem_id' => null, 
            'problem_reason' => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] != "") {
                $values[$key] = $data[$key];
            }
        }

        $condition = '';
        foreach($data['rfid_tag_id'] as $key => $rfid_tag_id) {
            $condition .= "(:rfid_tag_id_{$key}, :problem_id, :problem_number_{$key}, NOW(), :problem_reason),";
            $values["rfid_tag_id_{$key}"] = $rfid_tag_id;
            $values["problem_number_{$key}"] = $data['problem_number'][$key];
        }
        $condition = rtrim($condition, ',');

        $sql = "INSERT INTO rfid.problem_tag(rfid_tag_id, problem_id, problem_number, start_time, problem_reason)
                VALUES {$condition}
        ";

        $stmt = $this->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
    
    public function get_order_processes_id($data){

        $values = [
            'TA001' => null,
            'TA002' => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] != "") {
                $values[$key] = $data[$key];
            }
        }

        $lines_machines_processes_outer = $this->get_lines_machines_processes_outer($data);
        $lines_machines_processes_outer = json_encode($lines_machines_processes_outer);

        $sql = "WITH lines_machines_processes_outer AS (
                    SELECT *
                    FROM json_to_recordset('{$lines_machines_processes_outer}')
                    AS lines_machines_processes_outer(\"machine_code\" text,\"machine_name\" text,\"line_code\" text,\"line_name\" text,\"processes\" text)
                )
                SELECT order_processes_history.order_processes_id
                FROM(
                    SELECT order_processes.order_processes_id,order_processes.fk->>'TA001' \"TA001\",order_processes.fk->>'TA002' \"TA002\",order_processes.fk->>'TA003' \"WR007\",order_processes.fk->>'TA004' \"TA004\",MAX(order_processes_record.end_time)end_time,
                        MAX(order_processes_record.status)status,
                        ROW_NUMBER()OVER (PARTITION BY order_processes.fk->>'TA001',order_processes.fk->>'TA002' ORDER BY CASE order_processes_record.status WHEN 'ready'THEN 3 WHEN 'running' THEN 2 ELSE 1 END DESC ,COALESCE(order_processes.fk->>'TA003','')DESC,COALESCE(MAX(order_processes_record.end_time),'1999-01-01'::timestamp)DESC)row_num
                    FROM public.order_processes
                    LEFT JOIN rfid.order_processes_record ON order_processes_record.order_processes_id = order_processes.order_processes_id
                    LEFT JOIN lines_machines_processes_outer ON lines_machines_processes_outer.\"machine_code\" = order_processes_record.machine_code
                    WHERE TRIM(order_processes.fk->>'TA003') != ''
                    GROUP BY order_processes.order_processes_id,order_processes.fk->>'TA001',order_processes.fk->>'TA002',order_processes.fk->>'TA003',order_processes.fk->>'TA004',order_processes_record.status
                )order_processes_history
                WHERE order_processes_history.\"TA001\" = :TA001
                    AND order_processes_history.\"TA002\" = :TA002
                GROUP BY order_processes_history.\"TA001\",order_processes_history.\"TA002\", order_processes_history.order_processes_id
            ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function get_rfid_tag($data){

        $values = [
            'rfid_tag_id' => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] != "") {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT public.rfid_tag.rfid_tag
                FROM public.rfid_tag
                WHERE public.rfid_tag.rfid_tag_id = :rfid_tag_id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetchColumn(0);
        return $result;
    }

    public function update_rfid_tag($data)
    {
        $values = [
            "rfid_tag_id" => '',
            "rfid_tag" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "UPDATE public.rfid_tag
                SET rfid_tag=:rfid_tag
                WHERE rfid_tag_id = :rfid_tag_id
        ";
        $stmt = $this->container->db->prepare($sql);
        if(!$stmt->execute($values)) return ["status"=>"failure"];
        return ["status"=>"success"];
    }
}

