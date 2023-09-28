<?php

namespace App\Imports;

use App\Enums\OrderStatus;
use App\Models\Order;
use JetBrains\PhpStorm\ArrayShape;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OrderImport implements ToModel, WithValidation, SkipsEmptyRows, WithHeadingRow
{
    public function model(array $row): ?Order
    {
        return new Order([
            'serial_number' => $row[0],
            'user_name' => $row[1],
            'id_number' => $row[2],
            'mobile' => $row[3],
            'bank_number' => $row[4],
            'amount' => $row[5],
            'status' => OrderStatus::PREPARING->value,
        ]);
    }

    /**
     * @return array
     */
    #[ArrayShape([
        '*.0' => "string",
        '*.1' => "string",
        '*.2' => "string",
        '*.3' => "string",
        '*.4' => "string",
        '*.5' => "string"
    ])] public function rules(): array
    {
        return [
            '*.0' => 'required',
            '*.1' => 'required',
            '*.2' => 'required',
            '*.3' => 'required',
            '*.4' => 'required',
            '*.5' => 'required',
        ];
    }

    /**
     * @return array
     */
    #[ArrayShape([
        '*.0' => "mixed",
        '*.1' => "mixed",
        '*.2' => "mixed",
        '*.3' => "mixed",
        '*.4' => "mixed",
        '*.5' => "mixed",
    ])] public function customValidationMessages(): array
    {
        return [
            '*.0' => __('validation.required', ['attribute' => __('admin.serial_number')]),
            '*.1' => __('validation.required', ['attribute' => __('admin.user_name')]),
            '*.2' => __('validation.required', ['attribute' => __('admin.id_number')]),
            '*.3' => __('validation.required', ['attribute' => __('admin.mobile')]),
            '*.4' => __('validation.required', ['attribute' => __('admin.bank_number')]),
            '*.5' => __('validation.required', ['attribute' => __('admin.amount')]),
        ];
    }
}
