<?php
/**
 * AdaPay 更新普通用户
 * author: adapay.com https://docs.adapay.tech/api/04-trade.html
 * Date: 2019/09/17
 */

# 加载SDK需要的文件
include_once  dirname(__FILE__). "/../../AdapaySdk/init.php";
# 加载商户的配置文件
include_once  dirname(__FILE__). "/../config.php";


# 初始化用户对象类
$member = new \AdaPaySdk\Member();

# 更新用户对象设置
$member_params = array(
    'app_id'=> 'app_69934cfe-7504-461d-a5d4-a33734d92555',
    'member_id'=> 'TEST20230928AZS',
    'disabled'=> 'N',
    'nickname'=> '测试',
    'user_name'=> '测试',
);
# 更新用户对象
$member->update($member_params);

# 对更新用户对象结果进行处理
if ($member->isError()){
    //失败处理
    var_dump($member->result);
} else {
    //成功处理
    var_dump($member->result);
}
