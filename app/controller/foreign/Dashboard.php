<?php

namespace app\controller\foreign;

use app\BaseController;
use app\model\GameRecords;
use app\model\GameType;
use app\model\Odds;
use app\model\Table;
use app\model\UserModel;

/**
 * 对外统计控制器
 */
class Dashboard extends BaseController
{
    protected $model;
    protected $game_type;

    /**
     * 初始化方法
     */
    public function initialize()
    {
        $this->model = new GameRecords();
        parent::initialize();
    }

    /**
     * 对外统计列表接口
     * @return void
     */
    public function index()
    {
        // 获取游戏类型参数
        $game_type = $this->request->post('game_type/d', 0);
        if ($game_type <= 0) {
            show('game_type参数错误');
        }

        // 验证游戏类型是否存在
        $game_name = GameType::one([['id', '=', $game_type]]);
        if (empty($game_name)) {
            show('game_type游戏不存在');
        }

        // 获取游戏赔率配置
        $odds = Odds::get_page_list([['game_type_id', '=', $game_type]]);
        if (empty($odds)) {
            show('游戏赔率不存在');
        }

        // 设置当前游戏类型
        $this->game_type = $game_type;
        $table_id = $this->request->post('table_id/d', 0);

        // 构建查询条件
        $map = [];
        $map[] = ['close_status', '=', 1]; // 待开牌状态
        $map[] = ['game_type', '=', $game_type]; // 指定游戏类型
        if ($table_id > 0) {
            $map[] = ['table_id', '=', $table_id]; // 指定台座
        }

        // 第一步：查询下注的用户和台桌信息
        $user_table_list = $this->model
            ->hasWhere('profile', ['is_fictitious' => 0])
            ->where($map)
            ->whereTime('created_at', 'today')
            ->field('table_id,user_id')
            ->group('table_id,user_id')
            ->select()
            ->toArray();

        if (empty($user_table_list)) {
            return show([]);
        }

        // 构建统计查询字段
        $field = 'xue_number,pu_number,game_peilv_id,table_id,user_id,';
        $field .= 'sum(bet_amt) sum_bet_amt,sum(deposit_amt) sum_deposit_amt'; // 聚合字段

        // 分组和排序条件
        $group = 'table_id,game_peilv_id,pu_number,xue_number,user_id';
        $order = 'user_id asc,table_id asc';

        // 第二步：查询每个用户在各赔率下的下注金额统计
        $count_list = $this->model
            ->with('profile')
            ->where($map)
            ->field($field)
            ->group($group)
            ->order($order)
            ->select()
            ->toArray();

        // 处理游戏数据并返回结果
        $game_info_list = $this->game_type($game_type, $user_table_list, $count_list, $odds);
        return show($game_info_list);
    }

    /**
     * 根据游戏类型处理数据
     * @param int $game_type 游戏类型
     * @param array $user_table_list 用户台桌列表
     * @param array $count_list 统计数据列表
     * @param array $odds 赔率配置
     * @return array
     */
    protected function game_type($game_type, $user_table_list, $count_list, $odds)
    {
        return $this->game_pei_lv_bjl($user_table_list, $count_list, $odds);
    }

    /**
     * 处理百家乐游戏赔率数据
     * @param array $user_table_list 用户台桌列表
     * @param array $count_list 统计数据列表
     * @return array
     */
    protected function game_pei_lv_bjl($user_table_list, $count_list)
    {
        // 获取台桌名称列表
        $table_list = $this->table_name_list($this->game_type);
        $data = [];

        foreach ($user_table_list as $key => $value) {
            $data[$key]['count'] = 0;

            // 遍历统计数据，合并同一台桌同一用户在不同赔率下的投注
            foreach ($count_list as $k => $val) {
                // 匹配相同用户和台桌的记录
                if ($value['user_id'] == $val['user_id'] && $value['table_id'] == $val['table_id']) {
                    // 基础信息赋值
                    $data[$key]['table_id'] = $value['table_id'];
                    $data[$key]['table_name'] = $this->get_table_name($table_list, intval($value['table_id']));
                    $data[$key]['user_id'] = $value['user_id'];
                    $data[$key]['user_name'] = $val['profile']['user_name'];
                    $data[$key]['xue_number'] = $val['xue_number']; // 靴号（同一台桌唯一）
                    $data[$key]['pu_number'] = $val['pu_number']; // 铺号

                    // 赔率相关数据（动态字段，支持多个赔率）
                    $data[$key]['game_peilv_id_' . $val['game_peilv_id']] = $val['game_peilv_id'];
                    $data[$key]['sum_bet_amt_' . $val['game_peilv_id']] = $val['sum_bet_amt'];
                    $data[$key]['sum_deposit_amt_' . $val['game_peilv_id']] = $val['sum_deposit_amt'];

                    // 累计总下注金额
                    $data[$key]['count'] += $val['sum_bet_amt'];
                }
            }
        }

        return $data;
    }

    /**
     * 根据台桌ID获取台桌名称
     * @param array $table_list 台桌列表
     * @param int $table_id 台桌ID
     * @return string
     */
    protected function get_table_name($table_list, $table_id)
    {
        if (empty($table_list)) {
            return '';
        }

        foreach ($table_list as $key => $value) {
            if ($value['id'] == $table_id) {
                return $value['table_title'];
            }
        }

        return '';
    }

    /**
     * 获取指定游戏类型的台桌列表
     * @param int $game_type 游戏类型，默认为3
     * @return array
     */
    protected function table_name_list($game_type = 3)
    {
        return Table::where('game_type', $game_type)
            ->order('id asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取台桌列表接口
     * @return void
     */
    public function get_table_list()
    {
        $game_type = $this->request->param('game_type/d', 0);
        if ($game_type <= 0) {
            return show([], 0, 'game_type错误');
        }

        $table_list = $this->table_name_list($game_type);
        return show($table_list);
    }
}