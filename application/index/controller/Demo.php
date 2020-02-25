<?php


namespace app\index\controller;


use app\index\model\msgcount;
use app\index\model\poinfo;
use app\index\model\wxadmin;
use IcoTrace\IcoTrace;

class Demo
{
    public function index()
    {
        return 'index in demo controller';
    }
    public function getName($name='yang')
    {
        return 'Hello ' . $name;
    }

    public function getallrecord()
    {
        return json_encode(poinfo::getallrecord());
    }

    public function getone()
    {
        $ret = poinfo::querycp('1000111','1352468888','13910272025','gary.yang@iconics.com.cn');
        return json_encode($ret);
    }

    public function bindingstatus()
    {
        $ret = wxadmin::bindingstatus('100011','xxddeddefff');

        return $ret;
    }

    public function querybyarr()
    {
        $arr = ['companyid'=>'100011','regcode'=>'1352468888','phone'=>'13910272025','email'=>'gary.yang@iconics.com.cn', 'wxid'=>'wxid'];
        $ret = poinfo::querycpbyajax($arr);
        echo gettype($ret);
        echo '<br>';
        //echo gettype($ret);

        if( gettype($ret) == 'string' )
        {
            return 'return from is_null';
        }

        if( $ret->isEmpty() )
        {
            return 'no result';
        }
        else
        {
            return json_encode($ret);
        }

    }

    public function regprocess()
    {
        return wxadmin::regprocess('100011','xxddeefff');
    }

    public function testtrans()
    {
        echo 'being of test trans';
        IcoTrace::TraceMsgToDb('Demo.testtrans ==> Begin');
        $obj = new IcoTrace();
        IcoTrace::TraceMsgToDb('Demo.testtrans ==> After new');
        IcoTrace::TraceMsgToDb3('Demo.testtrans ==> After new');

        $obj->startTrans();

        IcoTrace::TraceMsgToDb('Demo.testtrans ==> After startTrans');
        IcoTrace::TraceMsgToDb3('Demo.testtrans ==> 333 After startTrans');

        $ret = $obj->TraceMsgToDb2("Demo.testtrans ==> using trans call Db2");

        IcoTrace::TraceMsgToDb('Demo.testtrans ==> after TraceMsgToDb2 execute');
        IcoTrace::TraceMsgToDb3('Demo.testtrans ==> 333 after TraceMsgToDb2 execute');
        $obj2 = new msgcount();
        $obj2->addnewrecord2('100011', 23);
        //IcoTrace::TraceMsgToDb('does not use trans');
        $wxobj = new wxadmin();
        $wxret = $wxobj->regprocess('100011','first1111111111111111');
        if( $wxret == false )
        {
            echo 'rollback';
            $obj->TraceMsgToDb2('Demo.testtrans ==> regprocess rollback');
            IcoTrace::TraceMsgToDb3('Demo.testtrans ==> 333 regprocess rollback');
            $obj->rollback();
        }
        else {
            echo 'commit';
            $obj->TraceMsgToDb2('Demo.testtrans ==> regprocess commit');
            IcoTrace::TraceMsgToDb3('Demo.testtrans ==> 333 regprocess commit');
            $obj->commit();
        }
        echo 'end of trans';
        echo '<br>';
        return json_encode($ret);
    }

    public function addnewrecord()
    {
        $obj = new msgcount();
        $obj->startTrans();
        $ret = $obj->addnewrecord('100012');
        //$obj->rollback();
        $obj->commit();
        return json_encode($ret);
    }

    public function testupdate()
    {
        $obj = new msgcount();
        $ret = $obj->testupdate('100011');
        return json_encode($ret);
        IcoTrace::TraceMsgToDb3('Demo.testupdate ==> update test');
    }

    public function getcurrentcount()
    {
        return msgcount::getcurrentcount('100011');
    }
}