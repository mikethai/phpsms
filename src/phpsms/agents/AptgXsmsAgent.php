<?php

namespace mikecai\PhpSms;

use Sabre;


/**
 * Class SendCloudAgent
 *
 * @property string $appId
 * @property string $appKey
 */
class AptgXsmsAgent extends Agent implements ContentSms
{

    protected $resultArr = [
        '16777217'  => '認證失敗(用戶/企業代表號不存在或密碼錯誤)',
        '16777218'  => '來源 IP 未授權使用',
        '16777219'  => '指定帳號不存在(或空白)',
        '33554433'  => '額度不足(或合約已開通未儲值)',
        '33554434'  => '連線數超過上限',
        '33554435'  => '回撥門號未申請授權使用',
        '33554436'  => '國際簡訊未授權使用',
        '33554437'  => '網內簡訊未授權使用',
        '33554438'  => '網外簡訊未授權使用',
        '33554439'  => '合約已終止，停止使用',
        '33554440'  => '帳號已終止，停止使用',
        '33554441'  => '帳號已鎖定(密碼錯誤超過三次以上)',
        '33554448'  => '未授權此功能',
        '33554449'  => '『他網國際漫遊簡訊』客戶不得發送 Unicode 字碼：【A,B,C,…】',
        '50331649'  => '參數不足',
        '50331650'  => '交易代號不存在',
        '50331651'  => '門號格式錯誤',
        '50331652'  => '日期格式錯誤',
        '50331653'  => '其他格式錯誤',
        '50331654'  => '接收門號數量超過上限',
        '50331655'  => '簡訊本文含有非法關鍵字',
        '50331656'  => '簡訊長度過長',
        '50331657'  => '長簡訊則數已超過上限',

        '50331664'  => '簡訊主旨不存在(或空白)',
        '50331665'  => 'API 簡訊發送啟始時間(StartDateTime)需晚於 API 呼叫時間',
        '50331666'  => 'API 簡訊發送結束時間(StopDateTime)需晚於發送起始時間以及起始時間+24 小時之內',
        '50331667'  => '簡訊已全部送出，無法異動(刪除簡訊失敗/預約簡訊本文修改失敗)',
        '50331668'  => '變更密碼失敗(長度不足或過長)',
        '50331669'  => '異動點數長度不符',
        '50331670'  => '異動點數格式錯誤',
        '50331671'  => '活動代號不存在',
        '50331672'  => '查無任何活動資料',
        '51450129'  => '系統維護時段，暫停使用',
        '286331153'  => '例外錯誤發生',
    ];

    public function sendContentSms($to, $content)
    {
        $url = 'https://xsms.aptg.com.tw/XSMSAP/api/APIRTFastHttpRequest';
        $params = [
            'UID' => $this->username,
            'UPASS' => $this->password,
            'Cotent' => $content,
            'MDN' => $to
        ];
        $params = array();
        $result = $this->curlPOST($url, $params);
        $this->setResult($result);
    }

    protected function setResult($result)
    {
        $reader = new Sabre\Xml\Reader();
        $reader->elementMap = [
            '{}Response' => 
            function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader, '');
            }
        ];
        $reader->xml($result["response"]);
        $result_status =  $reader->parse();
        $result_ary = $result_status["value"];


        if ($result_ary["TaskID"]) {
            // $result = $result['response'];
            $this->result(Agent::INFO, ([
                                         'BATCH_ID' => $result_ary["TaskID"]
                                                ]));
            $this->result(Agent::SUCCESS, isset($result_ary[4]));
            $this->result(Agent::CODE, 0);
        } else {
            $this->result(Agent::INFO, 'request failed - '.$result_ary["Reason"]);
        }
    }

   
}
