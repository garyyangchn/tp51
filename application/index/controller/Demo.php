<?php


namespace app\index\controller;


use app\index\model\poinfo;
use app\index\model\wxadmin;

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
}