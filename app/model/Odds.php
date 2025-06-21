<?php


namespace app\model;


use think\Model;

class Odds extends Model
{

    public $name = 'dianji_game_peilv';


    public static function get_page_list($map)
    {
        return self::where($map)->select()->toArray();
    }
}