<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
//        'admin:BetMoneyLogInsert'=>\app\command\BetMoneyLogInsert::class,
        'admin:DeleteOnTrialUser'=>\app\command\DeleteOnTrialUser::class,
        'admin:PlatformMaintenance'=>\app\command\PlatformMaintenance::class,
        'admin:RepairTicket'=>\app\command\RepairTicket::class,
        'admin:TodayCount'=>\app\command\TodayCount::class,
        'admin:UserAgentMoneySettlement'=>\app\command\UserAgentMoneySettlement::class,
        'admin:YesterdayCount'=>\app\command\YesterdayCount::class,
        'admin:LuZhuBackups'=>\app\command\LuZhuBackups::class,
    ],
];
