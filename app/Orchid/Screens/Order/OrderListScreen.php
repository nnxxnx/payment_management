<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Order;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;
use Orchid\Screen\Action;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class OrderListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    #[ArrayShape(['orders' => "mixed"])] public function query(): iterable
    {
        return [
            'orders' => Order::filters()
                ->with(['shop', 'detail', 'orderProducts', 'orderProducts.product'])
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('admin.orders');
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return __('admin.order_description');
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.services.orders',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            OrderListLayout::class,

            Layout::modal('addOrderSyncJobModal',
                [
                    Layout::rows([
                        DateTimer::make('order_sync_job.orders_start_from')
                            ->title(__('admin.orders_start_from'))
                            ->required(),
                        DateTimer::make('order_sync_job.orders_end_to')
                            ->title(__('admin.orders_end_to'))
                            ->required(),
                    ]),
                ])
                ->title(__('admin.add_order_sync_job')),

            Layout::modal('orderGetDeliveryCodeModal',
                [
                    Layout::rows([

                    ]),
                ])
                ->title(__('admin.order_dispatch'))
                ->rawClick(true),

            Layout::modal('orderBatchGetDeliveryCodeModal',
                [
                    Layout::rows([
                        TextArea::make('order_ids')
                            ->rows(20)
                            ->required()
                            ->placeholder(__('admin.batch_get_delivery_code_details_notice'))
                            ->title(__('admin.order_id')),
                    ]),
                ])
                ->title(__('admin.order_batch_get_delivery_code')),


        ];
    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function remove(Request $request): void
    {
        Order::findOrFail($request->get('id'))->delete();

        Toast::info(__('admin.delete_success'));
    }

    /**
     * @throws Throwable
     */
    public function orderGetDeliveryCode(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $order = Order::findOrFail($request->get('id'));
        $orderDetail = OrderDetail::find($order->id);
        if (!$orderDetail) {
            $orderDetail = new OrderDetail();
        }
        $request->validate([
            'order_detail.delivery_company' => [
                'required',
                Rule::in(DeliveryCompany::values()),
            ],
        ]);

        $thirdPartyPlatformService = new ThirdPartyPlatformService();
        $orderDetail->fill($request->collect('order_detail')->toArray());
        $orderDetail->id = $order->id;

        if ($printTemplate = $request->input('print_template')) {
            $printTemplate = explode('||', $printTemplate);
            if (count($printTemplate) != 2) {
                Toast::error(__('admin.invalid_print_template'));
                return;
            }
            if ($printTemplate[0] !== $orderDetail->delivery_company) {
                Toast::error(__('admin.print_template_not_match_company'));
                return;
            }
            $printTemplate = $printTemplate[1];
        } else {
            $printTemplate = DeliveryCompany::from($orderDetail->delivery_company)->getTaobaoDefaultTemplate();
        }

        DB::beginTransaction();
        try {
            $result = $thirdPartyPlatformService->getTaobaoWaybill($order, $orderDetail->delivery_company,
                $printTemplate);
            if (!$result['success']) {
                Toast::error(__('admin.order_get_delivery_code_failed',
                    ['message' => $result['message']]));
                return;
            }
            $orderDetail->delivery_number = $result['data']['waybill_code'];
            $orderDetail->print_data = $result['data']['print_data'];
            $orderDetail->save();
            $order->status = OrderStatus::WAITING_TO_PRINT->value;
            $order->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Toast::error(__('admin.system_error') . $e->getMessage());
            return;
        }

        Toast::info(__('admin.save_success'));
    }

    /**
     * @throws Throwable
     */
    public function orderBatchGetDeliveryCode(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $orderDetails = str_replace("\r\n", "\n", $request->input('dispatch_details'));
        $orderDetails = array_unique(explode("\n", $orderDetails));
        $orderInfos = [];
        foreach ($orderDetails as $key => $orderDetail) {
            $orderDetail = explode(',', $orderDetail);
            if (count($orderDetail) != 2) {
                Toast::error(__('admin.batch_dispatch_format_error', ['lineNumber' => $key + 1]));
                return;
            }
            $order = Order::find($orderDetail[0]);
            if (!$order) {
                Toast::error(__('admin.batch_dispatch_invalid_order_id', ['lineNumber' => $key + 1]));
                return;
            }
            if (!in_array($orderDetail[1], DeliveryCompany::values())) {
                Toast::error(__('admin.batch_dispatch_invalid_delivery_company', ['lineNumber' => $key + 1]));
                return;
            }
            $orderInfos[] = [
                'order' => $order,
                'delivery_company' => $orderDetail[1],
                'delivery_template' => DeliveryCompany::from($orderDetail[1])->getTaobaoDefaultTemplate(),
            ];
        }

        if (!empty($orderInfos)) {
            $thirdPartyPlatformService = new ThirdPartyPlatformService();
            DB::beginTransaction();
            try {
                foreach ($orderInfos as $orderInfo) {
                    $order = $orderInfo['order'];
                    $orderDetail = OrderDetail::find($order->id);
                    if (!$orderDetail) {
                        $orderDetail = new OrderDetail();
                    }

                    $orderDetail->delivery_company = $orderInfo['delivery_company'];
                    $result = $thirdPartyPlatformService->getTaobaoWaybill($order, $orderDetail->delivery_company,
                        $orderInfo['delivery_template']);
                    if (!$result['success']) {
                        Toast::error(__('admin.order_get_delivery_code_failed',
                            ['message' => $result['message']]));
                        return;
                    }
                    $orderDetail->delivery_number = $result['data']['waybill_code'];
                    $orderDetail->print_data = $result['data']['print_data'];
                    $orderDetail->dispatch_user_id = Auth::user()->id;
                    $order->status = OrderStatus::WAITING_TO_PRINT;
                    $orderDetail->save();
                    $order->save();
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Toast::error(__('admin.system_error') . $e->getMessage());
                return;
            }
        }

        Toast::info(__('admin.save_success'));
    }

    public function banReceiver(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.manage')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $order = Order::findOrFail($request->get('id'));
        $banList = BanList::whereReceiverMobile($order->receiver_mobile)
            ->first();
        if (!$banList) {
            $banList = new BanList();
            $banList->receiver_mobile = $order->receiver_mobile;
            $banList->receiver_name = $order->receiver_name;
            $banList->receiver_account = $order->receiver_account;
            $banList->save();
        }

        Toast::info(__('admin.save_success'));
    }

    public function addOrderSyncJob(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $request->validate([
            'order_sync_job.orders_start_from' => [
                'required',
            ],
            'order_sync_job.orders_end_to' => [
                'required',
            ],
        ]);

        if (OrderSyncJob::whereNotIn('status',
            [
                OrderSyncJobStatus::FINISHED->value,
                OrderSyncJobStatus::FAILED->value
            ])
            ->where('create_user_id', $user->id)
            ->exists()
        ) {
            Toast::error(__('admin.order_sync_job_exists'));
            return;
        }

        $orderSyncJob = new OrderSyncJob();
        $orderSyncJob->fill($request->collect('order_sync_job')->toArray());
        $orderSyncJob->create_user_id = Auth::user()->id;
        $orderSyncJob->save();

        Toast::info(__('admin.save_success'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function postPrintResult(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $orderId = $request->input('id', 0);
        $printSuccess = $request->input('print_success', 1);
        $order = Order::findOrFail($orderId);
        $cacheKey = "ORDER_PRINT_RESULT_" . $orderId;
        Log::info("Post result: $cacheKey");
        if ($printSuccess) {
            if ($order->status == OrderStatus::WAITING_TO_PRINT->value) {
                $order->status = OrderStatus::WAITING_TO_DISPATCH->value;
                $order->save();
            }
            Cache::set($cacheKey, 1, 30);
        } else {
            Cache::set($cacheKey, -1, 30);
        }
    }

    public function getPrintResult(int $id)
    {
        $count = 1;
        $cacheKey = "ORDER_PRINT_RESULT_" . $id;
        Log::info("Get result: $cacheKey");
        Log::info(Cache::offsetExists($cacheKey));
        while (!Cache::offsetExists($cacheKey) && $count < 15) {
            sleep(1);
            $count++;
        }

        if (!Cache::offsetExists($cacheKey)) {
            Toast::error(__('admin.print_timeout'));
        } else {
            $result = Cache::get($cacheKey);
            if ($result) {
                Toast::info(__('admin.print_success'));
            } else {
                Toast::error(__('admin.print_timeout'));
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function orderDispatch(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $order = Order::findOrFail($request->get('id'));
        $order->load(['detail']);
        if (!$order->detail->delivery_company || !$order->detail->delivery_number) {
            Toast::error(__('admin.order_delivery_info_invalid'));
            return;
        }

        $thirdPartyPlatformService = new ThirdPartyPlatformService();
        DB::beginTransaction();
        try {
            $order->detail->dispatch_user_id = $user->id;
            $order->status = OrderStatus::DISPATCHED->value;
            $order->detail->save();
            $order->dispatch_time = date('Y-m-d H:i:s');
            $order->save();
            $result = $thirdPartyPlatformService->dispatchOrder($order);
            if (!$result['success']) {
                DB::rollBack();
                Toast::error(__('admin.order_dispatch_sync_failed',
                    ['message' => $result['message']]));
                return;
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Toast::error(__('admin.system_error') . $e->getMessage());
            return;
        }

        Toast::info(__('admin.save_success'));
    }

    /**
     * @throws Throwable
     */
    public function orderCancelLabel(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $order = Order::findOrFail($request->get('id'));
        $order->load(['detail']);
        if (!$order->detail->delivery_company || !$order->detail->delivery_number) {
            Toast::error(__('admin.order_delivery_info_invalid'));
            return;
        }

        $thirdPartyPlatformService = new ThirdPartyPlatformService();
        DB::beginTransaction();
        try {
            $order->status = OrderStatus::WAITING_TO_FETCH_WAYBILL->value;
            $result = $thirdPartyPlatformService->cancelTaobaoWaybill($order);
            if (!$result['success']) {
                DB::rollBack();
                Toast::error(__('admin.order_dispatch_sync_failed',
                    ['message' => $result['message']]));
                return;
            }
            $order->detail->delete();
            $order->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Toast::error(__('admin.system_error') . $e->getMessage());
            return;
        }

        Toast::info(__('admin.save_success'));
    }

    public function orderLock(int $id)
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.sync') &&
            !$user->hasAccess('platform.services.orders.manage')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $order = Order::findOrFail($id);
        $order->is_locked = 1;
        $order->save();
        Toast::info(__('admin.save_success'));
    }

    public function orderUnlock(int $id)
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.locked')) {
            Toast::error(__('admin.permission_denied'));
            return;
        }
        $order = Order::findOrFail($id);
        $order->is_locked = 0;
        $order->save();
        Toast::info(__('admin.save_success'));
    }
}
