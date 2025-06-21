<?php

namespace app\controller\foreign;

use app\BaseController;
use think\facade\Db;
use think\facade\Cache;

/**
 * 骰宝监控控制器
 */
class Sicbo extends BaseController
{
    /**
     * 获取投注记录列表
     * @return void
     */
    public function records()
    {
        // 获取参数
        $page = $this->request->param('page/d', 1);
        $pageSize = $this->request->param('pageSize/d', 50);
        $search = $this->request->param('search/s', '');
        
        // 参数验证
        if ($page < 1) $page = 1;
        if ($pageSize < 1 || $pageSize > 200) $pageSize = 50;
        
        // 构建查询条件
        $where = [
            ['r.game_type', '=', 9],           // 骰宝游戏
            ['r.close_status', '=', 1],        // 当局数据
            ['u.is_fictitious', '=', 0]        // 排除虚拟用户
        ];
        
        // 搜索条件
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
                r.table_id, r.xue_number, r.pu_number,
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
            $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
            $item['detail'] = $item['detail'] ?: '';
            $item['result'] = $item['result'] ?: '';
        }
        
        $data = [
            'list' => $list,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $pageSize
        ];
        
        return show($data);
    }
    
    /**
     * 获取总览统计
     * @return void
     */
    public function overview()
    {
        // 缓存键
        $cacheKey = 'sicbo:overview:' . date('YmdHi');
        
        // 尝试从缓存获取
        $data = Cache::get($cacheKey);
        if ($data !== false) {
            return show($data);
        }
        
        // 查询统计数据
        $stats = Db::name('dianji_records')
            ->alias('r')
            ->leftJoin('common_user u', 'r.user_id = u.id')
            ->where([
                ['r.game_type', '=', 9],           // 骰宝游戏
                ['r.close_status', '=', 1],        // 当局数据
                ['u.is_fictitious', '=', 0]        // 排除虚拟用户
            ])
            ->field('
                SUM(r.bet_amt) as total_bet_amount,
                COUNT(DISTINCT r.user_id) as total_users,
                COUNT(*) as total_bets,
                MAX(r.bet_amt) as max_bet
            ')
            ->find();
        
        // 格式化数据
        $data = [
            'totalBetAmount' => sprintf('%.2f', $stats['total_bet_amount'] ?: 0),
            'totalUsers' => (int)($stats['total_users'] ?: 0),
            'totalBets' => (int)($stats['total_bets'] ?: 0),
            'maxBet' => sprintf('%.2f', $stats['max_bet'] ?: 0)
        ];
        
        // 缓存5秒
        Cache::set($cacheKey, $data, 5);
        
        return show($data);
    }
    
    /**
     * 获取52项投注统计
     * @return void
     */
    public function betStats()
    {
        // 缓存键
        $cacheKey = 'sicbo:bet_stats:' . date('YmdHi');
        
        // 尝试从缓存获取
        $data = Cache::get($cacheKey);
        if ($data !== false) {
            return show($data);
        }
        
        // 查询投注统计
        $stats = Db::name('dianji_records')
            ->alias('r')
            ->leftJoin('common_user u', 'r.user_id = u.id')
            ->where([
                ['r.game_type', '=', 9],           // 骰宝游戏
                ['r.close_status', '=', 1],        // 当局数据
                ['u.is_fictitious', '=', 0]        // 排除虚拟用户
            ])
            ->field('
                r.game_peilv_id as bet_id,
                COUNT(*) as bet_count,
                SUM(r.bet_amt) as total_amount,
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
        
        // 生成完整的52项数据 (304-355)
        $data = [];
        for ($betId = 304; $betId <= 355; $betId++) {
            if (isset($statsMap[$betId])) {
                $stat = $statsMap[$betId];
                $data[] = [
                    'betId' => (int)$betId,
                    'betCount' => (int)$stat['bet_count'],
                    'totalAmount' => sprintf('%.2f', $stat['total_amount']),
                    'userCount' => (int)$stat['user_count']
                ];
            } else {
                // 没有投注的项目补0
                $data[] = [
                    'betId' => (int)$betId,
                    'betCount' => 0,
                    'totalAmount' => '0.00',
                    'userCount' => 0
                ];
            }
        }
        
        // 缓存5秒
        Cache::set($cacheKey, $data, 5);
        
        return show($data);
    }
}