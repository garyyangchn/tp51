<?php


namespace app\index\controller;

use EasyWeChat\Factory;
use think\facade\Config;
//use think\Session;
use think\facade\Session;
use IcoTrace\IcoTrace;

class Wxauth
{
    public function sessiontest()
    {
        echo 'sessiontest in wxauth';
        Session::set('session_test', '/index/wxauth/success');
        echo '<br>';
        $ret = Session::get('session_test');
        dump($ret);
    }

    public function index()
    {
        //echo 'index in Wxauth controller';
        header('Location:http://www.alarmlab.net/index/wxauth/already');
        exit();
    }

    public function success()
    {
        echo 'oauth successfully';
    }

    public function already()
    {
        echo 'get auth already';
    }

    public function clear()
    {
        //Session::clear('wechat_user');
        $ret = Session::get('wechat_user');
        dump($ret);
        echo '<hr>';
        $ret2 = Session::get('company_info');
        dump($ret2);
        session(null);
        echo 'clear session finished';
    }

    public function icoerror()
    {
        return view();
    }

    public function reguser()
    {
        IcoTrace::TraceMsgToDb('reguser => begin in reguser');
        $options = Config::get('wechat.');
        $app = Factory::officialAccount($options);

        $oauth=$app->oauth;
        if (empty(Session::get('wechat_user'))){
            IcoTrace::TraceMsgToDb('reguser => can not find wechat_user in cookie, do oauth and redirect to userreg.regpage');
            Session::set('target_url', '/index/userreg/regpage');
            return $oauth->redirect();
        }

        $user=Session::get('wechat_user');
        IcoTrace::TraceMsgToDb('reguser => find wechat_user in cookie, directly to userreg.regpage '. json_encode($user));
        header('Location:/index/userreg/regpage');
        exit();
    }

    public function regcompany()
    {
        IcoTrace::TraceMsgToDb('regcompany => begin in regcompany');
        $options = Config::get('wechat.');
        $app = Factory::officialAccount($options);

        $oauth=$app->oauth;
        if (empty(Session::get('wechat_user'))){
            IcoTrace::TraceMsgToDb('regcompany => can not find wechat_user in cookie, do oauth and redirect to companyrreg.regcompany');
            Session::set('target_url', '/index/companyreg/regcompany');
            return $oauth->redirect();
        }

        $user=Session::get('wechat_user');
        IcoTrace::TraceMsgToDb('regcompany => find wechat_user in cookie, directly to companyreg.regcompany '. json_encode($user));
        header('Location:/index/companyreg/regcompany');
        exit();
    }

    public function getuser()
    {
        IcoTrace::TraceMsgToDb('getuser => begin in getuser');
        $options = Config::get('wechat.');
        $app = Factory::officialAccount($options);

        $oauth=$app->oauth;
        if (empty(Session::get('wechat_user'))){
            IcoTrace::TraceMsgToDb('getuser => can not find wechat_user in cookie');
            Session::set('target_url', '/index/wxauth/success');
            return $oauth->redirect();
        }

        $user=Session::get('wechat_user');
        IcoTrace::TraceMsgToDb('getuser => find wechat_user in cookie, directly to already '. json_encode($user));
        header('Location:/index/wxauth/already');
        exit();
    }

    public function oauth_callback()
    {
        IcoTrace::TraceMsgToDb('oauth_callback => begin in oauth_callback');
        $options = Config::get('wechat.');
        $app = Factory::officialAccount($options);
        $auth = $app->oauth;
        $user = $auth->user();
        $user->getName();
        $user->getId();
        IcoTrace::TraceMsgToDb('oauth_callback => get user from wx ' . json_encode($user));
        Session::set('wechat_user',$user);
        $targetUrl =empty(Session::get('target_url'))?'/index/wxauth/icoerror':Session::get('target_url');
        IcoTrace::TraceMsgToDb('oauth_callback => redirect to ' . json_encode($targetUrl));
        header('Location:'. $targetUrl);
        exit();
    }
}