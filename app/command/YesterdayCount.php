<?php


namespace app\command;


use app\model\UserCountModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
/**
 * 每日晚上12过后执行，统计昨日 每个用户的消费情况
 *CreateTime: 2021/04/01 14:01
 *UserName: fyclover
 **/
class YesterdayCount extends Command
{
    protected function configure()
    {
        $this->setName(' yesterday_bet_count')->setDescription('Here is the yesterday_bet_count');
    }

    protected function execute(Input $input, Output $output)
    {
        //每日提现，充值，注册统计
        $service = new UserCountModel();
        $service->add_count_insert();
        $output->writeln('执行成功');
    }
}