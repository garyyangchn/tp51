<?php


namespace app\index\model;


use IcoTrace\IcoTrace;
use think\Exception;
use think\Model;

class poinfo extends Model
{
    protected $table = 'poinfo';
    protected $pk = 'id';

    static public function getallrecord()
    {
        return poinfo::all();
    }
    static public function querycp($cpid, $regcode, $phone, $email)
    {
        return poinfo::where(['companyid'=>$cpid,'regcode'=>$regcode,'phone'=>$phone,'email'=>$email])
            ->findOrEmpty();
    }

    static public function querycpbyajax( $arr )
    {
        try {
            IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> Begin');
            $ret = poinfo::where($arr)->findOrEmpty();
            //$ret = poinfo::querycp($arr['companyid'], $arr['regcode'], $arr['phone'], $arr['email']);
            //$ret = poinfo::where(['companyid'=>$arr['companyid'],'regcode'=>$arr['regcode'],'phone'=>$arr['phone'],'email'=>$arr['email']])
            //    ->findOrEmpty();
            IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> End');
            return $ret;
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> exception'.$e->getMessage());
        }

        return 'error';
    }

    static public function queryCompanyInfoByID($companyid)
    {
        IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> Begin');
        $ret = poinfo::where('companyid',$companyid)->findOrEmpty();
        //返回公司合同信息
        IcoTrace::TraceMsgToDb('poinfo.querycpbyajax ==> End');

        return $ret;
    }
}