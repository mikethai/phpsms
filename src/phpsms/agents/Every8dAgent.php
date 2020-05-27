<?php

namespace mikecai\PhpSms;

/**
 * Class SendCloudAgent
 *
 * @property string $appId
 * @property string $appKey
 */
class Every8dAgent extends Agent implements ContentSms,BatchStatus
{

    protected $resultArr = [
        '0' => '訊息已成功傳送給電信端，電信基地台與受話⽅⼿機溝通中',
        '100' => '成功送達⼿機',
        '101' => '電信端回覆因受話⽅⼿機關機/訊號不良/簡訊功能異常等原因，該訊息無法送達受話⽅⼿機',
        '102' => '電信端回覆因網路系統/基地台設備異常等原因，該訊息無法送達受話⽅⼿機',
        '103' => '電信端回覆因受話⽅⼿機⾨號為空號或停⽤中，該訊息無法送達受話⽅⼿機',
        '104' => '電信端回覆因受話⽅⼿機規格不符(⼭寨機或海外機)，該訊息無法送達受話⽅⼿機',
        '105' => '電信端回覆因受話⽅⼿機設備問題/⼿機出現未預期錯誤等原因，該訊息無法送達受話⽅⼿機',
        '106' => '電信端回覆因系統傳送時發⽣非預期錯誤，該訊息無法送達受話⽅⼿機',
        '107' => '電信端回覆因受話⽅⼿機關機/訊號不良/簡訊功能異常等原因，該訊息無法送達受話⽅⼿機',
        '300' => '預約簡訊',
        '303' => '取消預約',
        '500' => '表該⾨號為國際⾨號，請⾄系統設定開啟國際簡訊發送功能',
        '-1' => '參數錯誤，該訊息傳送失敗',
        '-2' => 'API 帳號或密碼錯誤，該訊息傳送失敗',
        '-3' => '受話⽅⼿機號碼錯誤或是簡訊⿊名單，該訊息傳送失敗',
        '-4' => '訊息預計發送時間已逾期 24 ⼩時以上，該訊息傳送失敗',
        '-5' => 'Short Message 內容⻑度超過限制，該訊息傳送失敗',
        '-6' => 'DT(預計發送時間)格式錯誤，該訊息傳送失敗',
        '-99'  => '主機端發⽣不明錯誤，請與廠商窗⼝聯繫。',
    ];

    public function sendContentSms($to, $content)
    {
        $url = 'http://api.every8d.com/API21/HTTP/sendSMS.ashx';
        $params = [
            'UID' => $this->username,
            'PWD' => $this->password,
            'SB' => "",
            'MSG' => $content,
            'DEST' => $to
        ];
        $result = $this->curlGet($url, $params);
        $this->setResult($result);
    }

    public function requestBatchSatus($batchId){

        $url = 'http://api.every8d.com/API21/HTTP/getDeliveryStatus.ashx';

        $params = $this->params([
            'UID' => $this->username,
            'PWD' => $this->password,
            // 'BID' => $batchId,
            'PNO' => ""
        ]);
        $result = $this->curlGet($url, $params);
        
        $this->setBatchResult($result);
    }

    protected function setResult($result)
    {

        $result_ary = explode(",", $result["response"]);
        if (isset($result_ary[2])) {
            // $result = $result['response'];
            $this->result(Agent::INFO, ([
                                         'BATCH_ID' => $result_ary[4]
                                                ]));
            $this->result(Agent::SUCCESS, isset($result_ary[4]));
            $this->result(Agent::CODE, 0);
        } else {
            $this->result(Agent::INFO, 'request failed - '.$result_ary[1]);
            $this->result(Agent::CODE, $result_ary[0]);
        }
    }

    protected function setBatchResult($result){
        
        // $result_ary = explode("", $result["response"]);
        $result_ary = preg_split('/\s+/', $result["response"]);
        // exit(var_dump($result));
        // exit(var_dump($result_ary));

        // $result_ary = $result_status["value"];

        if (isset($result_ary[5]) && ($result_ary[5]=="0" || $result_ary[5]=="100")) {

            $status_code = $result_ary[5];
            $this->result(Agent::INFO, ([
                'Status_Code' => $status_code,
                'Status_Message' => (array_key_exists($status_code,$this->resultArr))?($this->resultArr[$status_code]):"UNKONW"
            ]));
            $this->result(Agent::SUCCESS, isset($result_ary[1]));
            $this->result(Agent::STATUS, "Send");
        } else {
            
            $this->result(Agent::INFO, ([
                'Status' => "Query Error",
                'Status_Message' => 'request failed',
                'log' => $result_ary
            ]));
            $this->result(Agent::STATUS, "UNKONW");
        }
    }

   
}
