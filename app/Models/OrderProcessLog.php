<?php

namespace App\Models;

use DateTimeInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderProcessLog
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property string|null $note 备注
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|OrderProcessLog newModelQuery()
 * @method static Builder|OrderProcessLog newQuery()
 * @method static Builder|OrderProcessLog query()
 * @method static Builder|OrderProcessLog whereCreatedAt($value)
 * @method static Builder|OrderProcessLog whereId($value)
 * @method static Builder|OrderProcessLog whereNote($value)
 * @method static Builder|OrderProcessLog whereOrderId($value)
 * @method static Builder|OrderProcessLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderProcessLog extends Model
{
    use HasFactory;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
