<?php

namespace App\Imports;

use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;

class OrderImport implements ToModel, WithValidation, SkipsEmptyRows
{
    private int $rows = 0;
    private int $createUserId;

    public function __construct()
    {
        $this->createUserId = Auth::id();
    }

    public function model(array $row): ?Task
    {
        ++$this->rows;
        if ($this->rows == 1) {
            return null;
        }

        return new Task([
            'name' => $row[0],
            'shop_name' => $row[1],
            'shop_platform' => $row[2],
            'product_name' => $row[3],
            'product_link' => $row[4],
            'product_price' => $row[5],
            'share_code' => $row[6],
            'order_price' => $row[7],
            'wx_nickname' => $row[8],
            'introducer' => $row[9],
            'tb_nickname' => $row[10],
            'note' => $row[11],
            'status' => $row[12] == 1 ? TaskStatus::WAITING_TO_CLEARING->value : TaskStatus::WAITING_TO_PUBLISH->value,
            'create_user_id' => $this->createUserId,
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        if ($this->rows == 0) {
            return [];
        }
        return [
            '*.0' => 'required',
            '*.1' => 'required',
            '*.2' => function ($attr, $value, $onFailure) {
                if (!in_array($value, ShopPlatform::values())) {
                    $onFailure(__('validation.not_in', ['attribute' => __('admin.shop_platform')]));
                }
            },
            '*.3' => 'required',
            '*.4' => 'required',
        ];
    }

    /**
     * @return array
     */
    #[ArrayShape([
        '*.0' => "mixed",
        '*.1' => "mixed",
        '*.3' => "mixed",
        '*.4' => "mixed",
    ])] public function customValidationMessages(): array
    {
        return [
            '*.0' => __('validation.required', ['attribute' => __('admin.task_name')]),
            '*.1' => __('validation.required', ['attribute' => __('admin.shop_name')]),
            '*.3' => __('validation.required', ['attribute' => __('admin.product_name')]),
            '*.4' => __('validation.required', ['attribute' => __('admin.product_link')]),
        ];
    }
}
