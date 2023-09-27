<?php

namespace App\Orchid\Layouts\Order;

use App\Orchid\Filters\BuyTimeFilter;
use App\Orchid\Filters\OrderStatusFilter;
use App\Orchid\Filters\PlatformOrderIdFilter;
use App\Orchid\Filters\ReceiverMobileFilter;
use App\Orchid\Filters\ShopFilter;
use App\Orchid\Filters\OrderShopPlatform;
use Orchid\Screen\Layouts\Selection;

class OrderManageFiltersLayout extends Selection
{
    /**
     * @return array
     */
    public function filters(): array
    {
        return [
            PlatformOrderIdFilter::class,
            OrderShopPlatform::class,
            ShopFilter::class,
            OrderStatusFilter::class,
            ReceiverMobileFilter::class,
            BuyTimeFilter::class,
        ];
    }
}
