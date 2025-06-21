<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class Preset extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'result_pai'=>'require|max:300',
        'result'=>'require|max:300',
        'sign' => 'require|max:32',
        'table_id'=>'require|number',
        'game_type'=>'require|number',
        'xue_number'=>'require|number',
        'pu_number'=>'require|number',
        'time'=>'require|number',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [

    ];

    /**
     * 验证场景
     * @var \string[][]
     */
    protected $scene  = [
        'preset'=>['result_pai','result','sign','table_id','game_type','xue_number','pu_number','time'],
        'count'=>['sign','table_id','game_type','xue_number','pu_number','time'],

    ];

}
