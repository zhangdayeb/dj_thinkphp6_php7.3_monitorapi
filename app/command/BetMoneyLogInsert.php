<?php

namespace app\command;

use app\model\MoneyLog;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class BetMoneyLogInsert extends Command
{
    /**
     * 抛弃了，现在是 通过消息队列写入
     * 资金结算数据写入到 数据库
     */
    protected function configure()
    {
        $this->setName('BetMoneyLogInsert')
            ->setDescription('this is BetMoneyLogInsert');
    }

    protected function execute(Input $input, Output $output)
    {
        //获取资金记录redis
        $list = redis()->LRANGE('bet_settlement_money_log', 0, -1);
        if (empty($list)) return true;
        $valueData = array();
        //1  取出所有数据 并进行 json转数组
        foreach ($list as $item => $value) {
            $valueData[] = json_decode($value, true);
        }
        //2 数组倒序排序
        if (empty($valueData)) return true;
       $array =  array_reverse($valueData);

        //3 依次插入数据库
        foreach ($array as $item => $value) {
            $insert = MoneyLog::settlement_bet_money_log($value);
            if ($insert) {
               $res = redis()->LREM('bet_settlement_money_log', json_encode($value));//删除当前已经计算过的值
                if (!$res){
                    return false;
                }
            } else {
                return false;
            }
        }
        $output->writeln("ok!,this is run BetMoneyLogInsert");
    }
}