<?php


namespace app\index\model;


use think\Model;

class wxuser extends Model
{
    protected $table = 'wxuser';
    protected $pk = 'id';
    static protected $wxadminRecord;
    static protected $wxuserRecord;

    static public function querySomeTable($companyid)
    {
        $map = ['companyid'=>$companyid,'enable'=>1];
        self::$wxadminRecord = wxadmin::where($map)->findOrEmpty();
    }

    static public function getWxUserRecord()
    {
        return self::$wxuserRecord;
    }

    static public function getWxAdminRecord()
    {
        return self::$wxadminRecord;
    }

    static public function getRegStep($wxid)
    {
        $map2 = ['wxid'=>$wxid, 'active'=>1];
        $ret = wxuser::where($map2)->findOrEmpty();

        if($ret->isEmpty() )
        {
            return 0;
        }

        return $ret['step'];
    }
}