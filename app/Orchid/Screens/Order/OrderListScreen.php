<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Order;

use App\Enums\OrderStatus;
use App\Imports\OrderImport;
use App\Imports\TaskImport;
use App\Models\Order;
use App\Orchid\Layouts\Order\OrderListLayout;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
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
            'platform.services.orders.manage',
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

            Link::make(__('admin.template_download'))
                ->icon('cloud-download')
                ->canSee(Auth::user()->hasAccess('platform.services.orders.manage'))
                ->href(url('templates/batchUploadTemplate.xls')),

            ModalToggle::make(__('admin.batch_upload'))
                ->icon('cloud-upload')
                ->canSee(Auth::user()->hasAccess('platform.services.orders.manage'))
                ->modal('batchUploadModal')
                ->method('batchUpload'),
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

            Layout::modal('batchUploadModal',
                [
                    Layout::rows([
                        Input::make('file')
                            ->required()
                            ->type('file'),
                    ]),
                ])
                ->title(__('admin.batch_upload')),
        ];
    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function remove(Request $request): void
    {
        $order = Order::findOrFail($request->get('id'));
        if (!OrderStatus::from($order->status)->isDeletable()) {
            Toast::error(__('admin.order_can_not_delete_in_current_status'));
        } else {
            $order->delete();
            Toast::info(__('admin.delete_success'));
        }
    }

    /**
     * @throws Throwable
     */
    public function batchUpload(Request $request): void
    {
        $user = Auth::user();
        if (!$user->hasAccess('platform.services.orders.manage')) {
            Toast::error(__('admin.permission_denied'));
        } else {
            $file = $request->file('file');
            DB::beginTransaction();
            try {
                Excel::import(new OrderImport(), $file->path(), null, \Maatwebsite\Excel\Excel::XLS);
                DB::commit();
                Toast::info(__('admin.save_success'));
            } catch (Throwable $e) {
                Toast::error($e->getMessage());
                DB::rollBack();
            }
        }
    }
}
