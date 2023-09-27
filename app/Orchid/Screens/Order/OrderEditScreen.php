<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Order;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class OrderEditScreen extends Screen
{
    /**
     * @var Order
     */
    public Order $order;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Order $order
     *
     * @return array
     */
    #[ArrayShape([
        'order' => "\App\Models\Order",
        'shops' => "mixed",
        'products' => "mixed",
    ])] public function query(Order $order): iterable
    {
        return [
            'order' => $order,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('admin.edit_order');
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
            'platform.services.orders.sync',
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
            Button::make(__('admin.save'))
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(OrderEditLayout::class)
                ->title(__('admin.order_information'))
                ->commands(
                    Button::make(__('admin.save'))
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->order->exists)
                        ->method('save')
                ),
        ];
    }

    /**
     * @param Order $order
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function save(Order $order, Request $request): RedirectResponse
    {
        $order
            ->fill($request->collect('order')->toArray())
            ->save();

        Toast::info(__('admin.save_success'));

        return redirect()->route('platform.services.orders');
    }

    /**
     * @param Order $order
     *
     * @return RedirectResponse
     * @throws Exception
     *
     */
    public function remove(Order $order): RedirectResponse
    {
        $order->delete();

        Toast::info(__('admin.delete_success'));

        return redirect()->route('platform.services.orders');
    }
}
