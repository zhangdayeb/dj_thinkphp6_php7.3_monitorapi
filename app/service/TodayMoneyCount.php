<?php

namespace app\service;


use app\model\GameRecords;
use app\model\MoneyLog;
use app\model\TodayCountModel;
use think\exception\ValidateException;

/**
 *   每日下注统计统计
 * Class TodayMoneyCount
 * @package app\common\service
 */
class TodayMoneyCount
{
    public $count = [];

    //下注数量
    public function game_records()
    {
        $model = new GameRecords();
        //今日订单总数。已经开牌的
        $this->count['today_order'] = $model->whereTime('created_at', 'today')->where('close_status', 2)->count();
        //今日订单金额。已经开牌的
        $this->count['today_money'] = $model->where('close_status', 2)->whereTime('created_at', 'today')->sum('bet_amt');
        //今日盈亏。已经开牌的
        $this->count['today_profit'] = MoneyLog::where('status','BETWEEN',[501,510])->whereTime('create_time', 'today')->sum('money');
            //$model->where('close_status', 2)->whereTime('created_at', 'today')->sum('delta_amt');
        return $this;
    }


    public function update()
    {
        $model = new TodayCountModel();
        //查询是否有今日的数据
        $find = $model->whereTime('date_time', 'today')->find();
        //存在时修改当前数据
        if ($find) {
            $find['today_order'] = $this->count['today_order'];
            $find['today_money'] = $this->count['today_money'];
            $find['today_profit'] = $this->count['today_profit'];
            try {
                $find->save();
            } catch (ValidateException $e) {
                return $e->getMessage();
            }
            return $this;
        }

        //不存在是插入数据
        try {
            $model->save([
                'today_order' => $this->count['today_order'],
                'today_money' => $this->count['today_money'],
                'today_profit' => $this->count['today_profit'],
                'date_time' => date('Y-m-d H:i:s'),
                'dates' => date('Y-m-d'),
            ]);
        } catch (ValidateException $e) {
            return $e->getMessage();
        }
        return $this;
    }
}