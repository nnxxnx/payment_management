<?php
/**
 * AdaPay 删除结算账户
 * author: adapay.com https://docs.adapay.tech/api/04-trade.html
 * Date: 2019/09/17
 */

# 加载SDK需要的文件
include_once  dirname(__FILE__). "/../../AdapaySdk/init.php";
# 加载商户的配置文件
include_once  dirname(__FILE__). "/../config.php";



# 初始化结算账户对象类
$account = new \AdaPaySdk\SettleAccount();

$account_params = array(
    'app_id'=> 'app_69934cfe-7504-461d-a5d4-a33734d92555',
    'member_id'=> 'TEST20230928AZS',
    'settle_account_id'=> '0526260437424320'
);

# 查询结算账户
$account->delete($account_params);

# 对查询结算账户结果进行处理
if ($account->isError()){
    //失败处理
    var_dump($account->result);
} else {
    //成功处理
    var_dump($account->result);
}
