<?php
return [
    'debug' => false,

    'app_id' => 'wx9e74d0086d0d9e3b',         // AppID
    'secret' => 'e68c18938f8375dec4f19cc704f90402',     // AppSecret
    'token' => 'AlarmToken',          // Token
    'response_type' => 'array',

    'log' => [
        'level' => 'debug',
        'permission' => 0777,
        'file' => '/tmp/easywechat.log',
    ],

    'oauth' => [
        'scopes' => ['snsapi_userinfo'],
        'callback' => '/index/wxauth/oauth_callback',
    ],

    'guzzle' => [
        'timeout' => 3.0, // 超时时间（秒）
        'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
    ],
];