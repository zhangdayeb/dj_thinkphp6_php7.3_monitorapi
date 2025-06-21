<?php
use think\facade\Route;

//对外统计
Route::rule('foreign/list$', '/foreign.Dashboard/index');//对外的统计
Route::rule('foreign/user/list$', '/foreign.Dashboard/order_user');//对外统计的用户列表
Route::rule('foreign/table/list$', '/foreign.Dashboard/get_table_list');//游戏列表
