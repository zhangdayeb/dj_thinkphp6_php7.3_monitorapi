<?php


namespace app\command;

use app\service\TransferBetService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
/**
 * 用户洗马金额结算 10分钟执行一次
 * 命令 php /www/wwwroot/yhvip666.net/common_dmin/think admin:user_agent_money_set
 *CreateTime: 2021/09/10 10:01
 *UserName: fyclover
 **/
class UserAgentMoneySettlement extends Command
{
    protected function configure()
    {
        $this->setName('user_agent_money_set')->setDescription('Here is the user_agent_money_set');
    }

    protected function execute(Input $input, Output $output)
    {
        //结算洗码费
        $service= new TransferBetService();
//        用户洗码费用 暂时 不返还
        $service->user_xima_money_settle();
        $output->writeln('执行成功');
    }



}