<?php


namespace app\model;


use think\Model;

class GameType extends Model
{
    public $name = 'dianji_game_type';

    public static function one($map)
    {
        $find = self::where($map)->find();
        if (empty($find)) return [];
        return $find->toArray();
    }
}