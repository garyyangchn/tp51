<?php


namespace app\index\controller;

//use think\Controller;
use app\index\model\wxuser;
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

        //判断用户当前注册状态，0，未注册；1，等待输入注册码；9，已完成
        $retStep = wxuser::getRegStep($openid);
        if( $retStep == 9 )
        {
            //用户已经注册完成，提示已注册
            return $this->fetch('finished');
        }

        if( $retStep == 1 )
        {
            //跳转至输入验证码页面
            $this->assign('openid', $openid);
            return $this->fetch('captcha');
        }

        if( $retStep != 0 )
        {
            //除去前面两个，应该为0，不为零意味着出错了，跳转至错误页面
            return $this->fetch('icoerror');
        }

        //到这里意味着用户还没有注册，启动注册流程，跳转至注册页面
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

    public function testfinished()
    {
        return $this->fetch('finished');
    }
}