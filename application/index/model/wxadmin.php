<?php


namespace app\index\model;


use IcoTrace\IcoTrace;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

class wxadmin extends Model
{
    protected $table = 'wxadmin';
    protected $pk = 'id';

    /**
     * @param $companyid
     * @param $wxid
     * @return array|int|\PDOStatement|string|Model
     * 0, 可以注册；1，已经被注册，2，注册人是本微信
     */
    static public function bindingstatus($companyid, $wxid)
    {
        $ret = 1;
        try {
            IcoTrace::TraceMsgToDb('wxadmin.bindingstatus ==> Being and companyid = '. $companyid);
            $map['companyid'] = $companyid;
            $map['enable'] = 1;
            $qret = wxadmin::where($map)->findOrEmpty();
            if( $qret->isEmpty())
            {
                $ret = 0;
            }
            else
            {
                IcoTrace::TraceMsgToDb('wxadmin.bindingstatus ==> After query = '. json_encode($ret));
                if( $qret->wxid == $wxid )
                {
                    $ret = 2;
                }
            }
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('wxadmin.bindingstatus ==> exception = '. $e->getMessage());
        }

        return $ret;
    }

    static public function wxidCompanyRegStatus($wxid)
    {
        $ret = 2;
        try {
            IcoTrace::TraceMsgToDb('wxadmin.bindingstatus ==> Being and companyid = '. $wxid);
            $map['wxid'] = $wxid;
            $map['enable'] = 1;
            $qret = wxadmin::where($map)->findOrEmpty();
            if( $qret->isEmpty())
            {
                //没有这个微信号的管理员记录，可以继续注册
                $ret = 0;
            }
            else
            {
                //有这个微信账号的的管理员注册记录，并且是激活状态，查出注册的公司id，设置session，然后跳转
                IcoTrace::TraceMsgToDb('wxadmin.bindingstatus ==> After query = '. json_encode($ret));
                if( $qret->wxid == $wxid )
                {
                    $ret = 1;
                }
            }
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('wxadmin.bindingstatus ==> exception = '. $e->getMessage());
        }

        return $ret;
    }

    public function regprocess( $companyid, $wxid)
    {
        try
        {
            IcoTrace::TraceMsgToDb('wxadmin.regprocess ==> Begin and compandid, wxid = '.$companyid.'  '.$wxid);
            $val = ['companyid'=>$companyid,'wxid'=>$wxid,'enable'=>'1','regtime'=>date('Y-m-d h:i:s', time())];

            $ret = self::create($val);
            if($ret->isEmpty())
            {
                return false;
            }
            else
            {
                return true;
            }
            //return $ret;
            return false;
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('wxadmin.regprocess ==> exception = '. $e->getMessage());
            return false;
        }
    }

    static public function getCompanyIdByWxid($wxid)
    {
        $ret = 'none';
        try {
            IcoTrace::TraceMsgToDb('wxadmin.getCompanyIdByWxid ==> Begin');
            $qret = wxadmin::where('wxid',$wxid)->findOrEmpty();
            //返回公司id
            if( $qret->isEmpty() == false )
            {
                //有这个记录，返回公司id
                $ret = $qret->companyid;
                IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> 找到这个微信号作为管理员的公司id = '.$ret);
            }
            IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> End');
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('poinfo.queryComapnyByWxid ==> exception'.$e->getMessage());
        }

        return $ret;
    }
}