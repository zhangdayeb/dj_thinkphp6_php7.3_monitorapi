<?php


namespace app\model;


use think\Model;

class UserCountModel extends Model
{

    public $name = 'dianji_user_count';

    //每日用户的下注统计情况 插入数据库
    public static function add_count_insert()
    {
        //查询是否存在了当日的数据
        $model = new GameRecords();
        //总下注  洗码量  总输赢赢
        $day = date('Y-m-d',strtotime("-1 day"));
        $bet_win_shuffling = $model->count_user_bet_win_shuffling($day);//抛弃内部的 总输赢
        //查询用户的总输和总赢
        $win = $model->count_user_bet_win($day);
        //昨日的洗码和非洗码
        $shuffling = $model->count_user_shuffling_num($day);

        //组装数据
        $array = self::win_merge_one($win, $bet_win_shuffling);
        $array = self::win_merge_tow($shuffling, $array);


        //查询是否有今天的数据
        $find = self::where('dates', date("Y-m-d", strtotime("-1 day")))->select();

        //没就新增。
        if (empty($array)) {
            return true;
        }
        $count = [];
        foreach ($array as $key => &$value) {
            $count[$key] = $value;
            //查询用户的总输赢
            $count[$key]['game_win_count'] = MoneyLog::where('status','BETWEEN',[501,510])->whereDay('create_time', $day)->where('uid',$value['user_id'])->sum('money');
            $count[$key]['create_time'] = date("Y-m-d H:i:s", strtotime("-1 day"));
            $count[$key]['dates'] = date("Y-m-d", strtotime("-1 day"));
        }
        unset($array);
        //存在今日的数据 就只能一一条查询，确认没有重复的用户
        if ($find) {
            foreach ($count as $key => $value) {
                $find = self::where('user_id', $value['user_id'])->where('dates', date("Y-m-d", strtotime("-1 day")))->find();
                if (empty($find)) {
                    self::insert($value);
                } else {
                    self::where('user_id', $value['user_id'])->where('dates', date("Y-m-d", strtotime("-1 day")))->update($value);
                }
            }
            return true;
        }

        self::insertAll($count);
        return true;
    }

    private static function win_merge_one($win, $array = [])
    {
        if (empty($win)) return $array;
        foreach ($win as $key => $value) {
            !isset($value['game_transport']) && $value['game_transport'] = 0;
            !isset($value['game_win']) && $value['game_win'] = 0;
            if (array_key_exists($value['user_id'], $array)) {
                $array[$value['user_id']]['game_win'] = $value['game_win'];
                $array[$value['user_id']]['game_transport'] = $value['game_transport'];
            } else {
                $array[$value['user_id']]['game_win'] = $value['game_win'];
                $array[$value['user_id']]['game_transport'] = $value['game_transport'];
                $array[$value['user_id']]['user_id'] = $value['user_id'];
            }
        }
        return $array;
    }

    private static function win_merge_tow($win, $array = [])
    {
        if (empty($win)) return $array;
        foreach ($win as $key => $value) {
            !isset($value['game_is_no_shuffling']) && $value['game_is_no_shuffling'] = 0;
            !isset($value['game_is_ok_shuffling']) && $value['game_is_ok_shuffling'] = 0;
            if (array_key_exists($value['user_id'], $array)) {
                $array[$value['user_id']]['game_is_ok_shuffling'] = $value['game_is_ok_shuffling'];
                $array[$value['user_id']]['game_is_no_shuffling'] = $value['game_is_no_shuffling'];
            } else {
                $array[$value['user_id']]['game_is_ok_shuffling'] = $value['game_is_ok_shuffling'];
                $array[$value['user_id']]['game_is_no_shuffling'] = $value['game_is_no_shuffling'];
                $array[$value['user_id']]['user_id'] = $value['user_id'];
            }
        }
        return $array;
    }
}