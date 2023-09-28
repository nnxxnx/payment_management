<?php

namespace App\Models;

use DateTimeInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $payment_account_id 支付账号ID
 * @property string $serial_number 流水号
 * @property string $user_name 账户姓名
 * @property string $mobile 手机
 * @property string $id_number 身份证号
 * @property string $bank_account 银行卡号
 * @property string $amount 付款金额
 * @property string|null $note 备注
 * @property int $status 状态
 * @property string|null $pre_processed_at 就绪时间
 * @property string|null $pay_at 付款时间
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, OrderProcessLog> $orderProcessLogs
 * @property-read int|null $order_process_logs_count
 * @property-read PaymentAccount|null $paymentAccount
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereAmount($value)
 * @method static Builder|Order whereBankAccount($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereIdNumber($value)
 * @method static Builder|Order whereMobile($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order wherePayAt($value)
 * @method static Builder|Order wherePaymentAccountId($value)
 * @method static Builder|Order wherePreProcessedAt($value)
 * @method static Builder|Order whereSerialNumber($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserName($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use HasFactory;

    public function paymentAccount(): HasOne
    {
        return $this->hasOne(PaymentAccount::class);
    }

    public function orderProcessLogs(): HasMany
    {
        return $this->hasMany(OrderProcessLog::class);
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
