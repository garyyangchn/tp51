<?php


namespace app\index\controller;

use EasyWeChat\Factory;
use think\facade\Config;
use IcoTrace\IcoTrace;

class Wxmenu
{
    public function index()
    {
        echo 'index in Wxmenu controller';
        echo '<hr>';
        $options = Config::get('wechat.');

        $app = Factory::officialAccount($options);
        $list = $app->menu->list();
        dump($list);
        //dump(Config::get('database.'));
        IcoTrace::TraceMsgToDb( json_encode($list) );
    }

    public function setmenu()
    {
        $buttons = [
            [
                "type" => "view",
                "name" => "注册",
                "url"  => "http://www.alarmlab.net/index/wxauth/reguser"
            ],
            [
                "type" => "view",
                "name" => "清除会话",
                "url"  => "http://www.alarmlab.net/index/wxauth/clear"
            ],

            [
                "name"       => "其他",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "公司注册",
                        "url"  => "http://www.alarmlab.net/index/wxauth/regcompany"
                    ],
                    [
                        "type" => "view",
                        "name" => "关于我们",
                        "url"  => "http://www.iconics.com.cn/"
                    ],
                ],
            ],
        ];

        $options = Config::get('wechat.');
        $app = Factory::officialAccount($options);
        $ret = $app->menu->create($buttons);
        dump($ret);
    }
}