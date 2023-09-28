<?php

namespace App\Models;

use DateTimeInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\PaymentAccount
 *
 * @property int $id
 * @property string $user_name 账户姓名
 * @property string $mobile 手机
 * @property string $id_number 身份证号
 * @property string $bank_account 银行卡号
 * @property string $ada_pay_member_id AdaPay MemberId
 * @property string $ada_pay_account_id AdaPay AccountId
 * @property string|null $note 备注
 * @property int $status 状态
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PaymentAccount newModelQuery()
 * @method static Builder|PaymentAccount newQuery()
 * @method static Builder|PaymentAccount query()
 * @method static Builder|PaymentAccount whereAdaPayAccountId($value)
 * @method static Builder|PaymentAccount whereAdaPayMemberId($value)
 * @method static Builder|PaymentAccount whereBankAccount($value)
 * @method static Builder|PaymentAccount whereCreatedAt($value)
 * @method static Builder|PaymentAccount whereId($value)
 * @method static Builder|PaymentAccount whereIdNumber($value)
 * @method static Builder|PaymentAccount whereMobile($value)
 * @method static Builder|PaymentAccount whereNote($value)
 * @method static Builder|PaymentAccount whereStatus($value)
 * @method static Builder|PaymentAccount whereUpdatedAt($value)
 * @method static Builder|PaymentAccount whereUserName($value)
 * @mixin Eloquent
 */
class PaymentAccount extends Model
{
    use HasFactory;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
