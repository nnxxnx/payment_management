<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot(Dashboard $dashboard)
    {
        $permissions = ItemPermission::group(__('admin.orders_center'))
            ->addPermission('platform.services.orders.manage', __('admin.order_managements'));
        $dashboard->registerPermissions($permissions);
    }
}
