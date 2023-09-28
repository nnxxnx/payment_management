<?php

namespace App\Enums;

enum TaskStatus: int
{
    case WAITING_TO_PUBLISH = 1;
    case WAITING_TO_CLAIM = 2;
    case CLAIMED = 3;
    case WAITING_TO_CLEARING = 4;
    case CLEARED = 5;

    public static function options(): array
    {
        return [
            self::WAITING_TO_PUBLISH->value => __('admin.status_' . self::WAITING_TO_PUBLISH->name),
            self::WAITING_TO_CLAIM->value => __('admin.status_' . self::WAITING_TO_CLAIM->name),
            self::CLAIMED->value => __('admin.status_' . self::CLAIMED->name),
            self::WAITING_TO_CLEARING->value => __('admin.status_' . self::WAITING_TO_CLEARING->name),
            self::CLEARED->value => __('admin.status_' . self::CLEARED->name),
        ];
    }

    public static function values(): array
    {
        return [
            self::WAITING_TO_PUBLISH->value,
            self::WAITING_TO_CLAIM->value,
            self::CLAIMED->value,
            self::WAITING_TO_CLEARING->value,
            self::CLEARED->value,
        ];
    }

    public function isDeletable(): bool
    {
        return in_array($this->value, [
                self::WAITING_TO_PUBLISH->value,
                self::WAITING_TO_CLAIM->value,
                self::CLAIMED->value,
            ]
        );
    }

    public function isEditable(): bool
    {
        return $this->value === self::WAITING_TO_PUBLISH->value;
    }
}
