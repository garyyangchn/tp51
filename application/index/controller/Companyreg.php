<?php


namespace app\index\controller;


use app\index\model\msgcount;
use app\index\model\poinfo;
use app\index\model\wxadmin;
use IcoTrace\IcoTrace;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Session;
use think\facade\Request;

class Companyreg extends Controller
{
    public function index()
    {
        echo 'index in Companyreg controller';
    }

    public function regsuccess()
    {
        try
        {
            IcoTrace::TraceMsgToDb('Companyreg.regsuccess ==> Begin');
            $user = Session::get('wechat_user');
            $this->assign('name', $user['nickname']);
            $this->assign('openid', $user['id']);
            $this->assign('headimg', $user['avatar']);

            $cpy = Session::get('company_info');
            $this->assign('companyid', $cpy['companyid']);
            $this->assign('cpname', $cpy['company']);
            $this->assign('usercount', $cpy['userlimit']);

            $this->assign('currentcount', 0);
            $this->assign('starttime', $cpy['startdate']);
            //计算结束日期
            $validperiod = $cpy['validperiod'];
            $enddate = date('Y-m-d', strtotime ('+'.$validperiod.'day', strtotime($cpy['startdate'])));
            IcoTrace::TraceMsgToDb('Companyreg.regsuccess ==> end date = '. $enddate);
            $this->assign('endtime', $enddate);

            $this->assign('msglimit', $cpy['msglimit']);
            $msgcount = msgcount::getcurrentcount($cpy['companyid']);
            $this->assign('msgsend', $msgcount);

            return $this->view->fetch();
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('Companyreg.regsuccess ==> exception = '.$e->getMessage());
        }

        return $this->view->fetch('icoerror');
    }

    public function regcommit()
    {
        try
        {
            IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Begin');
            //判断是否是由ajax发过来的请求
            if( Request::isAjax())
            {
                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Got Ajax query');
                //获取用户输入的参数
                $postdata = Request::post('works');
                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Got user input '.json_encode($postdata));
                if( is_null($postdata))
                {
                    IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Got user input and works is empty');
                    return 'error';
                }

                $jsObj = json_decode($postdata,true);
                $arr = $jsObj[0];
                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Got user input and works sub item = '.$arr['companyid']);

                $map['companyid'] = $arr['companyid'];
                $map['regcode'] = $arr['regcode'];
                $map['phone'] = $arr['phone'];
                $map['email'] = $arr['email'];
                $qret = poinfo::querycpbyajax($map);
                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> After querycpbyajax');
                if( gettype($qret) == 'string')
                {
                    IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Query poinfo and got no result');
                    return 'error';
                }

                if( $qret->isEmpty())
                {
                    IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Query poinfo and got empty result');
                    return 'error';
                }

                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Query poinfo and got company name = '.$qret->company);
                //设置公司信息session
                Session::set('company_info', $qret);
                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> After set company_info session');

                //调用注册程序，首先获取微信账号的信息
                $user = Session::get('wechat_user');

                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Begin reg process before startTrans');
                //开启注册事务
                $obj = new wxadmin();
                $obj->startTrans();

                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> Begin reg process after startTrans');
                $regret = $obj->regprocess($arr['companyid'], $user['id']);
                if( $regret == false )
                {
                    //插入注册数据失败
                    IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> regprocess failed!');
                    $obj->rollback();
                    return 'error';
                }

                $msgcountObj = new msgcount();
                $msgret = $msgcountObj->addnewrecord($arr['companyid']);
                if($msgret == false)
                {
                    //更新msglog表失败，回滚事务
                    IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> addnewrecord 失败，回滚事务');
                    $obj->rollback();;
                    return 'error';
                }

                //更新msglog成功，提交事务
                $obj->commit();

                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> regprocess successed!');
                return 'ok';
            }
            else
            {
                IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> It iss not a Ajax call');
            }
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('Companyreg.regcommit ==> exception = '.$e->getMessage());
        }
        //return json_encode('regcommit ok');
        return 'error';
    }

    //用户点击公司注册按钮，经过oauth认证之后，进入本function
    public function regcompany()
    {
        IcoTrace::TraceMsgToDB('Companyreg.regcompany ==> Before get name and openid');
        $user=Session::get('wechat_user');
        if(empty($user))
        {
            return $this->fetch('icoerror');
        }

        IcoTrace::TraceMsgToDb('Companyreg.regcompany ==> Get user from cookie '.json_encode($user));
        $name = $user['nickname'];
        $openid = $user['id'];
        $headimg = $user['avatar'];

        //首先检查这个微信用户是否已经作为管理员注册过，如果注册过，查出公司id，及该公司的信息，跳转至详细信息页面
        $companyid = wxadmin::getCompanyIdByWxid($openid);
        if( $companyid != 'none')
        {
            //确实已经注册过了，检索公司的合同信息，并设置session
            IcoTrace::TraceMsgToDb('Companyreg.regcompany ==> 找到了以这个微信为管理员的公司id = '.$companyid);
            $qret = poinfo::queryCompanyInfoByID($companyid);
            if( $qret->isEmpty())
            {
                IcoTrace::TraceMsgToDb('Companyreg.regcompany ==> 查询出这个公司的信息为空，跳转error页面');
                //公司信息是空的，出错
                return $this->fetch('icoerror');
            }

            IcoTrace::TraceMsgToDb('Companyreg.regcompany ==> 查询出了这个公司的信息 = '.json_encode($qret));
            Session::set('company_info',$qret);
            //跳转至详细信息页面
            header('Location:/index/companyreg/regsuccess');
            exit();
        }

        //否则正常打开注册页面
        IcoTrace::TraceMsgToDb('Companyreg.regcompany ==>该微信号还没有注册过任何公司的管理员 name, openid, headimg = '.$name.'  '.$openid.'  '.$headimg);

        $this->assign('name',$name);
        $this->assign('openid',$openid);
        $this->assign('headimg', $headimg);
        return $this->fetch();
    }
}