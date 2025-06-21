<?php


namespace app\service;

use app\model\AgentLavel;
use app\model\GameRecords;
use app\model\MoneyLog;
use app\model\UserModel;
use app\model\UserSet;
use think\facade\Db;


class TransferBetService
{

    /**
     * 定时任务 2
     * 半小时执行一次，避免数据太多
     * 计算用户洗码金额，免佣 : bool  //洗码费要注意 是否是退还的 输赢为0 的数据
     * @return bool
     */
    public function user_xima_money_settle(): bool
    {
        //查询数据库前一个小时产生的订单..条件.代理没结算，牌面 已结算 ，不是免佣 0 。。因为开了免佣的 没有代理结算没有洗码费
        //查询的都是 用户没开免佣的，并且代理都没结算。就可以计算 洗码费.洗码费大于 0 的。
        $records = GameRecords::alias('a')
            ->whereTime('a.created_at', '-1 hours')
            ->field('a.id,a.shuffling_amt,shuffling_num,a.user_id,b.id u_id,b.agent_id,lu_zhu_id,b.type')
            ->where(['a.agent_status' => 0, 'a.close_status' => 2, 'a.is_exempt' => 0, 'b.is_fictitious' => 0])
            ->where('a.shuffling_num', '>', 0)
            ->leftJoin('common_user b', 'b.id=a.user_id')
            ->select()
            ->toArray();
        
        if (empty($records)) return false;
       
        ##############计算洗码开始##############
        $user_data = [];//对用户数据过滤去重，并把洗码费加到一起。得到每个用户应该得到的洗码费
        $records_status = [];//保留订单里 需要修改的每条数据的 id，把 洗码状态改为 1 已结算
        foreach ($records as $key => $v) {
            $records_status[$key]['agent_status'] = 1;
            $records_status[$key]['id'] = $v['id'];
            $user_data[] = $v;
        }
        //删除多余的值
        unset($records);
        $save = false;

        Db::startTrans();
        try {
            foreach ($user_data as $key => $value) {
                $user = UserModel::where('id', $value['user_id'])->find();//查询用户信息
                //查询用户本身的洗码率
                $xima_lv = UserSet::where('u_id', $value['user_id'])->field('xima_lv')->find();//查询用户信息

                if (empty($user)) continue;
                //返用户本身的洗码费###############
                if (!empty($xima_lv) && $xima_lv->xima_lv > 0) {
                    UserModel::where('id', $value['user_id'])->inc('money_freeze', $value['shuffling_amt'])->update();
                    MoneyLog::insert([
                        'create_time' => date('y-m-d H:i:s'), 'type' => 1, 'status' => 602,
                        'money_before' => $user->money_freeze, 'money_end' => $user->money_freeze + $value['shuffling_amt'],
                        'money' => $value['shuffling_amt'], 'uid' => $value['user_id'],
                        'mark' => '结算洗码费订单批号:' . $value['id'] . ' 洗码率：' . $xima_lv->xima_lv . ' 洗码费：' . $value['shuffling_amt'], 'source_id' => $value['lu_zhu_id'],
                    ]);
                }
                ############################

                //该用户没有代理
                if ($user->agent_id <= 0) continue;
                ############################
                //有代理，查询该所有用户的代理信息
                $agent_lavel = AgentLavel::where('agent_id', $user->agent_id)
                    ->select()
                    ->toArray();

                //查询所有代理的洗码费
                $agent_column = array_column($agent_lavel, 'agent_pid');
                array_push($agent_column, $user->agent_id);//该用户本生的上一级
                $agent_column = array_unique($agent_column);//该用户所有上一级 去重

                //查询用户所有上一级的洗码率
                $agent_info = UserSet::where('u_id', 'in', $agent_column)
                    ->field('u_id,xima_lv')
                    ->order('u_id desc')
                    ->select()
                    ->toArray();
                
                //从最大的开始返洗码费。到谁哪里为负数就全部给谁，后面的就没有了
                //对用户所有上一级进行返利
                foreach ($agent_info as $k => $v) {
                    if ($v['xima_lv'] <= 0) continue;
                    //查询代理的信息
                    $agent_find = UserModel::where('id', $v['u_id'])->find();
                    UserModel::where('id', $v['u_id'])->inc('money_freeze', $value['shuffling_num'] * ($v['xima_lv'] / 100))->update();
                    MoneyLog::insert([
                        'create_time' => date('y-m-d H:i:s'), 'type' => 1, 'status' => 602,
                        'money_before' => $agent_find->money_freeze, 'money_end' => $agent_find->money_freeze + $value['shuffling_num'] * ($v['xima_lv'] / 100),
                        'money' => $value['shuffling_num'] * ($v['xima_lv'] / 100), 'uid' => $agent_find->id,
                        'mark' => '结算洗码费订单批号：' . $value['id'] . ' 洗码率：' . $v['xima_lv'] . ' 洗码费：' . $value['shuffling_num']* ($v['xima_lv'] / 100), 'source_id' => $value['lu_zhu_id'],
                    ]);
                }
            }

            (new GameRecords())->saveAll($records_status);
            $save = true;
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            Db::rollback();

        }

        if ($save) return true;
        return false;
    }
}
