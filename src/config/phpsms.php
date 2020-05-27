<?php

return [
    /*
     * The scheme information
     * -------------------------------------------------------------------
     *
     * The key-value paris: {name} => {value}
     *
     * Examples:
     * 'Log' => '10 backup'
     * 'SmsBao' => '100'
     * 'CustomAgent' => [
     *     '5 backup',
     *     'agentClass' => '/Namespace/ClassName'
     * ]
     *
     * Supported agents:
     * 'Log', 'YunPian', 'YunTongXun', 'SubMail', 'Luosimao',
     * 'Ucpaas', 'JuHe', 'Alidayu', 'SendCloud', 'SmsBao',
     * 'Qcloud', 'Aliyun'
     *
     */
    'scheme' => [
        'Log',
    ],

    /*
     * The configuration
     * -------------------------------------------------------------------
     *
     * Expected the name of agent to be a string.
     *
     */
    'agents' => [

        /*
         * -----------------------------------
         * Every8D
         * -----------------------------------
         * website:http://global.every8d.com.tw/#price
         * support content sms.
         */

        'Every8d' => [
            // 手機門號
            'mdn_number' => 'your_mdm',
            //帳號
            'username'  => 'your_username',
            //密碼
            'password'  => 'your_password',
        ],

        /*
         * -----------------------------------
         * AptgXsms 亞太企業簡訊- 舊版
         * -----------------------------------
         * http://xsms.aptg.com.tw/XSMSAP/userlogin.zul#
         * support content sms.
         */

        'AptgXsms' => [
            // 手機門號
            'mdn_number' => 'your_mdm',
            //帳號
            'username'  => 'your_username',
            //密碼
            'password'  => 'your_password',
        ],


        
    ],
];
