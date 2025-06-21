<?php


namespace app\command;


use app\model\HomeTokenModel;
use app\model\GameRecords;
use app\model\UserModel;
use app\model\UserSet;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 删除 试用用户
 * Class DeleteOnTrialUser
 * @package app\command
 */

class DeleteOnTrialUser extends Command
{
    protected function configure()
    {
        $this->setName('delete_on_trial_user')->setDescription('Here is the delete_on_trial_user');
    }

    protected function execute(Input $input, Output $output)
    {
        //1 查询试用用户
        $userId = UserModel::where('is_fictitious',2)->column('id');
        if (empty($userId)) {
            $output->writeln('执行成功');
            die;
        }
        $userId = array_values($userId);


        //3 删除金额流动记录
//        MoneyLog::destroy(function($query)use($userId){
//            $query->where('uid','in',$userId);
//        });

        //4 删除下注记录
        GameRecords::destroy(function($query)use($userId){
            $query->where('user_id','in',$userId);
        });

        //5 删除洗码数据
        UserSet::destroy(function($query)use($userId){
            $query->where('u_id','in',$userId);
        });

        //2 删除虚拟用户
        UserModel::destroy($userId);

        //3 删除过去长期未登陆的 token
        $date = date("Y-m-d", strtotime("-2 day"));
        HomeTokenModel::whereTime('create_time','<', $date)->delete();

        //清空注册的验证码
        redis()->delete('register_captcha');
        $output->writeln('执行成功');
    }
}