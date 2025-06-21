<?php


namespace app\model;


use think\Model;

class Table extends Model
{
    public $name = 'dianji_table';
    protected $autoWriteTimestamp = 'start_time';
    //台桌
    protected $status = [
        1=>'正常',
        2=>'暂停'
    ];

    protected $run_status = [
        0=>'暂停',
        1=>'投注',
        2=>'开牌',
        3=>'洗牌中'
    ];
    public function getStartTimeAttr($value)
    {
        if ($value == 0) return $value;
        $status = date('Y-m-d H:i:s',$value);
        return ['test'=>$status,'value'=>$value];
    }

    public static function page_one($map)
    {
        return  self::where($map)->find();
    }

    //荷官转换地址
    public function getHeGuanHeadImgAttr($value)
    {
        if (empty($value)) return '';
        if (is_array($value)) return '';
        $value = explode(',', $value);
        if (count($value) > 1) {
            foreach ($value as $key => $v) {
                $value[$key] = config('ToConfig.app_update.hg_image_url') . $v;
            }
            return $value;
        }
        return config('ToConfig.app_update.image_url') . $value[0];
    }
    /**
     * 台桌视频地址
     * @param $info /台桌信息
     * @return mixed
     */
    public static function table_open_video_url($info)
    {
        $info->video_near = $info->video_near . $info->id;
        $info->video_far = $info->video_far . $info->id;
        return $info;
    }
    //台桌开局倒计时 $info台桌信息
    public static function table_opening_count_down($info)
    {
        $end = time() - ($info->getData('start_time') + $info['countdown_time']);
        $info->end_time = 0;
        if ($end <= 0) {
            $info->end_time = abs($end);
        }
        return self::table_open_video_url($info);
    }
    //获取多条数据
    public static function page_repeat($where = [], $order = '')
    {
        $self = self::where($where);
        !empty($order) && $self->order($order);
        $sel = $self->select();
        return $sel;
    }
    //获取分页数据
    public static function page_list($map,$limit, $page)
    {
        return self::alias('a')
            ->where($map)
            ->join((new GameType())->name.' b', 'a.game_type = b.id', 'left')
            ->field('a.*,b.type_name')
            ->order('id desc')
            ->paginate(['list_rows' => $limit, 'page' => $page], false);
    }
}