<?php
/**
 * Created by VIM.
 * Author:YQ
 * Date:2020/03/03 18:30:23
 */

return [
    'app_id' => env('ALIYUN_API_APPID', ''), // 阿里云api授权应用的AppKey
    'app_secret' => env('ALIYUN_API_APP_SECRET', ''), // 阿里云api授权应用的AppSecret
    'hosts' => [ // 阿里云的接口域名
        'online' => 'http://api.public.com',
    ],
];
