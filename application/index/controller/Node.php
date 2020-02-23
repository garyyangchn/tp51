<?php


namespace app\index\controller;
use EasyWeChat\Kernel\Messages\Message;
use think\facade\Config;
use think\Controller;
use EasyWeChat\Factory;

class Node extends Controller
{
    public function index()
    {
        //echo 'index in Node controller';
        dump(env('yg.hostname'));
        echo '<br>';
        //dump(Config::get('database.'));
        echo '<br>';
        dump(Config::get('wechat.'));
        //    先初始化微信
    }

    public function svr()
    {
        $options = Config::get('wechat.');

        $app = Factory::officialAccount($options);
        //dump($app);

        $server = $app->server;

        /*
        $app->server->push(function ($message)
        {
            switch ($message['MsgType'])
            {
                case 'event':
                    return '收到事件消息' . json_encode($message);
                    break;
                case 'text':
                    return '收到文字消息' . json_encode($message);
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });
        */

        $response = $app->server->serve();
        $response->send();
    }
}