<?php
// 应用公共文件
function redis()
{
    return think\facade\Cache::store('redis');
}

function show($data = [], int $code = 1, string $message = 'ok！', int $httpStatus = 0)
{
    $result = [
        'code' => $code,
        'message' => lang($message),
        'data' => $data,
    ];
    header('Access-Control-Allow-Origin:*');
    if ($httpStatus != 0) {
        return json($result, $httpStatus);
    }
    echo json_encode($result);
    exit();
}

function use_mysql_query_sql($sql)
{
    $dbhost = env('database.hostname', '127.0.0.1');  // mysql服务器主机地址
    $dbuser = config('database.connections.mysql_user_auth_sql.username');            // mysql用户名
    $dbpass = config('database.connections.mysql_user_auth_sql.password');    // mysql用户名密码
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

    if (!$conn) {
        die('Could not connect: ' . mysqli_error());
    }
    echo '数据库连接成功！';
    mysqli_select_db($conn, env('database.database', ''));
    $retval = mysqli_query($conn, $sql);
    if (!$retval) {
        //echo "Error creating database: " . mysqli_error($conn);
    }
    mysqli_close($conn);
    return $retval;
}
