<?php


namespace app\index\model;


use IcoTrace\IcoTrace;
use think\Exception;
use think\Model;

class msgcount extends Model
{
    protected $table = 'msgcount';
    protected $pk = 'id';

    public function addnewrecord($companyid)
    {
        try {
            //如果公司已经注册过，再次注册的时候，先把之前的记录active字段清0
            self::where('companyid',$companyid)->setField('active',0);

            //更新完成插入一条新记录
            $data = ['companyid' => $companyid, 'active'=>1, 'curmsgcount'=>0];
            $ret = $this->save($data);
            IcoTrace::TraceMsgToDb(json_encode($ret));
            return $ret;
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('msgcount.addnewrecord ==> exception '. $e->getMessage());
            return false;
        }
    }

    static public function getcurrentcount($companyid)
    {
        $map = ['companyid'=>$companyid,'active'=>'1'];
        IcoTrace::TraceMsgToDb('msgcount.getcurrentcount ==>Begin');
        $ret = msgcount::where($map)->findOrEmpty();
        IcoTrace::TraceMsgToDb('msgcount.getcurrentcount ==> After findOrEnpty ');
        if($ret->isEmpty())
        {
            IcoTrace::TraceMsgToDb('msgcount.getcurrentcount ==> is empty ');
            return 0;
        }
        else
        {
            IcoTrace::TraceMsgToDb('msgcount.getcurrentcount ==> get current msg count '. json_encode($ret->curmsgcount));
            return $ret->curmsgcount;
        }
    }

    public function addnewrecord2($companyid, $msglimit)
    {
        try {
            $data = ['companyid' => $companyid, 'active'=>1, 'msglimit'=>$msglimit];
            $ret = self::create($data);
            IcoTrace::TraceMsgToDb(json_encode($ret));
            echo json_encode(json_encode($ret));
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('msgcount.addnewrecord ==> exception '. $e->getMessage());
            return false;
        }
    }

    public function testupdate($companyid)
    {
        try {
            $ret = self::where('companyid',$companyid)->setField('active',0);
            return $ret;
        }
        catch (Exception $e)
        {
            IcoTrace::TraceMsgToDb('msgcount.testupdate ==> exception '. $e->getMessage());
        }
    }
}