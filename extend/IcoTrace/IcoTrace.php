<?php


namespace IcoTrace;

use think\Model;

class IcoTrace extends Model
{
    protected $table = 'tracetb';
    protected $pk = 'id';

    static public function TraceMsgToDb( $msg )
    {
        $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳
        $timestamp = floor($mtimestamp); // 时间戳
        $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒
        $datetime = date("Y-m-d H:i:s", $timestamp) . '.' . $milliseconds;

        $data = ['time' => $datetime, 'msg' => $msg];
        $ret = self::create($data);
        //dump($ret);
    }
}