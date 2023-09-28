<?php
/**
 * AdaPay 创建结算账户
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
    'channel'=> 'bank_account',
    'account_info'=> [
        'card_id' => '6228431629537491470',
        'card_name' => '夏汉华',
        'cert_id' => '422130196402245627',
        'cert_type' => '00',
        'tel_no' => '18888818881',
        'bank_acct_type' => 2,
    ]
);

# 创建结算账户
$account->create($account_params);

# 对创建结算账户结果进行处理
if ($account->isError()){
    //失败处理
    var_dump($account->result);
} else {
    //成功处理
    var_dump($account->result);
}
