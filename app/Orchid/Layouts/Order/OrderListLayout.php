<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\ShopPlatform;
use App\Enums\ShopStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Shop;
use App\Orchid\CustomComponents\Actions\PrintModalToggle;
use App\Orchid\CustomComponents\Actions\TableActions;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
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
            TD::make('platform_order_id', __('admin.platform_order_id'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->platform_order_id),

            TD::make('shop_id', __('admin.shop_name'))
                ->cantHide(true)
                ->render(fn(Order $order) => $order->shop->name),

            TD::make('shop_platform', __('admin.shop_platform'))
                ->cantHide(true)
                ->render(fn(Order $order) => ShopPlatform::options()[$order->shop->shop_platform]),

            TD::make('receiver_name', __('admin.order_receiver_name'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->receiver_name),

            TD::make('receiver_mobile', __('admin.order_receiver_mobile'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->receiver_mobile),

            TD::make('receiver_district', __('admin.order_receiver_district'))
                ->cantHide(true)
                ->render(fn(Order $order) => $order->receiver_district),

            TD::make('receiver_address', __('admin.order_receiver_address'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->receiver_address),

            TD::make('amount', __('admin.order_amount'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->amount),

            TD::make('products', __('admin.products'))
                ->cantHide(true)
                ->render(function (Order $order) {
                    $products = [];
                    foreach ($order->orderProducts as $orderProduct) {
                        /** @var OrderProduct $orderProduct */
                        $product = $orderProduct->sku->product;
                        $products[] = "<div class='d-block'>$product->name-¥$product->price*" .
                            "$orderProduct->quantity" . ($product->code ? "($product->code)" : '') . "</div>";
                    }

                    return implode('<legend></legend>', $products);
                }),

            TD::make('type', __('admin.order_type'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => OrderType::options()[$order->type]),

            TD::make('product_img', __('admin.product_pictures'))
                ->cantHide(true)
                ->render(function (Order $order) {
                    $imgList = '';
                    foreach ($order->orderProducts as $orderProduct) {
                        /** @var OrderProduct $orderProduct */
                        $productSku = $orderProduct->sku;
                        $imgList .= "<img src='$productSku->pic_url'
                              alt='{$productSku->product->name}'
                              class='d-inline-flex img-thumbnail rounded-1 w-50 h-50' style='max-width: 100px'>";
                    }

                    return $imgList;
                }),

            TD::make('status', __('admin.status'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => OrderStatus::options()[$order->status]),

            TD::make('created_at', __('admin.buy_time'))
                ->sort()
                ->cantHide(true)
                ->render(fn(Order $order) => $order->created_at->toDateTimeString()),

            TD::make(__('admin.actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Order $order) => TableActions::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('admin.view'))
                            ->route('platform.services.orders.edit', $order->id)
                            ->icon('magnifier'),

                        ModalToggle::make(__('admin.order_get_delivery_code'))
                            ->modal('orderGetDeliveryCodeModal')
                            ->canSee($order->status == OrderStatus::WAITING_TO_FETCH_WAYBILL->value &&
                                (!$order->detail || !$order->detail->delivery_number) &&
                                Auth::user()->hasAccess('platform.services.orders.sync'))
                            ->icon('paper-plane')
                            ->method('orderGetDeliveryCode')
                            ->asyncParameters(['id' => $order->id]),

                        Button::make(__('admin.ban_receiver'))
                            ->canSee(Auth::user()->hasAccess('platform.services.orders.manage'))
                            ->icon('user-unfollow')
                            ->confirm(__('admin.ban_receiver_confirm'))
                            ->method('banReceiver', [
                                'id' => $order->id,
                            ]),

                        PrintModalToggle::make(__('admin.order_print'))
                            ->modal('orderPrintModal')
                            ->canSee(in_array($order->status, [
                                    OrderStatus::WAITING_TO_PRINT->value,
                                    OrderStatus::WAITING_TO_DISPATCH->value,
                                    OrderStatus::DISPATCHED->value,
                                ]) && ($order->detail && $order->detail->delivery_number) &&
                                Auth::user()->hasAccess('platform.services.orders.sync'))
                            ->icon('printer')
                            ->method('getPrintResult')
                            ->asyncParameters(['id' => $order->id,])
                            ->setPrintData(
                                $order->id,
                                $order->detail ? $order->detail->delivery_number : '',
                                $order->detail ? $order->detail->print_data : '',
                            ),

                        Button::make(__('admin.order_cancel_label'))
                            ->canSee(in_array($order->status, [
                                    OrderStatus::WAITING_TO_PRINT->value,
                                    OrderStatus::WAITING_TO_DISPATCH->value,
                                ]) && ($order->detail && $order->detail->delivery_number) &&
                                Auth::user()->hasAccess('platform.services.orders.sync'))
                            ->icon('reload')
                            ->confirm(__('admin.order_cancel_label_confirm'))
                            ->method('orderCancelLabel', [
                                'id' => $order->id,
                            ]),

                        Button::make(__('admin.lock_order'))
                            ->canSee(!$order->is_locked && !in_array($order->status, [
                                    OrderStatus::DISPATCHED->value,
                                    OrderStatus::RECEIVED->value,
                                ]) && ((Auth::user()->hasAccess('platform.services.orders.manage') ||
                                    Auth::user()->hasAccess('platform.services.orders.sync'))))
                            ->icon('cross')
                            ->confirm(__('admin.lock_order_confirm'))
                            ->method('orderLock', [
                                'id' => $order->id,
                            ]),

                        Button::make(__('admin.order_dispatch'))
                            ->canSee($order->status == OrderStatus::WAITING_TO_DISPATCH->value &&
                                Auth::user()->hasAccess('platform.services.orders.sync'))
                            ->icon('paper-plane')
                            ->confirm(__('admin.order_dispatch_confirm'))
                            ->method('orderDispatch', [
                                'id' => $order->id,
                            ]),
                    ])),
        ];
    }
}
