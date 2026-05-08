<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\Results\DashboardStateResult;
use App\DataTransferObjects\Results\DashboardStatsResult;
use App\Http\Requests\Dashboard\StateRequest;
use App\Http\Resources\DashboardStateResource;
use App\Http\Resources\DashboardStatsResource;
use App\Repositories\CartQueryRepository;
use App\Repositories\EventStoreRepository;
use App\Repositories\OrderQueryRepository;
use App\Repositories\ProductRepository;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly CartQueryRepository $carts,
        private readonly OrderQueryRepository $orders,
        private readonly ProductRepository $products,
        private readonly EventStoreRepository $eventStore,
    ) {}

    public function index(): View
    {
        $stats = $this->buildStats();

        return view('dashboard', [
            'recentEvents' => $this->eventStore->getRecent(50),
            'products' => $this->products->getActive(),
            'orders' => $this->orders->getRecent(5),
            'stats' => [
                'totalEvents' => $stats->totalEvents,
                'activeCarts' => $stats->activeCarts,
                'ordersToday' => $stats->ordersToday,
                'revenueToday' => $stats->revenueTodayFormatted,
            ],
        ]);
    }

    public function stats(): DashboardStatsResource
    {
        return new DashboardStatsResource($this->buildStats());
    }

    public function state(StateRequest $request): DashboardStateResource
    {
        $dto = $request->toDto();

        $cart = $dto->cartUuid !== null
            ? $this->carts->findByUuid($dto->cartUuid)
            : $this->carts->findMostRecentActive();

        $order = null;
        if ($dto->orderUuid !== null) {
            $order = $this->orders->findByUuid($dto->orderUuid);
        } elseif ($cart !== null) {
            $order = $this->orders->findMostRecentForCart($cart->uuid);
        }

        return new DashboardStateResource(new DashboardStateResult(cart: $cart, order: $order));
    }

    private function buildStats(): DashboardStatsResult
    {
        $revenueInCents = $this->orders->revenueToday();

        return new DashboardStatsResult(
            totalEvents: $this->eventStore->countAll(),
            activeCarts: $this->carts->countActive(),
            ordersToday: $this->orders->countToday(),
            revenueTodayInCents: $revenueInCents,
            revenueTodayFormatted: '$'.number_format($revenueInCents / 100, 2),
        );
    }
}
