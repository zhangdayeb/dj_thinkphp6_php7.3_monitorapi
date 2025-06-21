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
