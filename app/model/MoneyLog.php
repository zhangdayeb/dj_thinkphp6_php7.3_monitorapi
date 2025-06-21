<?php


namespace app\model;

use think\Model;

class MoneyLog extends Model
{
    public $name = 'common_pay_money_log';

    //下注插入资金记录
    public static function settlement_bet_money_log(array $info)
    {
        $save = self::insert($info);
        if ($save)  return true;
        return false;
    }
}