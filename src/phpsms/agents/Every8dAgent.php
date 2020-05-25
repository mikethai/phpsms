<?php

namespace mikecai\PhpSms;

/**
 * Class SendCloudAgent
 *
 * @property string $appId
 * @property string $appKey
 */
class Every8dAgent extends Agent implements ContentSms
{

    protected $resultArr = [
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

    protected function setResult($result)
    {

        $result_ary = explode(",", $result["response"]);
        if ($result_ary) {
            $result = $result['response'];
            $this->result(Agent::INFO, (['CREDIT' => $result_ary[0], 
                                                    'SENDED' => $result_ary[1], 
                                                    'COST' => $result_ary[2], 
                                                    'UNSEND' => $result_ary[3], 
                                                    'BATCH_ID' => $result_ary[4]
                                                ]));
            $this->result(Agent::SUCCESS, isset($result_ary[4]));
            $this->result(Agent::CODE, 0);
        } else {
            $this->result(Agent::INFO, 'request failed');
        }
    }

   
}
