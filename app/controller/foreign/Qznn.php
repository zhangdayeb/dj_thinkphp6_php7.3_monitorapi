<?php

namespace app\controller\foreign;

use app\BaseController;
use think\facade\Db;

/**
 * 抢庄牛牛监控控制器
 */
class Qznn extends BaseController
{
    /**
     * 获取投注记录列表
     * @return void
     */
    public function records()
    {
        try {
            // 获取参数
            $page = $this->request->param('page/d', 1);
            $pageSize = $this->request->param('pageSize/d', 50);
            $search = $this->request->param('search/s', '');
            $tableId = $this->request->param('table_id/s', '');
            
            // 参数验证
            if ($page < 1) $page = 1;
            if ($pageSize < 1 || $pageSize > 200) $pageSize = 50;
            
            // 构建查询条件
            $where = [
                ['r.game_type', '=', 5],           // 抢庄牛牛游戏
                ['r.close_status', '=', 1],        // 当局数据
                ['u.is_fictitious', '=', 0]        // 排除虚拟用户
            ];
            
            // 如果指定了桌子ID，添加桌子条件
            if (!empty($tableId)) {
                $where[] = ['r.table_id', '=', $tableId];
            }
            
            // 构建查询
            $query = Db::name('dianji_records')
                ->alias('r')
                ->leftJoin('common_user u', 'r.user_id = u.id')
                ->where($where);
                
            if (!empty($search)) {
                $query->where(function($query) use ($search) {
                    $query->whereLike('u.user_name', '%' . $search . '%')
                          ->whereOr('r.table_id', 'like', '%' . $search . '%');
                });
            }
            
            // 查询总数
            $total = $query->count();
            
            // 分页查询
            $offset = ($page - 1) * $pageSize;
            $list = $query->field('
                    r.id, r.user_id, r.bet_amt, r.game_peilv_id, r.game_peilv,
                    r.close_status, r.created_at, r.detail, r.result, 
                    r.table_id, r.xue_number, r.pu_number, r.deposit_amt,
                    u.user_name
                ')
                ->order('r.created_at desc')
                ->limit($offset, $pageSize)
                ->select()
                ->toArray();
            
            // 格式化数据
            foreach ($list as &$item) {
                $item['id'] = (string)$item['id'];
                $item['bet_amt'] = sprintf('%.2f', $item['bet_amt']);
                $item['deposit_amt'] = sprintf('%.2f', $item['deposit_amt'] ?: 0);
                $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
                $item['detail'] = $item['detail'] ?: '';
                $item['result'] = $item['result'] ?: '';
            }
            
            $data = [
                'list' => $list,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $pageSize,
                'table_id' => $tableId
            ];
            
            return show($data);
            
        } catch (\Exception $e) {
            return show(['error' => $e->getMessage()], 0, '查询出错');
        }
    }
    
    /**
     * 获取总览统计
     * @return void
     */
    public function overview()
    {
        try {
            // 获取桌子参数
            $tableId = $this->request->param('table_id/s', '');
            
            // 构建查询条件
            $where = [
                ['r.game_type', '=', 5],           // 抢庄牛牛游戏
                ['r.close_status', '=', 1],        // 当局数据
                ['u.is_fictitious', '=', 0]        // 排除虚拟用户
            ];
            
            // 如果指定了桌子ID，添加桌子条件
            if (!empty($tableId)) {
                $where[] = ['r.table_id', '=', $tableId];
            }
            
            // 查询统计数据
            $stats = Db::name('dianji_records')
                ->alias('r')
                ->leftJoin('common_user u', 'r.user_id = u.id')
                ->where($where)
                ->field('
                    SUM(r.bet_amt) as total_bet_amount,
                    SUM(r.deposit_amt) as total_deposit_amount,
                    COUNT(DISTINCT r.user_id) as total_users,
                    COUNT(*) as total_bets,
                    MAX(r.bet_amt) as max_bet
                ')
                ->find();
            
            // 格式化数据
            $data = [
                'totalBetAmount' => sprintf('%.2f', $stats['total_bet_amount'] ?: 0),
                'totalDepositAmount' => sprintf('%.2f', $stats['total_deposit_amount'] ?: 0),
                'totalUsers' => (int)($stats['total_users'] ?: 0),
                'totalBets' => (int)($stats['total_bets'] ?: 0),
                'maxBet' => sprintf('%.2f', $stats['max_bet'] ?: 0),
                'table_id' => $tableId
            ];
            
            return show($data);
            
        } catch (\Exception $e) {
            return show(['error' => $e->getMessage()], 0, '查询出错');
        }
    }
    
    /**
     * 获取投注统计
     * @return void
     */
    public function betStats()
    {
        try {
            // 获取桌子参数
            $tableId = $this->request->param('table_id/s', '');
            
            // 构建查询条件
            $where = [
                ['r.game_type', '=', 5],           // 抢庄牛牛游戏
                ['r.close_status', '=', 1],        // 当局数据
                ['u.is_fictitious', '=', 0]        // 排除虚拟用户
            ];
            
            // 如果指定了桌子ID，添加桌子条件
            if (!empty($tableId)) {
                $where[] = ['r.table_id', '=', $tableId];
            }
            
            // 查询投注统计
            $stats = Db::name('dianji_records')
                ->alias('r')
                ->leftJoin('common_user u', 'r.user_id = u.id')
                ->where($where)
                ->field('
                    r.game_peilv_id as bet_id,
                    COUNT(*) as bet_count,
                    SUM(r.bet_amt) as total_amount,
                    SUM(r.deposit_amt) as total_deposit_amount,
                    COUNT(DISTINCT r.user_id) as user_count
                ')
                ->group('r.game_peilv_id')
                ->select()
                ->toArray();
            
            // 转换为关联数组，便于查找
            $statsMap = [];
            foreach ($stats as $stat) {
                $statsMap[$stat['bet_id']] = $stat;
            }
            
            // 抢庄牛牛需要查询实际存在的投注项，因为赔率表中没有game_type_id=7的数据
            // 如果没有预定义的投注项，使用动态查询
            $data = [];
            foreach ($statsMap as $betId => $stat) {
                $data[] = [
                    'betId' => (int)$betId,
                    'betCount' => (int)$stat['bet_count'],
                    'totalAmount' => sprintf('%.2f', $stat['total_amount']),
                    'totalDepositAmount' => sprintf('%.2f', $stat['total_deposit_amount'] ?: 0),
                    'userCount' => (int)$stat['user_count']
                ];
            }
            
            // 按betId排序
            usort($data, function($a, $b) {
                return $a['betId'] - $b['betId'];
            });
            
            return show([
                'data' => $data,
                'table_id' => $tableId,
                'total_bet_types' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return show(['error' => $e->getMessage()], 0, '查询出错');
        }
    }

    /**
     * 获取可用的桌子列表
     * @return void
     */
    public function getTables()
    {
        try {
            // 查询有抢庄牛牛投注记录的桌子
            $tables = Db::name('dianji_records')
                ->alias('r')
                ->leftJoin('common_user u', 'r.user_id = u.id')
                ->where([
                    ['r.game_type', '=', 7],           // 抢庄牛牛游戏
                    ['r.close_status', '=', 1],        // 当局数据
                    ['u.is_fictitious', '=', 0]        // 排除虚拟用户
                ])
                ->field('r.table_id, COUNT(*) as bet_count, SUM(r.bet_amt) as total_amount, SUM(r.deposit_amt) as total_deposit_amount')
                ->group('r.table_id')
                ->order('r.table_id asc')
                ->select()
                ->toArray();
            
            // 格式化数据
            foreach ($tables as &$table) {
                $table['bet_count'] = (int)$table['bet_count'];
                $table['total_amount'] = sprintf('%.2f', $table['total_amount']);
                $table['total_deposit_amount'] = sprintf('%.2f', $table['total_deposit_amount'] ?: 0);
            }
            
            return show($tables);
            
        } catch (\Exception $e) {
            return show(['error' => $e->getMessage()], 0, '查询出错');
        }
    }
}