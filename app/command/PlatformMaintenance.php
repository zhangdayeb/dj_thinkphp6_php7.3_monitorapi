<?php


namespace app\command;


use app\model\SysConfig;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
/**
 * 平台维护
 * 平台维护命令 php /www/wwwroot/comapi/adminApi/gameCommandApi/think admin:PlatformMaintenance 1
 * 关闭平台维护命令 php /www/wwwroot/comapi/adminApi/gameCommandApi/think admin:PlatformMaintenance 0
 *CreateTime: 2021/04/01 14:01
 *UserName: fyclover
 **/
class PlatformMaintenance extends Command
{
    protected function configure()
    {
        $this->setName('PlatformMaintenance')
            ->addArgument('status', Argument::OPTIONAL, "your status")
            ->setDescription('Here is the platform_maintenance');
    }

    protected function execute(Input $input, Output $output)
    {
        $status = intval(trim($input->getArgument('status')));
        $service = new SysConfig();
        $service->where('name','web_maintain')->update(['value'=>$status]);
        $output->writeln('执行成功');
    }
}