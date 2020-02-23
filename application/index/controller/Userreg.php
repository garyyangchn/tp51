<?php


namespace app\index\controller;

//use think\Controller;
use IcoTrace\IcoTrace;
use think\facade\Session;
use think\View;
use think\Controller;

class Userreg extends Controller
{
    public function index()
    {
        echo 'index in userreg controller';
    }

    public function errort(View $view)
    {
        //return view('error');
        //$view = new View();
        return $view->fetch();
    }

    public function icoerror()
    {
        return $this->fetch();
    }

    public function regpage()
    {
        IcoTrace::TraceMsgToDB('regpage ==> Before get name and openid');
        $user=Session::get('wechat_user');
        if(empty($user))
        {
            return $this->fetch('icoerror');
        }
        IcoTrace::TraceMsgToDb('regpage ==> Get user from cookie '.json_encode($user));
        $name = $user['nickname'];
        $openid = $user['id'];
        $headimg = $user['avatar'];

        IcoTrace::TraceMsgToDb('regpage ==> name, openid, headimg = '.$name.'  '.$openid.'  '.$headimg);

        $this->assign('name',$name);
        $this->assign('openid',$openid);
        $this->assign('headimg', $headimg);
        return $this->fetch();

        /*
        //首先查询这个用户是否已经在注册过程中
        TraceToDB::TraceMsgToDB('In regpage Before queryregisterstatus, openid = '.$openid);
        $retReg = $this->queryregisterstatus($openid);
        TraceToDB::TraceMsgToDB('In regpage after queryregisterstatus, result = '.$retReg);
        if( $retReg == 'captcha')
        {
            TraceToDB::TraceMsgToDB('userreg regpage in captcha branch ');
            $this->assign('openid',$openid);
            return $this->fetch('captcha');
        }

        if( $retReg == 'exist' )
        {
            return $this->fetch('finished');
        }

        if( $retReg == 'new' )
        {
            $this->assign('name',$name);
            $this->assign('openid',$openid);
            $this->assign('headimg', $headimg);
            return $this->fetch();
        }
        */

        //return $this->fetch();
    }
}