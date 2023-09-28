<?php
/**
 * AdaPay 创建普通用户
 * author: adapay.com https://docs.adapay.tech/api/04-trade.html
 * Date: 2019/09/17
 */

# 加载SDK需要的文件
include_once  dirname(__FILE__). "/../../AdapaySdk/init.php";
# 加载商户的配置文件
include_once  dirname(__FILE__). "/../config.php";


# 初始化用户对象类
$member = new \AdaPaySdk\Member();
$member_params = array(
    # app_id
    'app_id'=> 'app_69934cfe-7504-461d-a5d4-a33734d92555',
    # 用户id
    'member_id'=> 'TEST20230928AZS',
//    # 用户地址
//    'location'=> '上海市闵行区汇付',
//    # 用户邮箱
//    'email'=> '123123@126.com',
//    # 性别
//    'gender'=> 'MALE',
//    # 用户手机号
//    'tel_no'=> '18177722312',
//    # 用户昵称
//    'nickname'=> 'test',
);
# 创建
$member->create($member_params);

# 对创建用户对象结果进行处理
if ($member->isError()){
    //失败处理
    var_dump($member->result);
} else {
    //成功处理
    var_dump($member->result);
}
