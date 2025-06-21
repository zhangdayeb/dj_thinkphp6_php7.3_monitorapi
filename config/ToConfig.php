<?php
//config('ToConfig.app_update.image_url')
return [
    'app_system' => [
        'app_system' => true,//false win系统 true linux
    ],
    'captcha'=>'aa123456',//万能验证码
    'http_code'=>[ //返回code
        'error'=>0,
        'success'=>200,
    ],
    'app_update' => [
        'image_url' => 'https://authapi.wan888.club/storage',//上传文件域名 图片视频等
        'app_qrcode' => 'https://authapi.wan888.club/',//二维码地址
        'hg_image_url' => 'https://resapi.wosb8.vip/',//荷官头像
    ],
];