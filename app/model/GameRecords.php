<?php


namespace app\model;

use think\Model;

class GameRecords extends Model
{
    public $name='dianji_records';


    public function profile()
    {   //is_fictitious
        return $this->hasOne(UserModel::class,'id','user_id');
    }





    ############## 查询的是昨日 统计会员每日 输 赢 总赢 洗码，非洗码 洗码总赢 #############################################
    //查询昨日的   总下注  洗码量  总输赢赢
    public static function count_user_bet_win_shuffling($day = 'yesterday')
    {
        //bet_amt总下注 总shuffling洗码量  win总赢
        $res = self::alias('a')->field('
        sum(bet_amt) game_bet,
        sum(shuffling_num) game_shuffling_num,
         sum(win_amt) game_win_count,user_id
        ')
            ->join('common_user b','a.user_id=b.id','left')
            ->whereDay('created_at', $day)
            ->where('is_fictitious', 0)
            ->where('close_status', 2)
            ->group('user_id')
            ->select()
            ->toArray();
        return  self::win_merge($res);
    }

    //查询用户昨日的洗码和非洗码
    public static function count_user_shuffling_num($day = 'yesterday')
    {
        $ok_exempt=  self::alias('a')->field('sum(shuffling_num) game_is_ok_shuffling,user_id')
            ->where('is_exempt',1)
            ->whereDay('created_at', $day)
            ->join('common_user b','a.user_id=b.id','left')
            ->where('is_fictitious', 0)
            ->where('close_status', 2)
            ->group('user_id')
            ->select()
            ->toArray();
        $ok_exempt = self::win_merge($ok_exempt);
        $no_exempt=  self::alias('a')->field('sum(shuffling_num) game_is_no_shuffling,user_id')
            ->where('is_exempt',0)
            ->whereDay('created_at', $day)
            ->join('common_user b','a.user_id=b.id','left')
            ->where('is_fictitious', 0)
            ->where('close_status', 2)
            ->group('user_id')
            ->select()
            ->toArray();
        $no_exempt = self::win_merge($no_exempt);
        $array = self::win_merge_all($ok_exempt,'game_is_ok_shuffling');
        return self::win_merge_all($no_exempt,'game_is_no_shuffling',$array);

    }

    //查询用户的总输和总赢
    public static function count_user_bet_win($day = 'yesterday')
    {
        //会员总赢
        $win = self::alias('a')->field('sum(win_amt) game_win,user_id')
            ->where('win_amt', '>', 0)
            ->whereDay('created_at', $day)
            ->join('common_user b','a.user_id=b.id','left')
            ->where('is_fictitious', 0)
            ->where('close_status', 2)
            ->group('user_id')
            ->select()
            ->toArray();
        $win = self::win_merge($win);
        //会员总输
        $transport = self::alias('a')->field('sum(win_amt) game_transport,user_id')
            ->where('win_amt', '<', 0)
            ->whereDay('created_at', $day)
            ->join('common_user b','a.user_id=b.id','left')
            ->where('is_fictitious', 0)
            ->where('close_status', 2)
            ->group('user_id')
            ->select()
            ->toArray();

        $transport = self::win_merge($transport);
        $array = self::win_merge_all($win,'game_win');
        return self::win_merge_all($transport,'game_transport',$array);
    }

    private static function win_merge($array)
    {
        $array_merge = [];
        if (empty($array)) return [];
        foreach ($array as $key => $value) {
            $array_merge[$value['user_id']] = $value;
        }
        return $array_merge;
    }

    private static function win_merge_all($win,$name = '',$array =[])
    {
        if (empty($win))return $array;
        foreach ($win as $key=>$value){
            !isset($value[$name]) && $value[$name]=0;
            if (array_key_exists($value['user_id'], $array))
            {
                $array[$value['user_id']][$name] =$value[$name];
            }else{
                $array[$value['user_id']][$name] =$value[$name];
                $array[$value['user_id']]['user_id'] =$value['user_id'];
            }
        }
        return $array;
    }
    ############################################################
}