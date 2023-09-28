<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PREPARING = 1;
    case PAYMENT_ACCOUNT_BOUND = 2;
    case PAYMENT_ACCOUNT_BIND_FAILED = -1;
    case PROCESSING = 3;
    case PAYMENT_PROCESSING = 4;
    case PAID_SUCCESS = 5;
    case PAYMENT_FAILED = -2;
    case REFUNDED = 6;

    public static function options(): array
    {
        return [
            self::PREPARING->value => __('admin.status_' . self::PREPARING->name),
            self::PAYMENT_ACCOUNT_BOUND->value => __('admin.status_' . self::PAYMENT_ACCOUNT_BOUND->name),
            self::PAYMENT_ACCOUNT_BIND_FAILED->value => __('admin.status_' . self::PAYMENT_ACCOUNT_BIND_FAILED->name),
            self::PROCESSING->value => __('admin.status_' . self::PROCESSING->name),
            self::PAYMENT_PROCESSING->value => __('admin.status_' . self::PAYMENT_PROCESSING->name),
            self::PAID_SUCCESS->value => __('admin.status_' . self::PAID_SUCCESS->name),
            self::PAYMENT_FAILED->value => __('admin.status_' . self::PAYMENT_FAILED->name),
            self::REFUNDED->value => __('admin.status_' . self::REFUNDED->name),
        ];
    }

    public static function values(): array
    {
        return [
            self::PREPARING->value,
            self::PAYMENT_ACCOUNT_BOUND->value,
            self::PAYMENT_ACCOUNT_BIND_FAILED->value,
            self::PROCESSING->value,
            self::PAYMENT_PROCESSING->value,
            self::PAID_SUCCESS->value,
            self::PAYMENT_FAILED->value,
            self::REFUNDED->value,
        ];
    }

    public static function checkStatusFlow(int $fromStatusValue, int $toStatusValue): bool
    {
        return false;
    }

    public function isEditable(): bool
    {
        return false;
    }

    public function isDeletable(): bool
    {
        return in_array($this->value, [
            self::PREPARING->value,
            self::PAYMENT_ACCOUNT_BIND_FAILED->value,
            self::PAYMENT_FAILED->value,
        ]);
    }
}
