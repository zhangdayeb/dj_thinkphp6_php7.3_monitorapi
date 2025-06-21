<?php
use think\facade\Route;

// 现有路由
Route::rule('foreign/list$', '/foreign.Dashboard/index');
Route::rule('foreign/user/list$', '/foreign.Dashboard/order_user');
Route::rule('foreign/table/list$', '/foreign.Dashboard/get_table_list');

// 新增骰宝路由
Route::rule('foreign/sicbo/records$', '/foreign.Sicbo/records');
Route::rule('foreign/sicbo/overview$', '/foreign.Sicbo/overview');
Route::rule('foreign/sicbo/bet-stats$', '/foreign.Sicbo/betStats');
Route::rule('foreign/sicbo/tables$', '/foreign.Sicbo/getTables');    // 桌子列表

// 百家乐路由
Route::rule('foreign/baccarat/tables$', '/foreign.Bjl/getTables');
Route::rule('foreign/baccarat/records$', '/foreign.Bjl/records');  
Route::rule('foreign/baccarat/overview$', '/foreign.Bjl/overview');
Route::rule('foreign/baccarat/bet-stats$', '/foreign.Bjl/betStats');


// 龙虎路由
Route::rule('foreign/lh/tables$', '/foreign.Lh/getTables');
Route::rule('foreign/lh/records$', '/foreign.Lh/records');  
Route::rule('foreign/lh/overview$', '/foreign.Lh/overview');
Route::rule('foreign/lh/bet-stats$', '/foreign.Lh/betStats');

// 牛牛路由
Route::rule('foreign/nn/tables$', '/foreign.Nn/getTables');
Route::rule('foreign/nn/records$', '/foreign.Nn/records');  
Route::rule('foreign/nn/overview$', '/foreign.Nn/overview');
Route::rule('foreign/nn/bet-stats$', '/foreign.Nn/betStats');

// 三公路由
Route::rule('foreign/sg/tables$', '/foreign.Sg/getTables');
Route::rule('foreign/sg/records$', '/foreign.Sg/records');  
Route::rule('foreign/sg/overview$', '/foreign.Sg/overview');
Route::rule('foreign/sg/bet-stats$', '/foreign.Sg/betStats');

// 抢庄牛牛路由
Route::rule('foreign/qznn/tables$', '/foreign.Qznn/getTables');
Route::rule('foreign/qznn/records$', '/foreign.Qznn/records');  
Route::rule('foreign/qznn/overview$', '/foreign.Qznn/overview');
Route::rule('foreign/qznn/bet-stats$', '/foreign.Qznn/betStats');