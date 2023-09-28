<?php

namespace App\Models;

use DateTimeInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\PaymentVerify
 *
 * @method static Builder|PaymentVerify newModelQuery()
 * @method static Builder|PaymentVerify newQuery()
 * @method static Builder|PaymentVerify query()
 * @property int $id
 * @property string $sms_code
 * @property int $status 状态
 * @property string|null $note 备注
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PaymentVerify whereCreatedAt($value)
 * @method static Builder|PaymentVerify whereId($value)
 * @method static Builder|PaymentVerify whereNote($value)
 * @method static Builder|PaymentVerify whereSmsCode($value)
 * @method static Builder|PaymentVerify whereStatus($value)
 * @method static Builder|PaymentVerify whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PaymentVerify extends Model
{
    use HasFactory;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
