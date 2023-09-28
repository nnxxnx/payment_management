<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class OrderListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'orders';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('admin.id'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->id),

            TD::make('serial_number', __('admin.serial_number'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->serial_number),

            TD::make('user_name', __('admin.user_name'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->user_name),

            TD::make('id_number', __('admin.id_number'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->user_name),

            TD::make('mobile', __('admin.mobile'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->mobile),

            TD::make('amount', __('admin.amount'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->amount),

            TD::make('status', __('admin.status'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => OrderStatus::options()[$order->status]),

            TD::make('created_at', __('admin.buy_time'))
                ->sort()
                ->filter()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->created_at->toDateTimeString()),

            TD::make(__('admin.actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Order $order) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('admin.view'))
                            ->route('platform.services.orders.edit', $order->id)
                            ->icon('magnifier'),

                        Button::make(__('admin.delete'))
                            ->icon('trash')
                            ->confirm(__('admin.delete_confirm'))
                            ->method('remove', [
                                'id' => $order->id,
                            ]),
                    ])),
        ];
    }
}
