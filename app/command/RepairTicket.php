<?php


namespace app\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

/**
 * 补票，没10分钟执行一次
 *CreateTime: 2021/04/01 14:01
 *UserName: fyclover
 **/
class RepairTicket extends Command
{
    protected function configure()
    {
        $this->setName(' RepairTicket')->setDescription('Here is the RepairTicket');
    }

    protected function execute(Input $input, Output $output)
    {
        $map = [];
        $map_user = [];
        $map_user['is_fictitious'] = 0;
        $map['user_id'] = Db::table('ntp_common_user')->where($map_user)->column('id');
        $info = Db::table('ntp_dianji_records')
            ->whereTime('created_at', '>=', date("Y-m-d H:i:s", time() - (60 * 2)))
            ->where($map)
            ->select()
            ->toArray();
        $map_log = [];
        $money_status = ''; // 初始化定义
        foreach ($info as $k => $v) {
            $map_log['uid'] = $v['user_id'];
            if ($v['delta_amt'] >= 0) {
                $money_status = "赢钱/ 返钱的";
                $map_log['source_id'] = $v['lu_zhu_id'];
            } else {
                $money_status = "输钱的";
                $map_log['source_id'] = $v['id'];
            }

            $logInfo = Db::table('ntp_common_pay_money_log')
                ->where('status', 'in', '502,503,504')
                ->where($map_log)
                ->find();
            if (!isset($logInfo['id'])) {
                $msg = "这个数据没有写入日志: " . $money_status . json_encode($v);
                Log::info($msg);
                Log::write($msg, 'info');
                // 目前无法写入日志
                $game_status = 503;
                if ($v['game_type'] == 2) {
                    $game_status = 502;
                }
                if ($v['game_type'] == 3) {
                    $game_status = 503;
                }
                if ($v['game_type'] == 6) {
                    $game_status = 504;
                }
                if ($money_status == '赢钱/ 返钱的') {
                    $data_win = [];
                    $data_win['create_time'] = date('Y-m-d H:i:s');
                    $data_win['type'] = 1;
                    $data_win['status'] = $game_status;
                    $data_win['money_before'] = 0;
                    $data_win['money_end'] = 0;
                    $data_win['money'] = $v['delta_amt'];
                    $data_win['uid'] = $v['user_id'];
                    $data_win['source_id'] = $v['lu_zhu_id'];
                    $data_win['mark'] = '下注开奖补充[重复]';
                    Db::table('ntp_common_pay_money_log')->insert($data_win);
                }
                if ($money_status == '输钱的') {
                    $data_loss = [];
                    $data_loss['money_before'] = 0;
                    $data_loss['money_end'] = 0;
                    $data_loss['uid'] = $v['user_id'];
                    $data_loss['type'] = 1;
                    $data_loss['status'] = $game_status;
                    $data_loss['source_id'] = $v['id'];
                    $data_loss['money'] = $v['bet_amt'] * -1;
                    $data_loss['create_time'] = date('Y-m-d H:i:s');
                    $data_loss['mark'] = '下注扣款补充[重复]';
                    Db::table('ntp_common_pay_money_log')->insert($data_loss);
                }
            }
        }
        $output->writeln('执行成功');
    }
}