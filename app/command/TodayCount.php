<?php


namespace app\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use \app\service\TodayCount as TodayCountServer;
use app\service\TodayMoneyCount as TodayMoneyCountServer;
/**
 * 每日晚上靠近12点执行  每日提现，充值，注册统计
 * 命令 php /www/wwwroot/yhvip666.net/common_dmin/think admin:today_count
 *CreateTime: 2021/04/01 14:01
 *UserName: fyclover
 **/
class TodayCount extends Command
{
    protected function configure()
    {
        $this->setName('today_count')->setDescription('Here is the today_count');
    }

    protected function execute(Input $input, Output $output)
    {
        //每日提现，充值，注册统计
        $service = new TodayCountServer();
        $service->recharge()->register()->withdrawal()->update();
        //每日订单 下注统计
        $service = new TodayMoneyCountServer();
        $service->game_records()->update();
        $output->writeln('执行成功');
    }
}