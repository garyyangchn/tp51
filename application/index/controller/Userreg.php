<?php


namespace app\index\controller;

//use think\Controller;
use app\index\model\poinfo;
use app\index\model\wxadmin;
use app\index\model\wxuser;
use IcoTrace\IcoTrace;
use think\Exception;
use think\facade\Request;
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
        IcoTrace::TraceMsgToDB('Userreg.regpage ==> Before get name and openid');
        $user=Session::get('wechat_user');
        if(empty($user))
        {
            return $this->fetch('icoerror');
        }
        IcoTrace::TraceMsgToDb('Userreg.regpage ==> Get user from cookie '.json_encode($user));
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
        IcoTrace::TraceMsgToDb('Userreg.regpage ==> name, openid, headimg = '.$name.'  '.$openid.'  '.$headimg);

        $this->assign('name',$name);
        $this->assign('openid',$openid);
        $this->assign('headimg', $headimg);
        return $this->fetch();
    }

    public function testfinished()
    {
        return $this->fetch('finished');
    }

    public function oncommit()
    {
        try {
            IcoTrace::TraceMsgToDb('Userreg.oncommit ==> Begin');
            //判断是否是由ajax发过来的请求
            if( Request::isAjax()) {
                IcoTrace::TraceMsgToDb('Userreg.oncommit ==> Got Ajax query');
                //获取用户输入的参数
                $postdata = Request::post('works');
                IcoTrace::TraceMsgToDb('Userreg.oncommit ==> Got user input ' . json_encode($postdata));
                if (is_null($postdata)) {
                    IcoTrace::TraceMsgToDb('Userreg.oncommit ==> Got user input and works is empty');
                    return 'error';
                }

                $jsObj = json_decode($postdata, true);
                $arr = $jsObj[0];
                IcoTrace::TraceMsgToDb('Userreg.oncommit ==> Got user input and works sub item = ' . $arr['companyid']);

                //查询wxadmin表，看看是否已完成公司注册
                $mapForWxadmin = ['companyid'=>$arr['companyid'],'enable'=>1];
                $retWxadmin = wxadmin::where($mapForWxadmin)->findOrEmpty();
                if( $retWxadmin->isEmpty())
                {
                    //该公司没有注册
                    IcoTrace::TraceMsgToDb('Userreg.oncommit ==> No valid contract for this company id ');
                    return 'error';
                }

                //准备插入wxuser表
                $map['companyid'] = $arr['companyid'];
                $user = Session::get('wechat_user');
                $map['wxid'] = $user['id'];
                $map['name']= $arr['username'];
                $map['regcode'] = $code = rand(100000, 999999);;
                $map['phone'] = $arr['cellnum'];
                $map['email'] = $arr['email'];
                $map['regtime'] = date('Y-m-d H:i:s', time());
                $map['active'] = 1;
                $map['step'] = 1;

                //执行wxuser表的插入
                $qret = wxuser::create($map);
                if($qret->isEmpty())
                {
                    //return $this->fetch('icoerror');
                    return 'error';
                }

                //插入成功，跳转至验证码输入页面
                return 'captcha';
                //$this->assign('openid', $user['id']);
                //return $this->fetch('captcha');
            }
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('Userreg.oncommit ==> exception = '.$e->getMessage());
        }

    }

    public function captcha()
    {
        $user = Session::get('wechat_user');
        $this->assign('openid', $user['id']);
        return $this->fetch('captcha');
    }

    protected function genRegCode()
    {
        $str = substr(time(), 0, 6);//md5加密，time()当前时间戳
        return $str;
    }
}