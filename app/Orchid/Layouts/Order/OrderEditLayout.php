<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Order;

use App\Enums\OrderStatus;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;
use Throwable;

class OrderEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     * @throws Throwable
     */
    public function fields(): array
    {
        return [
            Input::make('order.id')
                ->title(__('admin.order_id'))
                ->disabled(),

            Input::make('order.receiver_account')
                ->title(__('admin.order_receiver_account'))
                ->disabled(),

            Input::make('order.receiver_name')
                ->title(__('admin.order_receiver_name'))
                ->disabled(),

            Input::make('order.receiver_mobile')
                ->title(__('admin.order_receiver_mobile'))
                ->disabled(),

            Input::make('order.receiver_district')
                ->title(__('admin.order_receiver_district'))
                ->disabled(),

            Input::make('order.receiver_address')
                ->title(__('admin.order_receiver_address'))
                ->disabled(),

            Input::make('order.amount')
                ->title(__('admin.order_amount'))
                ->disabled(),

            Select::make('order.status')
                ->title(__('admin.status'))
                ->required()
                ->disabled()
                ->options(OrderStatus::options()),

            Input::make('order.dispatch_time')
                ->title(__('admin.order_dispatch_time'))
                ->disabled(),

            Input::make('order.created_at')
                ->title(__('admin.buy_time'))
                ->disabled(),
        ];
    }
}
